<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Like;
use App\Models\Photo;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Storage;

class PhotoService
{
    private Photo $photo;

    private Like $like;

    public function __construct(Photo $photo, Like $like)
    {
        $this->photo = $photo;
        $this->like  = $like;
    }

    /**
     * DBから写真のパスを全て取得
     *
     * @param  string|null  $genre
     * @return LengthAwarePaginator<Photo>
     */
    public function getAllPhoto(?string $genre): LengthAwarePaginator
    {
        return $this->photo->getAllPhoto($genre);
    }

    /**
     * DBから写真のパスをランダムで全て取得
     *
     * @return Collection<int, Photo>
     */
    public function getAllPhotoRandomly(): Collection
    {
        return $this->photo->getAllPhotoRandomly();
    }

    /**
     * 写真情報をデータベースにアップロード
     *
     * @param  string  $fileName
     * @param  int  $genre
     * @return string
     */
    public function uploadPhotoDataToDB(string $fileName, int $genre): string
    {
        //保存するS3のファイルパスを取得
        /** @var string $filePath */
        $filePath = config("const.PHOTO.GENRE_FILE_URL.$genre");
        //DBに新規の写真レコード追加
        return $this->photo->createPhotoInfo($fileName, $filePath, $genre);
    }

    /**
     * 写真をS3バケットにアップロード
     *
     * @param  UploadedFile  $file
     * @param  string  $uniqueFileName
     * @param  int  $genre
     */
    public function uploadPhotoToS3(UploadedFile $file, string $uniqueFileName, int $genre): void
    {
        //保存するS3のファイルパスを取得
        /** @var string $filePath */
        $filePath = config("const.PHOTO.GENRE_FILE_URL.$genre");

        //S3にファイルを追加
        Storage::disk('s3')->putFileAs($filePath, $file, $uniqueFileName, 'public');
    }

    /**
     * S3から写真のデータを削除
     *
     * @param  string  $fileName
     * @param  int  $genre
     */
    public function deletePhotoFromS3(string $fileName, int $genre): void
    {
        //ジャンルからファイルパスを取得
        $filePath = config("const.PHOTO.GENRE_FILE_URL.$genre");
        //S3から写真データを削除
        Storage::disk('s3')->delete($filePath . '/' . $fileName);
    }

    /**
     * DBから写真レコードを削除
     *
     * @param  string  $id
     *
     * @throws Exception
     */
    public function deletePhotoFromDB(string $id): void
    {
        //DBから写真のレコードを削除
        $this->photo->deletePhotoInfo($id);
        $this->like->deleteByPhotoId($id);
    }

    /**
     * DB内に重複している写真があれば、DBとS3から削除
     *
     * @return Collection<int, Photo>
     *
     * @throws Exception
     */
    public function deleteMultiplePhotosIfDuplicate(): Collection
    {
        //重複しているファイル一覧を取得
        $duplicatePhotoList = $this->searchMultipleDuplicatePhotos();

        //それぞれのファイルをデータベース・S3から削除
        foreach ($duplicatePhotoList as $duplicateFile) {
            $id = explode('.', $duplicateFile->file_name)[0];
            $this->deletePhotoFromS3($duplicateFile->file_name, $duplicateFile->genre);
            $this->deletePhotoFromDB($id);
        }

        return $duplicatePhotoList;
    }

    /**
     * 写真一覧から重複している写真データを探索（複数写真が対象）
     *
     * @return Collection<int, Photo>
     *
     * @throws Exception
     */
    public function searchMultipleDuplicatePhotos(): Collection
    {
        //写真一覧レコードを取得
        $photoList = $this->photo->getAllPhotoOrderByCreatedAtDesc();

        $photoNameList = [];

        //写真レコードから必要なデータを配列として抽出
        /**
         * @var int $key
         * @var Photo $photo
         */
        foreach ($photoList as $key => $photo) {
            $fileName                   = explode('.', $photo->file_name)[1];
            $photoNameList[$fileName][] = [
                'index'      => $key,
                'id'         => $photo->id,
                'created_at' => is_null($photo->created_at)
                    ? Carbon::now()->timestamp
                    : $photo->created_at->timestamp,
            ];
        }

        //重複しているレコードを残し、重複がないレコードはコレクションから削除
        foreach ($photoNameList as $photoInfoArray) {
            if (count($photoInfoArray) === 1) {
                unset($photoList[$photoInfoArray[0]['index']]);
            } else {
                $createdAtArr = array_column($photoInfoArray, 'created_at');
                //Unixタイムスタンプを基に写真配列を降順に並び替える
                array_multisort(
                    $createdAtArr,
                    SORT_DESC,
                    $photoInfoArray
                );
                //作成日が最新のものはDBとS3に残すので配列から削除する
                $deletePhotoInfo = array_values($photoInfoArray)[0];
                unset($photoList[$deletePhotoInfo['index']]);
            }
        }

        //コレクションが空の場合は、エラーを投げる
        if ($photoList->isEmpty()) {
            throw new Exception('There is no duplicate file in the database.');
        }

        return $photoList->values();
    }

    /**
     * 重複した写真をDBとS3から削除する
     *
     * @param  string  $fileName
     * @return array
     *
     * @throws Exception
     */
    public function deletePhotoIfDuplicate(string $fileName): array
    {
        //入力されたファイル名と一致する重複レコードを取得
        $duplicatePhotoFiles = $this->searchDuplicatePhoto($this->photo->getAllPhotoOrderByCreatedAtDesc(), $fileName);
        $fileName            = null;

        //重複レコードをDBとS3から削除
        foreach ($duplicatePhotoFiles as $duplicateFile) {
            $id = explode('.', $duplicateFile->file_name)[0];
            $this->deletePhotoFromS3($duplicateFile->file_name, $duplicateFile->genre);
            $this->deletePhotoFromDB($id);
            $fileName = $duplicateFile->file_name;
        }

        return ['deleteFile' => $fileName, 'count' => $duplicatePhotoFiles->count()];
    }

    /**
     * 重複する写真を検索（１つの写真が対象）
     *
     * @param  Collection<int, Photo>  $fileList
     * @param  string  $fileName
     * @return Collection<int, Photo>
     *
     * @throws Exception
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
            throw new Exception('There is no duplicate file in the database.');
        }

        //一番直近でアップロードされた写真は削除対象にしない
        $indexFileList = $fileList->values();
        unset($indexFileList[0]);

        return $indexFileList;
    }
}
