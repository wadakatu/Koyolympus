<?php
declare(strict_types=1);

namespace App\Http\Controllers\v1;

use DB;
use Log;
use Exception;
use App\Http\Models\Like;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LIkeRequest;
use App\Http\Controllers\Controller;

class LikeController extends Controller
{
    private $like;

    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    /**
     * いいね数を取得
     *
     * @param LIkeRequest $request
     * @return JsonResponse
     */
    public function getLikeSum(LIkeRequest $request): JsonResponse
    {
        return response()->json(['all_likes' => $this->like->getAllLike($request->get('id'))]);
    }

    /**
     * いいね数を+1する。
     *
     * @param LIkeRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function likePhoto(LIkeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->like->addLike($request->get('id'));
            DB::commit();
        } catch (Exception $e) {
            Log::error('[LIKE PHOTO]:' . $e->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'いいねに失敗しました。'], 400);
        }
        return response()->json([]);
    }

    /**
     * いいね数を-1する。
     *
     * @param LIkeRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function unlikePhoto(LIkeRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $this->like->subLike($request->get('id'));
            DB::commit();
        }catch(Exception $e){
            Log::error('[UNLIKE PHOTO]:' . $e->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'いいね解除に失敗しました。'], 400);
        }
        return response()->json([]);
    }
}
