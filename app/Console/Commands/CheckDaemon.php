<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDaemon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:daemon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        exec('ps aux | grep queue=download', $download);
        if (count($download) < 3) {
            exec('php artisan queue:work --queue=download --tries=2 &');
        }

        exec('ps aux | grep queue=upload', $upload);
        if (count($upload) < 3) {
            exec('php artisan queue:work --queue=upload --tries=2 &');
        }

        exec('ps aux | grep queue=preview', $preview);
        if (count($preview) < 3) {
            exec('php artisan queue:work --queue=preview --tries=2 &');
        }

        exec('ps aux | grep queue=reload', $reload);
        if (count($reload) < 3) {
            exec('php artisan queue:work --queue=reload --tries=2 &');
        }
    }
}