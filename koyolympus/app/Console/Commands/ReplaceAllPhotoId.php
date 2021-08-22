<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReplaceAllPhotoId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:replaceId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'replace all photo id to uuid.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('hello, world');
    }
}
