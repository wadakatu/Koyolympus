<?php
declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\Http\Controllers\v1\ImageController;
use App\Http\Models\Photo;
use App\Http\Requests\GetPhotoRequest;
use App\Http\Services\PhotoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

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

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getPhoto()
    {
        $request = new GetPhotoRequest;
        $request->merge(['genre' => 1]);

        $this->photoService
            ->shouldReceive('getAllPhoto')
            ->once()
            ->with(1)
            ->andReturn(new LengthAwarePaginator([], 1, 1));

        $this->imageController->getPhoto($request);
    }

    /**
     * @test
     */
    public function getRandomPhoto()
    {
        $this->photoService
            ->shouldReceive('getAllPhotoRandomly')
            ->once()
            ->withNoArgs()
            ->andReturn(Collect([]));

        $this->imageController->getRandomPhoto();
    }

    /**
     * @test
     * @dataProvider providerDownloadPhoto
     * @param $path
     * @param $expectedStatus
     */
    public function downloadPhoto($path, $expectedStatus)
    {
        $fakePhoto = UploadedFile::fake()->image('fake.jpg');
        Storage::fake('s3')->putFile('/photo/landscape', $fakePhoto);

        $photo = new Photo(['file_path' => $path]);

        $response = $this->imageController->downloadPhoto($photo);

        $this->assertSame($expectedStatus, $response->getStatusCode());

        if ($expectedStatus === 404) {
            $this->assertSame('{"error":"no image found"}', $response->getContent());
        }
    }

    public function providerDownloadPhoto(): array
    {
        return [
            '200' => [
                'params' => '/photo/landscape',
                'status' => 200,
            ],
            '404' => [
                'params' => '/',
                'status' => 404,
            ],
        ];
    }

    /**
     * @test
     */
    public function uploadPhoto_noError()
    {
        $fileName = 'fake.jpeg';
        $request = new Request;
        $request->merge(['genre' => 1]);
        $file = UploadedFile::fake()->image($fileName);
        $request->file = $file;

        DB::shouldReceive('beginTransaction')
            ->once()
            ->with();

        Log::shouldReceive('debug')
            ->once()
            ->with('ファイルのアップロード開始');

        Log::shouldReceive('debug')
            ->once()
            ->with('ファイルのアップロード終了');

        $this->photoService
            ->shouldReceive('uploadPhotoToS3')
            ->once()
            ->with($file, $fileName, 1)
            ->andReturn('noError.jpeg');

        DB::shouldReceive('commit')
            ->once()
            ->with();

        DB::shouldReceive('rollBack')
            ->never()
            ->with();

        $this->partialMock(ImageController::class, function ($mock) use ($request) {
            $mock->shouldReceive('removePhoto')->never()->with($request)->andReturn(response()->json([]));
        });

        Log::shouldReceive('debug')
            ->never()
            ->with('ファイルのアップロードに失敗しました。');

        Log::shouldReceive('error')
            ->never()
            ->with('noError');

        $response = $this->imageController->uploadPhoto($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('{"file":"noError.jpeg"}', $response->getContent());
    }

    /**
     * @test
     */
    public function uploadPhoto_withError()
    {
        $fileName = 'fake.jpeg';
        $request = new Request;
        $request->merge(['genre' => 1]);
        $file = UploadedFile::fake()->image($fileName);
        $request->file = $file;

        DB::shouldReceive('beginTransaction')
            ->once()
            ->with();

        Log::shouldReceive('debug')
            ->once()
            ->with('ファイルのアップロード開始');

        $this->photoService
            ->shouldReceive('uploadPhotoToS3')
            ->once()
            ->andThrow(Exception::class);

        Log::shouldReceive('debug')
            ->never()
            ->with('ファイルのアップロード終了');

        DB::shouldReceive('commit')
            ->never()
            ->with();

        DB::shouldReceive('rollBack')
            ->once()
            ->with();

        $this->imageController
            ->shouldReceive('removePhoto')
            ->with($request);

        Log::shouldReceive('debug')
            ->once()
            ->with('ファイルのアップロードに失敗しました。');

        Log::shouldReceive('error')
            ->once()
            ->with("");

        $response = $this->imageController->uploadPhoto($request);

        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function removePhoto()
    {
        $fileName = 'fake.jpeg';
        $file = [UploadedFile::fake()->image($fileName), 'custom' => $fileName];
        $request = new Request;
        $request->file = $file;
        $request->merge(['genre' => 1]);

        $this->photoService
            ->shouldReceive('deletePhotoFromS3')
            ->once()
            ->with($fileName, 1);

        $response = $this->imageController->removePhoto($request);

        $this->assertSame(200, $response->getStatusCode());
    }
}
