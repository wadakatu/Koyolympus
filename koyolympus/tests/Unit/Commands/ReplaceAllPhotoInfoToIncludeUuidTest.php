<?php

namespace Tests\Unit\Commands;


use App\Http\Services\PhotoService;
use DB;
use Mockery;
use Symfony\Component\Console\Helper\ProgressBar;
use Tests\TestCase;

class ReplaceAllPhotoInfoToIncludeUuidTest extends TestCase
{
    private $photoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photoService = Mockery::mock(PhotoService::class);
        $this->app->instance(PhotoService::class, $this->photoService);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * 例外なしの場合、サービスクラスが適切に呼び出されているかテスト
     *
     * @test
     */
    public function handle_withoutException()
    {
        //try statement.
        DB::shouldReceive('beginTransaction')->once();
        $this->photoService
            ->shouldReceive('includeUuidInRecord')
            ->once()
            ->with(Mockery::on(function ($actual) {
                $this->assertInstanceOf(ProgressBar::class, $actual);
                return true;
            }));
        DB::shouldReceive('commit')->once();

        //catch statement.
        DB::shouldReceive('rollBack')->never();

        //finally statement.
        $this->photoService
            ->shouldReceive('deleteAllLocalPhoto')
            ->once()
            ->with('/local/');

        $this->artisan('command:includeUuid')
            ->expectsOutput('UUID置換処理開始')
            ->expectsOutput('UUID置換処理終了');
    }


    /**
     * 例外ありの場合、その例外をcatchできているかテスト
     *
     * @test
     */
    public function handle_withException()
    {
        //try statement.
        DB::shouldReceive('beginTransaction')->once();
        $this->photoService
            ->shouldReceive('includeUuidInRecord')
            ->once()
            ->with(Mockery::on(function ($actual) {
                $this->assertInstanceOf(ProgressBar::class, $actual);
                return true;
            }))
            ->andThrow(new \Exception('エラー！'));
        DB::shouldReceive('commit')->never();

        //catch statement.
        DB::shouldReceive('rollBack')->once();

        //finally statement.
        $this->photoService
            ->shouldReceive('deleteAllLocalPhoto')
            ->once()
            ->with('/local/');

        $this->artisan('command:includeUuid')
            ->expectsOutput('Exception：エラー！')
            ->expectsOutput('例外発生');
    }
}
