<?php


namespace App\libraries\starrez_irio_sync\starrez;


interface SyncDataAccumulator
{
    public function clearData();

    public function accumulate(array $filteredData);

    public function getAccumulatedData();
}
