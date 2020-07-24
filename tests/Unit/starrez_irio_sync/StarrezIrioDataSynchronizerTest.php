<?php

namespace App\libraries\starrez_irio_sync;

use App\libraries\starrez_irio_sync\irio\IrioDataSender;
use App\libraries\starrez_irio_sync\models\SyncEntity;
use App\libraries\starrez_irio_sync\models\SyncEntityExtended;
use App\libraries\starrez_irio_sync\starrez\SyncDataAccumulator;
use App\libraries\starrez_irio_sync\starrez\SyncDataSelector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class StarrezIrioDataSynchronizerTest extends TestCase
{
    use RefreshDatabase;

    private $starrezIrioDataSynchronizer;

    protected function setUp(): void
    {
        parent::setUp();
        Log::shouldReceive("channel->info")
            ->zeroOrMoreTimes();
        Log::shouldReceive("channel->error")
            ->zeroOrMoreTimes();
        Log::shouldReceive("channel->warning")
            ->zeroOrMoreTimes();
        $starrezDataIterator = $this->createMock(\Iterator::class);
        $starrezDataIterator->expects($this->exactly(2))
            ->method('valid')
            ->willReturnOnConsecutiveCalls(true, false);
        $starrezDataIterator->expects($this->exactly(1))
            ->method('current')
            ->willReturn(array(new SyncEntityExtended()));
        $starrezDataIterator->expects($this->exactly(1))
            ->method('next');

        $syncDataSelector = $this->createMock(SyncDataSelector::class);
        $syncDataSelector->expects($this->exactly(1))
            ->method('filter')
            ->with(array(new SyncEntityExtended()))
            ->willReturn(array(new SyncEntity()));

        $syncDataAccumulator = $this->createMock(SyncDataAccumulator::class);
        $syncDataAccumulator->expects($this->exactly(1))
            ->method('accumulate')
            ->with(array(new SyncEntity()));
        $syncDataAccumulator->expects($this->exactly(1))
            ->method('clearData');
        $syncDataAccumulator->expects($this->exactly(1))
            ->method('getAccumulatedData')
            ->willReturn(array(new SyncEntity()));

        $irioDataSender = $this->createMock(IrioDataSender::class);
        $irioDataSender->expects($this->exactly(1))
            ->method('sendData')
            ->with(array(new SyncEntity()));

        $this->starrezIrioDataSynchronizer = new StarrezIrioDataSynchronizer($starrezDataIterator,
                                                                             $syncDataSelector,
                                                                             $syncDataAccumulator,
                                                                             $irioDataSender);
    }

    public function testSynchronize()
    {// TODO: migrate new sync_records table to both environments!
        $this->seed();
        $this->starrezIrioDataSynchronizer = resolve(StarrezIrioDataSynchronizer::class);
        $this->starrezIrioDataSynchronizer->synchronize();
    }
}
