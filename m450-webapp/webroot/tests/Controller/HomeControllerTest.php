<?php

namespace Test\Controller;

use App\Controller\HomeController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HomeControllerTest extends TestCase
{
    public function testInputParamsWithValues()
    {
        $controller = new HomeController();
        $sri = $this->createStub(ServerRequestInterface::class);
        $sri->method('getQueryParams')->willReturn([
            'zip' => '8000',
            'date' => 'date'
        ]);

        $result = $controller->getQueryParams($sri);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('zip', $result);
        $this->assertArrayHasKey('mode', $result);
        $this->assertEquals('historic', $result['mode']);
        $this->assertEquals('8000', $result['zip']);
        $this->assertEquals('date', $result['date']);
    }

    public function testGetWeatherDataHTML()
    {
        $req = $this->createStub(ServerRequestInterface::class);
        $req->method('getQueryParams')->willReturn([
            'zip' => '8000',
            'date' => 'date'
        ]);
        $res = $this->createStub(ResponseInterface::class);
        $bodyStream = $this->createStub(StreamInterface::class);
        $res->method('getBody')->willReturn($bodyStream);

        $controller = $this->getMockBuilder(HomeController::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getQueryParams',
                'getHistoricData',
                'getHistoricWeatherData',
                'getHistoricAirData',
                'getCurrentData',
                'getCurrentWeatherData',
                'normalizeWeatherData',
                'getCurrentAirData',
                'normalizeAirData'
            ])->getMock();

        $controller->expects($this->once())->method('getQueryParams')->with($req);
        $result = $controller->getWeatherDataHtml($req, $res, []);

        $this->assertInstanceOf(ResponseInterface::class, $result);
    }
}