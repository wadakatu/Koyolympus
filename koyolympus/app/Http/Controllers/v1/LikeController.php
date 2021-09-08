<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Str;

class LikeController extends Controller
{


    public function __construct()
    {

    }

    public function likePhoto(Request $request): JsonResponse
    {
        $id = $request->get('id');

        if (!Str::isUuid($id)) {
            return response()->json(['name' => 'sorry, it is invalid ID. LIKE']);
        }

        return response()->json(['name' => 'hello, world']);
    }

    public function unlikePhoto(Request $request): JsonResponse
    {
        $id = $request->get('id');

        if (!Str::isUuid($id)) {
            return response()->json(['name' => 'sorry, it is invalid ID. UNLIKE']);
        }

        return response()->json(['name' => 'hello, hell']);
    }
}
