<?php

namespace App\Console\Commands;

use App\libraries\ems_famis_sync\EmsFamisSynchronizer;
use Illuminate\Console\Command;

class SyncStarrezIrio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:emsfamis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs EMS data with FAMIS';

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
     * @param EmsFamisSynchronizer $EmsFamisSynchronizer
     * @return void
     */
    public function handle(EmsFamisSynchronizer $EmsFamisSynchronizer)
    {
        //$EmsFamisSynchronizer->synchronize();
        $EmsFamisSynchronizer->uploadfiles();
    }
}
