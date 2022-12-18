<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\v1\LikeController;
use App\Http\Requests\LikeRequest;
use App\Models\Like;
use DB;
use Exception;
use Log;
use Mockery;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    private $likeController;

    private $like;

    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->like           = Mockery::mock(Like::class);
        $this->request        = Mockery::mock(LikeRequest::class);
        $this->likeController = new LikeController($this->like);
    }

    /**
     * @test
     */
    public function getLikeSum()
    {
        $id       = '1';
        $allLikes = 100;

        $this->request
            ->expects('get')
            ->with('id')
            ->andReturns($id);

        $this->like
            ->expects('getAllLike')
            ->with($id)
            ->andReturns($allLikes);

        $response = $this->likeController->getLikeSum($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode(['all_likes' => $allLikes]), $response->getContent());
    }

    /**
     * @test
     */
    public function likePhoto()
    {
        $id = '2';

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        Log::shouldReceive('error')->never();

        $this->request
            ->expects('get')
            ->with('id')
            ->andReturns($id);

        $this->like
            ->expects('addLike')
            ->with($id);

        $response = $this->likeController->likePhoto($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode([]), $response->getContent());
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function likePhotoWithException()
    {
        $id        = '3';
        $exception = new Exception('例外発生！');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        Log::shouldReceive('error')->once()->with('[LIKE PHOTO]:' . $exception->getMessage());

        $this->request
            ->expects('get')
            ->with('id')
            ->andReturns($id);

        $this->like
            ->expects('addLike')
            ->with($id)
            ->andThrow($exception);

        $response = $this->likeController->likePhoto($this->request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => 'いいねに失敗しました。']), $response->getContent());
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function unlikePhoto()
    {
        $id = '4';

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        Log::shouldReceive('error')->never();

        $this->request
            ->expects('get')
            ->with('id')
            ->andReturns($id);

        $this->like
            ->expects('subLike')
            ->with($id);

        $response = $this->likeController->unlikePhoto($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode([]), $response->getContent());
    }

    /**
     * @test
     *
     * @throws Exception
     */
    public function unlikePhotoWithException()
    {
        $id        = '5';
        $exception = new Exception('例外発生！');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        Log::shouldReceive('error')->once()->with('[UNLIKE PHOTO]:' . $exception->getMessage());

        $this->request
            ->expects('get')
            ->with('id')
            ->andReturns($id);

        $this->like
            ->expects('subLike')
            ->with($id)
            ->andThrow($exception);

        $response = $this->likeController->unlikePhoto($this->request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => 'いいね解除に失敗しました。']), $response->getContent());
    }
}
