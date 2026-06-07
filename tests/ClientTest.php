<?php

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Zinad\Crowdstrike\Client;
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

#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client('test-id', 'test-secret');
    }

    public function testBaseUrlConstants(): void
    {
        $this->assertSame('https://api.crowdstrike.com', Client::BASE_URL_US1);
        $this->assertSame('https://api.us-2.crowdstrike.com', Client::BASE_URL_US2);
        $this->assertSame('https://api.eu-1.crowdstrike.com', Client::BASE_URL_EU1);
        $this->assertSame('https://api.laggar.gcw.crowdstrike.com', Client::BASE_URL_GOV1);
    }

    public function testAlertsReturnsAlertsResource(): void
    {
        $this->assertInstanceOf(Alerts::class, $this->client->alerts());
    }

    public function testDetectsReturnsDetectsResource(): void
    {
        $this->assertInstanceOf(Detects::class, $this->client->detects());
    }

    public function testDevicesReturnsDevicesResource(): void
    {
        $this->assertInstanceOf(Devices::class, $this->client->devices());
    }

    public function testHostGroupsReturnsHostGroupsResource(): void
    {
        $this->assertInstanceOf(HostGroups::class, $this->client->hostGroups());
    }

    public function testIncidentsReturnsIncidentsResource(): void
    {
        $this->assertInstanceOf(Incidents::class, $this->client->incidents());
    }

    public function testIocsReturnsIocsResource(): void
    {
        $this->assertInstanceOf(Iocs::class, $this->client->iocs());
    }

    public function testIntelReturnsIntelResource(): void
    {
        $this->assertInstanceOf(Intel::class, $this->client->intel());
    }

    public function testPreventionPoliciesReturnsResource(): void
    {
        $this->assertInstanceOf(PreventionPolicies::class, $this->client->preventionPolicies());
    }

    public function testFirewallPoliciesReturnsResource(): void
    {
        $this->assertInstanceOf(FirewallPolicies::class, $this->client->firewallPolicies());
    }

    public function testDeviceControlPoliciesReturnsResource(): void
    {
        $this->assertInstanceOf(DeviceControlPolicies::class, $this->client->deviceControlPolicies());
    }

    public function testSensorUpdatePoliciesReturnsResource(): void
    {
        $this->assertInstanceOf(SensorUpdatePolicies::class, $this->client->sensorUpdatePolicies());
    }

    public function testRealTimeResponseReturnsResource(): void
    {
        $this->assertInstanceOf(RealTimeResponse::class, $this->client->realTimeResponse());
    }

    public function testSpotlightReturnsResource(): void
    {
        $this->assertInstanceOf(Spotlight::class, $this->client->spotlight());
    }

    public function testUserManagementReturnsResource(): void
    {
        $this->assertInstanceOf(UserManagement::class, $this->client->userManagement());
    }

    public function testEventStreamsReturnsResource(): void
    {
        $this->assertInstanceOf(EventStreams::class, $this->client->eventStreams());
    }

    public function testResourcesAreNewInstancesEachCall(): void
    {
        $this->assertNotSame($this->client->alerts(), $this->client->alerts());
        $this->assertNotSame($this->client->devices(), $this->client->devices());
    }
}
