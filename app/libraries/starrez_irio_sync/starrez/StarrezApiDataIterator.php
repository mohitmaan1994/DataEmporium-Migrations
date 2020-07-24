<?php

namespace App\libraries\starrez_irio_sync\starrez;

use App\libraries\starrez_irio_sync\models\BookingEntryStatusFactory;
use App\libraries\starrez_irio_sync\models\SyncEntityExtended;
use Illuminate\Support\Facades\Log;

class StarrezApiDataIterator implements \Iterator
{
    const ENTRY_COUNT_PER_PAGE = 500;

    private $starrezApi;
    private $pageNumber;
    private $currentData;

    /**
     * StarrezApiDataIterator constructor.
     * @param StarrezApi $starrezApi
     */
    public function __construct(StarrezApi $starrezApi)
    {
        $this->starrezApi = $starrezApi;
        $this->pageNumber = 0;
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return SyncEntityExtended[]
     * @since 5.0.0
     */
    public function current()
    {
        return $this->currentData;
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->pageNumber += self::ENTRY_COUNT_PER_PAGE;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->pageNumber;
    }

    /**
     * Checks if current position is valid, and stores the result, if valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        $getEntriesQueryParams = array(
            '_pageIndex' => $this->pageNumber,
            '_pageSize' => self::ENTRY_COUNT_PER_PAGE,
            '_relatedTables' => ['EntryAddress', 'Booking'],
            'Testing' => 'false'
        );

        try {
            $this->currentData = $this->starrezApi->getEntries($getEntriesQueryParams);
        } catch (\Exception $e) {

            /* StarRez endpoint does not return a 'total_pages' variable that indicates the total pages / records.
            Thus only when they send a error response, do we know that all the records are covered. */

            Log::channel('starrez-irio-sync')->warning(
                'valid():- Fetching records starting from index:- '.$this->key().' failed.',
                array('Exception message' => $e->getMessage())
            );
            $this->currentData = null;
            return false;
        }

        foreach ($this->currentData as $entry)
        {
            $this->setEntryRoomNumber($entry);
            $this->setEntryRoomLocation($entry);
            $this->setEntryTermSessionDescription($entry);
            $this->setEntryTags($entry);
        }
        return true;
    }

    private function setEntryRoomNumber(SyncEntityExtended &$entry)
    {
        if ($entry->getRoomSpaceID() != null)
        {
            try {
                $entry->setRoomNumber(
                    $this->starrezApi->getRoomNumber($entry->getRoomSpaceID())
                );
            } catch (\Exception $e) {
                Log::channel('starrez-irio-sync')->warning(
                    'valid(): For UIN:- '.$entry->getUin().', could not set Room Number for RoomSpaceID:- '.$entry->getRoomSpaceID(),
                    array('Exception message' => $e->getMessage())
                );
            }
        }
    }

    private function setEntryRoomLocation(SyncEntityExtended &$entry)
    {
        if ($entry->getRoomLocationID() != null)
        {
            try {
                $entry->setRoomLocation(
                    $this->starrezApi->getRoomLocation($entry->getRoomLocationID())
                );
            } catch (\Exception $e) {
                Log::channel('starrez-irio-sync')->warning(
                    'valid(): For UIN:- '.$entry->getUin().', could not set Room Location for RoomLocationID:- '.$entry->getRoomLocationID(),
                    array('Exception message' => $e->getMessage())
                );
            }
        }
    }

    private function setEntryTermSessionDescription(SyncEntityExtended &$entry)
    {
        if ($entry->getTermSessionID() != null)
        {
            try {
                $entry->setTermSessionDescription(
                    $this->starrezApi->getTermSession($entry->getTermSessionID())
                );
            } catch (\Exception $e) {
                Log::channel('starrez-irio-sync')->warning(
                    'valid(): For UIN:- '.$entry->getUin().', could not set Term Session Description for TermSessionID:- '.$entry->getTermSessionID(),
                    array('Exception message' => $e->getMessage())
                );
            }
        }
    }

    private function setEntryTags(SyncEntityExtended &$entry)
    {
        $tags = array();
        if (!is_null($entry->getRoomLocation())) {
            array_push($tags, $entry->getRoomLocation());
        }
        if (!is_null($entry->getTermSessionDescription())) {
            array_push($tags, $entry->getTermSessionDescription());
        }
        if (!is_null($entry->getBookingEntryStatus()) &&
            $entry->getBookingEntryStatus()->getDescription() != BookingEntryStatusFactory::INVALID_ENTRY_STATUS_DESCRIPTION) {
            array_push($tags, $entry->getBookingEntryStatus()->getDescription());
        }
        $tags = implode('|', $tags);
        $entry->setTags($tags);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->pageNumber = 0;
    }
}
