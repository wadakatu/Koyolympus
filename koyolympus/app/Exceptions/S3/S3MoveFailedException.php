<?php

declare(strict_types=1);

namespace App\Exceptions\S3;

use Exception;
use Illuminate\Support\Facades\Log;

class S3MoveFailedException extends Exception
{
    private string $s3Path;

    private string $newS3Path;

    public function __construct(string $s3Path, string $newS3Path, string $message = '')
    {
        parent::__construct($message);
        $this->s3Path    = $s3Path;
        $this->newS3Path = $newS3Path;
    }

    public function report(): void
    {
        Log::error('s3 move failed.');
        Log::error('old S3 Path：' . $this->s3Path);
        Log::error('new S3 Path：' . $this->newS3Path);
        Log::error('ーーーーーーーーーーーーーーーーーーーーーーーーーーーー');
    }
}
