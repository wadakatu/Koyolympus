<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Log;
use Exception;
use Illuminate\Console\Command;
use App\Http\Services\LikeService;

class LikeAggregation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:aggregate_like';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $likeService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(LikeService $likeService)
    {
        parent::__construct();

        $this->likeService = $likeService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('[いいね集計バッチ] START');
        try {
            $this->likeService->setCommandStartAt();
            $this->likeService->aggregateLikeDaily();
            $this->likeService->aggregateLikeWeekly();
        } catch (Exception $e) {
            Log::error('[いいね集計バッチ] ' . $e->getMessage());
            return 1;
        }

        Log::info('[いいね集計バッチ] FINISH');
        return 0;
    }
}
