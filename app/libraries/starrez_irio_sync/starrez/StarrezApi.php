<?php

namespace App\libraries\starrez_irio_sync\starrez;

use App\libraries\starrez_irio_sync\GuzzleClientFactory;
use App\libraries\starrez_irio_sync\models\SyncEntityExtended;

class StarrezApi
{
    private $httpClient;
    private $starrezApiResponseMapper;

    /**
     * StarrezApi constructor.
     * @param StarrezApiResponseMapper $starrezApiResponseMapper
     * @param GuzzleClientFactory $guzzleClientFactory
     */
    public function __construct(StarrezApiResponseMapper $starrezApiResponseMapper,
                                GuzzleClientFactory $guzzleClientFactory)
    {
        $this->starrezApiResponseMapper = $starrezApiResponseMapper;
        $this->httpClient = $guzzleClientFactory->createClientForStarrez();
    }

    /**
     * @param array|null $getEntriesQueryParams
     * @return SyncEntityExtended[]
     * @throws \Exception
     */
    public function getEntries(array $getEntriesQueryParams = null)
    {
        $response = $this->httpClient->get('Select/Entry.json', ['query' => $this->getQueryStringFromQueryParamsArray($getEntriesQueryParams)]);
        return $this->starrezApiResponseMapper->mapToSyncEntityExtendedArray($response->getBody()->getContents());
    }

    /**
     * @param int $roomSpaceID
     * @return string
     * @throws \Exception
     */
    public function getRoomNumber(int $roomSpaceID)
    {
        $response = $this->httpClient->get('Select/RoomSpace.json', ['query' => ['RoomSpaceID' => $roomSpaceID]]);
        return $this->starrezApiResponseMapper->extractRoomNumber($response->getBody()->getContents());
    }

    /**
     * @param int $roomLocationID
     * @return string
     * @throws \Exception
     */
    public function getRoomLocation(int $roomLocationID)
    {
        $response = $this->httpClient->get('Select/RoomLocation.json', ['query' => ['RoomLocationID' => $roomLocationID]]);
        return $this->starrezApiResponseMapper->extractRoomLocation($response->getBody()->getContents());
    }

    /**
     * @param int $termSessionID
     * @return string
     * @throws \Exception
     */
    public function getTermSession(int $termSessionID)
    {
        $response = $this->httpClient->get('Select/TermSession.json', ['query' => ['TermSessionID' => $termSessionID]]);
        return $this->starrezApiResponseMapper->extractTermSessionDescription($response->getBody()->getContents());
    }

    public function getQueryStringFromQueryParamsArray($getQueryParams)
    {
        $queryString = "";
        foreach ($getQueryParams as $queryParam => $value) {
            if ($queryParam === '_relatedTables') {
                foreach ($value as $relatedTable) {
                    $queryString .= '_relatedTables='.$relatedTable.'&';
                }
            } else {
                $queryString .= $queryParam.'='.$value.'&';
            }
        }
        return substr($queryString, 0, -1);
    }
}
