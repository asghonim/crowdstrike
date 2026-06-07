<?php

namespace Zinad\Crowdstrike\Tests\Auth;

use Zinad\Crowdstrike\Auth\TokenManager;
use Zinad\Crowdstrike\Exception\AuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TokenManager::class)]
class TokenManagerTest extends TestCase
{
    private function makeClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        return new Client(['handler' => HandlerStack::create($mock)]);
    }

    public function testGetTokenFetchesAndReturnsAccessToken(): void
    {
        $httpClient = $this->makeClient([
            new Response(200, [], json_encode([
                'access_token' => 'test-token-abc',
                'expires_in' => 1800,
            ])),
        ]);

        $manager = new TokenManager($httpClient, 'id', 'secret');

        $this->assertSame('test-token-abc', $manager->getToken());
    }

    public function testGetTokenCachesTokenOnSubsequentCalls(): void
    {
        $httpClient = $this->makeClient([
            new Response(200, [], json_encode([
                'access_token' => 'cached-token',
                'expires_in' => 1800,
            ])),
        ]);

        $manager = new TokenManager($httpClient, 'id', 'secret');

        $first = $manager->getToken();
        $second = $manager->getToken();

        $this->assertSame($first, $second);
    }

    public function testInvalidateForcesFetch(): void
    {
        $httpClient = $this->makeClient([
            new Response(200, [], json_encode([
                'access_token' => 'first-token',
                'expires_in' => 1800,
            ])),
            new Response(200, [], json_encode([
                'access_token' => 'second-token',
                'expires_in' => 1800,
            ])),
        ]);

        $manager = new TokenManager($httpClient, 'id', 'secret');

        $first = $manager->getToken();
        $manager->invalidate();
        $second = $manager->getToken();

        $this->assertSame('first-token', $first);
        $this->assertSame('second-token', $second);
    }

    public function testThrowsAuthenticationExceptionWhenNoAccessToken(): void
    {
        $httpClient = $this->makeClient([
            new Response(200, [], json_encode(['token_type' => 'bearer'])),
        ]);

        $manager = new TokenManager($httpClient, 'id', 'secret');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('No access token in OAuth2 response');
        $manager->getToken();
    }

    public function testThrowsAuthenticationExceptionOnHttpError(): void
    {
        $httpClient = $this->makeClient([
            new Response(401, [], json_encode(['errors' => [['message' => 'invalid credentials']]])),
        ]);

        $manager = new TokenManager($httpClient, 'id', 'secret');

        $this->expectException(AuthenticationException::class);
        $manager->getToken();
    }
}
