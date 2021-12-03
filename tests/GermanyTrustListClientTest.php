<?php

declare(strict_types=1);

namespace Grambas\Test;

use Grambas\Client\GermanyTrustListClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GermanyTrustListClientTest extends TestCase
{
    /** @var string */
    private $testResourceDir;

    protected function setUp(): void
    {
        $this->testResourceDir = __DIR__ . '/test-data';
    }

    protected function tearDown(): void
    {
        @unlink($this->testResourceDir . '/de-demo-dsc-list.json');
    }

    public function test_dsc_ok(): void
    {
        $mockedClient = static::createMock(Client::class);
        $response = new Response(200, [], file_get_contents($this->testResourceDir . '/germany_dsc_example_response.txt'));
        $mockedClient->method('request')->willReturn($response);

        /** @var GermanyTrustListClient|MockObject $germanyTrustListClient */
        $germanyTrustListClient = $this->getMockBuilder(GermanyTrustListClient::class)
            ->onlyMethods(['getClient'])
            ->setConstructorArgs([$this->testResourceDir, true])
            ->getMock();

        $germanyTrustListClient->method('getClient')->willReturn($mockedClient);

        static::assertEquals(
            GermanyTrustListClient::UPDATED,
            $germanyTrustListClient->update()
        );

        static::assertEquals(
            GermanyTrustListClient::SKIP,
            $germanyTrustListClient->update()
        );
    }
}
