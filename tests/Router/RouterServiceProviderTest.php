<?php

namespace Bitty\Tests\Router;

use Bitty\Router\RouterInterface;
use Bitty\Router\RouterServiceProvider;
use Bitty\Tests\TestCase;
use Interop\Container\ServiceProviderInterface;
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

        $this->assertEquals(['router'], array_keys($actual));
        $this->assertInternalType('callable', $actual['router']);
    }

    public function testCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);

        $container = $this->createMock(ContainerInterface::class);
        $actual    = $callable($container);

        $this->assertInstanceOf(RouterInterface::class, $actual);
    }

    public function testCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(RouterInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }
}
