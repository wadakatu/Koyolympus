<?php
declare(strict_types=1);

namespace Tests\Unit\Controllers;

use DB;
use Log;
use Mockery;
use Exception;
use Tests\TestCase;
use App\Http\Models\Like;
use App\Http\Requests\LikeRequest;
use App\Http\Controllers\v1\LikeController;

class LikeControllerTest extends TestCase
{
    private $likeController;
    private $like;
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->like = Mockery::mock(Like::class);
        $this->request = Mockery::mock(LikeRequest::class);
        $this->likeController = new LikeController($this->like);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getLikeSum()
    {
        $id = '1';
        $allLikes = 100;

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('id')
            ->andReturn($id);

        $this->like
            ->shouldReceive('getAllLike')
            ->once()
            ->with($id)
            ->andReturn($allLikes);

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
            ->shouldReceive('get')
            ->once()
            ->with('id')
            ->andReturn($id);

        $this->like
            ->shouldReceive('addLike')
            ->once()
            ->with($id);

        $response = $this->likeController->likePhoto($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode([]), $response->getContent());
    }

    /**
     * @test
     * @throws Exception
     */
    public function likePhoto_withException()
    {
        $id = '3';
        $exception = new Exception('例外発生！');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        Log::shouldReceive('error')->once()->with('[LIKE PHOTO]:' . $exception->getMessage());

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('id')
            ->andReturn($id);

        $this->like
            ->shouldReceive('addLike')
            ->once()
            ->with($id)
            ->andThrow($exception);

        $response = $this->likeController->likePhoto($this->request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => 'いいねに失敗しました。']), $response->getContent());
    }

    /**
     * @test
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
            ->shouldReceive('get')
            ->once()
            ->with('id')
            ->andReturn($id);

        $this->like
            ->shouldReceive('subLike')
            ->once()
            ->with($id);

        $response = $this->likeController->unlikePhoto($this->request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(json_encode([]), $response->getContent());
    }

    /**
     * @test
     * @throws Exception
     */
    public function unlikePhoto_withException()
    {
        $id = '5';
        $exception = new Exception('例外発生！');

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        Log::shouldReceive('error')->once()->with('[UNLIKE PHOTO]:' . $exception->getMessage());

        $this->request
            ->shouldReceive('get')
            ->once()
            ->with('id')
            ->andReturn($id);

        $this->like
            ->shouldReceive('subLike')
            ->once()
            ->with($id)
            ->andThrow($exception);

        $response = $this->likeController->unlikePhoto($this->request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => 'いいね解除に失敗しました。']), $response->getContent());
    }

}
