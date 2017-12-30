<?php

namespace Bizurkur\Bitty\Tests\Http\Exception;

use Bizurkur\Bitty\Http\Exception\ConflictException;
use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Bizurkur\Bitty\Tests\TestCase;

class ConflictExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new ConflictException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new ConflictException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Conflict', $actual);
    }

    public function testCode()
    {
        $fixture = new ConflictException();

        $actual = $fixture->getCode();

        $this->assertEquals(409, $actual);
    }

    public function testTitle()
    {
        $fixture = new ConflictException();

        $actual = $fixture->getTitle();

        $this->assertEquals('409 Conflict', $actual);
    }

    public function testDescription()
    {
        $fixture = new ConflictException();

        $actual = $fixture->getDescription();

        $description = 'The request could not be completed due to a '
            .'conflict with the current state of the resource.';
        $this->assertEquals($description, $actual);
    }
}
