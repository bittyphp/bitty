<?php

namespace Bizurkur\Bitty\Tests\Http\Server;

use Bizurkur\Bitty\Http\Server\MiddlewareHandler;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareHandlerTest extends TestCase
{
    /**
     * @var MiddlewareHandler
     */
    protected $fixture = null;

    /**
     * @var MiddlewareInterface
     */
    protected $middleware = null;

    /**
     * @var RequestHandlerInterface
     */
    protected $handler = null;

    protected function setUp()
    {
        parent::setUp();

        $this->middleware = $this->createMock(MiddlewareInterface::class);
        $this->handler    = $this->createMock(RequestHandlerInterface::class);

        $this->fixture = new MiddlewareHandler($this->middleware, $this->handler);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testHandleCallsMiddleware()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $this->middleware->expects($this->once())
            ->method('process')
            ->with($request, $this->handler);

        $this->fixture->handle($request);
    }

    public function testMiddlewareResponseReturned()
    {
        $request  = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->middleware->method('process')->willReturn($response);

        $actual = $this->fixture->handle($request);

        $this->assertSame($response, $actual);
    }
}
