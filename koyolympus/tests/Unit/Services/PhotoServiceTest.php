<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Http\Models\Photo;
use App\Http\Services\PhotoService;
use Config;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Str;
use Tests\TestCase;

class PhotoServiceTest extends TestCase
{

    private $photoService;
    private $photo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->photo = $this->app->instance(Photo::class, Mockery::mock(Photo::class));
        $this->photoService = $this->app->instance(PhotoService::class,
            Mockery::mock(PhotoService::class, [$this->photo])->makePartial());
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

        $actual = $this->photoService->getAllPhoto($genre);

        $this->assertInstanceOf(LengthAwarePaginator::class, $actual);
    }

    public function providerGetAllPhoto(): array
    {
        return [
            'ジャンルなし' => [
                'genre' => null,
            ],
            'ジャンルあり' => [
                'genre' => 1
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
     */
    public function uploadPhotoToS3()
    {
        Storage::fake('s3');

        $genre = 1;
        $fileName = 'fake.jpeg';
        $filePath = '/photo/testUpload';
        $expectedUniqueFileName = 'testUnique.jpeg';
        $file = UploadedFile::fake()->image($fileName);
        Config::set("const.PHOTO.GENRE_FILE_URL.$genre", $filePath);

        $this->photo
            ->shouldReceive('createPhotoInfo')
            ->once()
            ->with($fileName, $filePath, $genre)
            ->andReturn($expectedUniqueFileName);

        $uniqueFileName = $this->photoService->uploadPhotoToS3($file, $fileName, $genre);

        $this->assertSame($expectedUniqueFileName, $uniqueFileName);
        Storage::disk('s3')->assertExists("$filePath/$expectedUniqueFileName");
    }

    /**
     * @test
     */
    public function deletePhotoFromS3()
    {
        Storage::fake('s3');

        $genre = 1;
        $fileName = 'fake.jpeg';
        $filePath = '/photo/testDelete';
        $expectedUniqueFileName = 'testUnique.jpeg';
        Config::set("const.PHOTO.GENRE_FILE_URL.$genre", $filePath);
        $file = UploadedFile::fake()->image($fileName);

        Storage::disk('s3')->putFileAs($filePath, $file, $expectedUniqueFileName, 'public');

        $this->photo
            ->shouldReceive('deletePhotoInfo')
            ->once()
            ->with($expectedUniqueFileName);

        $this->photoService->deletePhotoFromS3($expectedUniqueFileName, $genre);

        Storage::disk('s3')->assertMissing("$filePath/$expectedUniqueFileName");
    }

    /**
     * @test
     * @dataProvider providerDeleteMultiplePhotosIfDuplicate
     * @param $prepare
     * @param $expect
     */
    public function deleteMultiplePhotosIfDuplicate($prepare, $expect)
    {
        $this->photoService->shouldReceive('searchMultipleDuplicatePhotos')
            ->once()
            ->andReturn($prepare['searchMultipleDuplicatePhotos']['return']);

        $this->photoService->shouldReceive('deletePhotoFromS3')
            ->times($prepare['deletePhotoFromS3']['times'])
            ->with($prepare['deletePhotoFromS3']['with']['file_name'], $prepare['deletePhotoFromS3']['with']['genre']);

        $this->photo->shouldReceive('deletePhotoInfo')
            ->times($prepare['deletePhotoInfo']['times'])
            ->with($prepare['deletePhotoInfo']['with']['file_name']);

        $actual = $this->photoService->deleteMultiplePhotosIfDuplicate();

        $this->assertEquals($expect, $actual);
    }

    public function providerDeleteMultiplePhotosIfDuplicate(): array
    {
        return [
            '重複ファイル１つ' => [
                'prepare' => [
                    'searchMultipleDuplicatePhotos' => [
                        'return' => new Collection([
                            new Photo([
                                'id' => 'aaaa',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ])
                        ])
                    ],
                    'deletePhotoFromS3' => [
                        'times' => 1,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                            'genre' => 1,
                        ]
                    ],
                    'deletePhotoInfo' => [
                        'times' => 1,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                        ]
                    ],
                ],
                'expect' => new Collection([
                    0 => new Photo([
                        'id' => 'aaaa',
                        'file_name' => 'fake.jpeg',
                        'genre' => 1
                    ])
                ]),
            ],
            '重複ファイル複数' => [
                'prepare' => [
                    'searchMultipleDuplicatePhotos' => [
                        'return' => new Collection([
                            new Photo(['id' => 'aaaa', 'file_name' => 'fake.jpeg', 'genre' => 1]),
                            new Photo(['id' => 'bbbb', 'file_name' => 'fake.jpeg', 'genre' => 1])
                        ])
                    ],
                    'deletePhotoFromS3' => [
                        'times' => 2,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                            'genre' => 1,
                        ]
                    ],
                    'deletePhotoInfo' => [
                        'times' => 2,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                        ]
                    ],
                ],
                'expect' => new Collection([
                    0 => new Photo([
                        'id' => 'aaaa',
                        'file_name' => 'fake.jpeg',
                        'genre' => 1
                    ]),
                    1 => new Photo([
                        'id' => 'bbbb',
                        'file_name' => 'fake.jpeg',
                        'genre' => 1
                    ])
                ]),
            ],
        ];
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotos_duplicateTwoRecordsAboutOnePhoto()
    {
        $this->photo->shouldReceive('getAllPhotos')
            ->once()
            ->andReturn(new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => 'id01.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => 'id02.fake2.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => 'id03.fake3.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id04',
                    'file_name' => 'id04.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:01'
                ]),
            ]));

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertEquals(new Collection([
            new Photo([
                'id' => 'id01',
                'file_name' => 'id01.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:00'
            ])
        ]), $actualPhotoList);
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotos_duplicateOneRecordAboutOnePhoto()
    {
        $this->photo->shouldReceive('getAllPhotos')
            ->once()
            ->andReturn(new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:03'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => '2.fake2.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => '3.fake3.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id04',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:01'
                ]),
                new Photo([
                    'id' => 'id05',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:02'
                ]),
            ]));

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertEquals(new Collection([
            new Photo([
                'id' => 'id04',
                'file_name' => '1.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:01'
            ]),
            new Photo([
                'id' => 'id05',
                'file_name' => '1.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:02'
            ]),
        ]), $actualPhotoList);
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotos_duplicateTwoEachRecordsAboutTwoPhotos()
    {
        $this->photo->shouldReceive('getAllPhotos')
            ->once()
            ->andReturn(new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => '2.fake2.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => '3.fake3.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id04',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:01'
                ]),
                new Photo([
                    'id' => 'id05',
                    'file_name' => '3.fake3.jpeg',
                    'created_at' => '2021-01-02 00:00:01'
                ]),
            ]));

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertEquals(new Collection([
            new Photo([
                'id' => 'id01',
                'file_name' => '1.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:00'
            ]),
            new Photo([
                'id' => 'id03',
                'file_name' => '3.fake3.jpeg',
                'created_at' => '2021-01-01 00:00:00'
            ]),
        ]), $actualPhotoList);
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotos_duplicateThreeEachRecordsAboutTwoPhotos()
    {
        $this->photo->shouldReceive('getAllPhotos')
            ->once()
            ->andReturn(new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:03'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => '2.fake2.jpeg',
                    'created_at' => '2021-01-05 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => '3.fake3.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id04',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:01'
                ]),
                new Photo([
                    'id' => 'id05',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:02'
                ]),
                new Photo([
                    'id' => 'id06',
                    'file_name' => '2.fake2.jpeg',
                    'created_at' => '2021-01-04 00:00:01'
                ]),
                new Photo([
                    'id' => 'id07',
                    'file_name' => '2.fake2.jpeg',
                    'created_at' => '2021-01-02 00:00:02'
                ]),
            ]));

        $actualPhotoList = $this->photoService->searchMultipleDuplicatePhotos();

        $this->assertEquals(new Collection([
            new Photo([
                'id' => 'id04',
                'file_name' => '1.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:01'
            ]),
            new Photo([
                'id' => 'id05',
                'file_name' => '1.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:02'
            ]),
            new Photo([
                'id' => 'id06',
                'file_name' => '2.fake2.jpeg',
                'created_at' => '2021-01-04 00:00:01'
            ]),
            new Photo([
                'id' => 'id07',
                'file_name' => '2.fake2.jpeg',
                'created_at' => '2021-01-02 00:00:02'
            ]),
        ]), $actualPhotoList);
    }

    /**
     * @test
     */
    public function searchMultipleDuplicatePhotos_withException()
    {
        $this->photo->shouldReceive('getAllPhotos')
            ->once()
            ->andReturn(new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => '1.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => '2.fake2.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => '3.fake3.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
            ]));

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('There is no duplicate file in the database.');

        $this->photoService->searchMultipleDuplicatePhotos();
    }

    /**
     * @test
     * @dataProvider providerDeletePhotoIfDuplicate
     * @param $prepare
     * @param $expect
     */
    public
    function deletePhotoIfDuplicate(
        $prepare,
        $expect
    ) {
        $this->photoService = Mockery::mock(PhotoService::class, [$this->photo])->makePartial();

        $this->photo->shouldReceive('getAllPhotos')
            ->once()
            ->andReturn($prepare['searchDuplicatePhoto']['with']['getAllPhotos']);

        $this->photoService->shouldReceive('searchDuplicatePhoto')
            ->once()
            ->with(
                $prepare['searchDuplicatePhoto']['with']['getAllPhotos'],
                $prepare['searchDuplicatePhoto']['with']['fileName']
            )->andReturn($prepare['searchDuplicatePhoto']['return']);

        $this->photoService->shouldReceive('deletePhotoFromS3')
            ->times($prepare['deletePhotoFromS3']['times'])
            ->with($prepare['deletePhotoFromS3']['with']['file_name'], $prepare['deletePhotoFromS3']['with']['genre']);

        $this->photo->shouldReceive('deletePhotoInfo')
            ->times($prepare['deletePhotoInfo']['times'])
            ->with($prepare['deletePhotoInfo']['with']['file_name']);

        $actual = $this->photoService->deletePhotoIfDuplicate($prepare['file_name']);

        $this->assertSame($expect, $actual);
    }

    public
    function providerDeletePhotoIfDuplicate()
    {
        return [
            '重複レコード１件' => [
                'prepare' => [
                    'file_name' => 'fake.jpeg',
                    'searchDuplicatePhoto' => [
                        'with' => [
                            'getAllPhotos' => new Collection([
                                new Photo([
                                    'id' => 'id01',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 1
                                ]),
                                new Photo([
                                    'id' => 'id02',
                                    'file_name' => 'fake2.jpeg',
                                    'genre' => 2
                                ])
                            ]),
                            'fileName' => 'fake.jpeg',
                        ],
                        'return' => new Collection([
                            new Photo([
                                'id' => 'id01',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                        ]),
                    ],
                    'deletePhotoFromS3' => [
                        'times' => 1,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                            'genre' => 1
                        ]
                    ],
                    'deletePhotoInfo' => [
                        'times' => 1,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                        ]
                    ],
                ],
                'expect' => [
                    'deleteFile' => 'fake.jpeg',
                    'count' => 1
                ]
            ],
            '重複レコード３件' => [
                'prepare' => [
                    'file_name' => 'fake.jpeg',
                    'searchDuplicatePhoto' => [
                        'with' => [
                            'getAllPhotos' => new Collection([
                                new Photo([
                                    'id' => 'id01',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 1
                                ]),
                                new Photo([
                                    'id' => 'id02',
                                    'file_name' => 'fake2.jpeg',
                                    'genre' => 2
                                ]),
                                new Photo([
                                    'id' => 'id03',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 1
                                ]),
                                new Photo([
                                    'id' => 'id04',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 2
                                ])
                            ]),
                            'fileName' => 'fake.jpeg',
                        ],
                        'return' => new Collection([
                            new Photo([
                                'id' => 'id01',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                            new Photo([
                                'id' => 'id03',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                            new Photo([
                                'id' => 'id04',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ])
                        ]),
                    ],
                    'deletePhotoFromS3' => [
                        'times' => 3,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                            'genre' => 1
                        ]
                    ],
                    'deletePhotoInfo' => [
                        'times' => 3,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                        ]
                    ],
                ],
                'expect' => [
                    'deleteFile' => 'fake.jpeg',
                    'count' => 3
                ],
            ],
            '重複レコード５件' => [
                'prepare' => [
                    'file_name' => 'fake.jpeg',
                    'searchDuplicatePhoto' => [
                        'with' => [
                            'getAllPhotos' => new Collection([
                                new Photo([
                                    'id' => 'id01',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 1
                                ]),
                                new Photo([
                                    'id' => 'id02',
                                    'file_name' => 'fake2.jpeg',
                                    'genre' => 2
                                ]),
                                new Photo([
                                    'id' => 'id03',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 1
                                ]),
                                new Photo([
                                    'id' => 'id04',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 2
                                ]),
                                new Photo([
                                    'id' => 'id05',
                                    'file_name' => 'fake5.jpeg',
                                    'genre' => 2
                                ]),
                                new Photo([
                                    'id' => 'id06',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 1
                                ]),
                                new Photo([
                                    'id' => 'id07',
                                    'file_name' => 'fake.jpeg',
                                    'genre' => 1
                                ]),
                            ]),
                            'fileName' => 'fake.jpeg',
                        ],
                        'return' => new Collection([
                            new Photo([
                                'id' => 'id01',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                            new Photo([
                                'id' => 'id03',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                            new Photo([
                                'id' => 'id04',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                            new Photo([
                                'id' => 'id06',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                            new Photo([
                                'id' => 'id07',
                                'file_name' => 'fake.jpeg',
                                'genre' => 1
                            ]),
                        ]),
                    ],
                    'deletePhotoFromS3' => [
                        'times' => 5,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                            'genre' => 1
                        ]
                    ],
                    'deletePhotoInfo' => [
                        'times' => 5,
                        'with' => [
                            'file_name' => 'fake.jpeg',
                        ]
                    ],
                ],
                'expect' => [
                    'deleteFile' => 'fake.jpeg',
                    'count' => 5
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function searchDuplicatePhoto_duplicateTwoRecords()
    {
        $actual = $this->photoService->searchDuplicatePhoto(
            new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => 'id01.fake1.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => 'id02.fake2.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => 'id03.fake3.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id04',
                    'file_name' => 'id04.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ])
            ]), 'fake1.jpeg');

        $this->assertEquals(new Collection([
            1 => new Photo([
                'id' => 'id04',
                'file_name' => 'id04.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:00'
            ])
        ]), $actual);
    }

    /**
     * @test
     */
    public function searchDuplicatePhoto_duplicateThreeRecords()
    {
        $actual = $this->photoService->searchDuplicatePhoto(
            new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => 'id01.fake1.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => 'id02.fake2.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => 'id03.fake3.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id04',
                    'file_name' => 'id04.fake1.jpeg',
                    'created_at' => '2021-01-01 00:00:00'
                ]),
                new Photo([
                    'id' => 'id05',
                    'file_name' => 'id05.fake1.jpeg',
                    'created_at' => '2020-12-31 00:00:00'
                ])
            ]), 'fake1.jpeg');

        $this->assertEquals(new Collection([
            1 => new Photo([
                'id' => 'id04',
                'file_name' => 'id04.fake1.jpeg',
                'created_at' => '2021-01-01 00:00:00'
            ]),
            2 => new Photo([
                'id' => 'id05',
                'file_name' => 'id05.fake1.jpeg',
                'created_at' => '2020-12-31 00:00:00'
            ])
        ]), $actual);
    }

    /**
     * @test
     */
    public function searchDuplicatePhoto_oneElementInCollection()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('There is no duplicate file in the database.');

        $this->photoService->searchDuplicatePhoto(
            new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => 'id01.fake1.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => 'id02.fake2.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => 'id03.fake3.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
            ]), 'fake1.jpeg');
    }

    /**
     * @test
     */
    public function searchDuplicatePhoto_emptyCollection()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('There is no duplicate file in the database.');

        $this->photoService->searchDuplicatePhoto(
            new Collection([
                new Photo([
                    'id' => 'id01',
                    'file_name' => 'id01.fake1.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id02',
                    'file_name' => 'id02.fake2.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
                new Photo([
                    'id' => 'id03',
                    'file_name' => 'id03.fake3.jpeg',
                    'created_at' => '2021-01-02 00:00:00'
                ]),
            ]), 'fake1.jpeg');
    }
}
