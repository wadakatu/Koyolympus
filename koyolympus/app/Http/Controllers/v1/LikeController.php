<?php
declare(strict_types=1);

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Str;

class LikeController extends Controller
{


    public function __construct()
    {

    }

    public function getLikeSum(Request $request): JsonResponse
    {
        $id = $request->get('id');

        if (!Str::isUuid($id)) {
            return response()->json(['message' => 'sorry, it is invalid ID. GET_LIKE'], 404);
        }

        return response()->json(['num' => 0]);
    }

    public function likePhoto(Request $request): JsonResponse
    {
        $id = $request->get('id');

        if (!Str::isUuid($id)) {
            return response()->json(['message' => 'sorry, it is invalid ID. LIKE'], 404);
        }

        return response()->json(['name' => 'hello, world']);
    }

    public function unlikePhoto(Request $request): JsonResponse
    {
        $id = $request->get('id');

        if (!Str::isUuid($id)) {
            return response()->json(['message' => 'sorry, it is invalid ID. UNLIKE'], 404);
        }

        return response()->json(['name' => 'hello, hell']);
    }
}
