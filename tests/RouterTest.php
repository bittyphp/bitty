<?php

namespace Bizurkur\Bitty\Tests;

use Bizurkur\Bitty\Router;
use Bizurkur\Bitty\Router\Exception\NotFoundException;
use Bizurkur\Bitty\Router\RouteInterface;
use Bizurkur\Bitty\RouterInterface;
use Bizurkur\Bitty\Tests\TestCase;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Router();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RouterInterface::class, $this->fixture);
    }

    public function testAdd()
    {
        $methods     = ['get', 'pOsT'];
        $path        = uniqid();
        $constraints = [uniqid()];
        $name        = uniqid();
        $callback    = function () {
        }

        $this->fixture->add($methods, $path, $callback, $constraints, $name);

        $actual = $this->fixture->get($name);

        $this->assertInstanceOf(RouteInterface::class, $actual);
        $this->assertEquals(['GET', 'POST'], $actual->getMethods());
        $this->assertEquals($path, $actual->getPath());
        $this->assertEquals($callback, $actual->getCallback());
        $this->assertEquals($constraints, $actual->getConstraints());
        $this->assertEquals($name, $actual->getName());
        $this->assertEquals('route_0', $actual->getIdentifier());
    }

    public function testAddWithoutNameUsesIdentifier()
    {
        $methods     = ['get', 'pOsT'];
        $path        = uniqid();
        $constraints = [uniqid()];
        $callback    = function () {
        }

        $this->fixture->add($methods, $path, $callback, $constraints);

        $actual = $this->fixture->get('route_0');

        $this->assertInstanceOf(RouteInterface::class, $actual);
        $this->assertEquals(['GET', 'POST'], $actual->getMethods());
        $this->assertEquals($path, $actual->getPath());
        $this->assertEquals($callback, $actual->getCallback());
        $this->assertEquals($constraints, $actual->getConstraints());
        $this->assertNull($actual->getName());
        $this->assertEquals('route_0', $actual->getIdentifier());
    }

    public function testMultipleAddsIncrementsIdentifier()
    {
        $nameA    = uniqid();
        $nameB    = uniqid();
        $callback = function () {
        }

        $this->fixture->add(uniqid(), uniqid(), $callback, [], $nameA);
        $this->fixture->add(uniqid(), uniqid(), $callback, [], $nameB);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);

        $this->assertEquals('route_0', $actualA->getIdentifier());
        $this->assertEquals('route_1', $actualB->getIdentifier());
    }

    public function testAddInvalidCallbackThrowsException()
    {
        $message = 'Callback must be a callable; NULL given.';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->add(uniqid(), uniqid(), null);
    }

    public function testRemoveRoute()
    {
        $name     = uniqid();
        $callback = function () {
        }

        $this->fixture->add(uniqid(), uniqid(), $callback, [], $name);
        $this->fixture->remove($name);

        $this->assertFalse($this->fixture->has($name));
    }

    public function testRemoveUndefinedRoute()
    {
        $name = uniqid();

        $this->fixture->remove($name);

        $this->assertFalse($this->fixture->has($name));
    }

    public function testHasExistingRoute()
    {
        $name     = uniqid();
        $callback = function () {
        }

        $this->fixture->add(uniqid(), uniqid(), $callback, [], $name);

        $actual = $this->fixture->has($name);

        $this->assertTrue($actual);
    }

    public function testHasNonExistentRoute()
    {
        $actual = $this->fixture->has(uniqid());

        $this->assertFalse($actual);
    }

    public function testGetExistingRoute()
    {
        $name     = uniqid();
        $callback = function () {
        }

        $this->fixture->add(uniqid(), uniqid(), $callback, [], $name);

        $actual = $this->fixture->get($name);

        $this->assertInstanceOf(RouteInterface::class, $actual);
    }

    public function testGetNonExistentRouteThrowsException()
    {
        $name = uniqid();

        $message = 'No route named "'.$name.'" exists.';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->get($name);
    }

    /**
     * @dataProvider sampleFind
     */
    public function testFind($routes, $path, $method, $expectedName, $expecedParams)
    {
        foreach ($routes as $route) {
            call_user_func_array([$this->fixture, 'add'], $route);
        }

        $actual = $this->fixture->find($path, $method);

        $this->assertEquals($expectedName, $actual->getName());
        $this->assertEquals($expecedParams, $actual->getParams());
    }

    public function sampleFind()
    {
        $nameA    = uniqid('name');
        $nameB    = uniqid('name');
        $pathA    = '/'.uniqid('path');
        $pathB    = '/'.uniqid('path');
        $paramA   = uniqid('param');
        $paramB   = uniqid('param');
        $callback = function () {
        }

        return [
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

    public function testFindThrowsException()
    {
        $message = 'Route not found';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->find(uniqid(), uniqid());
    }

    public function testGenerateUriThrowsException()
    {
        $name = uniqid();

        $message = 'No route named "'.$name.'" exists.';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->generateUri($name);
    }

    /**
     * @dataProvider sampleGenerateUri
     */
    public function testGenerateUri($route, $name, $params, $expected)
    {
        call_user_func_array([$this->fixture, 'add'], $route);

        $actual = $this->fixture->generateUri($name, $params);

        $this->assertEquals($expected, $actual);
    }

    public function sampleGenerateUri()
    {
        $name     = uniqid('name');
        $path     = '/'.uniqid('path');
        $paramA   = uniqid('param');
        $paramB   = uniqid('param');
        $callback = function () {
        }

        return [
            'no params' => [
                'route' => ['GET', $path, $callback, [], $name],
                'name' => $name,
                'params' => [],
                'expected' => $path,
            ],
            'one param' => [
                'route' => [
                    'GET',
                    $path.'/{paramA}',
                    $callback,
                    ['paramA' => '.+'],
                    $name,
                ],
                'name' => $name,
                'params' => ['paramA' => $paramA],
                'expected' => $path.'/'.$paramA,
            ],
            'multiple params' => [
                'route' => [
                    'GET',
                    $path.'/{paramA}/{paramB}',
                    $callback,
                    ['paramA' => '.+', 'paramB' => '.+'],
                    $name,
                ],
                'name' => $name,
                'params' => ['paramA' => $paramA, 'paramB' => $paramB],
                'expected' => $path.'/'.$paramA.'/'.$paramB,
            ],
        ];
    }
}
