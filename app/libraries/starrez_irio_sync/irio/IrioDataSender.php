<?php


namespace App\libraries\starrez_irio_sync\irio;

use App\libraries\starrez_irio_sync\GuzzleClientFactory;
use App\libraries\starrez_irio_sync\models\SyncEntity;
use App\libraries\starrez_irio_sync\StarrezIrioConfig;

class IrioDataSender
{
    private $httpClient;
    private $csvHelper;
    private $starrezIrioConfig;

    /**
     * StarrezApi constructor.
     * @param CsvHelper $csvHelper
     * @param GuzzleClientFactory $guzzleClientFactory
     * @param StarrezIrioConfig $starrezIrioConfig
     */
    public function __construct(CsvHelper $csvHelper,
                                GuzzleClientFactory $guzzleClientFactory,
                                StarrezIrioConfig $starrezIrioConfig)
    {
        $this->csvHelper = $csvHelper;
        $this->httpClient = $guzzleClientFactory->createClientForIrio();
        $this->starrezIrioConfig = $starrezIrioConfig;
    }

    /**
     * @param SyncEntity[] $accumulatedData
     * @return string
     * @throws \Exception
     */
    public function sendData($accumulatedData)
    {
        $this->csvHelper->createCsvFileFromAccumulatedData($accumulatedData);
        $body = $this->csvHelper->getFilePointerToCsv();
        $response = $this->httpClient->post('api/v1/program/'.$this->starrezIrioConfig->getIrioImportSubscribersProgramId().'/importSubscribers', [
            'headers' => ['Content-Type' => 'text/csv'],
            'body' => $body
        ]);
        return $response->getBody()->getContents();
    }
}
