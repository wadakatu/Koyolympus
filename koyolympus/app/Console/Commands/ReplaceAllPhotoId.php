<?php

namespace App\Console\Commands;

use App\Http\Services\PhotoService;
use DB;
use Error;
use Exception;
use Illuminate\Console\Command;

class ReplaceAllPhotoId extends Command
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
     */
    public function handle()
    {
        $this->info('ID置換開始');

        try {
            DB::beginTransaction();
            $this->photoService->includeUuidFromIdToFilePath();
            DB::commit();
            return;
        } catch (Error | Exception $e) {
            DB::rollBack();
            report($e);
            $this->error(get_class($e) . '：' . $e->getMessage());
            $this->error('例外発生');
            return;
        } finally {
            $this->info('ID置換終了');
        }
    }
}
