<?php

namespace Test\Controller;

use App\Controller\HomeController;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class HomeControllerTest extends TestCase {
    public function testInputParamsWithValues() {
        $controller = new HomeController();
        $sri = $this->createStub(ServerRequestInterface::class);
        $sri->method('getQueryParams')->willReturn([
            'getQueryParams' => [
                'zip' => '8000',
                'date' => 'date'
            ]
        ]);

        $result = $controller->getQueryParams($sri);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('zip', $result);
        $this->assertArrayHasKey('mode', $result);
        $this->assertEquals('historic', $result['mode']);
    }
}