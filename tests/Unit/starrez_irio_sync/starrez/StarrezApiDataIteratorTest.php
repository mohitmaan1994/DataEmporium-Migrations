<?php

namespace App\libraries\starrez_irio_sync\starrez;

use App\libraries\starrez_irio_sync\models\SyncEntityExtended;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\TestCase;

class StarrezApiDataIteratorTest extends TestCase
{
    private $starrezApiDataIterator;

    protected function setUp(): void
    {
        $starrezApiMock = $this->createMock(StarrezApi::class);
        $this->starrezApiDataIterator = new StarrezApiDataIterator($starrezApiMock);
    }

    public function testInitialPageNumberIsZero()
    {
        $this->assertEquals(0, $this->starrezApiDataIterator->key(), "The initial page number to fetch is not 0");
    }

    public function testNext()
    {
        $this->starrezApiDataIterator->next();
        $this->assertEquals(500, $this->starrezApiDataIterator->key(), "next() did not increment the iterator key by right amount");
    }

    public function testRewind()
    {
        $this->starrezApiDataIterator->next();
        $this->starrezApiDataIterator->rewind();
        $this->assertEquals(0, $this->starrezApiDataIterator->key(), "rewind() did not set page number to 0");
    }

    public function testResultStoredIfValidIteration()
    {
        $starrezApiMock = $this->createMock(StarrezApi::class);
        $starrezApiGetEntriesResponse = array(new SyncEntityExtended(), new SyncEntityExtended());
        $starrezApiGetEntriesResponse[0]->setRoomSpaceID(14414);
        $starrezApiGetEntriesResponse[0]->setRoomLocationID(27);
        $starrezApiMock->method('getEntries')
            ->willReturn($starrezApiGetEntriesResponse);
        $starrezApiMock->method('getRoomNumber')
            ->willReturn('SCHU-114-A1');
        $starrezApiMock->method('getRoomLocation')
            ->willReturn('Schuhmacher Hall');

        $this->starrezApiDataIterator = new StarrezApiDataIterator($starrezApiMock);

        $this->assertTrue($this->starrezApiDataIterator->valid(), "Did not return true despite valid starrez api response");
        $this->assertEquals(2, count($this->starrezApiDataIterator->current()), "Stored data array does not have two elements");
        $this->assertEquals('SCHU-114-A1', $starrezApiGetEntriesResponse[0]->getRoomNumber(), "Room Number is not set to SCHU-114-A1");
        $this->assertEquals('Schuhmacher Hall', $starrezApiGetEntriesResponse[0]->getRoomLocation(), "Room Location is not set to Schuhmacher Hall");
    }

    public function testResultNotStoredIfInvalidIteration()
    {
        $starrezApiMock = $this->createMock(StarrezApi::class);
        $starrezApiMock->method('getEntries')
            ->willThrowException(new \Exception());
        $this->starrezApiDataIterator = new StarrezApiDataIterator($starrezApiMock);

        Log::shouldReceive("channel->warning")
            ->once();

        $this->assertFalse($this->starrezApiDataIterator->valid(), "Did not return false despite invalid starrez api response");
        $this->assertNull($this->starrezApiDataIterator->current(), "Did not return null content for an invalid iteration index");
    }

    public function testExceptionsLoggedWhenRoomNumberAndRoomLocationApiThrowException()
    {
        $starrezApiMock = $this->createMock(StarrezApi::class);
        $starrezApiGetEntriesResponse = array(new SyncEntityExtended(), new SyncEntityExtended());
        $starrezApiGetEntriesResponse[0]->setRoomSpaceID(14414);
        $starrezApiGetEntriesResponse[0]->setRoomLocationID(27);
        $starrezApiMock->method('getEntries')
            ->willReturn($starrezApiGetEntriesResponse);
        $starrezApiMock->method('getRoomNumber')
            ->willThrowException(new \Exception());
        $starrezApiMock->method('getRoomLocation')
            ->willThrowException(new \Exception());
        $this->starrezApiDataIterator = new StarrezApiDataIterator($starrezApiMock);

        Log::shouldReceive("channel->warning")
            ->twice();

        $this->assertTrue($this->starrezApiDataIterator->valid(), "Did not return true despite valid exception handling");
    }
}
