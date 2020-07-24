<?php


namespace App\libraries\starrez_irio_sync;

use App\libraries\starrez_irio_sync\starrez\MySqlSyncDataAccumulator;
use App\libraries\starrez_irio_sync\starrez\StarrezApiDataIterator;
use App\libraries\starrez_irio_sync\starrez\SyncDataAccumulator;
use Illuminate\Support\ServiceProvider;

class StarrezIrioSyncServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->when(StarrezIrioDataSynchronizer::class)
            ->needs(\Iterator::class)
            ->give(function ($app) {
                return $app->make(StarrezApiDataIterator::class);
            });

        $this->app->when(StarrezIrioDataSynchronizer::class)
            ->needs(SyncDataAccumulator::class)
            ->give(function ($app) {
                return $app->make(MySqlSyncDataAccumulator::class);
            });
    }
}
