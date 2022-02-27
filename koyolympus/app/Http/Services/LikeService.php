<?php
declare(strict_types=1);

namespace App\Http\Services;

use DB;
use Exception;
use Carbon\Carbon;
use ExceptionMail;
use App\Traits\LogTrait;
use App\Http\Models\Like;
use Carbon\CarbonImmutable;
use App\Mail\ThrowableMail;
use App\Http\Models\LikeAggregate;
use Illuminate\Support\Facades\Mail;

class LikeService
{
    use LogTrait;

    private $like;

    private $likeAggregate;

    /* @var CarbonImmutable */
    private $startAt;

    /* @var int */
    private $dailyType;

    /* @var int */
    private $weeklyType;

    /* @var int */
    private $monthlyType;

    public function __construct(Like $like, LikeAggregate $likeAggregate)
    {
        $this->like = $like;
        $this->likeAggregate = $likeAggregate;

        $this->dailyType = config('const.PHOTO_AGGREGATION.TYPE.DAILY');
        $this->weeklyType = config('const.PHOTO_AGGREGATION.TYPE.WEEKLY');
        $this->monthlyType = config('const.PHOTO_AGGREGATION.TYPE.MONTHLY');
    }


    public function setCommandStartAt(CarbonImmutable $carbon): void
    {
        $this->startAt = $carbon;
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeDaily(): void
    {
        $targetRecords = $this->like->getForDailyAggregation();

        if ($targetRecords->isEmpty()) {
            $this->outputLog('[いいね集計・日次]', '集計対象０件のためスキップ');
            return;
        }

        $this->outputLog('[いいね集計・日次]', '日次いいね集計 START');
        foreach ($targetRecords->toArray() as $record) {
            DB::beginTransaction();
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->registerAggregatedLike($record, $this->startAt, $this->startAt, $this->dailyType);
                $this->like->saveById($record['id'], ['likes' => 0]);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->outputErrorLog('[いいね集計・日次]', "例外発生　対象：$photoId");
                throw $e;
            }
        }

        $this->outputLog('[いいね集計・日次]', '日次いいね集計 END');
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeWeekly(): void
    {
        if (!$this->startAt->isSunday()) {
            Carbon::setLocale('ja');
            $dayOfWeek = $this->startAt->isoFormat('dddd');
            $this->outputLog('[いいね集計・週次]', "本日 $dayOfWeek なのでスキップ");
            return;
        }

        $startOfLastWeek = Carbon::startOfLastWeek($this->startAt);
        $endOfLastWeek = Carbon::endOfLastWeek($this->startAt);

        $targetRecords = $this->likeAggregate->getForAggregation($startOfLastWeek, $endOfLastWeek, $this->dailyType);

        $this->outputLog('[いいね集計・週次]', '週次いいね集計 START');
        foreach ($targetRecords->toArray() as $record) {
            DB::beginTransaction();
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->registerAggregatedLike($record, $startOfLastWeek, $endOfLastWeek,
                    $this->weeklyType);
                $this->like->find($record['id'], ['week_likes' => $record['likes']]);
                $this->likeAggregate->saveById(
                    $record['like_aggregate_id'],
                    ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
                );
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->outputErrorLog('[いいね集計・週次]', "対象：$photoId");
                throw $e;
            }
        }

        $this->outputLog('[いいね集計・週次]', '週次いいね集計 END');
    }

    /**
     * @throws Exception
     */
    public function aggregateLikeMonthly(): void
    {
        if (!Carbon::isFirstDayOfMonth($this->startAt)) {
            $day = $this->startAt->day;
            $this->outputLog('[いいね集計・月次]', "本日 $day 日なのでスキップ");
            return;
        }

        $startOfLastMonth = Carbon::startOfLastMonth($this->startAt);
        $endOfLastMonth = Carbon::endOfLastMonth($this->startAt);

        $targetRecords = $this->likeAggregate->getForAggregation($startOfLastMonth, $endOfLastMonth, $this->weeklyType);

        $this->outputLog('[いいね集計・月次]', '月次いいね集計 START');
        foreach ($targetRecords->toArray() as $record) {
            DB::beginTransaction();
            $photoId = $record['photo_id'];
            try {
                $this->likeAggregate->registerAggregatedLike($record, $startOfLastMonth, $endOfLastMonth,
                    $this->monthlyType);
                $this->like->saveById($record['id'], ['month_likes' => $record['likes']]);
                $this->likeAggregate->saveById(
                    $record['like_aggregate_id'],
                    ['status' => config('const.PHOTO_AGGREGATION.STATUS.COMPLETE')]
                );
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $this->outputErrorLog('[いいね集計・月次]', "例外発生　対象：$photoId");
                throw $e;
            }
        }

        $this->outputLog('[いいね集計・月次]', '月次いいね集計 END');
    }

    public function sendThrowableMail(string $subject, string $message)
    {
        $params = [
            'subject' => $subject,
            'message' => $message,
            'startAt' => Carbon::now()->toDateTimeString()
        ];

        Mail::to(config('const.MAIL'))->send(new ThrowableMail($params));
    }

}
