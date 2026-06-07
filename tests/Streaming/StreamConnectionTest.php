<?php

namespace Zinad\Crowdstrike\Tests\Streaming;

use Zinad\Crowdstrike\Exception\ApiException;
use Zinad\Crowdstrike\Streaming\StreamConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StreamConnection::class)]
class StreamConnectionTest extends TestCase
{
    private function makeConnection(
        string $feedUrl = 'https://firehose.example.com/feed',
        string $token = 'st=fake-token',
        int $refreshInterval = 1800,
        ?\Closure $onRefresh = null,
    ): StreamConnection {
        return new StreamConnection(
            dataFeedUrl: $feedUrl,
            sessionToken: $token,
            refreshInterval: $refreshInterval,
            onRefresh: $onRefresh ?? fn() => null,
        );
    }

    /**
     * Build a mock HTTP response body with newline-delimited JSON events.
     *
     * @param array[] $events
     */
    private function makeStreamBody(array $events): string
    {
        return implode("\n", array_map('json_encode', $events)) . "\n";
    }

    public function testGetDataFeedUrlReturnsConstructorValue(): void
    {
        $conn = $this->makeConnection(feedUrl: 'https://feed.example.com/stream');

        $this->assertSame('https://feed.example.com/stream', $conn->getDataFeedUrl());
    }

    public function testGetRefreshIntervalReturnsConstructorValue(): void
    {
        $conn = $this->makeConnection(refreshInterval: 900);

        $this->assertSame(900, $conn->getRefreshInterval());
    }

    public function testConsumeDecodesAndPassesEventsToCallback(): void
    {
        $events = [
            ['metadata' => ['eventType' => 'DetectionSummaryEvent', 'offset' => 1], 'event' => ['ComputerName' => 'host1']],
            ['metadata' => ['eventType' => 'DetectionSummaryEvent', 'offset' => 2], 'event' => ['ComputerName' => 'host2']],
        ];

        $conn = $this->stubConnectionWithBody($this->makeStreamBody($events));

        $received = [];
        $conn->consume(function (array $event) use (&$received): bool {
            $received[] = $event;
            return true;
        });

        $this->assertCount(2, $received);
        $this->assertSame('DetectionSummaryEvent', $received[0]['metadata']['eventType']);
        $this->assertSame('host1', $received[0]['event']['ComputerName']);
        $this->assertSame('host2', $received[1]['event']['ComputerName']);
    }

    public function testConsumeStopsWhenCallbackReturnsFalse(): void
    {
        $events = [
            ['metadata' => ['eventType' => 'A', 'offset' => 1], 'event' => []],
            ['metadata' => ['eventType' => 'B', 'offset' => 2], 'event' => []],
            ['metadata' => ['eventType' => 'C', 'offset' => 3], 'event' => []],
        ];

        $conn = $this->stubConnectionWithBody($this->makeStreamBody($events));

        $count = 0;
        $conn->consume(function (array $_event) use (&$count): bool {
            $count++;
            return $count < 2; // stop after 2nd event
        });

        $this->assertSame(2, $count);
    }

    public function testConsumeSkipsBlankLines(): void
    {
        $body = json_encode(['metadata' => ['eventType' => 'X', 'offset' => 1], 'event' => []]) . "\n\n\n" .
                json_encode(['metadata' => ['eventType' => 'Y', 'offset' => 2], 'event' => []]) . "\n";

        $conn = $this->stubConnectionWithBody($body);

        $received = [];
        $conn->consume(function (array $event) use (&$received): bool {
            $received[] = $event['metadata']['eventType'];
            return true;
        });

        $this->assertSame(['X', 'Y'], $received);
    }

    public function testConsumeSkipsInvalidJson(): void
    {
        $body = "not-valid-json\n" .
                json_encode(['metadata' => ['eventType' => 'Valid', 'offset' => 1], 'event' => []]) . "\n";

        $conn = $this->stubConnectionWithBody($body);

        $received = [];
        $conn->consume(function (array $event) use (&$received): bool {
            $received[] = $event['metadata']['eventType'];
            return true;
        });

        $this->assertSame(['Valid'], $received);
    }

    public function testConsumeAppendsOffsetToUrl(): void
    {
        $requestHistory = [];
        $event = ['metadata' => ['eventType' => 'T', 'offset' => 50], 'event' => []];
        $conn = $this->stubConnectionWithBody(json_encode($event) . "\n", $requestHistory);

        $conn->consume(fn() => false, offset: 50);

        $uri = (string) $requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('offset=50', $uri);
    }

    public function testConsumeAppendsOffsetWithAmpersandWhenQueryExists(): void
    {
        $requestHistory = [];
        $event = ['metadata' => ['eventType' => 'T', 'offset' => 10], 'event' => []];
        $conn = $this->stubConnectionWithBody(
            json_encode($event) . "\n",
            $requestHistory,
            feedUrl: 'https://firehose.example.com/feed?appId=test',
        );

        $conn->consume(fn() => false, offset: 10);

        $uri = (string) $requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('appId=test', $uri);
        $this->assertStringContainsString('offset=10', $uri);
        $this->assertStringContainsString('&', $uri);
    }

    public function testConsumeSetsAuthorizationHeader(): void
    {
        $requestHistory = [];
        $event = ['metadata' => ['eventType' => 'T', 'offset' => 1], 'event' => []];
        $conn = $this->stubConnectionWithBody(json_encode($event) . "\n", $requestHistory, token: 'st=my-session-token');

        $conn->consume(fn() => true);

        $authHeader = $requestHistory[0]['request']->getHeaderLine('Authorization');
        $this->assertSame('Token st=my-session-token', $authHeader);
    }

    public function testConsumeThrowsApiExceptionOnConnectionFailure(): void
    {
        $conn = new StreamConnection(
            dataFeedUrl: 'https://firehose.example.com/feed',
            sessionToken: 'st=fake',
            refreshInterval: 1800,
            onRefresh: fn() => null,
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Failed to open event stream');

        // No mock handler — will fail with a connection error
        $conn->consume(fn() => true);
    }

    /**
     * Helper: create a StreamConnection backed by a Guzzle mock returning the given body.
     *
     * @param string     $body           Raw stream body (newline-delimited JSON)
     * @param array      &$requestHistory Will be populated with recorded requests
     * @param string     $feedUrl        Override the feed URL
     * @param string     $token          Override the session token
     * @param \Closure|null $onRefresh   Override the refresh callback
     */
    private function stubConnectionWithBody(
        string $body,
        array &$requestHistory = [],
        string $feedUrl = 'https://firehose.example.com/feed',
        string $token = 'st=fake-token',
        ?\Closure $onRefresh = null,
    ): StreamConnection {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], Utils::streamFor($body)),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($requestHistory));
        $httpClient = new Client(['handler' => $stack]);

        // We need to inject the mock client; since StreamConnection creates its own
        // GuzzleClient internally, we use a test subclass.
        return new class(
            dataFeedUrl: $feedUrl,
            sessionToken: $token,
            refreshInterval: 1800,
            onRefresh: $onRefresh ?? fn() => null,
            httpClient: $httpClient,
        ) extends StreamConnection {
            public function __construct(
                string $dataFeedUrl,
                string $sessionToken,
                int $refreshInterval,
                \Closure $onRefresh,
                private readonly Client $httpClient,
            ) {
                parent::__construct($dataFeedUrl, $sessionToken, $refreshInterval, $onRefresh);
            }

            protected function makeHttpClient(): Client
            {
                return $this->httpClient;
            }
        };
    }
}
