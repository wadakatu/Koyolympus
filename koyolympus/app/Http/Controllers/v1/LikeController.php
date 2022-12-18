<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LikeRequest;
use App\Models\Like;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;

class LikeController extends Controller
{
    private Like $like;

    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    /**
     * いいね数を取得
     *
     * @param  LikeRequest  $request
     * @return JsonResponse
     */
    public function getLikeSum(LikeRequest $request): JsonResponse
    {
        /** @var string $uuid */
        $uuid = $request->get('id');

        return response()->json(['all_likes' => $this->like->getAllLike($uuid)]);
    }

    /**
     * いいね数を1増加
     *
     * @param  LikeRequest  $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function likePhoto(LikeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /** @var string $uuid */
            $uuid = $request->get('id');
            $this->like->addLike($uuid);
            DB::commit();
        } catch (Exception $e) {
            Log::error('[LIKE PHOTO]:' . $e->getMessage());
            DB::rollBack();

            return response()->json(['error' => 'いいねに失敗しました。'], 400);
        }

        return response()->json([]);
    }

    /**
     * いいね数を1減少
     *
     * @param  LikeRequest  $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function unlikePhoto(LikeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            /** @var string $uuid */
            $uuid = $request->get('id');
            $this->like->subLike($uuid);
            DB::commit();
        } catch (Exception $e) {
            Log::error('[UNLIKE PHOTO]:' . $e->getMessage());
            DB::rollBack();

            return response()->json(['error' => 'いいね解除に失敗しました。'], 400);
        }

        return response()->json([]);
    }
}
