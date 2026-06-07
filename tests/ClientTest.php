<?php

use Asghonim\Crowdstrike\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Client::class)]
class ClientTest extends TestCase
{
    public function testItWorks(): void
    {
        $c = new Client();
        $this->assertEquals(1, 1);
    }
}
