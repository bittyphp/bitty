<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\BadRequestException;
use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Tests\TestCase;

class BadRequestExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new BadRequestException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Bad Request', $actual);
    }

    public function testCode()
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getCode();

        $this->assertEquals(400, $actual);
    }

    public function testTitle()
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getTitle();

        $this->assertEquals('400 Bad Request', $actual);
    }

    public function testDescription()
    {
        $fixture = new BadRequestException();

        $actual = $fixture->getDescription();

        $description = 'The request could not be understood by the server '
            .'due to malformed syntax.';
        $this->assertEquals($description, $actual);
    }
}
