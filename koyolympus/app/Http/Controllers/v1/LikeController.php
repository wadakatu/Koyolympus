<?php
declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Models\Like;
use App\Http\Requests\LIkeRequest;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    private $like;

    public function __construct(Like $like)
    {
        $this->like = $like;
    }

    public function getLikeSum(LIkeRequest $request): JsonResponse
    {
        return response()->json(['all_likes' => $this->like->getAllLike($request->get('id'))]);
    }

    public function likePhoto(LIkeRequest $request): JsonResponse
    {
        $this->like->addLike($request->get('id'));
        return response()->json([]);
    }

    public function unlikePhoto(LIkeRequest $request): JsonResponse
    {
        $this->like->subLike($request->get('id'));
        return response()->json([]);
    }
}
