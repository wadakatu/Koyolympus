<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Config;
use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\Like;
use App\Models\Photo;
use App\Services\PhotoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PhotoServiceTest extends TestCase
{
    private $photoService;
    private $photo;
    private $like;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photo = Mockery::mock(Photo::class);
        $this->like = Mockery::mock(Like::class);
        $this->photoService = Mockery::mock(PhotoService::class, [$this->photo, $this->like])->makePartial();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider providerGetAllPhoto
     * @param $genre
     */
    public function getAllPhoto($genre)
    {
        $this->photo->shouldReceive('getAllPhoto')
            ->once()
            ->with($genre)
            ->andReturn(Mockery::mock(LengthAwarePaginator::class));

        $this->photoService->getAllPhoto($genre);
    }

    public function providerGetAllPhoto(): array
    {
        return [
            'ジャンルなし' => [
                'genre' => null,
            ],
            'ジャンルあり' => [
                'genre' => '1'
            ],
        ];
    }

    /**
     * @test
     */
    public function getAllPhotoRandomly()
    {
        $this->photo
            ->shouldReceive('getAllPhotoRandomly')
            ->once()
            ->withNoArgs()
            ->andReturn(Collect([]));

        $this->photoService->getAllPhotoRandomly();
    }

    /**
     * @test
     * @param $genre
     * @param $filePath
     * @dataProvider providerUploadPhotoDataToDB
     */
    public function uploadPhotoDataToDB($genre, $filePath)
    {
        $fileName = 'photo.jpeg';
        $uniqueFileName = 'uniquePhoto.jpeg';

        $this->photo
            ->shouldReceive('createPhotoInfo')
            ->once()
            ->with($fileName, $filePath, $genre)
            ->andReturn($uniqueFileName);

        $result = $this->photoService->uploadPhotoDataToDB($fileName, $genre);

        $this->assertSame($uniqueFileName, $result);
    }

    public function providerUploadPhotoDataToDB(): array
    {
        return [
            'genre landscape' => [
                'genre' => 1,
                'filePath' => 'photo/landscape'
            ],
            'genre animal' => [
                'genre' => 2,
                'filePath' => 'photo/animal'
            ],
            'genre portrait' => [
                'genre' => 3,
                'filePath' => 'photo/portrait'
            ],
            'genre snapshot' => [
                'genre' => 4,
                'filePath' => 'photo/others/snapshot'
            ],
            'genre livecomposite' => [
                'genre' => 5,
                'filePath' => 'photo/others/livecomposite'
            ],
            'genre pinfilm' => [
                'genre' => 6,
                'filePath' => 'photo/others/pinfilm'
            ],
        ];
    }


    /**
     * @test
     */
    public function uploadPhotoToS3()
    {
        $genre = 1;
        $fileName = 'fake.jpeg';
        $filePath = '/photo/testUpload';
        $expectedUniqueFileName = 'testUnique.jpeg';
        $file = UploadedFile::fake()->image($fileName);
        Config::set("const.PHOTO.GENRE_FILE_URL.$genre", $filePath);

        Storage::shouldReceive('disk')->once()->with('s3')->andReturn(
            $s3Disk = Mockery::mock(FilesystemAdapter::class)
        );
        $s3Disk->shouldReceive('putFileAs')->once()->with($filePath, $file, $expectedUniqueFileName, 'public');

        $this->photoService->uploadPhotoToS3($file, $expectedUniqueFileName, $genre);
    }

    /**
     * @test
     */
    public function deletePhotoFromS3()
    {
        $genre = 1;
        $filePath = '/photo/testDelete';
        $fileName = 'test.jpeg';
        Config::set("const.PHOTO.GENRE_FILE_URL.$genre", $filePath);

        Storage::shouldReceive('disk')->once()->with('s3')->andReturn(
            $s3Disk = Mockery::mock(FilesystemAdapter::class)
        );
        $s3Disk->shouldReceive('delete')->once()->with($filePath . '/' . $fileName);

        $this->photoService->deletePhotoFromS3($fileName, $genre);
    }

    /**
     * @test
     */
    public function deletePhotoFromDB()
    {
        $id = '1';
        $this->photo
            ->shouldReceive('deletePhotoInfo')
            ->once()
            ->with($id);

        $this->like
            ->shouldReceive('deleteByPhotoId')
            ->once()
            ->with($id);

        $this->photoService->deletePhotoFromDB($id);
    }

    /**
     * @test
     */
    public function deleteMultiplePhotosIfDuplicateOneDuplicateFile()
    {
        $fileName = 'id1.fake.jpeg';
        $genre = 1;
        $expected = new Collection(
            [
                factory(Photo::class)->make(
                    [
                        'file_name' => $fileName,
                        'genre' => $genre
                    ]
                ),
            ]
        );

        $this->photoService
            ->shouldReceive('searchMultipleDuplicatePhotos')
            ->once()
            ->andReturn($expected);

        $this->photoService
            ->shouldReceive('deletePhotoFromS3')
            ->once()
            ->with($fileName, $genre);

        $this->photoService
            ->shouldReceive('deletePhotoFromDB')
            ->once()
            ->with('id1');

        $actual = $this->photoService->deleteMultiplePhotosIfDuplicate();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function deleteMultiplePhotosIfDuplicateMultipleDuplicateFile()
    {
        $fileName = 'id1.fake.jpeg';
        $genre = 1;
        $expected = factory(Photo::class, 2)->make(
            [
                'file_name' => $fileName,
                'genre' => $genre
            ]
        );

        $this->photoService->shouldReceive('searchMultipleDuplicatePhotos')
            ->once()
            ->andReturn($expected);

        $this->photoService->shouldReceive('deletePhotoFromS3')
            ->twice()
            ->with($fileName, $genre);

        $this->photoService
            ->shouldReceive('deletePhotoFromDB')
            ->twice()
            ->with('id1');

        $actual = $this->photoService->deleteMultiplePhotosIfDuplicate();

        $this->assertEquals($expected, $actual);
    }


    /**
     * @test
     */
    public function searchMultipleDuplicatePhotosDuplicateTwoRecordsAboutOnePhoto()
    {
        $this->photo->shouldReceive('getAllPhotoOrderByCreatedAtDesc')
            ->once()
            ->andReturn(
                new Collection(
                    [
                        factory(Photo::class)->make(
                            [
                                'id' => 'id01',
                                'file_name' => 'id01.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:01'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget = factory(Photo::class)->make(
                            [
                                'id' => 'id02',
                                'file_name' => 'id02.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id03',
                                'file_name' => 'id03.fake2.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id04',
                                'file_name' => 'id04.fake3.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                    ]
                )
            );

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertSame(1, $actualPhotoList->count());
        $this->assertEquals(new Collection([$duplicateTarget]), $actualPhotoList);
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotosDuplicateOneRecordAboutOnePhoto()
    {
        $this->photo->shouldReceive('getAllPhotoOrderByCreatedAtDesc')
            ->once()
            ->andReturn(
                new Collection(
                    [
                        factory(Photo::class)->make(
                            [
                                'id' => 'id01',
                                'file_name' => '1.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:03'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget1 = factory(Photo::class)->make(
                            [
                                'id' => 'id02',
                                'file_name' => '2.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:02'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget2 = factory(Photo::class)->make(
                            [
                                'id' => 'id03',
                                'file_name' => '3.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:01'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id04',
                                'file_name' => '4.fake2.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id05',
                                'file_name' => '5.fake3.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                    ]
                )
            );

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertSame(2, $actualPhotoList->count());
        $this->assertEquals(new Collection([$duplicateTarget1, $duplicateTarget2]), $actualPhotoList);
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotosDuplicateTwoEachRecordsAboutTwoPhotos()
    {
        $this->photo->shouldReceive('getAllPhotoOrderByCreatedAtDesc')
            ->once()
            ->andReturn(
                new Collection(
                    [
                        factory(Photo::class)->make(
                            [
                                'id' => 'id01',
                                'file_name' => '1.fake3.jpeg',
                                'created_at' => '2021-01-02 00:00:01'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id02',
                                'file_name' => '2.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:01'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget1 = factory(Photo::class)->make(
                            [
                                'id' => 'id03',
                                'file_name' => '3.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id04',
                                'file_name' => '4.fake2.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget2 = factory(Photo::class)->make(
                            [
                                'id' => 'id05',
                                'file_name' => '5.fake3.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                    ]
                )
            );

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertSame(2, $actualPhotoList->count());
        $this->assertEquals(new Collection([$duplicateTarget1, $duplicateTarget2]), $actualPhotoList);
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotosDuplicateThreeEachRecordsAboutTwoPhotos()
    {
        $this->photo->shouldReceive('getAllPhotoOrderByCreatedAtDesc')
            ->once()
            ->andReturn(
                new Collection(
                    [
                        factory(Photo::class)->make(
                            [
                                'id' => 'id01',
                                'file_name' => '1.fake2.jpeg',
                                'created_at' => '2021-01-05 00:00:00'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget1 = factory(Photo::class)->make(
                            [
                                'id' => 'id02',
                                'file_name' => '2.fake2.jpeg',
                                'created_at' => '2021-01-04 00:00:01'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget2 = factory(Photo::class)->make(
                            [
                                'id' => 'id03',
                                'file_name' => '3.fake2.jpeg',
                                'created_at' => '2021-01-02 00:00:02'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id04',
                                'file_name' => '4.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:03'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget3 = factory(Photo::class)->make(
                            [
                                'id' => 'id05',
                                'file_name' => '5.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:02'
                            ]
                        ),
                        //duplicate record.
                        $duplicateTarget4 = factory(Photo::class)->make(
                            [
                                'id' => 'id06',
                                'file_name' => '6.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:01'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id07',
                                'file_name' => '7.fake3.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                    ]
                )
            );

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertEquals(
            new Collection([
                               $duplicateTarget1,
                               $duplicateTarget2,
                               $duplicateTarget3,
                               $duplicateTarget4
                           ]),
            $actualPhotoList
        );
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotosWithError()
    {
        $this->photo->shouldReceive('getAllPhotoOrderByCreatedAtDesc')
            ->once()
            ->andReturn(
                new Collection(
                    [
                        factory(Photo::class)->make(
                            [
                                'id' => 'id01',
                                'file_name' => '1.fake2.jpeg',
                                'created_at' => '2021-01-05 00:00:00'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id02',
                                'file_name' => '2.fake1.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                        factory(Photo::class)->make(
                            [
                                'id' => 'id03',
                                'file_name' => '3.fake3.jpeg',
                                'created_at' => '2021-01-01 00:00:00'
                            ]
                        ),
                    ]
                )
            );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no duplicate file in the database.');

        $this->photoService->searchMultipleDuplicatePhotos();
    }

    /**
     * @test
     */
    public function deletePhotoIfDuplicateWithOneDuplicate()
    {
        $fileName = 'id1.fake.jpeg';
        $genre = 1;
        $allPhotos = new Collection(
            [
                $duplicateTarget = factory(Photo::class)->make(
                    [
                        'id' => 'id01',
                        'file_name' => $fileName,
                        'genre' => $genre
                    ]
                ),
                factory(Photo::class)->make(
                    [
                        'id' => 'id02',
                        'file_name' => 'id2.fake2.jpeg',
                        'genre' => 2
                    ]
                ),
            ]
        );
        $duplicateList = new Collection([$duplicateTarget]);

        $this->photo
            ->shouldReceive('getAllPhotoOrderByCreatedAtDesc')
            ->once()
            ->andReturn($allPhotos);

        $this->photoService
            ->shouldReceive('searchDuplicatePhoto')
            ->once()
            ->with(
                $allPhotos,
                $fileName,
            )->andReturn($duplicateList);

        $this->photoService
            ->shouldReceive('deletePhotoFromS3')
            ->once()
            ->with($fileName, $genre);

        $this->photoService
            ->shouldReceive('deletePhotoFromDB')
            ->once()
            ->with('id1');

        $actual = $this->photoService->deletePhotoIfDuplicate($fileName);

        $this->assertSame(['deleteFile' => $fileName, 'count' => 1], $actual);
    }

    /**
     * @test
     */
    public function deletePhotoIfDuplicateWithThreeDuplicates()
    {
        $fileName = 'id1.fake.jpeg';
        $genre = 1;
        $allPhotos = new Collection(
            [
                $duplicateTarget1 = factory(Photo::class)->make(
                    [
                        'id' => 'id01',
                        'file_name' => $fileName,
                        'genre' => $genre,
                    ]
                ),
                factory(Photo::class)->make(
                    [
                        'id' => 'id02',
                        'file_name' => 'fake2.jpeg',
                        'genre' => 2
                    ]
                ),
                $duplicateTarget2 = factory(Photo::class)->make(
                    [
                        'id' => 'id03',
                        'file_name' => $fileName,
                        'genre' => $genre,
                    ]
                ),
                $duplicateTarget3 = factory(Photo::class)->make(
                    [
                        'id' => 'id04',
                        'file_name' => $fileName,
                        'genre' => $genre,
                    ]
                ),
            ]
        );
        $duplicateList = new Collection([$duplicateTarget1, $duplicateTarget2, $duplicateTarget3]);

        $this->photo->shouldReceive('getAllPhotoOrderByCreatedAtDesc')
            ->once()
            ->andReturn($allPhotos);

        $this->photoService->shouldReceive('searchDuplicatePhoto')
            ->once()
            ->with(
                $allPhotos,
                $fileName,
            )->andReturn($duplicateList);

        $this->photoService->shouldReceive('deletePhotoFromS3')
            ->times(3)
            ->with($fileName, $genre);

        $this->photoService
            ->shouldReceive('deletePhotoFromDB')
            ->times(3)
            ->with('id1');

        $actual = $this->photoService->deletePhotoIfDuplicate($fileName);

        $this->assertSame(['deleteFile' => $fileName, 'count' => 3], $actual);
    }

    /**
     * @test
     */
    public function searchDuplicatePhotoDuplicateTwoRecords()
    {
        $actual = $this->photoService->searchDuplicatePhoto(
            new Collection(
                [
                    factory(Photo::class)->make(
                        [
                            'id' => 'id01',
                            'file_name' => 'id01.fake1.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    factory(Photo::class)->make(
                        [
                            'id' => 'id02',
                            'file_name' => 'id02.fake2.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    factory(Photo::class)->make(
                        [
                            'id' => 'id03',
                            'file_name' => 'id03.fake3.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    //duplicate record.
                    $duplicateTarget = factory(Photo::class)->make(
                        [
                            'id' => 'id04',
                            'file_name' => 'id04.fake1.jpeg',
                            'created_at' => '2021-01-01 00:00:00'
                        ]
                    ),
                ]
            ),
            'fake1.jpeg'
        );

        $this->assertEquals(new Collection([1 => $duplicateTarget,]), $actual);
    }

    /**
     * @test
     */
    public function searchDuplicatePhotoDuplicateThreeRecords()
    {
        $actual = $this->photoService->searchDuplicatePhoto(
            new Collection(
                [
                    factory(Photo::class)->make(
                        [
                            'id' => 'id01',
                            'file_name' => 'id01.fake1.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    factory(Photo::class)->make(
                        [
                            'id' => 'id02',
                            'file_name' => 'id02.fake2.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    factory(Photo::class)->make(
                        [
                            'id' => 'id03',
                            'file_name' => 'id03.fake3.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    $duplicateTarget1 = factory(Photo::class)->make(
                        [
                            'id' => 'id04',
                            'file_name' => 'id04.fake1.jpeg',
                            'created_at' => '2021-01-01 00:00:00'
                        ]
                    ),
                    $duplicateTarget2 = factory(Photo::class)->make(
                        [
                            'id' => 'id05',
                            'file_name' => 'id05.fake1.jpeg',
                            'created_at' => '2020-12-31 00:00:00'
                        ]
                    ),
                ]
            ),
            'fake1.jpeg'
        );

        $this->assertEquals(
            new Collection(
                [
                    1 => $duplicateTarget1,
                    2 => $duplicateTarget2
                ]
            ),
            $actual
        );
    }

    /**
     * @test
     */
    public function searchDuplicatePhotoOneElementInCollection()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no duplicate file in the database.');

        $this->photoService->searchDuplicatePhoto(
            new Collection(
                [
                    factory(Photo::class)->make(
                        [
                            'id' => 'id01',
                            'file_name' => 'id01.fake1.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    factory(Photo::class)->make(
                        [
                            'id' => 'id02',
                            'file_name' => 'id02.fake2.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                    factory(Photo::class)->make(
                        [
                            'id' => 'id03',
                            'file_name' => 'id03.fake3.jpeg',
                            'created_at' => '2021-01-02 00:00:00'
                        ]
                    ),
                ]
            ),
            'fake1.jpeg'
        );
    }

    /**
     * @test
     */
    public function searchDuplicatePhotoEmptyCollection()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There is no duplicate file in the database.');

        $this->photoService->searchDuplicatePhoto(new Collection([]), 'fake1.jpeg');
    }
}
