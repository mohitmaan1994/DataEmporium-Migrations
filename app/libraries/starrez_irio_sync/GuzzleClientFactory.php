<?php


namespace App\libraries\starrez_irio_sync;


use GuzzleHttp\Client;

class GuzzleClientFactory
{
    private $starrezIrioConfig;

    /**
     * GuzzleClientFactory constructor.
     * @param $starrezIrioConfig
     */
    public function __construct(StarrezIrioConfig $starrezIrioConfig)
    {
        $this->starrezIrioConfig = $starrezIrioConfig;
    }

    public function createClientForStarrez()
    {
        return new Client([
            'base_uri' => $this->starrezIrioConfig->getStarrezBaseUrl(),
            'headers' => [
                'StarRezUsername' => $this->starrezIrioConfig->getStarrezUserName(),
                'StarRezPassword' => $this->starrezIrioConfig->getStarrezPassword()
            ],
            'verify' => false
        ]);
    }

    public function createClientForIrio()
    {
        return new Client([
            'base_uri' => $this->starrezIrioConfig->getIrioBaseUrl(),
            'headers' => [
                'Authorization' => $this->starrezIrioConfig->getIrioApiKey()
            ],
            'verify' => false
        ]);
    }
}
