<?php

namespace App\libraries\starrez_irio_sync\irio;

use App\libraries\starrez_irio_sync\models\SyncEntity;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class IrioDataSenderTest extends TestCase
{
    private $irioDataSender;

    public function testPostDataToIrio()
    {
        $irioApiResponse = '{"success":true,"messages":["File uploaded. Please look for an email upon completion.","Number of subscribers is 2"]}';
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], $irioApiResponse)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $csvHelperMock = $this->createMock(CsvHelper::class);
        $csvHelperMock->method('getFilePointerToCsv')
            ->willReturn(true);

        $this->irioDataSender = new IrioDataSender($client, $csvHelperMock);

        $accumulatedData = array(
            SyncEntity::getSyncEntityWithParams(
                null,
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
                null,
                'SCHU-114-A2',
                'Schuhmache Hall'
            )
        );

        $this->assertEquals($irioApiResponse, $this->irioDataSender->sendData($accumulatedData),
            "The returned value does not match mock values");
    }

    public function testPostDataToIrioForUnsuccessfulApiResponse()
    {
        $mock = new MockHandler([
            new RequestException('Error Communicating with Server', new Request('POST', 'test'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $csvHelperMock = $this->createMock(CsvHelper::class);
        $csvHelperMock->method('getFilePointerToCsv')
            ->willReturn(true);

        $this->irioDataSender = new IrioDataSender($client, $csvHelperMock);

        $accumulatedData = array(
            SyncEntity::getSyncEntityWithParams(
                null,
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
                null,
                'SCHU-114-A2',
                'Schuhmache Hall'
            )
        );

        $this->expectException(\Exception::class);
        $this->irioDataSender->sendData($accumulatedData);
    }
}
