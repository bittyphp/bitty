<?php

namespace Bizurkur\Bitty\Tests\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Bizurkur\Bitty\Http\Exception\UnauthorizedException;
use Bizurkur\Bitty\Tests\TestCase;

class UnauthorizedExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new UnauthorizedException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getMessage();

        $this->assertEquals('Unauthorized', $actual);
    }

    public function testCode()
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getCode();

        $this->assertEquals(401, $actual);
    }

    public function testTitle()
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getTitle();

        $this->assertEquals('401 Unauthorized', $actual);
    }

    public function testDescription()
    {
        $fixture = new UnauthorizedException();

        $actual = $fixture->getDescription();

        $description = 'The request requires user authentication.';
        $this->assertEquals($description, $actual);
    }
}
