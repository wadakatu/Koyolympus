<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Like;
use Carbon\CarbonImmutable;
use App\Models\LikeAggregate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeAggregateTest extends TestCase
{
    use RefreshDatabase;

    private $likeAggregate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->likeAggregate = new LikeAggregate();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider providerScopeForAggregation
     */
    public function scopeForAggregation($params, $expected)
    {
        LikeAggregate::factory()->create($params['like_aggregates']);

        $result = $this->likeAggregate
            ->query()
            ->forAggregation(
                CarbonImmutable::parse($params['start_at']),
                CarbonImmutable::parse($params['end_at']),
                $params['aggregate_type']
            )
            ->get();

        $this->assertSame($expected['count'], $result->count());
    }

    public function providerScopeForAggregation(): array
    {
        return [
            'success(date)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 0,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 1,
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-02',
                ],
                'expected' => [
                    'count' => 1,
                ]
            ],
            'success(dateTime_1sec)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 0,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 1,
                    'start_at' => '2021-01-01 00:00:01',
                    'end_at' => '2021-01-02 00:00:01',
                ],
                'expected' => [
                    'count' => 1,
                ]
            ],
            'success(dateTime_endOfDay)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 0,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 1,
                    'start_at' => '2021-01-01 23:59:59',
                    'end_at' => '2021-01-02 23:59:59',
                ],
                'expected' => [
                    'count' => 1,
                ]
            ],
            'success(startAt & endAt same date)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 0,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-02',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 1,
                    'start_at' => '2021-01-02',
                    'end_at' => '2021-01-02',
                ],
                'expected' => [
                    'count' => 1,
                ]
            ],
            'failed(status incomplete)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 1,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 1,
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-02',
                ],
                'expected' => [
                    'count' => 0,
                ]
            ],
            'failed(wrong aggregate_type)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 0,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 2,
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-02',
                ],
                'expected' => [
                    'count' => 0,
                ]
            ],
            'failed(start_at out of range)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 0,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 2,
                    'start_at' => '2021-01-02',
                    'end_at' => '2021-01-02',
                ],
                'expected' => [
                    'count' => 0,
                ]
            ],
            'failed(end_at out of range)' => [
                'params' => [
                    'like_aggregates' => [
                        'status' => 0,
                        'aggregate_type' => 1,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-02',
                    ],
                    'aggregate_type' => 2,
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-01',
                ],
                'expected' => [
                    'count' => 0,
                ]
            ],
        ];
    }

    /**
     * @test
     */
    public function scopeAddSelectWhenDailyAndDiffMonthTrue()
    {
        $startAt = CarbonImmutable::parse('2021-01-31');
        $endAt = CarbonImmutable::parse('2021-02-06');
        $type = 1;

        LikeAggregate::factory()->create(['start_at' => '2021-05-01']);

        $query = $this->likeAggregate->addSelectWhenDailyAndDiffMonth($startAt, $endAt, $type);

        $result = $query->get();
        $sql = $query->toSql();

        $this->assertArrayHasKey('carry_over', $result[0]->toArray());
        $this->assertSame(5, $result[0]->carry_over);
        $this->assertSame('select month(start_at) as carry_over from `like_aggregates`', $sql);
    }

    /**
     * @test
     * @dataProvider providerScopeAddSelectWhenDailyAndDiffMonthFalse
     */
    public function scopeAddSelectWhenDailyAndDiffMonthFalse($params)
    {
        $startAt = CarbonImmutable::parse($params['start_at']);
        $endAt = CarbonImmutable::parse($params['end_at']);

        LikeAggregate::factory()->create(['start_at' => '2021-05-01']);

        $result = $this->likeAggregate->addSelectWhenDailyAndDiffMonth($startAt, $endAt, $params['type'])->get();

        $this->assertArrayNotHasKey('carry_over', $result[0]->toArray());
    }

    public function providerScopeAddSelectWhenDailyAndDiffMonthFalse(): array
    {
        return [
            'weekly && diff month' => [
                'params' => [
                    'start_at' => '2021-01-31',
                    'end_at' => '2021-02-06',
                    'type' => 2
                ],
            ],
            'monthly && diff month' => [
                'params' => [
                    'start_at' => '2021-01-31',
                    'end_at' => '2021-02-06',
                    'type' => 3
                ],
            ],
            'daily && same month' => [
                'params' => [
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-07',
                    'type' => 1
                ],
            ],
            'weekly && same month' => [
                'params' => [
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-07',
                    'type' => 2
                ],
            ],
            'monthly && same month' => [
                'params' => [
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-07',
                    'type' => 3
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providerGetForAggregationSingle
     */
    public function getForAggregationSingle($params, $expected)
    {
        Like::factory()->create($params['likes']);
        LikeAggregate::factory()->create($params['like_aggregates']);

        $result = $this->likeAggregate->getForAggregation(
            CarbonImmutable::parse($params['start_at']),
            CarbonImmutable::parse($params['end_at']),
            $params['type']
        );

        $this->assertSame(1, $result->count());
        $this->assertSame($expected, $result->first()->toArray());
        $this->assertIsInt($result->first()->likes);
    }


    public function providerGetForAggregationSingle(): array
    {
        return [
            'success_first_of_week' => [
                'params' => [
                    'likes' => [
                        'photo_id' => 'success_first_of_week',
                    ],
                    'like_aggregates' => [
                        'photo_id' => 'success_first_of_week',
                        'aggregate_type' => 1,
                        'status' => 0,
                        'likes' => 10,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-01',
                    ],
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-07',
                    'type' => 1
                ],
                'expected' => [
                    'photo_id' => 'success_first_of_week',
                    'likes' => 10,
                ],
            ],
            'success_end_of_week' => [
                'params' => [
                    'likes' => [
                        'photo_id' => 'success_end_of_week',
                    ],
                    'like_aggregates' => [
                        'photo_id' => 'success_end_of_week',
                        'aggregate_type' => 1,
                        'status' => 0,
                        'likes' => 11,
                        'start_at' => '2021-01-07',
                        'end_at' => '2021-01-07',
                    ],
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-07',
                    'type' => 1
                ],
                'expected' => [
                    'photo_id' => 'success_end_of_week',
                    'likes' => 11,
                ],
            ],
            'success_first_of_month' => [
                'params' => [
                    'likes' => [
                        'photo_id' => 'success_first_of_month',
                    ],
                    'like_aggregates' => [
                        'photo_id' => 'success_first_of_month',
                        'aggregate_type' => 2,
                        'status' => 0,
                        'likes' => 12,
                        'start_at' => '2021-01-01',
                        'end_at' => '2021-01-07',
                    ],
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-31',
                    'type' => 2
                ],
                'expected' => [
                    'photo_id' => 'success_first_of_month',
                    'likes' => 12,
                ],
            ],
            'success_end_of_month' => [
                'params' => [
                    'likes' => [
                        'photo_id' => 'success_end_of_month',
                    ],
                    'like_aggregates' => [
                        'photo_id' => 'success_end_of_month',
                        'aggregate_type' => 2,
                        'status' => 0,
                        'likes' => 13,
                        'start_at' => '2021-01-25',
                        'end_at' => '2021-01-31',
                    ],
                    'start_at' => '2021-01-01',
                    'end_at' => '2021-01-31',
                    'type' => 2
                ],
                'expected' => [
                    'photo_id' => 'success_end_of_month',
                    'likes' => 13,
                ],
            ],
            'success_end_of_month_leap_year' => [
                'params' => [
                    'likes' => [
                        'photo_id' => 'success_end_of_month_leap_year',
                    ],
                    'like_aggregates' => [
                        'photo_id' => 'success_end_of_month_leap_year',
                        'aggregate_type' => 2,
                        'status' => 0,
                        'likes' => 14,
                        'start_at' => '2020-02-23',
                        'end_at' => '2020-02-29',
                    ],
                    'start_at' => '2020-02-01',
                    'end_at' => '2020-02-29',
                    'type' => 2
                ],
                'expected' => [
                    'photo_id' => 'success_end_of_month_leap_year',
                    'likes' => 14,
                ],
            ],
            'success_end_of_month_not_leap_year' => [
                'params' => [
                    'likes' => [
                        'photo_id' => 'success_end_of_month_not_leap_year',
                    ],
                    'like_aggregates' => [
                        'photo_id' => 'success_end_of_month_not_leap_year',
                        'aggregate_type' => 2,
                        'status' => 0,
                        'likes' => 15,
                        'start_at' => '2020-02-23',
                        'end_at' => '2020-02-29',
                    ],
                    'start_at' => '2020-02-01',
                    'end_at' => '2020-02-29',
                    'type' => 2
                ],
                'expected' => [
                    'photo_id' => 'success_end_of_month_not_leap_year',
                    'likes' => 15,
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function getForAggregationSum()
    {
        $photoIdForSum = 'test_sum';

        Like::factory()->create([
                                         'photo_id' => $photoIdForSum,
                                     ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photoIdForSum,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 20,
                                                  'start_at' => '2021-01-08',
                                                  'end_at' => '2021-01-14',
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photoIdForSum,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 30,
                                                  'start_at' => '2021-01-15',
                                                  'end_at' => '2021-01-21',
                                              ]);

        $result = $this->likeAggregate->getForAggregation(
            CarbonImmutable::parse('2021-01-01'),
            CarbonImmutable::parse('2021-01-31'),
            2
        );

        $this->assertSame(1, $result->count());
        $this->assertSame([
                              'photo_id' => $photoIdForSum,
                              'likes' => 50,
                          ], $result->where('photo_id', $photoIdForSum)->first()->toArray());
        $this->assertIsInt($result->where('photo_id', $photoIdForSum)->first()->likes);
    }

    /**
     * @test
     */
    public function getForAggregationWeeklySameMonth()
    {
        $photo1 = 'photo1';
        $photo2 = 'photo2';
        $photo3 = 'photo3';

        Like::factory()->create([
                                         'photo_id' => $photo1
                                     ]);
        Like::factory()->create([
                                         'photo_id' => $photo2
                                     ]);

        //１週間全部のレコード
        for ($i = 1; $i <= 7; $i++) {
            LikeAggregate::factory()->create([
                                                      'photo_id' => $photo1,
                                                      'aggregate_type' => 1,
                                                      'status' => 0,
                                                      'likes' => $i,
                                                      'start_at' => "2021-01-0$i",
                                                      'end_at' => "2021-01-0$i",
                                                  ]);
        }

        //1週間の内三日分
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo2,
                                                  'aggregate_type' => 1,
                                                  'status' => 0,
                                                  'likes' => 1,
                                                  'start_at' => '2021-01-01',
                                                  'end_at' => '2021-01-01',
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo2,
                                                  'aggregate_type' => 1,
                                                  'status' => 0,
                                                  'likes' => 4,
                                                  'start_at' => '2021-01-04',
                                                  'end_at' => '2021-01-04',
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo2,
                                                  'aggregate_type' => 1,
                                                  'status' => 0,
                                                  'likes' => 7,
                                                  'start_at' => '2021-01-07',
                                                  'end_at' => '2021-01-07',
                                              ]);

        //取得できない（join先なし）
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo3,
                                                  'aggregate_type' => 1,
                                                  'status' => 0,
                                                  'likes' => 10,
                                                  'start_at' => '2021-01-01',
                                                  'end_at' => '2021-01-01',
                                              ]);

        $result = $this->likeAggregate->getForAggregation(
            CarbonImmutable::parse('2021-01-01'),
            CarbonImmutable::parse('2021-01-07'),
            1
        );

        $this->assertSame(2, $result->count());
        $this->assertSame(
            ['photo_id' => $photo1, 'likes' => 28],
            $result->where('photo_id', $photo1)->first()->toArray()
        );
        $this->assertSame(
            ['photo_id' => $photo2, 'likes' => 12],
            $result->where('photo_id', $photo2)->first()->toArray()
        );
        $this->assertIsInt($result->where('photo_id', $photo1)->first()->likes);
        $this->assertIsInt($result->where('photo_id', $photo2)->first()->likes);
        $this->assertNull($result->where('photo_id', $photo3)->first());
    }

    /**
     * @test
     */
    public function getForAggregationWeeklyDiffMonth()
    {
        $photo1 = 'photo1';

        Like::factory()->create([
                                         'photo_id' => $photo1
                                     ]);

        //１週間全部のレコード
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo1,
                                                  'aggregate_type' => 1,
                                                  'status' => 0,
                                                  'likes' => 1,
                                                  'start_at' => '2021-02-27',
                                                  'end_at' => '2021-02-27',
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo1,
                                                  'aggregate_type' => 1,
                                                  'status' => 0,
                                                  'likes' => 3,
                                                  'start_at' => '2021-02-28',
                                                  'end_at' => '2021-02-28',
                                              ]);
        for ($i = 1; $i <= 5; $i++) {
            LikeAggregate::factory()->create([
                                                      'photo_id' => $photo1,
                                                      'aggregate_type' => 1,
                                                      'status' => 0,
                                                      'likes' => $i,
                                                      'start_at' => "2021-03-0$i",
                                                      'end_at' => "2021-03-0$i",
                                                  ]);
        }

        $result = $this->likeAggregate->getForAggregation(
            CarbonImmutable::parse('2021-02-27'),
            CarbonImmutable::parse('2021-03-05'),
            1
        );

        $this->assertSame(2, $result->count());
        $this->assertSame(4, $result->where('carry_over', '=', 2)->first()->likes);
        $this->assertSame(15, $result->where('carry_over', '=', 3)->first()->likes);
        $this->assertSame(2, $result->where('likes', '=', 4)->first()->carry_over);
        $this->assertSame(3, $result->where('likes', '=', 15)->first()->carry_over);
        $this->assertIsInt($result->where('carry_over', '=', 2)->first()->likes);
        $this->assertIsInt($result->where('carry_over', '=', 3)->first()->likes);
    }

    /**
     * @test
     */
    public function getForAggregationMonthly()
    {
        $photo1 = 'photo1';
        $photo2 = 'photo2';
        $photo3 = 'photo3';

        Like::factory()->create([
                                         'photo_id' => $photo1
                                     ]);
        Like::factory()->create([
                                         'photo_id' => $photo2
                                     ]);

        //1ヶ月分の全週間レコード（週の初め1日パターン）
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo1,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 1,
                                                  'start_at' => "2021-01-01",
                                                  'end_at' => "2021-01-07",
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo1,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 2,
                                                  'start_at' => "2021-01-08",
                                                  'end_at' => "2021-01-14",
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo1,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 3,
                                                  'start_at' => "2021-01-15",
                                                  'end_at' => "2021-01-21",
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo1,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 4,
                                                  'start_at' => "2021-01-22",
                                                  'end_at' => "2021-01-28",
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo1,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 5,
                                                  'start_at' => "2021-01-29",
                                                  'end_at' => "2021-01-31",
                                              ]);

        //３週間弱分レコード（週の初めが前月パターン）
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo2,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 2,
                                                  'start_at' => "2021-01-01",
                                                  'end_at' => "2021-01-03",
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo2,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 4,
                                                  'start_at' => "2021-01-04",
                                                  'end_at' => "2021-01-10",
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo2,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 6,
                                                  'start_at' => "2021-01-18",
                                                  'end_at' => "2021-01-24",
                                              ]);

        //取得できない（join先なし）
        LikeAggregate::factory()->create([
                                                  'photo_id' => $photo3,
                                                  'aggregate_type' => 2,
                                                  'status' => 0,
                                                  'likes' => 10,
                                                  'start_at' => '2021-01-01',
                                                  'end_at' => '2021-01-01',
                                              ]);

        $result = $this->likeAggregate->getForAggregation(
            CarbonImmutable::parse('2020-12-31'),
            CarbonImmutable::parse('2021-01-31'),
            2
        );

        $this->assertSame(2, $result->count());
        $this->assertSame(
            ['photo_id' => $photo1, 'likes' => 15],
            $result->where('photo_id', $photo1)->first()->toArray()
        );
        $this->assertSame(
            ['photo_id' => $photo2, 'likes' => 12],
            $result->where('photo_id', $photo2)->first()->toArray()
        );
        $this->assertIsInt($result->where('photo_id', $photo1)->first()->likes);
        $this->assertIsInt($result->where('photo_id', $photo2)->first()->likes);
        $this->assertNull($result->where('photo_id', $photo3)->first());
    }

    /**
     * @test
     */
    public function getForAggregationGroupBy()
    {
        $photo1 = Like::factory()->create();
        $photo2 = Like::factory()->create();

        LikeAggregate::factory()->count(2)->create(
            [
                'photo_id' => $photo1->photo_id,
                'start_at' => '2021-01-01',
                'end_at' => '2021-01-31',
                'likes' => 10,
                'aggregate_type' => 1,
                'status' => 0
            ]
        );
        LikeAggregate::factory()->create(
            [
                'photo_id' => $photo2->photo_id,
                'start_at' => '2021-02-01',
                'end_at' => '2021-02-28',
                'likes' => 20,
                'aggregate_type' => 1,
                'status' => 0
            ]
        );
        LikeAggregate::factory()->create(
            [
                'photo_id' => $photo2->photo_id,
                'start_at' => '2021-03-01',
                'end_at' => '2021-03-31',
                'likes' => 30,
                'aggregate_type' => 1,
                'status' => 0
            ]
        );

        $result = $this->likeAggregate->getForAggregation(
            CarbonImmutable::parse('2021-01-01'),
            CarbonImmutable::parse('2021-03-31'),
            1
        );

        $this->assertSame(1, $result->where('photo_id', $photo1->photo_id)->count());
        $this->assertSame(20, $result->where('photo_id', $photo1->photo_id)->first()->likes);
        $this->assertSame(2, $result->where('photo_id', $photo2->photo_id)->count());
        $this->assertContains(20, $result->where('photo_id', $photo2->photo_id)->pluck('likes'));
        $this->assertContains(30, $result->where('photo_id', $photo2->photo_id)->pluck('likes'));
    }

    /**
     * @test
     */
    public function registerForAggregation()
    {
        $photoId = 'test_id';
        $type = 1;
        $likes = 100;
        $startAt = '2021-01-01';
        $endAt = '2021-01-02';

        $params = [
            'photo_id' => $photoId,
            'aggregate_type' => $type,
            'likes' => $likes,
            'start_at' => $startAt,
            'end_at' => $endAt
        ];

        $this->assertDatabaseMissing('like_aggregates', $params);

        $this->likeAggregate->registerForAggregation(
            ['photo_id' => $photoId, 'likes' => $likes],
            CarbonImmutable::parse($startAt),
            CarbonImmutable::parse($endAt),
            $type
        );

        $this->assertDatabaseHas('like_aggregates', $params);
    }

    /**
     * @test
     */
    public function updateForAggregation()
    {
        $targetId = 'targetPhotoId';
        $notTargetId = 'notTargetPhotoId';
        $startAt = CarbonImmutable::parse('2021-01-01');
        $endAt = CarbonImmutable::parse('2021-01-01');
        $type = 1;

        LikeAggregate::factory()->create([
                                                  'photo_id' => $targetId,
                                                  'aggregate_type' => 1,
                                                  'likes' => 10,
                                                  'status' => 0,
                                                  'start_at' => '2021-01-01',
                                                  'end_at' => '2021-01-01',
                                              ]);
        LikeAggregate::factory()->create([
                                                  'photo_id' => $notTargetId,
                                                  'aggregate_type' => 1,
                                                  'likes' => 10,
                                                  'status' => 0,
                                                  'start_at' => '2021-01-01',
                                                  'end_at' => '2021-01-01',
                                              ]);

        $this->assertDatabaseHas('like_aggregates', [
            'photo_id' => $targetId,
            'aggregate_type' => 1,
            'likes' => 10,
            'status' => 0,
            'start_at' => '2021-01-01',
            'end_at' => '2021-01-01',
        ]);
        $this->assertDatabaseHas('like_aggregates', [
            'photo_id' => $notTargetId,
            'aggregate_type' => 1,
            'likes' => 10,
            'status' => 0,
            'start_at' => '2021-01-01',
            'end_at' => '2021-01-01',
        ]);

        $updateParams = [
            'aggregate_type' => 2,
            'likes' => 100,
            'status' => 1,
            'start_at' => '2021-02-01',
            'end_at' => '2021-02-28',
        ];

        $this->likeAggregate->updateForAggregation($targetId, $startAt, $endAt, $type, $updateParams);

        //更新後のデータがあることを確認
        $this->assertDatabaseHas('like_aggregates', [
            'photo_id' => $targetId,
            'aggregate_type' => 2,
            'likes' => 100,
            'status' => 1,
            'start_at' => '2021-02-01',
            'end_at' => '2021-02-28',
        ]);
        //ID違いのため更新なし（そのまま）
        $this->assertDatabaseHas('like_aggregates', [
            'photo_id' => $notTargetId,
            'aggregate_type' => 1,
            'likes' => 10,
            'status' => 0,
            'start_at' => '2021-01-01',
            'end_at' => '2021-01-01',
        ]);
        //変更前のデータがないことを確認
        $this->assertDatabaseMissing('like_aggregates', [
            'photo_id' => $targetId,
            'aggregate_type' => 1,
            'likes' => 10,
            'status' => 0,
            'start_at' => '2021-01-01',
            'end_at' => '2021-01-01',
        ]);
    }
}
