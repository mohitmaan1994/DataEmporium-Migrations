<?php


namespace App\libraries\starrez_irio_sync;

class StarrezIrioConfig
{
    private $starrezUserName;
    private $starrezPassword;
    private $starrezBaseUrl;
    private $irioApiKey;
    private $irioBaseUrl;
    private $irioImportSubscribersProgramId;

    /**
     * StarrezIrioConfig constructor.
     */
    public function __construct()
    {
        $this->starrezUserName = config('services.starrez.username');
        $this->starrezPassword = config('services.starrez.password');
        $this->starrezBaseUrl = config('services.starrez.base_url');
        $this->irioApiKey = config('services.irio.api_key');
        $this->irioBaseUrl = config('services.irio.base_url');
        $this->irioImportSubscribersProgramId = config('services.irio.import_subscribers_program_id');
    }

    /**
     * @return string
     */
    public function getStarrezUserName()
    {
        return $this->starrezUserName;
    }

    /**
     * @return string
     */
    public function getStarrezPassword()
    {
        return $this->starrezPassword;
    }

    /**
     * @return mixed
     */
    public function getStarrezBaseUrl()
    {
        return $this->starrezBaseUrl;
    }

    /**
     * @return string
     */
    public function getIrioApiKey()
    {
        return $this->irioApiKey;
    }

    /**
     * @return string
     */
    public function getIrioBaseUrl()
    {
        return $this->irioBaseUrl;
    }

    /**
     * @return string
     */
    public function getIrioImportSubscribersProgramId()
    {
        return $this->irioImportSubscribersProgramId;
    }
}
