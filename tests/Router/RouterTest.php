<?php

namespace Bitty\Tests\Router;

use Bitty\Router\Exception\NotFoundException;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\Router;
use Bitty\Router\RouterInterface;
use Bitty\Tests\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    protected $fixture = null;

    /**
     * @var RouteCollectionInterface
     */
    protected $routes = null;

    /**
     * @var RouteMatcherInterface
     */
    protected $matcher = null;

    protected function setUp()
    {
        parent::setUp();

        $this->routes  = $this->createMock(RouteCollectionInterface::class);
        $this->matcher = $this->createMock(RouteMatcherInterface::class);

        $this->fixture = new Router($this->routes, $this->matcher);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RouterInterface::class, $this->fixture);
    }

    public function testAdd()
    {
        $methods     = [uniqid('method'), uniqid('method')];
        $path        = uniqid('path');
        $callable    = function () {
        };
        $constraints = [uniqid('key') => uniqid('value')];
        $name        = uniqid('name');

        $this->routes->expects($this->once())
            ->method('add')
            ->with($methods, $path, $callable, $constraints, $name);

        $this->fixture->add($methods, $path, $callable, $constraints, $name);
    }

    public function testHas()
    {
        $name = uniqid();
        $has  = (bool) rand(0, 1);

        $this->routes->expects($this->once())
            ->method('has')
            ->with($name)
            ->willReturn($has);

        $actual = $this->fixture->has($name);

        $this->assertEquals($has, $actual);
    }

    public function testGetExistingRoute()
    {
        $name  = uniqid();
        $route = $this->createMock(RouteInterface::class);

        $this->routes->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($route);

        $actual = $this->fixture->get($name);

        $this->assertSame($route, $actual);
    }

    public function testGetNonExistentRouteThrowsException()
    {
        $name = uniqid();

        $message = 'No route named "'.$name.'" exists.';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->get($name);
    }

    public function testFind()
    {
        $name    = uniqid();
        $route   = $this->createMock(RouteInterface::class);
        $request = $this->createMock(ServerRequestInterface::class);

        $this->matcher->expects($this->once())
            ->method('match')
            ->with($request)
            ->willReturn($route);

        $actual = $this->fixture->find($request);

        $this->assertSame($route, $actual);
    }

    public function testFindThrowsException()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $message = 'Route not found';
        $this->setExpectedException(NotFoundException::class, $message);

        $exception = new NotFoundException();
        $this->matcher->method('match')->willThrowException($exception);

        $this->fixture->find($request);
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
    public function testGenerateUri($path, $name, $params, $expected)
    {
        $route = $this->createConfiguredMock(RouteInterface::class, ['getPath' => $path]);

        $this->routes->expects($this->once())
            ->method('get')
            ->with($name)
            ->willReturn($route);

        $actual = $this->fixture->generateUri($name, $params);

        $this->assertEquals($expected, $actual);
    }

    public function sampleGenerateUri()
    {
        $name   = uniqid('name');
        $path   = '/'.uniqid('path');
        $paramA = uniqid('param');
        $paramB = uniqid('param');

        return [
            'no params' => [
                'path' => $path,
                'name' => $name,
                'params' => [],
                'expected' => $path,
            ],
            'one param' => [
                'path' => $path.'/{paramA}',
                'name' => $name,
                'params' => ['paramA' => $paramA],
                'expected' => $path.'/'.$paramA,
            ],
            'multiple params' => [
                'path' => $path.'/{paramA}/{paramB}',
                'name' => $name,
                'params' => ['paramA' => $paramA, 'paramB' => $paramB],
                'expected' => $path.'/'.$paramA.'/'.$paramB,
            ],
        ];
    }
}
