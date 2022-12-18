<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\Photo;
use Illuminate\Http\Request;
use App\Services\PhotoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\GetPhotoRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\v1\ImageController;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Pagination\LengthAwarePaginator;

class ImageControllerTest extends TestCase
{
    private $imageController;
    private $photoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photoService = Mockery::mock(PhotoService::class);
        $this->imageController = Mockery::mock(ImageController::class, [$this->photoService])->makePartial();
    }

    /**
     * @test
     */
    public function getPhoto()
    {
        $genre = '1';
        $request = Mockery::mock(GetPhotoRequest::class);
        $request->expects('input')
            ->once()
            ->with('genre')
            ->andReturns($genre);

        $this->photoService
            ->expects('getAllPhoto')
            ->once()
            ->with($genre)
            ->andReturns(new LengthAwarePaginator([], 2, 10));

        $result = $this->imageController->getPhoto($request);

        $this->assertSame(10, $result->perPage());
        $this->assertSame(2, $result->total());
    }

    /**
     * @test
     */
    public function getRandomPhoto()
    {
        $this->photoService
            ->expects('getAllPhotoRandomly')
            ->once()
            ->withNoArgs()
            ->andReturns(Collect([]));

        $this->imageController->getRandomPhoto();
    }

    /**
     * @test
     */
    public function downloadPhotoSuccess()
    {
        $filePath = '/photo/landscape';
        $photo = new Photo(['file_path' => $filePath]);
        $fileSystemAdapter = Mockery::mock(FilesystemAdapter::class);

        Storage::shouldReceive('disk')
            ->once()
            ->with('s3')
            ->andReturn($fileSystemAdapter);

        $fileSystemAdapter
            ->expects('exists')
            ->once()
            ->with('/photo/landscape')
            ->andReturnTrue();

        $fileSystemAdapter
            ->expects('get')
            ->once()
            ->with($filePath)
            ->andReturns('success');

        Log::shouldReceive('debug')->never();

        $response = $this->imageController->downloadPhoto($photo);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('success', $response->getContent());
    }

    /**
     * @test
     */
    public function downloadPhotoError()
    {
        $photo = new Photo(['file_path' => '/photo/landscape']);
        $fileSystemAdapter = Mockery::mock(FilesystemAdapter::class);

        Storage::shouldReceive('disk')
            ->once()
            ->with('s3')
            ->andReturn($fileSystemAdapter);

        $fileSystemAdapter
            ->expects('exists')
            ->once()
            ->with('/photo/landscape')
            ->andReturnFalse();

        Log::shouldReceive('debug')
            ->once()
            ->with('画像が見つかりませんでした。');

        $response = $this->imageController->downloadPhoto($photo);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(json_encode(['error' => 'no image found']), $response->getContent());
    }

    /**
     * @test
     */
    public function uploadPhotoSuccess()
    {
        $fileName = 'fake.jpeg';
        $request = new Request();
        $request->merge(['genre' => 1]);
        $file = UploadedFile::fake()->image($fileName);
        $request->file = $file;

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never();

        Log::shouldReceive('debug')
            ->once()
            ->with('ファイルのアップロード開始');
        Log::shouldReceive('debug')
            ->once()
            ->with('ファイルのアップロード終了');
        Log::shouldReceive('error')
            ->never()
            ->with('ファイルのアップロードに失敗しました。');
        Log::shouldReceive('error')->never();

        $this->photoService
            ->expects('uploadPhotoDataToDB')
            ->once()
            ->with($fileName, 1)
            ->andReturns($uniqueFileName = 'noError.jpeg');

        $this->photoService
            ->expects('uploadPhotoToS3')
            ->once()
            ->with($file, $uniqueFileName, 1);

        $response = $this->imageController->uploadPhoto($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"file":"noError.jpeg"}', $response->getContent());
    }

    /**
     * @test
     */
    public function uploadPhotoError()
    {
        $fileName = 'fake.jpeg';
        $request = new Request();
        $request->merge(['genre' => 1]);
        $file = UploadedFile::fake()->image($fileName);
        $request->file = $file;

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->once();

        Log::shouldReceive('debug')
            ->once()
            ->with('ファイルのアップロード開始');
        Log::shouldReceive('debug')
            ->never()
            ->with('ファイルのアップロード終了');
        Log::shouldReceive('error')
            ->once()
            ->with('ファイルのアップロードに失敗しました。');
        Log::shouldReceive('error')
            ->once()
            ->with("");

        $this->photoService
            ->expects('uploadPhotoDataToDB')
            ->once()
            ->with($fileName, 1)
            ->andThrow(Exception::class);

        $this->photoService
            ->expects('uploadPhotoToS3')
            ->never();

        $response = $this->imageController->uploadPhoto($request);

        $this->assertSame(500, $response->getStatusCode());
    }
}
