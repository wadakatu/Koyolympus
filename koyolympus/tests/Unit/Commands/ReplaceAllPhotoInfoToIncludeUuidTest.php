<?php
declare(strict_types=1);

namespace Tests\Unit\Commands;


use App\Http\Services\ReplaceUuid\BaseService;
use DB;
use Mockery;
use Tests\TestCase;

class ReplaceAllPhotoInfoToIncludeUuidTest extends TestCase
{
    private $replaceUuidService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->replaceUuidService = Mockery::mock(BaseService::class);
        $this->app->instance(BaseService::class, $this->replaceUuidService);
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
        $this->replaceUuidService
            ->shouldReceive('includeUuidInRecord')
            ->once();
        DB::shouldReceive('commit')->once();

        //catch statement.
        DB::shouldReceive('rollBack')->never();

        //finally statement.
        $this->replaceUuidService
            ->shouldReceive('deleteAllLocalPhoto')
            ->once()
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
    public function handle_withException()
    {
        //try statement.
        DB::shouldReceive('beginTransaction')->once();
        $this->replaceUuidService
            ->shouldReceive('includeUuidInRecord')
            ->once()
            ->andThrow(new \Exception('エラー！'));
        DB::shouldReceive('commit')->never();

        //catch statement.
        DB::shouldReceive('rollBack')->once();

        //finally statement.
        $this->replaceUuidService
            ->shouldReceive('deleteAllLocalPhoto')
            ->once()
            ->andReturnTrue();

        $this->artisan('command:includeUuid')
            ->expectsOutput('Exception：エラー！')
            ->expectsOutput('例外発生');
    }
}
