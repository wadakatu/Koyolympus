<?php

namespace App\Console\Commands;

use App\Http\Services\PhotoService;
use Error;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReplaceAllPhotoInfoToIncludeUuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:includeUuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'make all photo data including uuid.(id, file_name, file_path)';

    private $photoService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PhotoService $photoService)
    {
        parent::__construct();
        $this->photoService = $photoService;
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $this->info('UUID置換処理開始');

        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\n");
        try {
            DB::beginTransaction();
            $this->photoService->includeUuidInRecord($progressBar);
            DB::commit();
            $progressBar->finish();
            return;
        } catch (Error | Exception $e) {
            DB::rollBack();
            report($e);
            $this->error(get_class($e) . '：' . $e->getMessage());
            $this->error('例外発生');
            return;
        } finally {
            $this->photoService->deleteAllLocalPhoto('/local/');
            $this->info('UUID置換処理終了');
        }
    }
}
