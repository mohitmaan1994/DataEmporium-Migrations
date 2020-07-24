<?php


namespace App\libraries\starrez_irio_sync\starrez;

use App\libraries\starrez_irio_sync\models\SyncEntity;
use App\libraries\starrez_irio_sync\models\SyncEntityExtended;
use App\libraries\starrez_irio_sync\models\SyncFilterCriteria;

class SyncDataSelector
{
    const UIN_LENGTH = 9;

    private $syncFilterCriteria;

    /**
     * SyncDataSelector constructor.
     * @param SyncFilterCriteria $syncFilterCriteria
     */
    public function __construct(SyncFilterCriteria $syncFilterCriteria)
    {
        $this->syncFilterCriteria = $syncFilterCriteria;
        $this->syncFilterCriteria->populateFromRepository();
    }

    /**
     * @param SyncEntityExtended[] $fetchedData
     * @return SyncEntity[]
     */
    public function filter(array $fetchedData)
    {
        $fetchedData = $this->filterEntitiesByRoomLocation($fetchedData);
        $fetchedData = $this->filterEntitiesByTermSession($fetchedData);
        $fetchedData = $this->filterEntitiesByEntryStatusEnum($fetchedData);
        $fetchedData = $this->filterEntitiesByUinLength($fetchedData);
        return $this->getSyncEntityArray($fetchedData);
    }

    /**
     * @param SyncEntityExtended[] $fetchedData
     * @return SyncEntityExtended[]
     */
    private function filterEntitiesByRoomLocation(array $fetchedData)
    {
        return array_filter($fetchedData, function ($entity) {
            return in_array($entity->getRoomLocationID(), $this->syncFilterCriteria->getRoomLocationIds());
        });
    }

    /**
     * @param SyncEntityExtended[] $fetchedData
     * @return SyncEntityExtended[]
     */
    private function filterEntitiesByTermSession(array $fetchedData)
    {
        return array_filter($fetchedData, function ($entity) {
            return in_array($entity->getTermSessionID(), $this->syncFilterCriteria->getTermSessionIds());
        });
    }

    /**
     * @param SyncEntityExtended[] $fetchedData
     * @return SyncEntityExtended[]
     */
    private function filterEntitiesByEntryStatusEnum(array $fetchedData)
    {
        return array_filter($fetchedData, function ($entity) {
            return !is_null($entity->getBookingEntryStatus()) &&
                in_array($entity->getBookingEntryStatus()->getId(), $this->syncFilterCriteria->getEntryStatusEnums());
        });
    }

    /**
     * @param SyncEntityExtended[] $fetchedData
     * @return SyncEntityExtended[]
     */
    private function filterEntitiesByUinLength(array $fetchedData)
    {
        return array_filter($fetchedData, function ($entity) {
            return strlen($entity->getUin()) == self::UIN_LENGTH;
        });
    }

    /**
     * @param SyncEntityExtended[] $fetchedData
     * @return SyncEntity[]
     */
    private function getSyncEntityArray(array $fetchedData)
    {
        return array_map(function ($syncEntityExtended) {
            return SyncEntity::getSyncEntity($syncEntityExtended);
        }, $fetchedData);
    }
}
