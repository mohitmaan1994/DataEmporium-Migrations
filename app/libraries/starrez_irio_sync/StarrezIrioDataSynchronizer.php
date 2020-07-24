<?php


namespace App\libraries\starrez_irio_sync;

use App\libraries\starrez_irio_sync\irio\IrioDataSender;
use App\libraries\starrez_irio_sync\starrez\StarrezApiDataIterator;
use App\libraries\starrez_irio_sync\starrez\SyncDataAccumulator;
use App\libraries\starrez_irio_sync\starrez\SyncDataSelector;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class StarrezIrioDataSynchronizer
{
    private $starrezDataIterator;
    private $syncDataSelector;
    private $syncDataAccumulator;
    private $irioDataSender;

    /**
     * StarrezIrioDataSynchronizer constructor.
     * @param \Iterator $starrezDataIterator
     * @param SyncDataSelector $syncDataSelector
     * @param SyncDataAccumulator $syncDataAccumulator
     * @param IrioDataSender $irioDataSender
     */
    public function __construct(\Iterator $starrezDataIterator,
                                 SyncDataSelector $syncDataSelector,
                                 SyncDataAccumulator $syncDataAccumulator,
                                 IrioDataSender $irioDataSender)
    {
        $this->starrezDataIterator = $starrezDataIterator;
        $this->syncDataSelector = $syncDataSelector;
        $this->syncDataAccumulator = $syncDataAccumulator;
        $this->irioDataSender = $irioDataSender;
    }

    public function synchronize()
    {
        Log::channel('starrez-irio-sync')->info('synchronize():- Process started in '.App::environment().' environment');

        $this->syncDataAccumulator->clearData();

        while ($this->starrezDataIterator->valid())
        {
            Log::channel('starrez-irio-sync')->info('synchronize():- Fetching '.StarrezApiDataIterator::ENTRY_COUNT_PER_PAGE.' records, starting from index:- '.$this->starrezDataIterator->key());

            $fetchedData = $this->starrezDataIterator->current();
            $filteredData = $this->syncDataSelector->filter($fetchedData);
            $this->syncDataAccumulator->accumulate($filteredData);
            $this->starrezDataIterator->next();
        }
        try {
            $irioResponse = $this->irioDataSender->sendData($this->syncDataAccumulator->getAccumulatedData());
            Log::channel('starrez-irio-sync')->info(
                'synchronize(): Irio SUCCESSFULLY synced with StarRez.',
                array('Response message' => $irioResponse)
            );
        } catch (\Exception $e) {
            Log::channel('starrez-irio-sync')->error(
                'synchronize(): Error occurred while sending sync data to Irio.',
                array('Exception message' => $e->getMessage())
            );
        }
    }
}
