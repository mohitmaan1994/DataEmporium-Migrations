<?php


namespace App\libraries\starrez_irio_sync\starrez;


use App\libraries\starrez_irio_sync\models\SyncEntity;
use Illuminate\Support\Facades\Log;
use stdClass;

class MySqlSyncDataAccumulator implements SyncDataAccumulator
{
    public function clearData()
    {
        Log::channel('starrez-irio-sync')->info('clearData():- Truncating current records');

        SyncEntity::query()->truncate();

        Log::channel('starrez-irio-sync')->info('clearData():- Current records truncated');
    }

    /**
     * @param SyncEntity[]
     */
    public function accumulate(array $filteredData)
    {
        $filteredDataFormattedForInsert = array_map(function ($syncRecord) {
            return $this->syncEntityFormatterForDbInsertion($syncRecord);
        }, $filteredData);

        SyncEntity::query()->insert($filteredDataFormattedForInsert);
    }

    /**
     * @return SyncEntity[]
     */
    public function getAccumulatedData()
    {
        $fetchedDataFromDb = SyncEntity::all();
        return array_map(function ($dbRecord) {
            return $this->formatDbResponseToSyncEntity($dbRecord);
        }, $fetchedDataFromDb->toArray());
    }

    /**
     * @param SyncEntity $syncRecord
     * @return array
     */
    private function syncEntityFormatterForDbInsertion($syncRecord)
    {
        return array(
            'uin' => $syncRecord->getUin(),
            'name' => $syncRecord->getName(),
            'sms' => $syncRecord->getPhoneNumber(),
            'email' => $syncRecord->getEmail(),
            'room_number' => $syncRecord->getRoomNumber(),
            'room_location' => $syncRecord->getRoomLocation(),
            'tags' => $syncRecord->getTags()
        );
    }

    /**
     * @param stdClass $syncRecord
     * @return SyncEntity
     */
    private function formatDbResponseToSyncEntity($syncRecord)
    {
        return SyncEntity::getSyncEntityWithParams(
            $syncRecord['uin'],
            $syncRecord['name'],
            $syncRecord['sms'],
            $syncRecord['email'],
            $syncRecord['room_number'],
            $syncRecord['room_location'],
            $syncRecord['tags']
        );
    }
}
