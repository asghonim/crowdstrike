<?php

namespace Zinad\Crowdstrike\Tests\Resources;

use Zinad\Crowdstrike\Auth\TokenManager;
use Zinad\Crowdstrike\Exception\ApiException;
use Zinad\Crowdstrike\Resources\EventStreams;
use Zinad\Crowdstrike\Streaming\StreamConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventStreams::class)]
class EventStreamsTest extends TestCase
{
    private array $requestHistory = [];

    private function makeEventStreams(array $responses): EventStreams
    {
        $this->requestHistory = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->requestHistory));

        $httpClient = new Client(['handler' => $stack]);
        $tokenManager = $this->createStub(TokenManager::class);
        $tokenManager->method('getToken')->willReturn('fake-token');

        return new EventStreams($httpClient, $tokenManager);
    }

    private function streamListResponse(array $overrides = []): Response
    {
        $resource = array_merge([
            'dataFeedURL' => 'https://firehose.us-2.crowdstrike.com/sensors/entities/datafeed/v2/0?appId=test-app',
            'sessionToken' => [
                'token' => 'st=eyJhbGciOiJSUzI1NiJ9',
                'expiration' => '2099-01-01T00:00:00.000000000Z',
            ],
            'refreshActiveSessionURL' => 'https://api.us-2.crowdstrike.com/sensors/entities/datafeed-actions/v1/0',
            'refreshActiveSessionInterval' => 1800,
            'partition' => 0,
        ], $overrides);

        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'meta' => ['query_time' => 0.01, 'trace_id' => 'abc'],
            'resources' => [$resource],
            'errors' => [],
        ]));
    }

    public function testListSendsGetWithAppIdAndFormat(): void
    {
        $streams = $this->makeEventStreams([$this->streamListResponse()]);

        $result = $streams->list('my-siem');

        $this->assertArrayHasKey('resources', $result);
        $this->assertCount(1, $result['resources']);

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('/sensors/entities/datafeed/v2', (string) $request->getUri());
        $this->assertStringContainsString('appId=my-siem', (string) $request->getUri());
        $this->assertStringContainsString('format=json', (string) $request->getUri());
    }

    public function testListPassesFlatjsonFormat(): void
    {
        $streams = $this->makeEventStreams([$this->streamListResponse()]);

        $streams->list('my-siem', 'flatjson');

        $uri = (string) $this->requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('format=flatjson', $uri);
    }

    public function testRefreshPostsToCorrectEndpoint(): void
    {
        $streams = $this->makeEventStreams([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resources' => [],
                'errors' => [],
            ])),
        ]);

        $streams->refresh('my-siem', 0);

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('/sensors/entities/datafeed-actions/v1/0', (string) $request->getUri());
        $this->assertStringContainsString('action_name=refresh_active_stream_session', (string) $request->getUri());
        $this->assertStringContainsString('appId=my-siem', (string) $request->getUri());
    }

    public function testRefreshUsesCorrectPartitionInPath(): void
    {
        $streams = $this->makeEventStreams([
            new Response(200, [], json_encode(['resources' => [], 'errors' => []])),
        ]);

        $streams->refresh('my-siem', 3);

        $uri = (string) $this->requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('/sensors/entities/datafeed-actions/v1/3', $uri);
    }

    public function testConnectReturnsStreamConnection(): void
    {
        $streams = $this->makeEventStreams([$this->streamListResponse()]);

        $connection = $streams->connect('my-siem');

        $this->assertInstanceOf(StreamConnection::class, $connection);
    }

    public function testConnectPopulatesConnectionFromStreamResponse(): void
    {
        $streams = $this->makeEventStreams([$this->streamListResponse()]);

        $connection = $streams->connect('my-siem');

        $this->assertSame(
            'https://firehose.us-2.crowdstrike.com/sensors/entities/datafeed/v2/0?appId=test-app',
            $connection->getDataFeedUrl()
        );
        $this->assertSame(1800, $connection->getRefreshInterval());
    }

    public function testConnectSelectsCorrectPartition(): void
    {
        $this->requestHistory = [];
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resources' => [
                    [
                        'dataFeedURL' => 'https://firehose.example.com/feed/0',
                        'sessionToken' => ['token' => 'token-0'],
                        'refreshActiveSessionInterval' => 1800,
                        'partition' => 0,
                    ],
                    [
                        'dataFeedURL' => 'https://firehose.example.com/feed/1',
                        'sessionToken' => ['token' => 'token-1'],
                        'refreshActiveSessionInterval' => 1800,
                        'partition' => 1,
                    ],
                ],
                'errors' => [],
            ])),
        ]);
        $stack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $stack]);
        $tokenManager = $this->createStub(TokenManager::class);
        $tokenManager->method('getToken')->willReturn('fake');

        $streams = new EventStreams($httpClient, $tokenManager);
        $connection = $streams->connect('my-siem', 'json', 1);

        $this->assertSame('https://firehose.example.com/feed/1', $connection->getDataFeedUrl());
    }

    public function testConnectThrowsWhenNoStreamsAvailable(): void
    {
        $streams = $this->makeEventStreams([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resources' => [],
                'errors' => [],
            ])),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('No event streams are available');
        $streams->connect('my-siem');
    }

    public function testConnectFallsBackToFirstResourceWhenPartitionNotFound(): void
    {
        $streams = $this->makeEventStreams([$this->streamListResponse()]);

        $connection = $streams->connect('my-siem', 'json', 99);

        $this->assertSame(
            'https://firehose.us-2.crowdstrike.com/sensors/entities/datafeed/v2/0?appId=test-app',
            $connection->getDataFeedUrl()
        );
    }
}
