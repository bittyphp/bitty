<?php

namespace Bitty\Tests;

use Bitty\Application;
use Bitty\Application\EventManagerServiceProvider;
use Bitty\Application\RequestServiceProvider;
use Bitty\Application\RouterServiceProvider;
use Bitty\Container\Container;
use Bitty\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Bitty\Http\Stream;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Tests\Stubs\ContainerAwareMiddlewareStubInterface;
use Bitty\Tests\Stubs\ContainerAwareRequestHandlerStubInterface;
use Interop\Container\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    protected $fixture = null;

    /**
     * @var Container
     */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->createContainer();

        $this->fixture = new Application($this->container);
    }

    public function testDefaultServicesRegistered()
    {
        $spy = $this->once();
        $this->container->expects($spy)->method('register');

        new Application($this->container);

        $actual = [];
        foreach ($spy->getInvocations()[0]->getParameters()[0] as $item) {
            $actual[] = get_class($item);
        }

        $expected = [
            EventManagerServiceProvider::class,
            RequestServiceProvider::class,
            RouterServiceProvider::class,
        ];

        sort($actual);
        sort($expected);

        $this->assertEquals($expected, $actual);
    }

    public function testNoContainerSetsContainer()
    {
        $fixture = new Application();

        $actual = $fixture->getContainer();

        $this->assertInstanceOf(ContainerInterface::class, $actual);
    }

    public function testGetContainer()
    {
        $actual = $this->fixture->getContainer();

        $this->assertSame($this->container, $actual);
    }

    public function testContainerAwareMiddlewareSetsContainer()
    {
        $middleware = $this->createMock(ContainerAwareMiddlewareStubInterface::class);

        $middleware->expects($this->once())
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->add($middleware);
    }

    public function testRegister()
    {
        $provider = $this->createMock(ServiceProviderInterface::class);

        $this->container->expects($this->once())
            ->method('register')
            ->with([$provider]);

        $this->fixture->register([$provider]);
    }

    /**
     * @dataProvider sampleMapRoutes
     */
    public function testMapRoutes($method, $expected)
    {
        $path        = uniqid('path');
        $callable    = uniqid('callable');
        $constraints = [uniqid('key') => uniqid('value')];
        $name        = uniqid('name');

        $routes = $this->createMock(RouteCollectionInterface::class);
        $this->setUpDependencies(null, null, null, $routes);

        $routes->expects($this->once())
            ->method('add')
            ->with($expected, $path, $callable, $constraints, $name);

        $this->fixture->$method($path, $callable, $constraints, $name);
    }

    /**
     * @dataProvider sampleMapRoutes
     */
    public function testMapRoutesResponse($method, $expected)
    {
        $route  = $this->createMock(RouteInterface::class);
        $routes = $this->createConfiguredMock(
            RouteCollectionInterface::class,
            ['add' => $route]
        );
        $this->setUpDependencies(null, null, null, $routes);

        $actual = $this->fixture->$method(uniqid(), uniqid(), [uniqid()], uniqid());

        $this->assertSame($route, $actual);
    }

    public function sampleMapRoutes()
    {
        return [
            ['get', 'GET'],
            ['post', 'POST'],
            ['put', 'PUT'],
            ['patch', 'PATCH'],
            ['delete', 'DELETE'],
            ['options', 'OPTIONS'],
        ];
    }

    public function testMap()
    {
        $methods     = [uniqid('method'), uniqid('method')];
        $path        = uniqid('path');
        $callable    = uniqid('callable');
        $constraints = [uniqid('key') => uniqid('value')];
        $name        = uniqid('name');

        $routes = $this->createMock(RouteCollectionInterface::class);
        $this->setUpDependencies(null, null, null, $routes);

        $routes->expects($this->once())
            ->method('add')
            ->with($methods, $path, $callable, $constraints, $name);

        $this->fixture->map($methods, $path, $callable, $constraints, $name);
    }

    public function testMapResponse()
    {
        $route  = $this->createMock(RouteInterface::class);
        $routes = $this->createConfiguredMock(
            RouteCollectionInterface::class,
            ['add' => $route]
        );
        $this->setUpDependencies(null, null, null, $routes);

        $actual = $this->fixture->map(uniqid(), uniqid(), uniqid(), [uniqid()], uniqid());

        $this->assertSame($route, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testContainerAwareRouteHandlerSetsContainer()
    {
        $routeHandler = $this->createMock(ContainerAwareRequestHandlerStubInterface::class);
        $this->setUpDependencies(null, null, $routeHandler);

        $routeHandler->expects($this->once())
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->run();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunCallsRouteHandlerHandle()
    {
        $request      = $this->createMock(ServerRequestInterface::class);
        $routeHandler = $this->createMock(RequestHandlerInterface::class);
        $this->setUpDependencies($request, null, $routeHandler);

        $routeHandler->expects($this->once())
            ->method('handle')
            ->with($request);

        $this->fixture->run();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunCallsMiddleware()
    {
        $request        = $this->createMock(ServerRequestInterface::class);
        $response       = $this->createResponse();
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->setUpDependencies($request, null, $requestHandler);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middleware);

        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(RequestHandlerInterface::class))
            ->willReturn($response);

        $this->fixture->run();
    }

    /**
     * @runInSeparateProcess
     * @dataProvider sampleHeaders
     */
    public function testRunSetsResponseHeaders($headers, $expected)
    {
        if (!function_exists('xdebug_get_headers')) {
            $this->markTestSkipped('xdebug_get_headers() is not available.');

            return;
        }

        $response = $this->createResponse($headers);
        $this->setUpDependencies(null, $response, null);

        $this->fixture->run();
        $actual = xdebug_get_headers();

        $this->assertEquals($expected, $actual);
    }

    public function sampleHeaders()
    {
        $headerA = uniqid('header');
        $headerB = uniqid('header');
        $valueA  = uniqid('value');
        $valueB  = uniqid('value');
        $valueC  = uniqid('value');
        $valueD  = uniqid('value');

        return [
            'no headers' => [
                'headers' => [],
                'expected' => [],
            ],
            'single header, single value' => [
                'headers' => [$headerA => [$valueA]],
                'expected' => [$headerA.': '.$valueA],
            ],
            'single header, multiple values' => [
                'headers' => [$headerA => [$valueA, $valueB]],
                'expected' => [$headerA.': '.$valueA, $headerA.': '.$valueB],
            ],
            'multiple headers, single values' => [
                'headers' => [$headerA => [$valueA], $headerB => [$valueB]],
                'expected' => [$headerA.': '.$valueA, $headerB.': '.$valueB],
            ],
            'multiple headers, multiple values' => [
                'headers' => [
                    $headerA => [$valueA, $valueB],
                    $headerB => [$valueC, $valueD],
                ],
                'expected' => [
                    $headerA.': '.$valueA,
                    $headerA.': '.$valueB,
                    $headerB.': '.$valueC,
                    $headerB.': '.$valueD,
                ],
            ],
        ];
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunOutputsResponseBody()
    {
        $body     = uniqid('body');
        $response = $this->createResponse([], $body);
        $this->setUpDependencies(null, $response);

        ob_start();
        $this->fixture->run();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($body, $actual);
    }

    /**
     * Creates a container.
     *
     * @return ContainerInterface
     */
    protected function createContainer()
    {
        return $this->createMock(ContainerInterface::class);
    }

    /**
     * Creates a response.
     *
     * @param array $headers
     * @param string $body
     *
     * @return ResponseInterface
     */
    protected function createResponse(array $headers = [], $body = '')
    {
        return $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getHeaders' => $headers,
                'getBody' => new Stream($body),
            ]
        );
    }

    /**
     * Sets up dependencies.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param RequestHandlerInterface $requestHandler
     * @param RouteCollectionInterface $routes
     */
    protected function setUpDependencies(
        ServerRequestInterface $request = null,
        ResponseInterface $response = null,
        RequestHandlerInterface $requestHandler = null,
        RouteCollectionInterface $routes = null
    ) {
        if (null === $request) {
            $request = $this->createMock(ServerRequestInterface::class);
        }
        if (null === $response) {
            $response = $this->createResponse();
        }
        if (null === $requestHandler) {
            $requestHandler = $this->createMock(RequestHandlerInterface::class);
        }
        if (null === $routes) {
            $routes = $this->createMock(RouteCollectionInterface::class);
        }

        $requestHandler->method('handle')->willReturn($response);

        $this->container->method('get')
            ->willReturnMap(
                [
                    ['request', $request],
                    ['route.handler', $requestHandler],
                    ['route.collection', $routes],
                ]
            );
    }
}
