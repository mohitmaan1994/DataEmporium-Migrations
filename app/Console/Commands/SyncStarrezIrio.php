<?php

namespace App\Console\Commands;

use App\libraries\starrez_irio_sync\StarrezIrioDataSynchronizer;
use Illuminate\Console\Command;

class SyncStarrezIrio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:irio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs Irio data with Starrez data';

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
     * @param StarrezIrioDataSynchronizer $starrezIrioDataSynchronizer
     * @return void
     */
    public function handle(StarrezIrioDataSynchronizer $starrezIrioDataSynchronizer)
    {
        $starrezIrioDataSynchronizer->synchronize();
    }
}
