<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConvertLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert From ta_log to preson_log';

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
        //
    }
}
