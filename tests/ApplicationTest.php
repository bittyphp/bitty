<?php

namespace Bitty\Tests;

use Bitty\Application;
use Bitty\Application\EventManagerServiceProvider;
use Bitty\Application\RequestServiceProvider;
use Bitty\Application\RouterServiceProvider;
use Bitty\Container\ContainerInterface;
use Bitty\Http\Stream;
use Bitty\Router\RouteInterface;
use Bitty\Router\RouterInterface;
use Bitty\Tests\Stubs\ContainerAwareMiddlewareStubInterface;
use Bitty\Tests\Stubs\ContainerAwareRequestHandlerStubInterface;
use Interop\Container\ServiceProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationTest extends TestCase
{
    /**
     * @var Application
     */
    private $fixture = null;

    /**
     * @var ContainerInterface|MockObject
     */
    private $container = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createContainer();

        $this->fixture = new Application($this->container);
    }

    public function testDefaultServicesRegistered(): void
    {
        $spy = self::exactly(3);
        $this->container->expects($spy)->method('register');

        new Application($this->container);

        $actual = [];
        foreach ($spy->getInvocations() as $invocation) {
            $actual[] = get_class($invocation->getParameters()[0]);
        }

        $expected = [
            EventManagerServiceProvider::class,
            RequestServiceProvider::class,
            RouterServiceProvider::class,
        ];

        sort($actual);
        sort($expected);

        self::assertEquals($expected, $actual);
    }

    public function testNoContainerSetsContainer(): void
    {
        $fixture = new Application();

        $actual = $fixture->getContainer();

        self::assertInstanceOf(ContainerInterface::class, $actual);
    }

    public function testGetContainer(): void
    {
        $actual = $this->fixture->getContainer();

        self::assertSame($this->container, $actual);
    }

    public function testContainerAwareMiddlewareSetsContainer(): void
    {
        $middleware = $this->createMock(ContainerAwareMiddlewareStubInterface::class);

        $middleware->expects(self::once())
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->add($middleware);
    }

    public function testRegister(): void
    {
        $provider = $this->createMock(ServiceProviderInterface::class);

        $this->container->expects(self::once())
            ->method('register')
            ->with($provider);

        $this->fixture->register($provider);
    }

    /**
     * @param string $method
     * @param string $expected
     *
     * @dataProvider sampleMapRoutes
     */
    public function testMapRoutes(string $method, string $expected): void
    {
        $path        = uniqid('path');
        $callable    = uniqid('callable');
        $constraints = [uniqid('key') => uniqid('value')];
        $name        = uniqid('name');

        $router = $this->createMock(RouterInterface::class);
        $this->setUpDependencies(null, null, null, $router);

        $router->expects(self::once())
            ->method('add')
            ->with($expected, $path, $callable, $constraints, $name);

        $this->fixture->$method($path, $callable, $constraints, $name);
    }

    /**
     * @param string $method
     *
     * @dataProvider sampleMapRoutes
     */
    public function testMapRoutesResponse(string $method): void
    {
        $route  = $this->createMock(RouteInterface::class);
        $router = $this->createConfiguredMock(
            RouterInterface::class,
            ['add' => $route]
        );
        $this->setUpDependencies(null, null, null, $router);

        $actual = $this->fixture->$method(uniqid(), uniqid(), [uniqid()], uniqid());

        self::assertSame($route, $actual);
    }

    public function sampleMapRoutes(): array
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

    public function testMap(): void
    {
        $methods     = [uniqid('method'), uniqid('method')];
        $path        = uniqid('path');
        $callable    = uniqid('callable');
        $constraints = [uniqid('key') => uniqid('value')];
        $name        = uniqid('name');

        $router = $this->createMock(RouterInterface::class);
        $this->setUpDependencies(null, null, null, $router);

        $router->expects(self::once())
            ->method('add')
            ->with($methods, $path, $callable, $constraints, $name);

        $this->fixture->map($methods, $path, $callable, $constraints, $name);
    }

    public function testMapResponse(): void
    {
        $route  = $this->createMock(RouteInterface::class);
        $router = $this->createConfiguredMock(
            RouterInterface::class,
            ['add' => $route]
        );
        $this->setUpDependencies(null, null, null, $router);

        $actual = $this->fixture->map(uniqid(), uniqid(), uniqid(), [uniqid()], uniqid());

        self::assertSame($route, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testContainerAwareRouteHandlerSetsContainer(): void
    {
        $routeHandler = $this->createMock(ContainerAwareRequestHandlerStubInterface::class);
        $this->setUpDependencies(null, null, $routeHandler);

        $routeHandler->expects(self::once())
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->run();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunCallsRouteHandlerHandle(): void
    {
        $request      = $this->createMock(ServerRequestInterface::class);
        $routeHandler = $this->createMock(RequestHandlerInterface::class);
        $this->setUpDependencies($request, null, $routeHandler);

        $routeHandler->expects(self::once())
            ->method('handle')
            ->with($request);

        $this->fixture->run();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunCallsMiddleware(): void
    {
        $request        = $this->createMock(ServerRequestInterface::class);
        $response       = $this->createResponse();
        $requestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->setUpDependencies($request, null, $requestHandler);

        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->fixture->add($middleware);

        $middleware->expects(self::once())
            ->method('process')
            ->with($request, self::isInstanceOf(RequestHandlerInterface::class))
            ->willReturn($response);

        $this->fixture->run();
    }

    /**
     * @param array $headers
     * @param array $expected
     *
     * @runInSeparateProcess
     * @dataProvider sampleHeaders
     */
    public function testRunSetsResponseHeaders(array $headers, array $expected): void
    {
        if (!function_exists('xdebug_get_headers')) {
            self::markTestSkipped('xdebug_get_headers() is not available.');

            return;
        }

        $code     = rand(400, 404);
        $response = $this->createResponse($headers, '', $code);
        $this->setUpDependencies(null, $response, null);

        $this->fixture->run();
        $actual = xdebug_get_headers();

        self::assertEquals($expected, $actual);
        self::assertEquals($code, http_response_code());
    }

    public function sampleHeaders(): array
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
    public function testRunOutputsResponseBody(): void
    {
        $body     = uniqid('body');
        $response = $this->createResponse([], $body);
        $this->setUpDependencies(null, $response);

        ob_start();
        $this->fixture->run();
        $actual = ob_get_contents();
        ob_end_clean();

        self::assertEquals($body, $actual);
    }

    /**
     * Creates a container.
     *
     * @return ContainerInterface|MockObject
     */
    private function createContainer(): ContainerInterface
    {
        return $this->createMock(ContainerInterface::class);
    }

    /**
     * Creates a response.
     *
     * @param array $headers
     * @param string $body
     *
     * @return ResponseInterface|MockObject
     */
    private function createResponse(
        array $headers = [],
        string $body = '',
        int $code = 200
    ): ResponseInterface {
        return $this->createConfiguredMock(
            ResponseInterface::class,
            [
                'getHeaders' => $headers,
                'getBody' => new Stream($body),
                'getProtocolVersion' => '1.1',
                'getReasonPhrase' => 'OK',
                'getStatusCode' => $code,
            ]
        );
    }

    /**
     * Sets up dependencies.
     *
     * @param ServerRequestInterface|null $request
     * @param ResponseInterface|null $response
     * @param RequestHandlerInterface|null $requestHandler
     * @param RouterInterface|null $router
     */
    private function setUpDependencies(
        ?ServerRequestInterface $request = null,
        ?ResponseInterface $response = null,
        ?RequestHandlerInterface $requestHandler = null,
        ?RouterInterface $router = null
    ): void {
        if ($request === null) {
            $request = $this->createMock(ServerRequestInterface::class);
        }
        if ($response === null) {
            $response = $this->createResponse();
        }
        if ($requestHandler === null) {
            $requestHandler = $this->createMock(RequestHandlerInterface::class);
        }
        if ($router === null) {
            $router = $this->createMock(RouterInterface::class);
        }

        if ($requestHandler instanceof MockObject) {
            $requestHandler->method('handle')->willReturn($response);
        }

        $this->container->method('get')
            ->willReturnMap(
                [
                    ['request', $request],
                    ['route.handler', $requestHandler],
                    ['router', $router],
                ]
            );
    }
}
