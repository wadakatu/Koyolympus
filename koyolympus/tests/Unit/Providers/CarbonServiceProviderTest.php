<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use Carbon\Carbon;
use Tests\TestCase;
use Carbon\CarbonImmutable;

class CarbonServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * @test
     * @dataProvider providerLastWeek
     */
    public function startOfLastWeek($dayOfWeekInt, $date)
    {
        $carbon = CarbonImmutable::parse($date);

        $this->assertSame($dayOfWeekInt, $carbon->dayOfWeek);

        $result = Carbon::startOfLastWeek($carbon);

        Carbon::setLocale('ja');
        $dayOfWeek = $result->isoFormat('dddd');

        $this->assertSame('2020-12-27', $result->toDateString());
        $this->assertSame(0, $result->dayOfWeek);
        $this->assertSame('日曜日', $dayOfWeek);
    }

    /**
     * @test
     * @dataProvider providerLastWeek
     */
    public function endOfLastWeek($dayOfWeekInt, $date)
    {
        $carbon = CarbonImmutable::parse($date);

        $this->assertSame($dayOfWeekInt, $carbon->dayOfWeek);

        $result = Carbon::endOfLastWeek($carbon);

        Carbon::setLocale('ja');
        $dayOfWeek = $result->isoFormat('dddd');

        $this->assertSame('2021-01-02', $result->toDateString());
        $this->assertSame(6, $result->dayOfWeek);
        $this->assertSame('土曜日', $dayOfWeek);
    }

    public function providerLastWeek()
    {
        return [
            'Sunday' => [
                'dayOfWeek' => 0,
                'carbon' => '2021-01-03',
            ],
            'Monday' => [
                'dayOfWeek' => 1,
                'carbon' => '2021-01-04',
            ],
            'Tuesday' => [
                'dayOfWeek' => 2,
                'carbon' => '2021-01-05',
            ],
            'Wednesday' => [
                'dayOfWeek' => 3,
                'carbon' => '2021-01-06',
            ],
            'Thursday' => [
                'dayOfWeek' => 4,
                'carbon' => '2021-01-07',
            ],
            'Friday' => [
                'dayOfWeek' => 5,
                'carbon' => '2021-01-08',
            ],
            'Saturday' => [
                'dayOfWeek' => 6,
                'carbon' => '2021-01-09',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider providerStartOfLastMonth
     */
    public function startOfLastMonth($date, $expected)
    {
        $carbon = CarbonImmutable::parse($date);

        $result = Carbon::startOfLastMonth($carbon);

        $this->assertSame($expected, $result->toDateString());
    }

    public function providerStartOfLastMonth(): array
    {
        return [
            '1日' => [
                'carbon' => '2021-01-01',
                'expected' => '2020-12-01',
            ],
            '15日' => [
                'carbon' => '2021-01-15',
                'expected' => '2020-12-01',
            ],
            '30日' => [
                'carbon' => '2021-01-30',
                'expected' => '2020-12-01',
            ],
            '31日' => [
                'carbon' => '2021-01-31',
                'expected' => '2020-12-01',
            ],
            '28日・閏年ではない' => [
                'carbon' => '2021-02-28',
                'expected' => '2021-01-01',
            ],
            '29日・閏年' => [
                'carbon' => '2020-02-29',
                'expected' => '2020-01-01',
            ]
        ];
    }

    /**
     * @test
     * @dataProvider providerEndOfLastMonth
     */
    public function endOfLastMonth($date, $expected)
    {
        $carbon = CarbonImmutable::parse($date);

        $result = Carbon::endOfLastMonth($carbon);

        $this->assertSame($expected, $result->toDateString());
    }

    public function providerEndOfLastMonth(): array
    {
        return [
            '1日' => [
                'carbon' => '2021-01-01',
                'expected' => '2020-12-31',
            ],
            '15日' => [
                'carbon' => '2021-01-15',
                'expected' => '2020-12-31',
            ],
            '30日' => [
                'carbon' => '2021-01-30',
                'expected' => '2020-12-31',
            ],
            '31日' => [
                'carbon' => '2021-01-31',
                'expected' => '2020-12-31',
            ],
            '2月・閏年でない' => [
                'carbon' => '2021-03-31',
                'expected' => '2021-02-28',
            ],
            '2月・閏年' => [
                'carbon' => '2020-03-31',
                'expected' => '2020-02-29',
            ]
        ];
    }

    /**
     * @test
     */
    public function isFirstDayOfMonth()
    {
        $firstDayCarbon = CarbonImmutable::parse('2021-01-01');
        $notFirstDayCarbon = CarbonImmutable::parse('2021-01-02');

        $this->assertTrue(Carbon::isFirstDayOfMonth($firstDayCarbon));
        $this->assertFalse(Carbon::isFirstDayOfMonth($notFirstDayCarbon));
    }
}
