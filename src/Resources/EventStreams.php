<?php

namespace Zinad\Crowdstrike\Resources;

use Zinad\Crowdstrike\Exception\ApiException;
use Zinad\Crowdstrike\Streaming\StreamConnection;

class EventStreams extends Resource
{
    /**
     * Discover all available event streams in your environment.
     *
     * Returns stream URLs, session tokens, refresh intervals, and partition info.
     * Scope required: Event Streams: READ
     *
     * @param string $appId   Connection identifier (max 32 alphanumeric chars, e.g. "my-siem")
     * @param string $format  Event format: "json" (default) or "flatjson"
     */
    public function list(string $appId, string $format = 'json'): array
    {
        return $this->httpGet('/sensors/entities/datafeed/v2', [
            'appId' => $appId,
            'format' => $format,
        ]);
    }

    /**
     * Refresh an active stream session to prevent it from expiring.
     *
     * Call this at least every refreshActiveSessionInterval seconds.
     * Scope required: Event Streams: READ
     *
     * @param string $appId     Connection identifier used when opening the stream
     * @param int    $partition Partition to refresh (default 0)
     */
    public function refresh(string $appId, int $partition = 0): array
    {
        return $this->httpPost(
            "/sensors/entities/datafeed-actions/v1/{$partition}",
            [],
            [
                'action_name' => 'refresh_active_stream_session',
                'appId' => $appId,
            ],
            ['Accept' => '*/*']  // refresh endpoint returns no body
        );
    }

    /**
     * Discover available streams and return a StreamConnection for the given partition.
     *
     * The returned StreamConnection is ready to consume events.
     * It automatically calls refresh() before the session expires.
     *
     * @param string $appId     Connection identifier (max 32 alphanumeric chars)
     * @param string $format    Event format: "json" or "flatjson"
     * @param int    $partition Data partition to connect to (default 0)
     *
     * @throws ApiException When no streams are available
     */
    public function connect(string $appId, string $format = 'json', int $partition = 0): StreamConnection
    {
        $response = $this->list($appId, $format);
        $resources = $response['resources'] ?? [];

        if (empty($resources)) {
            throw new ApiException('No event streams are available for appId: ' . $appId, 404);
        }

        $stream = $this->findPartition($resources, $partition) ?? $resources[0];

        return new StreamConnection(
            dataFeedUrl: $stream['dataFeedURL'],
            sessionToken: $stream['sessionToken']['token'],
            refreshInterval: (int) ($stream['refreshActiveSessionInterval'] ?? 1800),
            onRefresh: fn() => $this->refresh($appId, $partition),
        );
    }

    private function findPartition(array $resources, int $partition): ?array
    {
        foreach ($resources as $resource) {
            if (isset($resource['partition']) && (int) $resource['partition'] === $partition) {
                return $resource;
            }
        }

        // Fall back to using the array index as the partition number
        return $resources[$partition] ?? null;
    }
}
