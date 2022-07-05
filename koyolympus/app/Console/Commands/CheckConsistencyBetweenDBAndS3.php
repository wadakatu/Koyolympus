<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\PhotoService;

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
     * @return int
     * @throws Exception
     */
    public function handle()
    {
        $fileName = $this->argument('fileName');
        $shouldSearchAll = $this->option('all');

        if (isset($fileName) && $shouldSearchAll) {
            $this->error("You cannot select specific file name when you put '--all' option.");
            return 1;
        }

        if (!isset($fileName) && !$shouldSearchAll) {
            $this->error("You have to choose either putting 'fileName' or '--all' option in the command.");
            return 1;
        }

        DB::beginTransaction();
        try {
            if (isset($fileName) && !$shouldSearchAll) {
                $deletedFileInfo = $this->photoService->deletePhotoIfDuplicate($fileName);
                $this->info(
                    "The duplicate file '$deletedFileInfo[deleteFile]' is successfully deleted.\n" .
                    "The number of deleted files is $deletedFileInfo[count]."
                );
            }

            if (!isset($fileName) && $shouldSearchAll) {
                $deletedFile = $this->photoService->deleteMultiplePhotosIfDuplicate();
                $deletedFileNum = $deletedFile->count();
                $this->info("The $deletedFileNum files are completely deleted from S3 and DB because of duplication.");
                foreach ($deletedFile as $photoInfo) {
                    $this->info("The duplicate file $photoInfo->file_name is successfully deleted.");
                }
            }

            DB::commit();
            return 0;
        } catch (Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return 1;
        }
    }
}
