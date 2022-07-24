<?php

declare(strict_types=1);

namespace App\Services\ReplaceUuid;

use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\Model\ModelUpdateFailedException;
use App\Exceptions\S3\S3MoveFailedException;
use App\Models\Photo;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Storage;

class BaseService
{
    private Photo $photo;

    private string $publicDir;

    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
        $this->publicDir = 'local/';
    }

    /**
     * 写真情報を１つずつ確認し、IdがUuidでない場合は
     * [id, file_name, file_path]をUuidを含むものに変更する。
     *
     * @throws FileNotFoundException
     * @throws S3MoveFailedException
     * @throws ModelUpdateFailedException
     */
    public function includeUuidInRecord(): void
    {
        //写真情報を全件取得
        /** @var Collection<Photo> $photoList */
        $photoList = $this->photo::all();

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
                $moveResult = $this->movePhotoToNewFolder($oldPath, $newInfo['file_name'], $photo->genre);
                if (!$moveResult) {
                    throw new S3MoveFailedException(
                        $oldPath,
                        $newInfo['file_path'],
                        'A file move failed for some reason.'
                    );
                }
                //DB内の写真情報をUUIDを含むものに更新
                $updateResult = $photo->update($newInfo);
                if (!$updateResult) {
                    throw new ModelUpdateFailedException($photo, 'Model update Failed for some reason.');
                }
            }
        }
    }

    /**
     * Uuidを含むレコードを作成する
     *
     * @param string $oldS3Path S3パス（uuidなし）
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

        return ['id' => $uuid, 'file_name' => $newPhotoName, 'file_path' => $newS3Path];
    }

    /**
     * S3上の写真を新しいフォルダーに移動する
     *
     * @param string $oldS3Path S3パス（uuidなし）
     * @param string $fileName 新しいファイル名
     * @param int $genre 写真のジャンル
     * @return bool
     * @throws FileNotFoundException
     */
    public function movePhotoToNewFolder(string $oldS3Path, string $fileName, int $genre): bool
    {
        //S3のストレージ
        $disk = Storage::disk('s3');

        //ジャンルからS3ファイルパスを取得
        /** @var string $filePath */
        $filePath = config("const.PHOTO.GENRE_FILE_URL.$genre");

        //古い写真をS3からローカルにダウンロード
        $file = $this->downloadS3PhotoToLocal($fileName, $disk->get($oldS3Path));

        //S3に写真をアップロード（新しいS3パス）
        $disk->putFileAs($filePath, $file, $fileName, 'public');

        //古いパスの写真をS3から削除
        return $disk->delete($oldS3Path);
    }

    /**
     * s3にある写真をローカルディレクトリにダウンロードする
     * その写真をUploadedFileオブジェクトにして返却
     *
     * @param string $fileName ファイル名
     * @param string $content 画像の中身
     * @return UploadedFile
     */
    public function downloadS3PhotoToLocal(string $fileName, string $content): UploadedFile
    {
        //ローカルストレージ
        $disk = Storage::disk('public');

        //ローカルの保存先パスを生成
        $path = $this->publicDir . $fileName;
        $localFullPath = $disk->path($path);

        //S3の写真をローカルにダウンロード
        $disk->put($path, $content);

        //ローカルの写真をUploadedFileオブジェクトに変換し、返却
        return new UploadedFile($localFullPath, $fileName);
    }

    /**
     * ローカルのディレクトリを削除する。
     *
     * @return bool
     */
    public function deleteAllLocalPhoto(): bool
    {
        return Storage::disk('public')->deleteDirectory($this->publicDir);
    }
}
