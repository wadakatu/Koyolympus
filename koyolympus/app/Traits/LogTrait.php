<?php
declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogTrait
{
    public function outputLog(string $title, $message)
    {
        Log::info("$title $message");
    }

    public function outputErrorLog(string $title, string $message)
    {
        Log::error("$title $message");
    }

    public function outputThrowableLog(string $title, string $message)
    {
        Log::error("$title 例外/エラー発生");
        Log::error("$title $message");
    }
}
