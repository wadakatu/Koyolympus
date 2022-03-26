<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Services\ReplaceUuid\BaseService;
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

    private $replaceUuIdService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BaseService $baseService)
    {
        parent::__construct();
        $this->replaceUuIdService = $baseService;
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

        try {
            DB::beginTransaction();
            $this->replaceUuIdService->includeUuidInRecord();
            DB::commit();
            return;
        } catch (Error | Exception $e) {
            DB::rollBack();
            report($e);
            $this->error(get_class($e) . '：' . $e->getMessage());
            $this->error('例外発生');
            return;
        } finally {
            $this->replaceUuIdService->deleteAllLocalPhoto();
            $this->info('UUID置換処理終了');
        }
    }
}
