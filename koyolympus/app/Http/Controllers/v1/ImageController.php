<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1;

use Exception;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\PhotoService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetPhotoRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ImageController extends Controller
{
    private PhotoService $photoService;

    public function __construct(PhotoService $photoService)
    {
        $this->photoService = $photoService;
    }

    /**
     * S3内の全写真取得処理
     * (10件ごとのページネーション)
     *
     * @param GetPhotoRequest $request
     * @return LengthAwarePaginator
     */
    public function getPhoto(GetPhotoRequest $request): LengthAwarePaginator
    {
        /** @var string $genre */
        $genre = $request->input('genre');
        return $this->photoService->getAllPhoto($genre);
    }

    /**
     * S3内の全写真取得処理
     * (ランダム)
     *
     * @return Collection
     */
    public function getRandomPhoto(): Collection
    {
        return $this->photoService->getAllPhotoRandomly();
    }

    /**
     * 写真パスを基にS3から写真取得
     *
     * @param Photo $photo
     * @return Response
     * @throws FileNotFoundException
     */
    public function downloadPhoto(Photo $photo): Response
    {
        $storage = Storage::disk('s3');

        if (!$storage->exists($photo->file_path)) {
            Log::debug('画像が見つかりませんでした。');
            return response(['error' => 'no image found'], 404);
        }

        return response($storage->get($photo->file_path), 200);
    }

    /**
     * 写真をS3に、写真のパス・名前・ジャンルをDBにアップロード
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file;
        $fileName = $file->getClientOriginalName();
        /** @var string $genre */
        $genre = $request->input('genre');

        DB::beginTransaction();

        try {
            Log::debug('ファイルのアップロード開始');
            $uniqueFileName = $this->photoService->uploadPhotoDataToDB($fileName, (int)$genre);
            $this->photoService->uploadPhotoToS3($file, $uniqueFileName, (int)$genre);
            DB::commit();
            Log::debug('ファイルのアップロード終了');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ファイルのアップロードに失敗しました。');
            Log::error($e->getMessage());
            return response()->json([], 500);
        }

        return response()->json(['file' => $uniqueFileName]);
    }
}
