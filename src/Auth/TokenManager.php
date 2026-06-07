<?php

namespace Zinad\Crowdstrike\Auth;

use Zinad\Crowdstrike\Exception\AuthenticationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TokenManager
{
    private ?string $accessToken = null;
    private ?int $expiresAt = null;

    public function __construct(
        private readonly Client $httpClient,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {}

    public function getToken(): string
    {
        if ($this->isTokenValid()) {
            return $this->accessToken;
        }

        $this->fetchToken();

        return $this->accessToken;
    }

    public function invalidate(): void
    {
        $this->accessToken = null;
        $this->expiresAt = null;
    }

    private function isTokenValid(): bool
    {
        return $this->accessToken !== null
            && $this->expiresAt !== null
            && time() < ($this->expiresAt - 60);
    }

    private function fetchToken(): void
    {
        try {
            $response = $this->httpClient->post('/oauth2/token', [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            if (empty($data['access_token'])) {
                throw new AuthenticationException('No access token in OAuth2 response');
            }

            $this->accessToken = $data['access_token'];
            $this->expiresAt = time() + ($data['expires_in'] ?? 1800);
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Failed to obtain OAuth2 token: ' . $e->getMessage(),
                0,
                [],
                $e
            );
        }
    }
}
