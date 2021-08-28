<?php

namespace App\Http\Services;

use App\Exceptions\Model\ModelUpdateFailedException;
use App\Exceptions\S3\S3MoveFailedException;
use App\Http\Models\Photo;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Storage;
use Symfony\Component\Console\Helper\ProgressBar;


class PhotoService
{

    private $photo;

    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    /**
     * DBから写真のパスを全て取得
     * @param int|null $genre
     * @return LengthAwarePaginator
     */
    public function getAllPhoto(?int $genre): LengthAwarePaginator
    {
        return $this->photo->getAllPhoto($genre);
    }

    /**
     * DBから写真のパスをランダムで全て取得
     * @return Collection
     */
    public function getAllPhotoRandomly(): Collection
    {
        return $this->photo->getAllPhotoRandomly();
    }

    /**
     * 写真をS3バケットにアップロード
     * @param UploadedFile $file
     * @param string $fileName
     * @param int $genre
     * @return string
     */
    public function uploadPhotoToS3(UploadedFile $file, string $fileName, int $genre): string
    {
        //保存するS3のファイルパスを取得
        $filePath = config("const.PHOTO.GENRE_FILE_URL.$genre");
        //DBに新規の写真レコード追加
        $uniqueFileName = $this->photo->createPhotoInfo($fileName, $filePath, $genre);
        //S3にファイルを追加
        Storage::disk('s3')->putFileAs($filePath, $file, $uniqueFileName, 'public');

        return $uniqueFileName;
    }

    /**
     * S3から写真のデータを削除
     * @param string $fileName
     * @param int $genre
     */
    public function deletePhotoFromS3(string $fileName, int $genre): void
    {
        //ジャンルからファイルパスを取得
        $filePath = config("const.PHOTO.GENRE_FILE_URL.$genre");
        //DBから写真のレコードを削除
        $this->photo->deletePhotoInfo($fileName);
        //S3から写真データを削除
        Storage::disk('s3')->delete($filePath . '/' . $fileName);
    }

    /**
     * DB内に重複している写真があれば、DBとS3から削除
     * @return Collection
     */
    public function deleteMultiplePhotosIfDuplicate(): Collection
    {
        //重複しているファイル一覧を取得
        $duplicatePhotoList = $this->searchMultipleDuplicatePhotos();

        //それぞれのファイルをデータベース・S3から削除
        foreach ($duplicatePhotoList as $duplicateFile) {
            $this->deletePhotoFromS3($duplicateFile->file_name, $duplicateFile->genre);
            $this->photo->deletePhotoInfo($duplicateFile->file_name);
        }

        return $duplicatePhotoList;
    }

    /**
     * 写真一覧から重複している写真データを探索（複数写真が対象）
     * @return Collection
     */
    public function searchMultipleDuplicatePhotos(): Collection
    {
        //写真一覧レコードを取得
        $photoList = $this->photo->getAllPhotos();

        $photoNameList = [];

        //写真レコードから必要なデータを配列として抽出
        foreach ($photoList as $key => $photoInfo) {
            $fileName = explode('.', $photoInfo->file_name)[1];
            $photoNameList[$fileName][] = [
                'index' => $key,
                'id' => $photoInfo->id,
                'created_at' => $photoInfo->created_at
            ];
        }

        //重複しているレコードを残し、重複がないレコードはコレクションから削除
        foreach ($photoNameList as $fileName => $photoInfoArray) {
            if (count($photoInfoArray) === 1) {
                unset($photoList[$photoInfoArray[0]['index']]);
            } else {
                array_multisort(array_map("strtotime", array_column($photoInfoArray, "created_at")),
                    SORT_DESC, $photoInfoArray);
                $deletePhotoInfo = array_values($photoInfoArray)[0];
                unset($photoList[$deletePhotoInfo['index']]);
            }
        }

        //コレクションが空の場合は、エラーを投げる
        if ($photoList->isEmpty()) {
            throw new \Error('There is no duplicate file in the database.');
        }

        return $photoList->values();
    }

    /**
     * 重複した写真をDBとS3から削除する
     *
     * @param string $fileName
     * @return array
     */
    public function deletePhotoIfDuplicate(string $fileName): array
    {
        //入力されたファイル名と一致する重複レコードを取得
        $duplicatePhotoFiles = $this->searchDuplicatePhoto($this->photo->getAllPhotos(), $fileName);
        $fileName = null;

        //重複レコードをDBとS3から削除
        foreach ($duplicatePhotoFiles as $duplicateFile) {
            $this->deletePhotoFromS3($duplicateFile->file_name, $duplicateFile->genre);
            $this->photo->deletePhotoInfo($duplicateFile->file_name);
            $fileName = $duplicateFile->file_name;
        }

        return ['deleteFile' => $fileName, 'count' => $duplicatePhotoFiles->count()];
    }

    /**
     * 重複する写真を検索（１つの写真が対象）
     *
     * @param Collection $fileList
     * @param string $fileName
     * @return Collection
     */
    public function searchDuplicatePhoto(Collection $fileList, string $fileName): Collection
    {
        //取得した写真レコードから、入力されたファイル名と一致するレコードのみ残す
        foreach ($fileList as $index => $fileInfo) {
            $searchFileName = $fileInfo->id . '.' . $fileName;
            if ($searchFileName !== $fileInfo->file_name) {
                unset($fileList[$index]);
                $fileList->values();
            }
        }

        //検索の結果レコードが見つからない
        //またはレコードの検索結果１件のみの場合は重複なしでエラーを投げる。
        if ($fileList->isEmpty() || $fileList->count() === 1) {
            throw new \Error('There is no duplicate file in the database.');
        }

        //一番直近でアップロードされた写真は削除対象にしない
        $indexFileList = $fileList->values();
        unset($indexFileList[0]);

        return $indexFileList;
    }

    /**
     * @throws FileNotFoundException
     * @throws S3MoveFailedException
     * @throws ModelUpdateFailedException
     */
    public function includeUuidInRecord(ProgressBar $progressBar)
    {
        //写真情報を全件取得
        $photoList = $this->photo::all();

        //プログレスバーに値をセットし、スタート
        $progressBar->setMaxSteps($photoList->count());
        $progressBar->start();

        //一件ずつ写真情報を取り出す。
        foreach ($photoList as $photo) {
            $id = $photo->id;
            //IDがUUIDでない場合
            if (!Str::isUuid($id)) {
                //古いS3パスを取得
                $oldPath = $photo->file_path;
                //UUIDを含む写真名とS3パスを新たに生成
                $newInfo = $this->createLatestPhotoInfoIncludingUuid($oldPath);
                //S3の写真を移動させる
                $moveResult = $this->movePhotoToNewFolder($oldPath, $newInfo['path'], $photo->genre);
                if (!$moveResult) {
                    throw new S3MoveFailedException($oldPath, $newInfo['path'], 'A file move failed for some reason.');
                }
                //DB内の写真情報をUUIDを含むものに更新
                $updateResult = $this->updatePhotoInfoToIncludeUuid($photo, $newInfo);
                if (!$updateResult) {
                    throw new ModelUpdateFailedException($photo, 'Model update Failed for some reason.');
                }
            }
            //一件処理が終わるごとに、バーを１つ進める
            $progressBar->advance();
        }
    }

    /**
     * @param string $oldS3Path
     * @return array
     * @throws FileNotFoundException
     */
    public function createLatestPhotoInfoIncludingUuid(string $oldS3Path): array
    {
        $disk = Storage::disk('s3');
        $uuid = Str::uuid()->toString();

        //S3に写真が存在しない場合
        if (!$disk->exists($oldS3Path)) {
            throw new FileNotFoundException("Photo file not found. Path: $oldS3Path");
        }

        //変数に渡されたS3パスの中から、写真名を検索
        $pathInfo = explode('/', $oldS3Path);
        $oldNameArr = explode('.', $pathInfo[array_key_last($pathInfo)]);

        //写真名先頭の文字列をUUIDに変更
        $oldNameArr[0] = $uuid;
        //新しい写真名を生成
        $newPhotoName = implode('.', $oldNameArr);

        //S3パス内の古い写真名を新しい写真名で上書き
        $pathInfo[array_key_last($pathInfo)] = $newPhotoName;
        //新しい写真名を含むS3パスを生成
        $newS3Path = implode('/', $pathInfo);

        return ['id' => $uuid, 'name' => $newPhotoName, 'path' => $newS3Path];
    }

    /**
     * @param Photo $photo
     * @param array $newInfo
     * @return bool
     */
    public function updatePhotoInfoToIncludeUuid(Photo $photo, array $newInfo): bool
    {
        return $photo->update([
            'id' => $newInfo['id'],
            'file_name' => $newInfo['name'],
            'file_path' => $newInfo['path'],
        ]);
    }

    /**
     * @param string $oldS3Path
     * @param string $newS3Path
     * @param int $genre
     * @return bool
     * @throws FileNotFoundException
     */
    public function movePhotoToNewFolder(string $oldS3Path, string $newS3Path, int $genre): bool
    {
        //S3のストレージ
        $disk = Storage::disk('s3');
        //新しい写真名を新しいS3パスから取得
        $fileName = basename($newS3Path);

        //ジャンルからS3ファイルパスを取得
        $filePath = config("const.PHOTO.GENRE_FILE_URL.$genre");

        //古い写真をS3からローカルにダウンロード
        $file = $this->downloadS3PhotoToPublicDir($fileName, $disk->get($oldS3Path));

        //S3に写真をアップロード（新しいS3パス）
        $disk->putFileAs($filePath, $file, $fileName, 'public');

        //古いパスの写真をS3から削除
        return $disk->delete($oldS3Path);
    }

    /**
     * @throws FileNotFoundException
     */
    public function downloadS3PhotoToPublicDir(string $fileName, string $content): UploadedFile
    {
        //ローカルストレージ
        $disk = Storage::disk('public');

        //ローカルのファイルパスを取得
        $path = '/local/' . $fileName;
        $localFullPath = storage_path('app/public') . $path;

        //S3の写真をローカルにダウンロード
        $disk->put($path, $content);

        //ローカルの写真をUploadedFileオブジェクトに変換し、返却
        return new UploadedFile($localFullPath, $fileName);
    }

    /**
     * ローカルのディレクトリを削除する。
     * @param string $dir ディレクトリ名（例：/local/)
     * @return bool
     */
    public function deleteAllLocalPhoto(string $dir): bool
    {
        return Storage::disk('public')->deleteDirectory($dir);
    }

}
