<?php

namespace Bitty\Tests\Router;

use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Router\RouteMatcherInterface;
use Bitty\Router\Router;
use Bitty\Router\RouterInterface;
use Bitty\Router\UriGeneratorInterface;
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

    /**
     * @var UriGeneratorInterface
     */
    protected $uriGenerator = null;

    protected function setUp()
    {
        parent::setUp();

        $this->routes       = $this->createMock(RouteCollectionInterface::class);
        $this->matcher      = $this->createMock(RouteMatcherInterface::class);
        $this->uriGenerator = $this->createMock(UriGeneratorInterface::class);

        $this->fixture = new Router($this->routes, $this->matcher, $this->uriGenerator);
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

    public function testGet()
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

    public function testGenerateUri()
    {
        $name   = uniqid('name');
        $params = [uniqid('param'), uniqid('param')];
        $type   = uniqid('type');
        $uri    = uniqid('uri');

        $this->uriGenerator->expects($this->once())
            ->method('generate')
            ->with($name, $params, $type)
            ->willReturn($uri);

        $actual = $this->fixture->generateUri($name, $params, $type);

        $this->assertEquals($uri, $actual);
    }
}
