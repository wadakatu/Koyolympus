<?php
declare(strict_types=1);

namespace Tests\Unit\Services\ReplaceUuid;

use App\Exceptions\Model\ModelUpdateFailedException;
use App\Exceptions\S3\S3MoveFailedException;
use App\Http\Models\Photo;
use App\Http\Services\ReplaceUuid\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Str;
use Tests\TestCase;

class BaseServiceTest extends TestCase
{
    private $photo;
    private $baseService;

    protected function setUp(): void
    {
        parent::setUp();
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
        $oldPath = 'old/test.jpeg';
        $genre = 1;
        $path = 'test/test.jpeg';
        $id = 'id_test';

        $newInfo = ['path' => $path];

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
            ->with($oldPath, $path, $genre)
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
                    ]
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
        $oldPath = 'old/test.jpeg';
        $genre = 1;
        $path = 'test/test.jpeg';
        $id = 'id_test';

        $newInfo = ['path' => $path];

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
            ->with($oldPath, $path, $genre)
            ->andReturn($params['movePhotoToNewFolder']['return']);
        $photo->shouldReceive('update')
            ->times($params['updatePhoto']['times'])
            ->with($newInfo)
            ->andReturn($params['updatePhoto']['return']);

        $this->expectException($expected);

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
                'expected' => S3MoveFailedException::class
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
                'expected' => ModelUpdateFailedException::class
            ]
        ];
    }

}
