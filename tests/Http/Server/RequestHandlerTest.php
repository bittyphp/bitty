<?php

namespace Bizurkur\Bitty\Tests\Http\Server;

use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Http\Exception\InternalServerErrorException;
use Bizurkur\Bitty\Http\Exception\NotFoundException;
use Bizurkur\Bitty\Http\Server\RequestHandler;
use Bizurkur\Bitty\Http\Server\RequestHandlerInterface;
use Bizurkur\Bitty\Router\RouteInterface;
use Bizurkur\Bitty\Router\RouterInterface;
use Bizurkur\Bitty\Tests\Stubs\InvokableContainerAwareStubInterface;
use Bizurkur\Bitty\Tests\Stubs\InvokableResponseStub;
use Bizurkur\Bitty\Tests\Stubs\InvokableStubInterface;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RequestHandlerTest extends TestCase
{
    /**
     * @var RequestHandler
     */
    protected $fixture = null;

    /**
     * @var RouterInterface
     */
    protected $router = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->router = $this->createMock(RouterInterface::class);

        $this->fixture = new RequestHandler($this->router);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RequestHandlerInterface::class, $this->fixture);
        $this->assertInstanceOf(ContainerAwareInterface::class, $this->fixture);
    }

    public function testHandleCallsRouter()
    {
        $path     = uniqid();
        $method   = uniqid();
        $request  = $this->createRequest($path, $method);
        $callback = function () {
        };

        $route = $this->createRoute($callback);
        $this->router->expects($this->once())
            ->method('find')
            ->with('/'.$path, $method)
            ->willReturn($route);

        $this->fixture->handle($request);
    }

    public function testHandleThrowsNotFoundException()
    {
        $request = $this->createRequest();
        $this->router->method('find')->willReturn(false);

        $message = 'Not Found';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->handle($request);
    }

    public function testHandleCallsClosureCallback()
    {
        $request  = $this->createRequest();
        $params   = [uniqid(), uniqid()];
        $self     = $this;
        $callback = function ($a, $b) use ($self, $request, $params) {
            $this->assertSame($request, $a);
            $this->assertSame($params, $b);
        };

        $route = $this->createRoute($callback, $params);
        $this->router->method('find')->willReturn($route);

        $this->fixture->handle($request);
    }

    public function testHandleReturnsClosureCallbackResponse()
    {
        $request  = $this->createRequest();
        $response = $this->createMock(ResponseInterface::class);
        $callback = function () use ($response) {
            return $response;
        };

        $route = $this->createRoute($callback);
        $this->router->method('find')->willReturn($route);

        $actual = $this->fixture->handle($request);

        $this->assertSame($response, $actual);
    }

    /**
     * @dataProvider sampleInvokables
     */
    public function testHandleCallsInvokableCallback($data)
    {
        $this->setContainer();

        $request  = $this->createRequest();
        $params   = [uniqid(), uniqid()];
        $callback = $this->createMock($data['class']);

        $route = $this->createRoute($callback, $params);
        $this->router->method('find')->willReturn($route);

        $callback->expects($this->once())
            ->method('__invoke')
            ->with($request, $params);

        $this->fixture->handle($request);
    }

    /**
     * @dataProvider sampleInvokables
     */
    public function testHandleSetsContainerOnInvokableCallback($data)
    {
        $this->setContainer();

        $request  = $this->createRequest();
        $callback = $this->createMock($data['class']);

        $route = $this->createRoute($callback);
        $this->router->method('find')->willReturn($route);

        $callback->expects($this->exactly($data['calls']))
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->handle($request);
    }

    /**
     * @dataProvider sampleInvokables
     */
    public function testHandleReturnsInvokableCallbackResponse($data)
    {
        $this->setContainer();

        $request  = $this->createRequest();
        $response = $this->createMock(ResponseInterface::class);
        $callback = $this->createConfiguredMock(
            $data['class'],
            ['__invoke' => $response]
        );

        $route = $this->createRoute($callback);
        $this->router->method('find')->willReturn($route);

        $actual = $this->fixture->handle($request);

        $this->assertSame($response, $actual);
    }

    public function testHandleCallsArrayCallback()
    {
        $this->setContainer();

        $request  = $this->createRequest();
        $callback = [InvokableResponseStub::class, '__invoke'];

        $route = $this->createRoute($callback);
        $this->router->method('find')->willReturn($route);

        $actual = $this->fixture->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $actual);
    }

    /**
     * @dataProvider sampleInvokables
     */
    public function testHandleCallsArrayObjectCallback($data)
    {
        $this->setContainer();

        $request    = $this->createRequest();
        $params     = [uniqid(), uniqid()];
        $controller = $this->createMock($data['class']);
        $callback   = [$controller, '__invoke'];

        $route = $this->createRoute($callback, $params);
        $this->router->method('find')->willReturn($route);

        $controller->expects($this->once())
            ->method('__invoke')
            ->with($request, $params);

        $this->fixture->handle($request);
    }

    /**
     * @dataProvider sampleInvokables
     */
    public function testHandleSetsContainerOnArrayCallback($data)
    {
        $this->setContainer();

        $request    = $this->createRequest();
        $controller = $this->createMock($data['class']);
        $callback   = [$controller, '__invoke'];

        $route = $this->createRoute($callback);
        $this->router->method('find')->willReturn($route);

        $controller->expects($this->exactly($data['calls']))
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->handle($request);
    }

    /**
     * @dataProvider sampleInvokables
     */
    public function testHandleReturnsArrayCallbackResponse($data)
    {
        $this->setContainer();

        $request    = $this->createRequest();
        $response   = $this->createMock(ResponseInterface::class);
        $controller = $this->createConfiguredMock(
            $data['class'],
            ['__invoke' => $response]
        );
        $callback   = [$controller, '__invoke'];

        $route = $this->createRoute($callback);
        $this->router->method('find')->willReturn($route);

        $actual = $this->fixture->handle($request);

        $this->assertSame($response, $actual);
    }

    public function sampleInvokables()
    {
        return [
            [
                [
                    'class' => InvokableStubInterface::class,
                    'calls' => 0,
                ],
            ],
            [
                [
                    'class' => InvokableContainerAwareStubInterface::class,
                    'calls' => 1,
                ],
            ],
        ];
    }

    public function testHandleThrowsInternalServerErrorException()
    {
        $path    = uniqid();
        $method  = uniqid();
        $request = $this->createRequest($path, $method);

        $route = $this->createRoute();
        $this->router->method('find')->willReturn($route);

        $message = 'Internal Server Error';
        $this->setExpectedException(InternalServerErrorException::class, $message);

        $this->fixture->handle($request);
    }

    /**
     * Creates a request.
     *
     * @param string $path
     * @param string $method
     *
     * @return ServerRequestInterface
     */
    protected function createRequest($path = '', $method = 'GET')
    {
        $uri = $this->createConfiguredMock(
            UriInterface::class,
            ['getPath' => $path]
        );

        return $this->createConfiguredMock(
            ServerRequestInterface::class,
            [
                'getUri' => $uri,
                'getMethod' => $method,
            ]
        );
    }

    /**
     * Creates a route.
     *
     * @param callback|null $callback
     * @param array $params
     *
     * @return RouteInterface
     */
    protected function createRoute($callback = null, array $params = [])
    {
        return $this->createConfiguredMock(
            RouteInterface::class,
            [
                'getCallback' => $callback,
                'getParams' => $params,
            ]
        );
    }

    /**
     * Creates a container and sets it on the fixture.
     */
    protected function setContainer()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->fixture->setContainer($this->container);
    }
}
