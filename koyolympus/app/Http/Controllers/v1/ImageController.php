<?php
declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Models\Photo;
use App\Http\Requests\GetPhotoRequest;
use App\Http\Services\PhotoService;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{

    private $photoService;

    public function __construct(PhotoService $photoService)
    {
        $this->photoService = $photoService;
    }

    /**
     * S3内の写真のパスを全て取得する。(10件ごとのページネーション)
     * @param GetPhotoRequest $request
     * @return LengthAwarePaginator
     */
    public function getPhoto(GetPhotoRequest $request): LengthAwarePaginator
    {
        $genre = $request->input('genre');
        return $this->photoService->getAllPhoto($genre);
    }

    /**
     * S3内の写真のパスをランダムに全て取得する。
     * @return Collection
     */
    public function getRandomPhoto(): Collection
    {
        return $this->photoService->getAllPhotoRandomly();
    }

    /**
     * 写真パスを基にS3から写真を取得
     * @param Photo $photo
     * @return Application|ResponseFactory|Response
     * @throws FileNotFoundException
     */
    public function downloadPhoto(Photo $photo)
    {
        if (!Storage::disk('s3')->exists($photo->file_path)) {
            Log::debug('画像が見つかりませんでした。');
            return response(['error' => 'no image found'], 404);
        }

        return response(Storage::disk('s3')->get($photo->file_path), 200);
    }

    /**
     * 写真をS3に、写真のパス・名前・ジャンルをDBにアップロードする
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $file = $request->file;
        $fileName = $file->getClientOriginalName();
        $genre = $request->input('genre');

        $uniqueFileName = null;
        DB::beginTransaction();

        try {
            Log::debug('ファイルのアップロード開始');
            $uniqueFileName = $this->photoService->uploadPhotoToS3($file, $fileName, $genre);
            DB::commit();
            Log::debug('ファイルのアップロード終了');
        } catch (Exception $e) {
            Log::debug('ファイルのアップロードに失敗しました。');
            DB::rollBack();
            $this->removePhoto($request);
            Log::error($e->getMessage());
            return response()->json([], 500);
        }

        return response()->json(['file' => $uniqueFileName]);
    }

    /**
     * 写真をS3から、写真情報をDBから削除する。
     * @param Request $request
     * @return JsonResponse
     */
    public function removePhoto(Request $request): JsonResponse
    {
        $file = $request->file;
        $fileName = $file['custom'];
        $genre = $request->input('genre');
        $this->photoService->deletePhotoFromS3($fileName, $genre);

        return response()->json([]);
    }

}
