<?php

declare(strict_types=1);

namespace Tests\Unit\Commands;

use Mockery;
use Exception;
use Tests\TestCase;
use App\Models\Photo;
use App\Services\PhotoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CheckConsistencyBetweenDBAndS3Test extends TestCase
{
    /**
     * @test
     * @dataProvider providerHandle
     * @param $prepare
     * @param $expect
     */
    public function handle($prepare, $expect)
    {
        $this->app->instance(
            PhotoService::class,
            Mockery::mock(PhotoService::class, function ($mock) use ($prepare) {
                $mock->shouldReceive('deletePhotoIfDuplicate')
                    ->times($prepare['deletePhotoIfDuplicate']['times'])
                    ->with($prepare['deletePhotoIfDuplicate']['with'])
                    ->andReturn($prepare['deletePhotoIfDuplicate']['return']);
                $mock->shouldReceive('deleteMultiplePhotosIfDuplicate')
                    ->times($prepare['deleteMultiplePhotosIfDuplicate']['times'])
                    ->andReturn($prepare['deleteMultiplePhotosIfDuplicate']['return']);
            })
        );

        DB::shouldReceive('beginTransaction')
            ->times($prepare['beginTransaction']['times']);

        DB::shouldReceive('commit')
            ->times($prepare['commit']['times']);

        DB::shouldReceive('rollBack')
            ->times($prepare['rollBack']['times']);

        if (!isset($expect['message2'])) {
            $this->artisan(
                'command:checkDBandS3',
                ['fileName' => $prepare['fileName'], '--all' => $prepare['all']]
            )->expectsOutput($expect['message1']);
        } elseif (!isset($expect['message3'])) {
            $this->artisan(
                'command:checkDBandS3',
                ['fileName' => $prepare['fileName'], '--all' => $prepare['all']]
            )->expectsOutput($expect['message1'])->expectsOutput($expect['message2']);
        } else {
            $this->artisan(
                'command:checkDBandS3',
                ['fileName' => $prepare['fileName'], '--all' => $prepare['all']]
            )->expectsOutput($expect['message1'])
                ->expectsOutput($expect['message2'])
                ->expectsOutput($expect['message3']);
        }
    }

    /**
     * @test
     */
    public function handleWithException()
    {
        $this->app->instance(
            PhotoService::class,
            Mockery::mock(PhotoService::class, function ($mock) {
                $mock->shouldReceive('deletePhotoIfDuplicate')
                    ->once()
                    ->with('test.jpeg')
                    ->andThrow(new Exception('例外発生！'));
                $mock->shouldReceive('deleteMultiplePhotosIfDuplicate')
                    ->never()
                    ->andReturn(null);
            })
        );

        DB::shouldReceive('beginTransaction')
            ->once();

        DB::shouldReceive('commit')
            ->never();

        DB::shouldReceive('rollBack')
            ->once();

        $this->artisan(
            'command:checkDBandS3',
            ['fileName' => 'test.jpeg', '--all' => false]
        )
            ->expectsOutput("例外発生！");
    }

    public function providerHandle(): array
    {
        return [
            '正常(ファイル名あり・全て検索なし)' => [
                'prepare' => [
                    'fileName' => 'test.jpeg',
                    'all' => false,
                    'deletePhotoIfDuplicate' => [
                        'times' => 1,
                        'with' => 'test.jpeg',
                        'return' => ['deleteFile' => 'id01.test.jpeg', 'count' => 1],
                    ],
                    'deleteMultiplePhotosIfDuplicate' => [
                        'times' => 0,
                        'return' => null,
                    ],
                    'beginTransaction' => [
                        'times' => 1
                    ],
                    'commit' => [
                        'times' => 1
                    ],
                    'rollBack' => [
                        'times' => 0,
                    ]
                ],
                'expect' => [
                    'message1' => "The duplicate file 'id01.test.jpeg' is successfully deleted.\n" .
                        "The number of deleted files is 1.",
                ],
            ],
            '正常(ファイル名なし・全件検索あり/削除ファイル１つ)' => [
                'prepare' => [
                    'fileName' => null,
                    'all' => true,
                    'deletePhotoIfDuplicate' => [
                        'times' => 0,
                        'with' => null,
                        'return' => null,
                    ],
                    'deleteMultiplePhotosIfDuplicate' => [
                        'times' => 1,
                        'return' => new Collection([new Photo(['file_name' => 'id01.test.jpeg'])]),
                    ],
                    'beginTransaction' => [
                        'times' => 1
                    ],
                    'commit' => [
                        'times' => 1
                    ],
                    'rollBack' => [
                        'times' => 0,
                    ]
                ],
                'expect' => [
                    'message1' => "The 1 files are completely deleted from S3 and DB because of duplication.",
                    'message2' => "The duplicate file id01.test.jpeg is successfully deleted."
                ],
            ],
            '正常(ファイル名なし・全件検索あり/削除ファイル2つ)' => [
                'prepare' => [
                    'fileName' => null,
                    'all' => true,
                    'deletePhotoIfDuplicate' => [
                        'times' => 0,
                        'with' => null,
                        'return' => null,
                    ],
                    'deleteMultiplePhotosIfDuplicate' => [
                        'times' => 1,
                        'return' => new Collection([
                                                       new Photo(['file_name' => 'id01.test.jpeg']),
                                                       new Photo(['file_name' => 'id02.test.jpeg'])
                                                   ]),
                    ],
                    'beginTransaction' => [
                        'times' => 1
                    ],
                    'commit' => [
                        'times' => 1
                    ],
                    'rollBack' => [
                        'times' => 0,
                    ]
                ],
                'expect' => [
                    'message1' => "The 2 files are completely deleted from S3 and DB because of duplication.",
                    'message2' => "The duplicate file id01.test.jpeg is successfully deleted.",
                    'message3' => "The duplicate file id02.test.jpeg is successfully deleted."
                ],
            ],
            'エラー(ファイル名あり・全件検索あり)' => [
                'prepare' => [
                    'fileName' => 'test.jpeg',
                    'all' => true,
                    'deletePhotoIfDuplicate' => [
                        'times' => 0,
                        'with' => null,
                        'return' => null,
                    ],
                    'deleteMultiplePhotosIfDuplicate' => [
                        'times' => 0,
                        'return' => null,
                    ],
                    'beginTransaction' => [
                        'times' => 0
                    ],
                    'commit' => [
                        'times' => 0
                    ],
                    'rollBack' => [
                        'times' => 0,
                    ]
                ],
                'expect' => [
                    'message1' => "You cannot select specific file name when you put '--all' option.",
                ],
            ],
            'エラー(ファイル名なし・全件検索なし)' => [
                'prepare' => [
                    'fileName' => null,
                    'all' => false,
                    'deletePhotoIfDuplicate' => [
                        'times' => 0,
                        'with' => null,
                        'return' => null,
                    ],
                    'deleteMultiplePhotosIfDuplicate' => [
                        'times' => 0,
                        'return' => null,
                    ],
                    'beginTransaction' => [
                        'times' => 0
                    ],
                    'commit' => [
                        'times' => 0
                    ],
                    'rollBack' => [
                        'times' => 0,
                    ]
                ],
                'expect' => [
                    'message1' => "You have to choose either putting 'fileName' or '--all' option in the command.",
                ],
            ],
        ];
    }
}
