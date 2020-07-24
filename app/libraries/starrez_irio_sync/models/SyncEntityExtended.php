<?php


namespace App\libraries\starrez_irio_sync\models;


/**
 * App\libraries\starrez_irio_sync\models\SyncEntityExtended
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncEntityExtended newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncEntityExtended newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\libraries\starrez_irio_sync\models\SyncEntityExtended query()
 * @mixin \Eloquent
 */
class SyncEntityExtended extends SyncEntity
{
    private $roomSpaceID;
    private $roomLocationID;
    private $termSessionID;
    private $termSessionDescription;
    private $bookingEntryStatus;

    /**
     * @return int
     */
    public function getRoomSpaceID()
    {
        return $this->roomSpaceID;
    }

    /**
     * @param int $roomSpaceID
     */
    public function setRoomSpaceID($roomSpaceID): void
    {
        $this->roomSpaceID = $roomSpaceID;
    }

    /**
     * @return int
     */
    public function getRoomLocationID()
    {
        return $this->roomLocationID;
    }

    /**
     * @param int $roomLocationID
     */
    public function setRoomLocationID($roomLocationID): void
    {
        $this->roomLocationID = $roomLocationID;
    }

    /**
     * @return int
     */
    public function getTermSessionID()
    {
        return $this->termSessionID;
    }

    /**
     * @param int $termSessionID
     */
    public function setTermSessionID($termSessionID): void
    {
        $this->termSessionID = $termSessionID;
    }

    /**
     * @return mixed
     */
    public function getTermSessionDescription()
    {
        return $this->termSessionDescription;
    }

    /**
     * @param mixed $termSessionDescription
     */
    public function setTermSessionDescription($termSessionDescription): void
    {
        $this->termSessionDescription = $termSessionDescription;
    }

    /**
     * @return BookingEntryStatus
     */
    public function getBookingEntryStatus()
    {
        return $this->bookingEntryStatus;
    }

    /**
     * @param BookingEntryStatus $bookingEntryStatus
     */
    public function setBookingEntryStatus($bookingEntryStatus): void
    {
        $this->bookingEntryStatus = $bookingEntryStatus;
    }
}
