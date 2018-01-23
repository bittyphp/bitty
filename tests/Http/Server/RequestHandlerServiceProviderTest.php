<?php

namespace Bitty\Tests\Http\Server;

use Bitty\Http\Server\RequestHandlerInterface;
use Bitty\Http\Server\RequestHandlerServiceProvider;
use Bitty\Router\RouterInterface;
use Bitty\Tests\TestCase;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class RequestHandlerServiceProviderTest extends TestCase
{
    /**
     * @var RequestHandlerServiceProvider
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new RequestHandlerServiceProvider();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ServiceProviderInterface::class, $this->fixture);
    }

    public function testGetFactories()
    {
        $actual = $this->fixture->getFactories();

        $this->assertEquals([], $actual);
    }

    public function testGetExtensions()
    {
        $actual = $this->fixture->getExtensions();

        $this->assertEquals(['request.handler'], array_keys($actual));
        $this->assertInternalType('callable', $actual['request.handler']);
    }

    public function testCallbackWithoutPreviousCallsContainer()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);

        $router    = $this->createMock(RouterInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with('router')
            ->willReturn($router);

        $callable($container);
    }

    public function testCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);

        $router    = $this->createMock(RouterInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn($router);

        $actual = $callable($container);

        $this->assertInstanceOf(RequestHandlerInterface::class, $actual);
    }

    public function testCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RequestHandlerInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }
}
