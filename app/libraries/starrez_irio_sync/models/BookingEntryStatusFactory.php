<?php


namespace App\libraries\starrez_irio_sync\models;


class BookingEntryStatusFactory
{
    const INVALID_ENTRY_STATUS_DESCRIPTION = 'NA';
    /**
     * @param $apiResponseEntryStatusEnum
     * @return BookingEntryStatus
     */
    public function create($apiResponseEntryStatusEnum)
    {
        switch ($apiResponseEntryStatusEnum) {
            case 'Admin':
                return new BookingEntryStatus(0, 'Admin');
                break;
            case 'Tentative':
                return new BookingEntryStatus(1, 'Tentative');
                break;
            case 'Reserved':
                return new BookingEntryStatus(2, 'Reserved');
                break;
            case 'Held':
                return new BookingEntryStatus(3, 'Held');
                break;
            case 'InRoom':
                return new BookingEntryStatus(5, 'In Room');
                break;
            case 'History':
                return new BookingEntryStatus(10, 'History');
                break;
            case 'Occupant':
                return new BookingEntryStatus(20, 'Occupant');
                break;
            case 'OccupantHistory':
                return new BookingEntryStatus(21, 'Occupant History');
                break;
            case 'OccupantInRoom':
                return new BookingEntryStatus(22, 'Occupant In Room');
                break;
            case 'Attendee':
                return new BookingEntryStatus(30, 'Attendee');
                break;
            case 'AttendeeArrived':
                return new BookingEntryStatus(31, 'Attendee Arrived');
                break;
            case 'AttendeeDeparted':
                return new BookingEntryStatus(32, 'Attendee Departed');
                break;
            case 'Account':
                return new BookingEntryStatus(40, 'Account');
                break;
            case 'MasterAccount':
                return new BookingEntryStatus(50, 'Master Account');
                break;
            case 'Cancelled':
                return new BookingEntryStatus(70, 'Cancelled');
                break;
            case 'Erased':
                return new BookingEntryStatus(75, 'Erased');
                break;
            case 'Application':
                return new BookingEntryStatus(80, 'Application');
                break;
            case 'Incident':
                return new BookingEntryStatus(90, 'Incident');
                break;
            case 'Contact':
                return new BookingEntryStatus(100, 'Contact');
                break;
            case 'Alumni':
                return new BookingEntryStatus(110, 'Alumni');
                break;
            default:
                return new BookingEntryStatus(-1, self::INVALID_ENTRY_STATUS_DESCRIPTION);
        }
    }
}
