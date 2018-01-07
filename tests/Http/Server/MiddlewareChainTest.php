<?php

namespace Bizurkur\Bitty\Tests\Http\Server;

use Bizurkur\Bitty\Http\Server\MiddlewareChain;
use Bizurkur\Bitty\Http\Server\MiddlewareHandler;
use Bizurkur\Bitty\Http\Server\MiddlewareInterface;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareChainTest extends TestCase
{
    /**
     * @var MiddlewareChain
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new MiddlewareChain();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
    }

    public function testDefaultHandler()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->fixture->setDefaultHandler($handler);
        $actual = $this->fixture->getDefaultHandler();

        $this->assertSame($handler, $actual);
    }

    public function testNoMiddlewareCallsDefaultHandler()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->with($request);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testOneMiddleware()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middleware);

        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $handler);

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }

    public function testMultipleMiddlewares()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $middlewareA = $this->createMock(MiddlewareInterface::class);
        $middlewareB = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middlewareA);
        $this->fixture->add($middlewareB);

        $middlewareA->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(MiddlewareHandler::class));

        $this->fixture->setDefaultHandler($handler);
        $this->fixture->handle($request);
    }
}
