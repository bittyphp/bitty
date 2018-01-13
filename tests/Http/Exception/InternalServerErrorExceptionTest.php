<?php

namespace Bitty\Tests\Http\Exception;

use Bitty\Http\Exception\HttpExceptionInterface;
use Bitty\Http\Exception\InternalServerErrorException;
use Bitty\Tests\TestCase;

class InternalServerErrorExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new InternalServerErrorException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Internal Server Error', $actual);
    }

    public function testCode()
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getCode();

        $this->assertEquals(500, $actual);
    }

    public function testTitle()
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getTitle();

        $this->assertEquals('500 Internal Server Error', $actual);
    }

    public function testDescription()
    {
        $fixture = new InternalServerErrorException();

        $actual = $fixture->getDescription();

        $description = 'The server encountered an unexpected condition '
            .'which prevented it from fulfilling the request.';
        $this->assertEquals($description, $actual);
    }
}
