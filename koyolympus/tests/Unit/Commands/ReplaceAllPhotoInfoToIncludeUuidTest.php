<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use DB;
use Mockery;
use Tests\TestCase;
use App\Services\ReplaceUuid\BaseService;

class ReplaceAllPhotoInfoToIncludeUuidTest extends TestCase
{
    private $replaceUuidService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->replaceUuidService = Mockery::mock(BaseService::class);
        $this->app->instance(BaseService::class, $this->replaceUuidService);
    }

    /**
     * 例外なしの場合、サービスクラスが適切に呼び出されているかテスト
     *
     * @test
     */
    public function handleWithoutException()
    {
        //try statement.
        DB::shouldReceive('beginTransaction')->once();
        $this->replaceUuidService
            ->expects('includeUuidInRecord');
        DB::shouldReceive('commit')->once();

        //catch statement.
        DB::shouldReceive('rollBack')->never();

        //finally statement.
        $this->replaceUuidService
            ->expects('deleteAllLocalPhoto')
            ->andReturnTrue();

        $this->artisan('command:includeUuid')
            ->expectsOutput('UUID置換処理開始')
            ->expectsOutput('UUID置換処理終了');
    }


    /**
     * 例外ありの場合、その例外をcatchできているかテスト
     *
     * @test
     */
    public function handleWithException()
    {
        //try statement.
        DB::shouldReceive('beginTransaction')->once();
        $this->replaceUuidService
            ->expects('includeUuidInRecord')
            ->andThrow(new \Exception('エラー！'));
        DB::shouldReceive('commit')->never();

        //catch statement.
        DB::shouldReceive('rollBack')->once();

        //finally statement.
        $this->replaceUuidService
            ->expects('deleteAllLocalPhoto')
            ->andReturnTrue();

        $this->artisan('command:includeUuid')
            ->expectsOutput('Exception：エラー！')
            ->expectsOutput('例外発生');
    }
}
