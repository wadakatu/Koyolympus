<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Str;

class LikeController extends Controller
{


    public function __construct()
    {

    }

    public function likePhoto(string $id)
    {
        if (!Str::isUuid($id)) {
            return response()->json(['sorry, it is invalid ID.']);
        }

        return response()->json(['name' => 'hello, world']);
    }

    public function unlikePhoto()
    {

    }
}
