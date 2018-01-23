<?php

namespace Bitty\Tests\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\Route;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteMatcher;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Tests\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RouteMatcherTest extends TestCase
{
    /**
     * @var RouteMatcher
     */
    protected $fixture = null;

    /**
     * @var RouteCollectionInterface
     */
    protected $routes = null;

    protected function setUp()
    {
        parent::setUp();

        $this->routes = $this->createMock(RouteCollectionInterface::class);

        $this->fixture = new RouteMatcher($this->routes);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RouteMatcherInterface::class, $this->fixture);
    }

    /**
     * @dataProvider sampleMatch
     */
    public function testMatch($routeData, $path, $method, $expectedName, $expecedParams)
    {
        $request = $this->createRequest($method, $path);
        $routes  = [];
        foreach ($routeData as $data) {
            $routes[] = $this->createRoute($data[0], $data[1], $data[2], $data[3], $data[4]);
        }

        $this->setMockIteratorData($this->routes, $routes);

        $actual = $this->fixture->match($request);

        $this->assertEquals($expectedName, $actual->getName());
        $this->assertEquals($expecedParams, $actual->getParams());
    }

    public function sampleMatch()
    {
        $nameA    = uniqid('name');
        $nameB    = uniqid('name');
        $pathA    = '/'.uniqid('path');
        $pathB    = '/'.uniqid('path');
        $paramA   = uniqid('param');
        $paramB   = uniqid('param');
        $callback = function () {
        };

        return [
            'open route' => [
                'routes' => [
                    [[], $pathA, $callback, [], $nameA],
                ],
                'path' => $pathA,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => [],
            ],
            'simple route' => [
                'routes' => [
                    ['GET', $pathA, $callback, [], $nameA],
                ],
                'path' => $pathA,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => [],
            ],
            'simple route, multiple methods' => [
                'routes' => [
                    [['GET', 'POST'], $pathA, $callback, [], $nameA],
                ],
                'path' => $pathA,
                'method' => 'POST',
                'expectedName' => $nameA,
                'expectedParams' => [],
            ],
            'multiple simple routes, same path' => [
                'routes' => [
                    ['GET', $pathA, $callback, [], $nameA],
                    ['POST', $pathA, $callback, [], $nameB],
                ],
                'path' => $pathA,
                'method' => 'POST',
                'expectedName' => $nameB,
                'expectedParams' => [],
            ],
            'multiple simple routes, unique paths' => [
                'routes' => [
                    ['GET', $pathA, $callback, [], $nameA],
                    ['POST', $pathB, $callback, [], $nameB],
                ],
                'path' => $pathB,
                'method' => 'POST',
                'expectedName' => $nameB,
                'expectedParams' => [],
            ],
            'constraint route' => [
                'routes' => [
                    ['GET', $pathA.'/{paramA}', $callback, ['paramA' => '.+'], $nameA],
                ],
                'path' => $pathA.'/'.$paramA,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => ['paramA' => $paramA],
            ],
            'constraint route, multiple params' => [
                'routes' => [
                    ['GET', $pathA.'/{paramA}/{paramB}', $callback, ['paramA' => '\w+', 'paramB' => '.+'], $nameA],
                ],
                'path' => $pathA.'/'.$paramA.'/'.$paramB,
                'method' => 'GET',
                'expectedName' => $nameA,
                'expectedParams' => ['paramA' => $paramA, 'paramB' => $paramB],
            ],
            'multiple constraint routes, same path' => [
                'routes' => [
                    ['GET', $pathA.'/{paramA}', $callback, ['paramA' => '\d+'], $nameA],
                    ['GET', $pathA.'/{paramA}', $callback, ['paramA' => '\w+'], $nameB],
                ],
                'path' => $pathA.'/'.$paramA,
                'method' => 'GET',
                'expectedName' => $nameB,
                'expectedParams' => ['paramA' => $paramA],
            ],
        ];
    }

    public function testMatchThrowsException()
    {
        $request = $this->createRequest();
        $this->setMockIteratorData($this->routes, []);

        $message = 'Route not found';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->match($request);
    }

    /**
     * Creates a route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param callable $callback
     * @param array $constraints
     * @param string $name
     *
     * @return Route
     */
    protected function createRoute(
        $methods,
        $path,
        $callback,
        array $constraints,
        $name
    ) {
        return new Route($methods, $path, $callback, $constraints, $name);
    }

    /**
     * Creates a request.
     *
     * @param string $method
     * @param string $path
     *
     * @return ServerRequestInterface
     */
    protected function createRequest($method = 'GET', $path = '/')
    {
        $uri = $this->createConfiguredMock(UriInterface::class, ['getPath' => $path]);

        return $this->createConfiguredMock(
            ServerRequestInterface::class,
            [
                'getMethod' => $method,
                'getUri' => $uri,
            ]
        );
    }
}
