<?php

namespace Bitty\Tests\Http\Server;

use Bitty\Http\Server\MiddlewareChain;
use Bitty\Http\Server\MiddlewareHandler;
use Bitty\Http\Server\MiddlewareInterface;
use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Tests\Stubs\ContainerAwareMiddlewareStubInterface;
use Bitty\Tests\Stubs\ContainerAwareRequestHandlerStubInterface;
use Bitty\Tests\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareChainTest extends TestCase
{
    /**
     * @var MiddlewareChain
     */
    protected $fixture = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->fixture = new MiddlewareChain($this->container);
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

    public function testContainerAwareDefaultHandler()
    {
        $handler = $this->createMock(ContainerAwareRequestHandlerStubInterface::class);

        $handler->expects($this->once())
            ->method('setContainer')
            ->with($this->container);

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

    public function testContainerAwareMiddleware()
    {
        $middleware = $this->createMock(ContainerAwareMiddlewareStubInterface::class);
        $middleware->expects($this->once())
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->add($middleware);
    }
}
