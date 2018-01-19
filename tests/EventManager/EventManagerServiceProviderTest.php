<?php

namespace Bitty\Tests\EventManager;

use Bitty\EventManager\EventManagerInterface;
use Bitty\EventManager\EventManagerServiceProvider;
use Bitty\Tests\TestCase;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class EventManagerServiceProviderTest extends TestCase
{
    /**
     * @var EventManagerServiceProvider
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new EventManagerServiceProvider();
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

        $this->assertEquals(['event_manager'], array_keys($actual));
        $this->assertInternalType('callable', $actual['event_manager']);
    }

    public function testCallbackResponseWithoutPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);

        $container = $this->createMock(ContainerInterface::class);
        $actual    = $callable($container);

        $this->assertInstanceOf(EventManagerInterface::class, $actual);
    }

    public function testCallbackResponseWithPrevious()
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(EventManagerInterface::class);
        $actual    = $callable($container, $previous);

        $this->assertSame($previous, $actual);
    }
}
