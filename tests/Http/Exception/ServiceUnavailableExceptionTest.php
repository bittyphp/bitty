<?php

namespace Bizurkur\Bitty\Tests\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Bizurkur\Bitty\Http\Exception\ServiceUnavailableException;
use Bizurkur\Bitty\Tests\TestCase;

class ServiceUnavailableExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new ServiceUnavailableException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Service Unavailable', $actual);
    }

    public function testCode()
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getCode();

        $this->assertEquals(503, $actual);
    }

    public function testTitle()
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getTitle();

        $this->assertEquals('503 Service Unavailable', $actual);
    }

    public function testDescription()
    {
        $fixture = new ServiceUnavailableException();

        $actual = $fixture->getDescription();

        $description = 'The server is currently unable to handle the request '
            .'due to a temporary overloading or maintenance of the server.';
        $this->assertEquals($description, $actual);
    }
}
