<?php

namespace Zinad\Crowdstrike\Streaming;

use Zinad\Crowdstrike\Exception\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class StreamConnection
{
    private float $lastRefreshedAt;

    /**
     * @param string   $dataFeedUrl      Firehose URL returned by listAvailableStreams
     * @param string   $sessionToken     Bearer token for the firehose connection
     * @param int      $refreshInterval  Seconds between required session refreshes
     * @param \Closure $onRefresh        Callable invoked to refresh the stream session
     */
    public function __construct(
        private readonly string $dataFeedUrl,
        private readonly string $sessionToken,
        private readonly int $refreshInterval,
        private readonly \Closure $onRefresh,
    ) {
        $this->lastRefreshedAt = microtime(true);
    }

    /**
     * Open the event stream and invoke $callback for every event.
     *
     * The callback receives a decoded event array:
     * ['metadata' => [...], 'event' => [...]]
     *
     * Return false from the callback to stop consuming.
     * Heartbeat events (eventType === '') are passed to the callback too —
     * filter them out if you don't need them.
     *
     * @param callable $callback fn(array $event): bool
     * @param int|null $offset   Start from a specific event offset number
     */
    public function consume(callable $callback, ?int $offset = null): void
    {
        $url = $this->buildUrl($offset);

        $httpClient = $this->makeHttpClient();

        try {
            $response = $httpClient->get($url, [
                'stream' => true,
                'headers' => [
                    'Authorization' => 'Token ' . $this->sessionToken,
                    'Accept' => 'application/json',
                    'Connection' => 'keep-alive',
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new ApiException('Failed to open event stream: ' . $e->getMessage(), 0, [], $e);
        }

        $body = $response->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $this->maybeRefresh();

            $chunk = $body->read(8192);
            if ($chunk === false || $chunk === '') {
                continue;
            }

            $buffer .= $chunk;

            while (($newlinePos = strpos($buffer, "\n")) !== false) {
                $line = trim(substr($buffer, 0, $newlinePos));
                $buffer = substr($buffer, $newlinePos + 1);

                if ($line === '') {
                    continue;
                }

                $event = json_decode($line, true);
                if ($event === null) {
                    continue;
                }

                if ($callback($event) === false) {
                    return;
                }
            }
        }
    }

    /**
     * The URL of the event stream (the firehose endpoint).
     */
    public function getDataFeedUrl(): string
    {
        return $this->dataFeedUrl;
    }

    /**
     * The token lifetime in seconds before a refresh is required.
     */
    public function getRefreshInterval(): int
    {
        return $this->refreshInterval;
    }

    protected function makeHttpClient(): Client
    {
        return new Client(['timeout' => 0, 'http_errors' => true]);
    }

    private function buildUrl(?int $offset): string
    {
        if ($offset === null) {
            return $this->dataFeedUrl;
        }

        $separator = str_contains($this->dataFeedUrl, '?') ? '&' : '?';
        return $this->dataFeedUrl . $separator . 'offset=' . $offset;
    }

    private function maybeRefresh(): void
    {
        $elapsed = microtime(true) - $this->lastRefreshedAt;

        if ($elapsed >= $this->refreshInterval - 60) {
            try {
                ($this->onRefresh)();
            } catch (\Throwable) {
                // Refresh failures are non-fatal; the stream continues until
                // the session actually expires, at which point the server will
                // close the connection and consume() will return naturally.
            }
            $this->lastRefreshedAt = microtime(true);
        }
    }
}
