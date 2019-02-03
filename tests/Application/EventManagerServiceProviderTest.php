<?php

namespace Bitty\Tests\Application;

use Bitty\Application\EventManagerServiceProvider;
use Bitty\EventManager\EventManagerInterface;
use Interop\Container\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class EventManagerServiceProviderTest extends TestCase
{
    /**
     * @var EventManagerServiceProvider
     */
    private $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new EventManagerServiceProvider();
    }

    public function testInstanceOf(): void
    {
        self::assertInstanceOf(ServiceProviderInterface::class, $this->fixture);
    }

    public function testGetFactories(): void
    {
        $actual = $this->fixture->getFactories();

        self::assertEquals([], $actual);
    }

    public function testGetExtensions(): void
    {
        $actual = $this->fixture->getExtensions();

        self::assertEquals(['event.manager'], array_keys($actual));
        self::assertIsCallable($actual['event.manager']);
    }

    public function testCallbackResponseWithoutPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);
        if (false === $callable) {
            self::fail('No callable found.');

            return;
        }

        $container = $this->createMock(ContainerInterface::class);
        $actual    = $callable($container);

        self::assertInstanceOf(EventManagerInterface::class, $actual);
    }

    public function testCallbackResponseWithPrevious(): void
    {
        $extensions = $this->fixture->getExtensions();
        $callable   = reset($extensions);
        if (false === $callable) {
            self::fail('No callable found.');

            return;
        }

        $container = $this->createMock(ContainerInterface::class);
        $previous  = $this->createMock(EventManagerInterface::class);
        $actual    = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }
}
