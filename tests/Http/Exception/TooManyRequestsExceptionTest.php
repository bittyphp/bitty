<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\TooManyRequestsException;
use Bitty\Tests\TestCase;

class TooManyRequestsExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new TooManyRequestsException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Too Many Requests', $actual);
    }

    public function testCode()
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getCode();

        $this->assertEquals(429, $actual);
    }

    public function testTitle()
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getTitle();

        $this->assertEquals('429 Too Many Requests', $actual);
    }

    public function testDescription()
    {
        $fixture = new TooManyRequestsException();

        $actual = $fixture->getDescription();

        $description = 'Too many requests sent in a given amount of time.';
        $this->assertEquals($description, $actual);
    }
}
