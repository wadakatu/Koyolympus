<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use App\Exceptions\S3\S3MoveFailedException;

class S3MoveFailedExceptionTest extends TestCase
{
    /**
     * ログが適切に出力されるかテスト
     *
     * @test
     * @return void
     */
    public function report()
    {
        $s3Path = 'old/s3/file';
        $newS3Path = 'new/s3/file';

        Log::shouldReceive('error')
            ->once()
            ->with('s3 move failed.');
        Log::shouldReceive('error')
            ->once()
            ->with('old S3 Path：' . $s3Path);
        Log::shouldReceive('error')
            ->once()
            ->with('new S3 Path：' . $newS3Path);
        Log::shouldReceive('error')
            ->once()
            ->with('ーーーーーーーーーーーーーーーーーーーーーーーーーーーー');

        (new S3MoveFailedException($s3Path, $newS3Path))->report();
    }
}
