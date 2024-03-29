<?php

declare(strict_types=1);

namespace Tests\Unit\Exceptions;

use App\Exceptions\Model\ModelUpdateFailedException;
use App\Models\Photo;
use Log;
use Tests\TestCase;

class ModelUpdateFailedExceptionTest extends TestCase
{
    /**
     * ログが適切に出力されるかテスト
     *
     * @test
     *
     * @return void
     */
    public function report()
    {
        $photo = new Photo();

        Log::shouldReceive('error')
            ->once()
            ->with('Model update failed.');
        Log::shouldReceive('error')
            ->once()
            ->with('Table Name：photos');
        Log::shouldReceive('error')
            ->once()
            ->with('Target Record：' . $photo->id);
        Log::shouldReceive('error')
            ->once()
            ->with('ーーーーーーーーーーーーーーーーーーーーーーーーーーーー');

        (new ModelUpdateFailedException($photo, 'エラーメッセージ'))->report();
    }
}
