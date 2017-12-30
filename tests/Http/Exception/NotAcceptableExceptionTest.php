<?php

namespace Bizurkur\Bitty\Tests\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Bizurkur\Bitty\Http\Exception\NotAcceptableException;
use Bizurkur\Bitty\Tests\TestCase;

class NotAcceptableExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new NotAcceptableException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Not Acceptable', $actual);
    }

    public function testCode()
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getCode();

        $this->assertEquals(406, $actual);
    }

    public function testTitle()
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getTitle();

        $this->assertEquals('406 Not Acceptable', $actual);
    }

    public function testDescription()
    {
        $fixture = new NotAcceptableException();

        $actual = $fixture->getDescription();

        $description = 'The resource is not capable of generating '
            .'responses acceptable to the requested accept headers.';
        $this->assertEquals($description, $actual);
    }
}
