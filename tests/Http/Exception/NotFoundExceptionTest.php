<?php

namespace Bizurkur\Bitty\Tests\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Bizurkur\Bitty\Http\Exception\NotFoundException;
use Bizurkur\Bitty\Tests\TestCase;

class NotFoundExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new NotFoundException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Not Found', $actual);
    }

    public function testCode()
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getCode();

        $this->assertEquals(404, $actual);
    }

    public function testTitle()
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getTitle();

        $this->assertEquals('404 Not Found', $actual);
    }

    public function testDescription()
    {
        $fixture = new NotFoundException();

        $actual = $fixture->getDescription();

        $description = 'The server cannot find the requested resource.';
        $this->assertEquals($description, $actual);
    }
}
