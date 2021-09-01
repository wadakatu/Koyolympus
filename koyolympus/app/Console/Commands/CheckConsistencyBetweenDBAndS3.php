<?php

namespace App\Console\Commands;

use App\Http\Services\PhotoService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckConsistencyBetweenDBAndS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkDBandS3 {fileName?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check the consistency between DB and Aws S3 bucket';

    private $photoService;

    /**
     * Create a new command instance.
     *
     * @param PhotoService $photoService
     */
    public function __construct(PhotoService $photoService)
    {
        parent::__construct();

        $this->photoService = $photoService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle(): void
    {
        $fileName = $this->argument('fileName');
        $shouldSearchAll = $this->option('all');

        if (isset($fileName) && $shouldSearchAll) {
            $this->error("You cannot select specific file name when you put '--all' option.");
            return;
        }

        if (!isset($fileName) && !$shouldSearchAll) {
            $this->error("You have to choose either putting 'fileName' or '--all' option in the command.");
            return;
        }

        try {
            DB::beginTransaction();
            if (isset($fileName) && !$shouldSearchAll) {
                $deletedFileInfo = $this->photoService->deletePhotoIfDuplicate($fileName);
                DB::commit();
                $this->info("The duplicate file '$deletedFileInfo[deleteFile]' is successfully deleted.\nThe number of deleted files is $deletedFileInfo[count].");
                return;
            }

            if (!isset($fileName) && $shouldSearchAll) {
                $deletedFile = $this->photoService->deleteMultiplePhotosIfDuplicate();
                $deletedFileNum = $deletedFile->count();
                DB::commit();
                $this->info("The $deletedFileNum files are completely deleted from S3 and DB because of duplication.");
                foreach ($deletedFile as $photoInfo) {
                    $this->info("The duplicate file $photoInfo->file_name is successfully deleted.");
                }
                return;
            }
        } catch (\Error $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return;
        }
    }
}
