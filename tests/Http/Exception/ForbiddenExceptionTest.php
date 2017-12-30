<?php

namespace Bizurkur\Bitty\Tests\Http\Exception;

use Bizurkur\Bitty\Http\Exception\ForbiddenException;
use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Bizurkur\Bitty\Tests\TestCase;

class ForbiddenExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new ForbiddenException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Forbidden', $actual);
    }

    public function testCode()
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getCode();

        $this->assertEquals(403, $actual);
    }

    public function testTitle()
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getTitle();

        $this->assertEquals('403 Forbidden', $actual);
    }

    public function testDescription()
    {
        $fixture = new ForbiddenException();

        $actual = $fixture->getDescription();

        $description = 'The server understood the request, but is refusing to fulfill it.';
        $this->assertEquals($description, $actual);
    }
}
