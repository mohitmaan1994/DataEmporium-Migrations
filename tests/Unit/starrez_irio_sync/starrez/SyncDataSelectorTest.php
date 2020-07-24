<?php

namespace App\libraries\starrez_irio_sync\starrez;

use App\libraries\starrez_irio_sync\models\SyncEntity;
use App\libraries\starrez_irio_sync\models\SyncEntityExtended;
use App\libraries\starrez_irio_sync\models\SyncFilterCriteria;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\TestCase;

class SyncDataSelectorTest extends TestCase
{
    private $syncDataSelector;

    protected function setUp(): void
    {
        $dbResponse = array(
            json_decode("{\"starrez_field\":\"RoomLocationID\",\"constraint_values\":\"9,20,38,14,30,22,10,39,24,40,45,46,47,48,49,50,51,52,53,54,15,35,43,31,16,25,8,26,37,23,33,17,28,34,18,29,21,19,11,4,27,36,12,44,32,13,5,6,7,42,41\"}"),
            json_decode("{\"starrez_field\":\"TermSessionID\",\"constraint_values\":\"49,50,46\"}"),
            json_decode("{\"starrez_field\":\"EntryStatusEnum\",\"constraint_values\":\"1,2,5\"}")
        );
        $temp = new SyncFilterCriteria();
        $this->syncDataSelector = new SyncDataSelector($temp);
    }

    public function testFilterAllowsValidEntryToPass()
    {
        $fetchedData = array(new SyncEntityExtended());
        $fetchedData[0]->setRoomLocationID(9);
        $fetchedData[0]->setTermSessionID(49);
        $fetchedData[0]->setEntryStatusEnum(1);
        $fetchedData[0]->setName('abc');
        $filteredResponse = $this->syncDataSelector->filter($fetchedData);
        $this->assertEquals(1, count($filteredResponse), 'Filtered response array does not have length 1');
        $this->assertTrue($filteredResponse[0] instanceof SyncEntity, 'Returned object is not an instance of SyncEntity');
        $this->assertEquals('abc', $filteredResponse[0]->getName(), 'Filtered response does not have name:- abc');
    }

    /**
     * @dataProvider roomLocationProvider
     * @param $roomLocationID
     */
    public function testFilterRemovesEntryWithInvalidRoomLocationID($roomLocationID)
    {
        $fetchedData = array(new SyncEntityExtended());
        $fetchedData[0]->setRoomLocationID($roomLocationID);
        $fetchedData[0]->setTermSessionID(49);
        $fetchedData[0]->setEntryStatusEnum(1);
        $filteredResponse = $this->syncDataSelector->filter($fetchedData);
        $this->assertEquals(0, count($filteredResponse), 'Filtered response array does not have length 0');
    }
    public function roomLocationProvider()
    {
        return array(
            array(0),
            array(null)
        );
    }

    /**
     * @dataProvider termSessionProvider
     * @param $termSessionID
     */
    public function testFilterRemovesEntryWithInvalidTermSessionID($termSessionID)
    {
        $fetchedData = array(new SyncEntityExtended());
        $fetchedData[0]->setRoomLocationID(9);
        $fetchedData[0]->setTermSessionID($termSessionID);
        $fetchedData[0]->setEntryStatusEnum(1);
        $filteredResponse = $this->syncDataSelector->filter($fetchedData);
        $this->assertEquals(0, count($filteredResponse), 'Filtered response array does not have length 0');
    }
    public function termSessionProvider()
    {
        return array(
            array(0),
            array(null)
        );
    }

    /**
     * @dataProvider entryStatusEnumProvider
     * @param $entryStatusEnum
     */
    public function testFilterRemovesEntryWithInvalidEntryStatusEnum($entryStatusEnum)
    {
        $fetchedData = array(new SyncEntityExtended());
        $fetchedData[0]->setRoomLocationID(9);
        $fetchedData[0]->setTermSessionID(49);
        $fetchedData[0]->setEntryStatusEnum($entryStatusEnum);
        $filteredResponse = $this->syncDataSelector->filter($fetchedData);
        $this->assertEquals(0, count($filteredResponse), 'Filtered response array does not have length 0');
    }
    public function entryStatusEnumProvider()
    {
        return array(
            array(0),
            array(null)
        );
    }
}
