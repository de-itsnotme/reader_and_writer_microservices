<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Http;

use App\Infrastructure\Http\WriterHttpGateway;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WriterHttpGatewayTest extends TestCase
{
    public function testSendItem(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn(202);

        $httpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'http://writer:8000/products',
                $this->callback(function ($options) {
                    return isset($options['json']['foo']) && $options['json']['foo'] === 'bar';
                })
            )
            ->willReturn($response);

        $gateway = new WriterHttpGateway($httpClient, 'http://writer:8000');

        $gateway->sendBulkProducts(['foo' => 'bar']);

        // No exception means success
        $this->assertTrue(true);
    }

    public function testSendItemThrowsOnError(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $response   = $this->createMock(ResponseInterface::class);

        $response->method('getStatusCode')->willReturn(500);
        $httpClient->method('request')->willReturn($response);

        $gateway = new WriterHttpGateway($httpClient, 'http://writer:8000');

        $this->expectException(\RuntimeException::class);
        $gateway->sendBulkProducts(['foo' => 'bar']);
    }
}
