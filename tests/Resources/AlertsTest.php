<?php

namespace Zinad\Crowdstrike\Tests\Resources;

use Zinad\Crowdstrike\Auth\TokenManager;
use Zinad\Crowdstrike\Exception\ApiException;
use Zinad\Crowdstrike\Exception\RateLimitException;
use Zinad\Crowdstrike\Resources\Alerts;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Alerts::class)]
class AlertsTest extends TestCase
{
    private array $requestHistory = [];

    private function makeAlerts(array $responses): Alerts
    {
        $this->requestHistory = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->requestHistory));

        $httpClient = new Client(['handler' => $stack]);
        $tokenManager = $this->createStub(TokenManager::class);
        $tokenManager->method('getToken')->willReturn('fake-bearer-token');

        return new Alerts($httpClient, $tokenManager);
    }

    private function alertsResponse(array $ids = ['alert-1', 'alert-2']): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'meta' => ['query_time' => 0.01, 'trace_id' => 'abc'],
            'resources' => $ids,
            'errors' => [],
        ]));
    }

    public function testQuerySendsGetRequest(): void
    {
        $alerts = $this->makeAlerts([$this->alertsResponse()]);

        $result = $alerts->query(filter: "status:'new'", limit: 10);

        $this->assertSame(['alert-1', 'alert-2'], $result['resources']);

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('/alerts/queries/alerts/v2', (string) $request->getUri());
        $this->assertStringContainsString("filter=", (string) $request->getUri());
        $this->assertStringContainsString("limit=10", (string) $request->getUri());
    }

    public function testQueryOmitsNullParams(): void
    {
        $alerts = $this->makeAlerts([$this->alertsResponse()]);

        $alerts->query();

        $uri = (string) $this->requestHistory[0]['request']->getUri();
        $this->assertStringNotContainsString('filter=', $uri);
        $this->assertStringNotContainsString('sort=', $uri);
    }

    public function testGetByIdsPostsBody(): void
    {
        $alerts = $this->makeAlerts([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resources' => [['id' => 'alert-1', 'status' => 'new']],
                'errors' => [],
            ])),
        ]);

        $result = $alerts->getByIds(['alert-1']);

        $this->assertCount(1, $result['resources']);

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame(['alert-1'], $body['ids']);
    }

    public function testUpdateSendsCorrectPayload(): void
    {
        $alerts = $this->makeAlerts([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['resources' => [], 'errors' => []])),
        ]);

        $alerts->update(['alert-1'], ['status' => 'closed']);

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('PATCH', $request->getMethod());
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame(['alert-1'], $body['ids']);
        $this->assertSame('closed', $body['status']);
    }

    public function testSetsAuthorizationHeader(): void
    {
        $alerts = $this->makeAlerts([$this->alertsResponse()]);
        $alerts->query();

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('Bearer fake-bearer-token', $request->getHeaderLine('Authorization'));
    }

    public function testThrowsApiExceptionOn400(): void
    {
        $alerts = $this->makeAlerts([
            new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'errors' => [['code' => 400, 'message' => 'Bad filter syntax']],
            ])),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Bad filter syntax');
        $this->expectExceptionCode(400);
        $alerts->query(filter: 'bad syntax!!!');
    }

    public function testThrowsRateLimitExceptionOn429(): void
    {
        $alerts = $this->makeAlerts([
            new Response(429, ['Content-Type' => 'application/json', 'X-RateLimit-RetryAfter' => '60'], json_encode([
                'errors' => [['code' => 429, 'message' => 'Rate limit exceeded']],
            ])),
        ]);

        $this->expectException(RateLimitException::class);
        $alerts->query();
    }

    public function testApiExceptionExposesStatusCodeAndErrors(): void
    {
        $alerts = $this->makeAlerts([
            new Response(403, ['Content-Type' => 'application/json'], json_encode([
                'errors' => [['code' => 403, 'message' => 'Access denied']],
            ])),
        ]);

        try {
            $alerts->query();
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertCount(1, $e->getErrors());
            $this->assertSame('Access denied', $e->getErrors()[0]['message']);
        }
    }
}
