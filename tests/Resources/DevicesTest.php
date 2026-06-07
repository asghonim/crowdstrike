<?php

namespace Zinad\Crowdstrike\Tests\Resources;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zinad\Crowdstrike\Auth\TokenManager;
use Zinad\Crowdstrike\Resources\Devices;

#[CoversClass(Devices::class)]
class DevicesTest extends TestCase
{
    private array $requestHistory = [];

    private function makeDevices(array $responses): Devices
    {
        $this->requestHistory = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->requestHistory));

        $httpClient = new Client(['handler' => $stack]);
        $tokenManager = $this->createStub(TokenManager::class);
        $tokenManager->method('getToken')->willReturn('fake-token');

        return new Devices($httpClient, $tokenManager);
    }

    private function okResponse(array $resources = []): Response
    {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'resources' => $resources,
            'errors' => [],
        ]));
    }

    public function testQueryBuildsCorrectUrl(): void
    {
        $devices = $this->makeDevices([$this->okResponse(['device-1'])]);

        $result = $devices->query(filter: "platform_name:'Windows'", limit: 50);

        $this->assertSame(['device-1'], $result['resources']);
        $uri = (string) $this->requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('/devices/queries/devices/v1', $uri);
        $this->assertStringContainsString('limit=50', $uri);
    }

    public function testScrollUsesScrollEndpoint(): void
    {
        $devices = $this->makeDevices([$this->okResponse(['device-1'])]);

        $devices->scroll(filter: "platform_name:'Linux'", limit: 100);

        $uri = (string) $this->requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('/devices/queries/devices-scroll/v1', $uri);
    }

    public function testGetByIdsPostsToEntitiesEndpoint(): void
    {
        $devices = $this->makeDevices([$this->okResponse([['id' => 'dev-1', 'hostname' => 'host1']])]);

        $devices->getByIds(['dev-1', 'dev-2']);

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString('/devices/entities/devices/v2', (string) $request->getUri());
        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame(['dev-1', 'dev-2'], $body['ids']);
    }

    public function testActionSendsActionName(): void
    {
        $devices = $this->makeDevices([$this->okResponse()]);

        $devices->action('contain', ['dev-1'], 'Testing containment');

        $request = $this->requestHistory[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $uri = (string) $request->getUri();
        $this->assertStringContainsString('action_name=contain', $uri);
    }

    public function testGetOnlineStateBuildsQueryString(): void
    {
        $devices = $this->makeDevices([$this->okResponse()]);

        $devices->getOnlineState(['dev-1', 'dev-2']);

        $uri = (string) $this->requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('/devices/entities/online-state/v1', $uri);
    }

    public function testListUsesCompiledEndpoint(): void
    {
        $devices = $this->makeDevices([$this->okResponse()]);

        $devices->list(limit: 25);

        $uri = (string) $this->requestHistory[0]['request']->getUri();
        $this->assertStringContainsString('/devices/combined/devices/v1', $uri);
        $this->assertStringContainsString('limit=25', $uri);
    }
}
