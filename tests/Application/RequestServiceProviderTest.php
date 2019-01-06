<?php

namespace Bitty\Tests\Application;

use Bitty\Application\RequestServiceProvider;
use Interop\Container\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestServiceProviderTest extends TestCase
{
    /**
     * @var RequestServiceProvider
     */
    protected $fixture = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixture = new RequestServiceProvider();
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

        self::assertEquals(['request'], array_keys($actual));
        self::assertIsCallable($actual['request']);
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

        self::assertInstanceOf(ServerRequestInterface::class, $actual);
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
        $previous  = $this->createMock(ServerRequestInterface::class);
        $actual    = $callable($container, $previous);

        self::assertSame($previous, $actual);
    }
}
