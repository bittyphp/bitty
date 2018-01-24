<?php

namespace Bitty\Tests\Router;

use Bitty\Router\RouteCollection;
use Bitty\Router\RouteCollectionInterface;
use Bitty\Router\RouteInterface;
use Bitty\Tests\TestCase;

class RouteCollectionTest extends TestCase
{
    /**
     * @var RouteCollection
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new RouteCollection();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(RouteCollectionInterface::class, $this->fixture);
    }

    public function testAdd()
    {
        $methods     = ['get', 'pOsT'];
        $path        = uniqid();
        $constraints = [uniqid()];
        $name        = uniqid();
        $callback    = function () {
        };

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
        };

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
        };

        $this->fixture->add(uniqid(), uniqid(), $callback, [], $nameA);
        $this->fixture->add(uniqid(), uniqid(), $callback, [], $nameB);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);

        $this->assertEquals('route_0', $actualA->getIdentifier());
        $this->assertEquals('route_1', $actualB->getIdentifier());
    }

    public function testAddInvalidCallbackThrowsException()
    {
        $message = 'Callback must be a callable or string; NULL given.';
        $this->setExpectedException(\InvalidArgumentException::class, $message);

        $this->fixture->add(uniqid(), uniqid(), null);
    }
}
