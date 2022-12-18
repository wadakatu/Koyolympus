<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait LogTrait
{
    /**
     * infoログを出力
     *
     * @param  string  $title
     * @param  string  $message
     * @return void
     */
    public function outputLog(string $title, string $message)
    {
        Log::info("$title $message");
    }

    /**
     * errorログを出力
     *
     * @param  string  $title
     * @param  string  $message
     * @return void
     */
    public function outputErrorLog(string $title, string $message)
    {
        Log::error("$title $message");
    }

    /**
     * エラー・例外発生時にerrorログを出力
     *
     * @param  string  $title
     * @param  string  $message
     * @return void
     */
    public function outputThrowableLog(string $title, string $message)
    {
        Log::error("$title 例外/エラー発生");
        Log::error("$title $message");
    }
}
