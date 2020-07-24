<?php


namespace App\libraries\starrez_irio_sync\starrez;

use App\libraries\starrez_irio_sync\models\BookingEntryStatusFactory;
use App\libraries\starrez_irio_sync\models\SyncEntityExtended;

class StarrezApiResponseMapper
{
    private $bookingEntryStatusFactory;

    /**
     * StarrezApiResponseMapper constructor.
     * @param BookingEntryStatusFactory $bookingEntryStatusFactory
     */
    public function __construct(BookingEntryStatusFactory $bookingEntryStatusFactory)
    {
        $this->bookingEntryStatusFactory = $bookingEntryStatusFactory;
    }

    /**
     * @param string $starrezApiResponse
     * @return SyncEntityExtended[]
     */
    public function mapToSyncEntityExtendedArray(string $starrezApiResponse)
    {
        $syncEntityExtendedArray = [];
        $jsonStarrezApiResponse = json_decode($starrezApiResponse);
        foreach ($jsonStarrezApiResponse as $responseObj)
        {
            $syncEntityExtendedObj = new SyncEntityExtended();
            if (isset($responseObj->ID1)) {
                $syncEntityExtendedObj->setUin($responseObj->ID1);
            }
            if (isset($responseObj->NameFirst) || isset($responseObj->NameLast)) {
                $responseObj->NameFirst = isset($responseObj->NameFirst) ? $responseObj->NameFirst : '';
                $responseObj->NameLast = isset($responseObj->NameLast) ? $responseObj->NameLast : '';
                $syncEntityExtendedObj->setName($responseObj->NameFirst.' '.$responseObj->NameLast);
            }

            $this->extractEntityAddressInfo($responseObj, $syncEntityExtendedObj);
            $this->extractBookingInfo($responseObj, $syncEntityExtendedObj);

            array_push($syncEntityExtendedArray, $syncEntityExtendedObj);
        }
        return $syncEntityExtendedArray;
    }

    /**
     * @param string $starrezApiResponse
     * @return string
     */
    public function extractRoomNumber(string $starrezApiResponse)
    {
        $jsonStarrezApiResponse = json_decode($starrezApiResponse);
        if (count($jsonStarrezApiResponse) == 0 || !isset($jsonStarrezApiResponse[0]->Description)) {
            return "";
        } else {
            return $jsonStarrezApiResponse[0]->Description;
        }
    }

    /**
     * @param string $starrezApiResponse
     * @return string
     */
    public function extractRoomLocation(string $starrezApiResponse)
    {
        $jsonStarrezApiResponse = json_decode($starrezApiResponse);
        if (count($jsonStarrezApiResponse) == 0 || !isset($jsonStarrezApiResponse[0]->Description)) {
            return "";
        } else {
            return $jsonStarrezApiResponse[0]->Description;
        }
    }

    /**
     * @param string $starrezApiResponse
     * @return string
     */
    public function extractTermSessionDescription(string $starrezApiResponse)
    {
        $jsonStarrezApiResponse = json_decode($starrezApiResponse);
        if (count($jsonStarrezApiResponse) == 0 || !isset($jsonStarrezApiResponse[0]->Description)) {
            return "";
        } else {
            return $jsonStarrezApiResponse[0]->Description;
        }
    }

    private function extractEntityAddressInfo($responseObj, &$syncEntityExtendedObj)
    {
        if (isset($responseObj->EntryAddress)) {
            $filteredEntryAddress = array_filter($responseObj->EntryAddress, function ($entryAddress) {
                return $entryAddress->AddressTypeID == 3;
            });
            $filteredEntryAddress = array_values($filteredEntryAddress);
            if (count($filteredEntryAddress) > 0)
            {
                $filteredEntryAddress = $filteredEntryAddress[0];
                if (isset($filteredEntryAddress->Email)) {
                    $syncEntityExtendedObj->setEmail($filteredEntryAddress->Email);
                }
                if (isset($filteredEntryAddress->PhoneMobileCell)) {
                    $syncEntityExtendedObj->setPhoneNumber($filteredEntryAddress->PhoneMobileCell);
                }
            }
        }
    }

    private function extractBookingInfo($responseObj, &$syncEntityExtendedObj)
    {
        if (isset($responseObj->Booking)) {
            $filteredBooking = array_filter($responseObj->Booking, function ($booking) use ($responseObj) {
                return $booking->BookingID == $responseObj->BookingID;
            });
            $filteredBooking = array_values($filteredBooking);
            if (count($filteredBooking) > 0)
            {
                $filteredBooking = $filteredBooking[0];
                if (isset($filteredBooking->RoomSpaceID)) {
                    $syncEntityExtendedObj->setRoomSpaceID($filteredBooking->RoomSpaceID);
                }
                if (isset($filteredBooking->RoomLocationID)) {
                    $syncEntityExtendedObj->setRoomLocationID($filteredBooking->RoomLocationID);
                }
                if (isset($filteredBooking->TermSessionID)) {
                    $syncEntityExtendedObj->setTermSessionID($filteredBooking->TermSessionID);
                }
                if (isset($filteredBooking->EntryStatusEnum)) {
                    $bookingEntryStatus = $this->bookingEntryStatusFactory->create($filteredBooking->EntryStatusEnum);
                    $syncEntityExtendedObj->setBookingEntryStatus($bookingEntryStatus);
                }
            }
        }
    }
}
