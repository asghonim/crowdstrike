<?php

namespace Zinad\Crowdstrike\Tests\E2E;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Zinad\Crowdstrike\Client;
use Zinad\Crowdstrike\Exception\ApiException;
use Zinad\Crowdstrike\Streaming\StreamConnection;

#[Group('e2e')]
#[CoversNothing]
class EventStreamsE2ETest extends TestCase
{
    private Client $client;
    private string $appId;

    protected function setUp(): void
    {
        $clientId     = getenv('CS_CLIENT_ID');
        $clientSecret = getenv('CS_CLIENT_SECRET');
        $baseUrl      = getenv('CS_BASE_URL') ?: Client::BASE_URL_US2;
        $this->appId  = getenv('CS_APP_ID') ?: 'zinad-e2e-test';

        if (!$clientId || !$clientSecret) {
            $this->markTestSkipped(
                'E2E tests require CS_CLIENT_ID and CS_CLIENT_SECRET environment variables.'
            );
        }

        $this->client = new Client($clientId, $clientSecret, $baseUrl);
    }

    public function testListAvailableStreamsReturnsAtLeastOneStream(): void
    {
        $response  = $this->client->eventStreams()->list($this->appId);
        $resources = $response['resources'] ?? [];

        $this->assertNotEmpty($resources, 'Expected at least one available stream.');
        $this->assertArrayHasKey('dataFeedURL', $resources[0]);
        $this->assertArrayHasKey('sessionToken', $resources[0]);
        $this->assertArrayHasKey('token', $resources[0]['sessionToken']);
        $this->assertArrayHasKey('expiration', $resources[0]['sessionToken']);
        $this->assertArrayHasKey('refreshActiveSessionInterval', $resources[0]);
        $this->assertNotEmpty($resources[0]['dataFeedURL']);
        $this->assertNotEmpty($resources[0]['sessionToken']['token']);
        $this->assertGreaterThan(0, $resources[0]['refreshActiveSessionInterval']);
    }

    public function testListAvailableStreamsFlatjsonFormat(): void
    {
        $response = $this->client->eventStreams()->list($this->appId, 'flatjson');

        $this->assertArrayHasKey('resources', $response);
    }

    public function testConnectReturnsStreamConnection(): void
    {
        $connection = $this->client->eventStreams()->connect($this->appId);

        $this->assertInstanceOf(StreamConnection::class, $connection);
        $this->assertNotEmpty($connection->getDataFeedUrl());
        $this->assertGreaterThan(0, $connection->getRefreshInterval());
        $this->assertStringContainsString('firehose', $connection->getDataFeedUrl());
    }

    public function testConsumeReceivesRealEvents(): void
    {
        $connection = $this->client->eventStreams()->connect($this->appId);

        $events    = [];
        $startTime = microtime(true);
        $timeout   = 20.0;
        $maxEvents = 5;

        $connection->consume(function (array $event) use (&$events, $startTime, $timeout, $maxEvents): bool {
            $events[] = $event;
            return count($events) < $maxEvents && (microtime(true) - $startTime) < $timeout;
        });

        $this->assertNotEmpty($events, 'Expected to receive at least one event within 20 seconds.');

        foreach ($events as $event) {
            $this->assertArrayHasKey('metadata', $event, 'Each event must have a metadata key.');
            $this->assertArrayHasKey('event', $event, 'Each event must have an event key.');
            $this->assertArrayHasKey('eventType', $event['metadata']);
            $this->assertArrayHasKey('offset', $event['metadata']);
            $this->assertNotEmpty($event['metadata']['eventType']);
        }
    }

    public function testConsumeOffsetsAreMonotonicallyIncreasing(): void
    {
        $connection = $this->client->eventStreams()->connect($this->appId);

        $offsets   = [];
        $startTime = microtime(true);

        $connection->consume(function (array $event) use (&$offsets, $startTime): bool {
            $offsets[] = (int) ($event['metadata']['offset'] ?? 0);
            return count($offsets) < 10 && (microtime(true) - $startTime) < 20.0;
        });

        $this->assertNotEmpty($offsets);

        for ($i = 1; $i < count($offsets); $i++) {
            $this->assertGreaterThanOrEqual(
                $offsets[$i - 1],
                $offsets[$i],
                "Offsets must be monotonically non-decreasing (got {$offsets[$i - 1]} then {$offsets[$i]})."
            );
        }
    }

    public function testConsumeStopsWhenCallbackReturnsFalse(): void
    {
        $connection = $this->client->eventStreams()->connect($this->appId);

        $count = 0;

        $connection->consume(function (array $event) use (&$count): bool {
            $count++;
            return false;
        });

        $this->assertSame(1, $count, 'consume() must stop after callback returns false.');
    }

    public function testConnectWithExplicitOffset(): void
    {
        // First pass: collect one event to get a real offset
        $connection = $this->client->eventStreams()->connect($this->appId);
        $firstOffset = null;

        $connection->consume(function (array $event) use (&$firstOffset): bool {
            $firstOffset = (int) ($event['metadata']['offset'] ?? null);
            return false;
        });

        $this->assertNotNull($firstOffset, 'Could not determine a starting offset.');

        // Second pass: resume from that same offset and verify we get it back
        $connection2    = $this->client->eventStreams()->connect($this->appId);
        $resumedOffsets = [];

        $connection2->consume(function (array $event) use (&$resumedOffsets): bool {
            $resumedOffsets[] = (int) ($event['metadata']['offset'] ?? 0);
            return count($resumedOffsets) < 3;
        }, offset: $firstOffset);

        $this->assertNotEmpty($resumedOffsets);
        $this->assertGreaterThanOrEqual(
            $firstOffset,
            $resumedOffsets[0],
            'Resuming from offset should yield events at or after that offset.'
        );
    }

    public function testThrowsApiExceptionWithInvalidAppId(): void
    {
        // An app_id longer than 32 characters is rejected by the API
        $this->expectException(ApiException::class);
        $this->client->eventStreams()->list(str_repeat('x', 33));
    }
}
