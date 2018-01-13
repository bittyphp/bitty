<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\MethodNotAllowedException;
use Bitty\Tests\TestCase;

class MethodNotAllowedExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new MethodNotAllowedException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Method Not Allowed', $actual);
    }

    public function testCode()
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getCode();

        $this->assertEquals(405, $actual);
    }

    public function testTitle()
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getTitle();

        $this->assertEquals('405 Method Not Allowed', $actual);
    }

    public function testDescription()
    {
        $fixture = new MethodNotAllowedException();

        $actual = $fixture->getDescription();

        $description = 'The method specified is not allowed for the resource identified.';
        $this->assertEquals($description, $actual);
    }
}
