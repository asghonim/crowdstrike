<?php

namespace Zinad\Crowdstrike;

use GuzzleHttp\Client as GuzzleClient;
use Zinad\Crowdstrike\Auth\TokenManager;
use Zinad\Crowdstrike\Resources\Alerts;
use Zinad\Crowdstrike\Resources\Detects;
use Zinad\Crowdstrike\Resources\DeviceControlPolicies;
use Zinad\Crowdstrike\Resources\Devices;
use Zinad\Crowdstrike\Resources\EventStreams;
use Zinad\Crowdstrike\Resources\FirewallPolicies;
use Zinad\Crowdstrike\Resources\HostGroups;
use Zinad\Crowdstrike\Resources\Incidents;
use Zinad\Crowdstrike\Resources\Intel;
use Zinad\Crowdstrike\Resources\Iocs;
use Zinad\Crowdstrike\Resources\PreventionPolicies;
use Zinad\Crowdstrike\Resources\RealTimeResponse;
use Zinad\Crowdstrike\Resources\SensorUpdatePolicies;
use Zinad\Crowdstrike\Resources\Spotlight;
use Zinad\Crowdstrike\Resources\UserManagement;

class Client
{
    public const BASE_URL_US1 = 'https://api.crowdstrike.com';
    public const BASE_URL_US2 = 'https://api.us-2.crowdstrike.com';
    public const BASE_URL_EU1 = 'https://api.eu-1.crowdstrike.com';
    public const BASE_URL_GOV1 = 'https://api.laggar.gcw.crowdstrike.com';

    private readonly GuzzleClient $httpClient;
    private readonly TokenManager $tokenManager;

    /**
     * Create a new CrowdStrike API client.
     *
     * @param string $clientId      OAuth2 client ID
     * @param string $clientSecret  OAuth2 client secret
     * @param string $baseUrl       API base URL (defaults to US-2)
     * @param array  $httpOptions   Additional Guzzle client options
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $baseUrl = self::BASE_URL_US2,
        array $httpOptions = [],
    ) {
        $this->httpClient = new GuzzleClient(array_merge([
            'base_uri' => rtrim($baseUrl, '/'),
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => true,
        ], $httpOptions));

        $this->tokenManager = new TokenManager($this->httpClient, $clientId, $clientSecret);
    }

    public function alerts(): Alerts
    {
        return new Alerts($this->httpClient, $this->tokenManager);
    }

    public function detects(): Detects
    {
        return new Detects($this->httpClient, $this->tokenManager);
    }

    public function devices(): Devices
    {
        return new Devices($this->httpClient, $this->tokenManager);
    }

    public function hostGroups(): HostGroups
    {
        return new HostGroups($this->httpClient, $this->tokenManager);
    }

    public function incidents(): Incidents
    {
        return new Incidents($this->httpClient, $this->tokenManager);
    }

    public function iocs(): Iocs
    {
        return new Iocs($this->httpClient, $this->tokenManager);
    }

    public function intel(): Intel
    {
        return new Intel($this->httpClient, $this->tokenManager);
    }

    public function preventionPolicies(): PreventionPolicies
    {
        return new PreventionPolicies($this->httpClient, $this->tokenManager);
    }

    public function firewallPolicies(): FirewallPolicies
    {
        return new FirewallPolicies($this->httpClient, $this->tokenManager);
    }

    public function deviceControlPolicies(): DeviceControlPolicies
    {
        return new DeviceControlPolicies($this->httpClient, $this->tokenManager);
    }

    public function sensorUpdatePolicies(): SensorUpdatePolicies
    {
        return new SensorUpdatePolicies($this->httpClient, $this->tokenManager);
    }

    public function realTimeResponse(): RealTimeResponse
    {
        return new RealTimeResponse($this->httpClient, $this->tokenManager);
    }

    public function spotlight(): Spotlight
    {
        return new Spotlight($this->httpClient, $this->tokenManager);
    }

    public function userManagement(): UserManagement
    {
        return new UserManagement($this->httpClient, $this->tokenManager);
    }

    public function eventStreams(): EventStreams
    {
        return new EventStreams($this->httpClient, $this->tokenManager);
    }
}
