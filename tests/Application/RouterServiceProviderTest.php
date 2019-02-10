<?php

namespace Bitty\Tests\Application;

use Bitty\Application\RouterServiceProvider;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteHandler;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\RouterInterface;
use Bitty\Router\UriGeneratorInterface;
use Interop\Container\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterServiceProviderTest extends TestCase
{
    /**
     * @var RouterServiceProvider
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new RouterServiceProvider();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(ServiceProviderInterface::class, $this->fixture);
    }

    public function testGetFactories(): void
    {
        $actual = $this->fixture->getFactories();

        self::assertEquals([], $actual);
    }

    public function testGetExtensions(): void
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

        self::assertEquals($expected, array_keys($actual));
        self::assertIsCallable($actual['route.collection']);
        self::assertIsCallable($actual['route.matcher']);
        self::assertIsCallable($actual['route.callback.builder']);
        self::assertIsCallable($actual['uri.generator']);
        self::assertIsCallable($actual['router']);
        self::assertIsCallable($actual['route.handler']);
    }

    public function testRouteCollectionCallbackResponseWithoutPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.collection'];

        $container = $this->createMock(ContainerInterface::class);
        $actual    = $callable($container);

        self::assertInstanceOf(RouteCollectionInterface::class, $actual);
    }

    public function testRouteCollectionCallbackResponseWithPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.collection'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RouteCollectionInterface::class);
        $actual    = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }

    public function testRouteMatcherCallbackResponseWithoutPrevious(): void
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

        self::assertInstanceOf(RouteMatcherInterface::class, $actual);
    }

    public function testRouteMatcherCallbackResponseWithPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.matcher'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RouteMatcherInterface::class);
        $actual    = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }

    public function testRouteCallbackBuilderCallbackResponseWithoutPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.callback.builder'];

        $container = $this->createMock(ContainerInterface::class);
        $actual    = $callable($container);

        self::assertInstanceOf(CallbackBuilderInterface::class, $actual);
    }

    public function testRouteCallbackBuilderCallbackResponseWithPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.callback.builder'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(CallbackBuilderInterface::class);
        $actual    = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }

    public function testUriGeneratorCallbackChecksForDomain(): void
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

        $container->expects(self::once())->method('has')->with('uri.domain');

        $callable($container);
    }

    public function testUriGeneratorCallbackResponseWithoutPreviousWithDomain(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->expects(self::exactly(2))->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
                ['uri.domain', uniqid()],
            ]
        );

        $actual = $callable($container);

        self::assertInstanceOf(UriGeneratorInterface::class, $actual);
    }

    public function testUriGeneratorCallbackResponseWithoutPreviousWithoutDomain(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $routes    = $this->createMock(RouteCollectionInterface::class);
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(false);
        $container->expects(self::once())->method('get')->willReturnMap(
            [
                ['route.collection', $routes],
            ]
        );

        $actual = $callable($container);

        self::assertInstanceOf(UriGeneratorInterface::class, $actual);
    }

    public function testUriGeneratorCallbackResponseWithoutPrevious(): void
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

        self::assertInstanceOf(UriGeneratorInterface::class, $actual);
    }

    public function testUriGeneratorCallbackResponseWithPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['uri.generator'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(UriGeneratorInterface::class);
        $actual    = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }

    public function testRouterCallbackResponseWithoutPrevious(): void
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

        self::assertInstanceOf(RouterInterface::class, $actual);
    }

    public function testRouterCallbackResponseWithPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['router'];

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RouterInterface::class);
        $actual    = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }

    public function testRouteHandlerCallbackResponseWithoutPrevious(): void
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

        self::assertInstanceOf(RouteHandler::class, $actual);
    }

    public function testRouteHandlerCallbackResponseWithPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = $extensions['route.handler'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects(self::never())->method('get');

        $previous = $this->createMock(RouteHandler::class);
        $actual   = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }
}
