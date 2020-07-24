<?php

namespace App\libraries\starrez_irio_sync\starrez;

use App\libraries\starrez_irio_sync\models\SyncEntity;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Tests\TestCase;

class MySqlSyncDataAccumulatorTest extends TestCase
{
    use RefreshDatabase;

    private $mySqlSyncDataAccumulator;

    protected function setUp(): void
    {
        parent::setUp();
        Log::shouldReceive("channel->info")
            ->zeroOrMoreTimes();
        $this->mySqlSyncDataAccumulator = new MySqlSyncDataAccumulator();
    }

    public function testDbGetsTruncated()
    {
        $this->mySqlSyncDataAccumulator->clearData();
        $this->assertTrue(SyncEntity::all()->isEmpty(), 'Table not empty after truncation');
    }

    /**
     * @dataProvider filteredDataProvider
     * @param $filteredData
     */
    public function testAccumulationOfFilteredArray($filteredData)
    {
        $this->mySqlSyncDataAccumulator->accumulate($filteredData);
        $this->assertTrue(SyncEntity::all()->count() == 2, 'Total count of sync entities was not 2');
    }
    public function filteredDataProvider()
    {
        $input1 = array(
            SyncEntity::getSyncEntityWithParams(
                '000000000',
                'John Doe',
                '1111111111',
                'jdoe@tamu.edu',
                'SCHU-114-A1',
                'Schuhmacher Hall'
            ),
            SyncEntity::getSyncEntityWithParams(
                '000000001',
                'Jane Doe',
                '1111111110',
                'jjdoe@tamu.edu',
                'SCHU-114-A2',
                'Schuhmache Hall'
            )
        );
        $input2 = array(
            SyncEntity::getSyncEntityWithParams(
                '011919191',
                'John Doe',
                null,
                null,
                'SCHU-114-A1',
                'Schuhmacher Hall'
            ),
            SyncEntity::getSyncEntityWithParams(
                '000000001',
                'Jane Doe',
                '1111111110',
                'jjdoe@tamu.edu',
                null,
                null
            )
        );
        return array(
            array($input1),
            array($input2)
        );
    }

    public function testDbReturnsSyncEntityArray()
    {
        $this->mySqlSyncDataAccumulator->accumulate(array(
            SyncEntity::getSyncEntityWithParams(
                '000000000',
                'John Doe',
                '1111111111',
                'jdoe@tamu.edu',
                'SCHU-114-A1',
                'Schuhmacher Hall'
            ),
            SyncEntity::getSyncEntityWithParams(
                '000000001',
                'Jane Doe',
                '1111111110',
                'jjdoe@tamu.edu',
                'SCHU-114-A2',
                'Schuhmache Hall'
            )
        ));
        $dbResponse = $this->mySqlSyncDataAccumulator->getAccumulatedData();
        $this->assertSame('John Doe', $dbResponse[0]->getName(), 'First element should have name John Doe');
        $this->assertSame('Jane Doe', $dbResponse[1]->getName(), 'Second element should have name Jane Doe');
    }
}
