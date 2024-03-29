<?php
declare(strict_types=1);

namespace Tests\Unit\Services\ReplaceUuid;

use App\Exceptions\Model\ModelUpdateFailedException;
use App\Exceptions\S3\S3MoveFailedException;
use App\Http\Models\Photo;
use App\Http\Services\ReplaceUuid\BaseService;
use Config;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Mockery;
use Storage;
use Str;
use Tests\TestCase;

class BaseServiceTest extends TestCase
{
    /** @var Photo|Mockery\LegacyMockInterface|Mockery\MockInterface photo */
    private $photo;

    /** @var Mockery\Mock|BaseService baseService */
    private $baseService;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Mockery\MockInterface|Photo photo */
        $this->photo = Mockery::mock(Photo::class);

        /** @var Mockery\MockInterface|BaseService baseService */
        $this->baseService = Mockery::mock(BaseService::class, [$this->photo])->makePartial();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Idの値がUuidでない場合に、処理が適切に行われるかのテスト
     * 例外なしバージョン
     *
     * @test
     * @dataProvider providerIncludeUuidInRecord_withoutException
     * @param $params
     */
    public function includeUuidInRecord_withoutException($params)
    {
        $fileName = 'test.jpeg';
        $oldPath = 'old/' . $fileName;
        $path = 'test/' . $fileName;
        $genre = 1;
        $id = 'id_test';

        $newInfo = ['file_name' => $fileName, 'file_path' => $path];

        $p = new Photo(['id' => $id, 'file_path' => $oldPath, 'genre' => $genre]);
        $photo = Mockery::mock($p);

        $array1 = array_fill(0, $params['uuidPad'], new Photo(['id' => Str::uuid()->toString()]));
        $array2 = array_fill(0, $params['nonUuidPad'], $photo);

        $photoArray = array_merge($array1, $array2);

        $photoList = new Collection($photoArray);

        $this->photo->shouldReceive('all')->once()->andReturn($photoList);
        $this->baseService->shouldReceive('createLatestPhotoInfoIncludingUuid')
            ->times($params['createLatest'])
            ->with($oldPath)
            ->andReturn($newInfo);
        $this->baseService->shouldReceive('movePhotoToNewFolder')
            ->times($params['movePhotoToNewFolder']['times'])
            ->with($oldPath, $fileName, $genre)
            ->andReturn(true);
        $photo->shouldReceive('update')
            ->times($params['updatePhoto']['times'])
            ->with($newInfo)
            ->andReturn(true);

        $this->baseService->includeUuidInRecord();
    }

    /**
     * providerIncludeUuidInRecordに渡すパラメータと期待値
     * 例外なしバージョン
     *
     * @return \array[][]
     */
    public function providerIncludeUuidInRecord_withoutException(): array
    {
        return [
            'Uuidを含まないレコードが1件' => [
                'params' => [
                    'uuidPad' => 0,
                    'nonUuidPad' => 1,
                    'createLatest' => 1,
                    'movePhotoToNewFolder' => [
                        'times' => 1,
                    ],
                    'updatePhoto' => [
                        'times' => 1,
                    ],
                ],
            ],
            'Uuidを含まないレコードが2件' => [
                'params' => [
                    'uuidPad' => 0,
                    'nonUuidPad' => 2,
                    'createLatest' => 2,
                    'movePhotoToNewFolder' => [
                        'times' => 2,
                    ],
                    'updatePhoto' => [
                        'times' => 2,
                    ]
                ],
            ],
            'Uuidを含むレコードが1件' => [
                'params' => [
                    'uuidPad' => 1,
                    'nonUuidPad' => 0,
                    'createLatest' => 0,
                    'movePhotoToNewFolder' => [
                        'times' => 0,
                    ],
                    'updatePhoto' => [
                        'times' => 0,
                    ]
                ],
            ],
            'Uuidを含むレコードが2件' => [
                'params' => [
                    'uuidPad' => 2,
                    'nonUuidPad' => 0,
                    'createLatest' => 0,
                    'movePhotoToNewFolder' => [
                        'times' => 0,
                    ],
                    'updatePhoto' => [
                        'times' => 0,
                    ]
                ],
            ],
            'Uuidを含まないレコードが1件_Uuidを含むレコードが1件' => [
                'params' => [
                    'uuidPad' => 1,
                    'nonUuidPad' => 1,
                    'createLatest' => 1,
                    'movePhotoToNewFolder' => [
                        'times' => 1,
                    ],
                    'updatePhoto' => [
                        'times' => 1,
                    ]
                ],
            ],
            'Uuidを含まないレコードが2件_Uuidを含むレコードが1件' => [
                'params' => [
                    'uuidPad' => 1,
                    'nonUuidPad' => 2,
                    'createLatest' => 2,
                    'movePhotoToNewFolder' => [
                        'times' => 2,
                    ],
                    'updatePhoto' => [
                        'times' => 2,
                    ]
                ],
            ],
            'Uuidを含まないレコードが1件_Uuidを含むレコードが2件' => [
                'params' => [
                    'uuidPad' => 2,
                    'nonUuidPad' => 1,
                    'createLatest' => 1,
                    'movePhotoToNewFolder' => [
                        'times' => 1,
                    ],
                    'updatePhoto' => [
                        'times' => 1,
                    ]
                ],
            ],
            'Uuidを含まないレコードが2件_Uuidを含むレコードが2件' => [
                'params' => [
                    'uuidPad' => 2,
                    'nonUuidPad' => 2,
                    'createLatest' => 2,
                    'movePhotoToNewFolder' => [
                        'times' => 2,
                    ],
                    'updatePhoto' => [
                        'times' => 2,
                    ]
                ],
            ],
        ];
    }

    /**
     * Idの値がUuidでない場合に、処理が適切に行われるかのテスト
     * 例外ありバージョン
     *
     * @test
     * @dataProvider providerIncludeUuidInRecord_withException
     * @param $params
     * @param $expected
     */
    public function includeUuidInRecord_withException($params, $expected)
    {
        $fileName = 'exception.jpeg';
        $genre = 1;
        $oldPath = 'old/' . $fileName;
        $path = 'test/' . $fileName;
        $id = 'id_test';

        $newInfo = ['file_name' => $fileName, 'file_path' => $path];

        $p = new Photo(['id' => $id, 'file_path' => $oldPath, 'genre' => $genre]);
        $photo = Mockery::mock($p);

        $array = array_fill(0, 1, $photo);

        $photoList = new Collection($array);

        $this->photo->shouldReceive('all')->once()->andReturn($photoList);
        $this->baseService->shouldReceive('createLatestPhotoInfoIncludingUuid')
            ->once()
            ->with($oldPath)
            ->andReturn($newInfo);
        $this->baseService->shouldReceive('movePhotoToNewFolder')
            ->times($params['movePhotoToNewFolder']['times'])
            ->with($oldPath, $fileName, $genre)
            ->andReturn($params['movePhotoToNewFolder']['return']);
        $photo->shouldReceive('update')
            ->times($params['updatePhoto']['times'])
            ->with($newInfo)
            ->andReturn($params['updatePhoto']['return']);

        $this->expectException($expected['exception']);
        $this->expectExceptionMessage($expected['message']);

        $this->baseService->includeUuidInRecord();
    }

    /**
     * providerIncludeUuidInRecordに渡すパラメータと期待値
     * 例外ありバージョン
     *
     * @return array[]
     */
    public function providerIncludeUuidInRecord_withException(): array
    {
        return [
            'S3移動失敗' => [
                'param' => [
                    'movePhotoToNewFolder' => [
                        'times' => 1,
                        'return' => false,
                    ],
                    'updatePhoto' => [
                        'times' => 0,
                        'return' => true
                    ],
                ],
                'expected' => [
                    'exception' => S3MoveFailedException::class,
                    'message' => 'A file move failed for some reason.',
                ],
            ],
            'DB更新失敗' => [
                'param' => [
                    'movePhotoToNewFolder' => [
                        'times' => 1,
                        'return' => true,
                    ],
                    'updatePhoto' => [
                        'times' => 1,
                        'return' => false
                    ],
                ],
                'expected' => [
                    'exception' => ModelUpdateFailedException::class,
                    'message' => 'Model update Failed for some reason.',
                ],
            ]
        ];
    }

    /**
     * 古いS3パスから生成された配列の中身が、Uuidを含む
     * [id, file_name, file_path]になっているかどうかテスト
     * 例外なしバージョン
     *
     * @test
     */
    public function createLatestPhotoInfoIncludingUuid_withoutException()
    {
        $fileName = '12345.test.jpeg';
        $oldS3Path = 'old/' . $fileName;

        Storage::shouldReceive('disk')->with('s3')->andReturn($s3Disk = Mockery::mock(FilesystemAdapter::class));
        $s3Disk->shouldReceive('exists')->once()->with($oldS3Path)->andReturnTrue();

        $result = $this->baseService->createLatestPhotoInfoIncludingUuid($oldS3Path);

        $nameArr = explode('.', $result['file_name']);
        $pathArr = explode('/', $result['file_path']);
        $pathNameArr = explode('.', $pathArr[1]);

        $this->assertTrue(Str::isUuid($result['id']));
        $this->assertTrue(Str::isUuid($nameArr[0]));
        $this->assertTrue(Str::isUuid($pathNameArr[0]));

        $this->assertSame($result['id'], $nameArr[0]);
        $this->assertSame($result['id'], $pathNameArr[0]);
    }

    /**
     * 引数のS3パスにファイルが存在しない場合、例外が投げられることをテスト
     *
     * @test
     */
    public function createLatestPhotoInfoIncludingUuid_withException()
    {
        $oldS3Path = 'old/test.jpeg';
        Storage::shouldReceive('disk')->with('s3')->andReturn($s3Disk = Mockery::mock(FilesystemAdapter::class));
        $s3Disk->shouldReceive('exists')->once()->with($oldS3Path)->andReturnFalse();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("Photo file not found. Path: $oldS3Path");

        $this->baseService->createLatestPhotoInfoIncludingUuid($oldS3Path);
    }

    /**
     * Storageとモデルのメソッドが適切に呼び出されているかテスト
     *
     * @test
     */
    public function movePhotoToNewFolder()
    {
        Config::set('const.PHOTO.GENRE_FILE_URL.1', 'new/');

        $fileName = 'test.jpeg';
        $oldS3Path = 'old/' . $fileName;
        $genre = 1;
        $file = UploadedFile::fake()->image($fileName);
        $content = 'test';

        Storage::shouldReceive('disk')->with('s3')->andReturn($s3Disk = Mockery::mock(FilesystemAdapter::class));

        $s3Disk->shouldReceive('get')->once()->with($oldS3Path)->andReturn($content);
        $s3Disk->shouldReceive('putFileAs')->once()->with('new/', $file, $fileName, 'public');
        $s3Disk->shouldReceive('delete')->once()->with($oldS3Path)->andReturnTrue();

        $this->baseService
            ->shouldReceive('downloadS3PhotoToLocal')
            ->once()
            ->with($fileName, $content)
            ->andReturn($file);

        $result = $this->baseService->movePhotoToNewFolder($oldS3Path, $fileName, $genre);

        $this->assertTrue($result);
    }

    /**
     * ローカルにファイルをダウンロードし、そのファイルを適切に返却できるかテスト
     *
     * @test
     */
    public function downloadS3PhotoToLocal()
    {
        $disk = Storage::fake('public');
        $fileName = 'test.jpeg';
        $content = 'test';

        $file = $this->baseService->downloadS3PhotoToLocal($fileName, $content);

        $disk->assertExists('local/' . $fileName);
        $this->assertSame('jpeg', $file->getExtension());
        $this->assertSame($fileName, $file->getClientOriginalName());
        $this->assertSame($disk->path('local/' . $fileName), $file->getPathname());
    }

    /**
     * Storageのメソッドが適切に呼び出されるかテスト
     *
     * @test
     */
    public function deleteAllLocalPhoto()
    {
        Storage::shouldReceive('disk')->with('public')->andReturn($publicDisk = Mockery::mock(FilesystemAdapter::class));
        $publicDisk->shouldReceive('deleteDirectory')->once()->with('local/')->andReturnTrue();

        $result = $this->baseService->deleteAllLocalPhoto();

        $this->assertTrue($result);
    }

}
