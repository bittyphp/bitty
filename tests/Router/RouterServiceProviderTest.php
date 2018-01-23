<?php

namespace Bitty\Tests\Router;

use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\RouterInterface;
use Bitty\Router\RouterServiceProvider;
use Bitty\Tests\TestCase;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class RouterServiceProviderTest extends TestCase
{
    /**
     * @var RouterServiceProvider
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new RouterServiceProvider();
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

        $expected = ['route.collection', 'route.matcher', 'router'];
        $this->assertEquals($expected, array_keys($actual));
        $this->assertInternalType('callable', $actual['route.collection']);
        $this->assertInternalType('callable', $actual['route.matcher']);
        $this->assertInternalType('callable', $actual['router']);
    }

    public function testRouteCollectionCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.collection'];

        $container = $this->createMock(ContainerInterface::class);
        $actual    = $callable($container);

        $this->assertInstanceOf(RouteCollectionInterface::class, $actual);
    }

    public function testRouteCollectionCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.collection'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RouteCollectionInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }

    public function testRouteMatcherCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.matcher'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
            ]
        );

        $actual = $callable($container);

        $this->assertInstanceOf(RouteMatcherInterface::class, $actual);
    }

    public function testRouteMatcherCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.matcher'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RouteMatcherInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }

    public function testRouterCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['router'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $matcher   = $this->createMock(RouteMatcherInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
                ['route.matcher', $matcher],
            ]
        );

        $actual = $callable($container);

        $this->assertInstanceOf(RouterInterface::class, $actual);
    }

    public function testRouterCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['router'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RouterInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }
}
