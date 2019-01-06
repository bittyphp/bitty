<?php

namespace Bitty\Tests\Application;

use Bitty\Application\RouterServiceProvider;
use Psr\Http\Server\RequestHandlerInterface;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteHandler;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\RouterInterface;
use Bitty\Router\UriGeneratorInterface;
use Interop\Container\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
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

        $expected = [
            'route.collection',
            'route.matcher',
            'route.callback.builder',
            'uri.generator',
            'router',
            'route.handler',
        ];

        $this->assertEquals($expected, array_keys($actual));
        $this->assertInternalType('callable', $actual['route.collection']);
        $this->assertInternalType('callable', $actual['route.matcher']);
        $this->assertInternalType('callable', $actual['route.callback.builder']);
        $this->assertInternalType('callable', $actual['uri.generator']);
        $this->assertInternalType('callable', $actual['router']);
        $this->assertInternalType('callable', $actual['route.handler']);
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

    public function testRouteCallbackBuilderCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.callback.builder'];

        $container = $this->createMock(ContainerInterface::class);
        $actual    = $callable($container);

        $this->assertInstanceOf(CallbackBuilderInterface::class, $actual);
    }

    public function testRouteCallbackBuilderCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.callback.builder'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(CallbackBuilderInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }

    public function testUriGeneratorCallbackChecksForDomain()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
            ]
        );

        $container->expects($this->once())->method('has')->with('uri.domain');

        $callable($container);
    }

    public function testUriGeneratorCallbackResponseWithoutPreviousWithDomain()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->expects($this->exactly(2))->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
                ['uri.domain', uniqid()],
            ]
        );

        $actual = $callable($container);

        $this->assertInstanceOf(UriGeneratorInterface::class, $actual);
    }

    public function testUriGeneratorCallbackResponseWithoutPreviousWithoutDomain()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(false);
        $container->expects($this->once())->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
            ]
        );

        $actual = $callable($container);

        $this->assertInstanceOf(UriGeneratorInterface::class, $actual);
    }

    public function testUriGeneratorCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
            ]
        );

        $actual = $callable($container);

        $this->assertInstanceOf(UriGeneratorInterface::class, $actual);
    }

    public function testUriGeneratorCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(UriGeneratorInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }

    public function testRouterCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['router'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $matcher   = $this->createMock(RouteMatcherInterface::class);
        $generator = $this->createMock(UriGeneratorInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
                ['route.matcher', $matcher],
                ['uri.generator', $generator],
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

    public function testRouteHandlerCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.handler'];

        $router    = $this->createMock(RouterInterface::class);
        $builder   = $this->createMock(CallbackBuilderInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturnMap(
            [
                ['router', $router],
                ['route.callback.builder', $builder],
            ]
        );

        $actual = $callable($container);

        $this->assertInstanceOf(RouteHandler::class, $actual);
    }

    public function testRouteHandlerCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.handler'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->never())->method('get');

        $previous = $this->createMock(RouteHandler::class);
        $actual   = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }
}
