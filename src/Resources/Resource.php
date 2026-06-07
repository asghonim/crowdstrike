<?php

namespace Zinad\Crowdstrike\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Zinad\Crowdstrike\Auth\TokenManager;
use Zinad\Crowdstrike\Exception\ApiException;
use Zinad\Crowdstrike\Exception\RateLimitException;

abstract class Resource
{
    public function __construct(
        protected readonly Client $httpClient,
        protected readonly TokenManager $tokenManager,
    ) {
    }

    protected function httpGet(string $path, array $query = []): array
    {
        return $this->request('GET', $path, $this->buildOptions(query: $query));
    }

    protected function httpPost(string $path, array $body = [], array $query = [], array $headers = []): array
    {
        return $this->request('POST', $path, $this->buildOptions(body: $body, query: $query), $headers);
    }

    protected function httpPatch(string $path, array $body = [], array $query = []): array
    {
        return $this->request('PATCH', $path, $this->buildOptions(body: $body, query: $query));
    }

    protected function httpDelete(string $path, array $query = []): array
    {
        return $this->request('DELETE', $path, $this->buildOptions(query: $query));
    }

    private function buildOptions(array $body = [], array $query = []): array
    {
        $options = [];

        if (!empty($body)) {
            $options['json'] = $body;
        }

        $filtered = array_filter($query, fn ($v) => $v !== null && $v !== '');
        if (!empty($filtered)) {
            $options['query'] = $filtered;
        }

        return $options;
    }

    private function request(string $method, string $path, array $options = [], array $extraHeaders = []): array
    {
        $options['headers'] = array_merge(
            [
                'Authorization' => 'Bearer ' . $this->tokenManager->getToken(),
                'Accept' => 'application/json',
            ],
            $extraHeaders
        );

        try {
            $response = $this->httpClient->request($method, $path, $options);
            $body = (string) $response->getBody();
            return $body !== '' ? (json_decode($body, true) ?? []) : [];
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = (string) $response->getBody();
            $data = json_decode($body, true) ?? [];
            $errors = $data['errors'] ?? [];
            $message = $errors[0]['message'] ?? $e->getMessage();

            if ($statusCode === 429) {
                $retryAfter = (int) ($response->getHeaderLine('X-RateLimit-RetryAfter') ?: 0);
                throw new RateLimitException($message, $retryAfter, $e);
            }

            throw new ApiException($message, $statusCode, $errors, $e);
        } catch (GuzzleException $e) {
            throw new ApiException($e->getMessage(), 0, [], $e);
        }
    }
}
