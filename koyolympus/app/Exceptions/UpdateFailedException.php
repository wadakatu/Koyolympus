<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UpdateFailedException extends Exception
{
    public function report()
    {
        Log::error('hello, world');
    }

    public function render(): JsonResponse
    {
        return response()->json(
            $this->message,
            401
        );
    }
}
