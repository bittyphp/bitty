<?php

namespace Bizurkur\Bitty\Tests\Http\Exception;

use Bizurkur\Bitty\Http\Exception\HttpException;
use Bizurkur\Bitty\Http\Exception\HttpExceptionInterface;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpExceptionTest extends TestCase
{
    public function testInstanceOf()
    {
        $fixture = new HttpException();

        $this->assertInstanceOf(HttpExceptionInterface::class, $fixture);
    }

    public function testMessage()
    {
        $message = uniqid();
        $fixture = new HttpException($message);

        $actual = $fixture->getMessage();

        $this->assertEquals($message, $actual);
    }

    public function testCode()
    {
        $code    = rand();
        $fixture = new HttpException(null, $code);

        $actual = $fixture->getCode();

        $this->assertEquals($code, $actual);
    }

    public function testRequest()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $fixture = new HttpException(null, null, $request);

        $actual = $fixture->getRequest();

        $this->assertEquals($request, $actual);
    }

    public function testResponse()
    {
        $response = $this->createMock(ResponseInterface::class);
        $fixture  = new HttpException(null, null, null, $response);

        $actual = $fixture->getResponse();

        $this->assertEquals($response, $actual);
    }

    public function testTitle()
    {
        $fixture = new HttpException();

        $actual = $fixture->getTitle();

        $this->assertEquals('', $actual);
    }

    public function testDescription()
    {
        $fixture = new HttpException();

        $actual = $fixture->getDescription();

        $this->assertEquals('', $actual);
    }
}
