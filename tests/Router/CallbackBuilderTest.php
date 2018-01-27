<?php

namespace Bitty\Tests\Router;

use Bitty\Router\CallbackBuilder;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\Exception\RouterException;
use Bitty\Tests\Stubs\InvokableContainerAwareStubInterface;
use Bitty\Tests\Stubs\InvokableResponseStub;
use Bitty\Tests\TestCase;
use Psr\Container\ContainerInterface;

class CallbackBuilderTest extends TestCase
{
    /**
     * @var CallbackBuilder
     */
    protected $fixture = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->fixture = new CallbackBuilder($this->container);
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(CallbackBuilderInterface::class, $this->fixture);
    }

    public function testBuildSetsContainerOnClosure()
    {
        $callback = function () {
            if (!$this instanceof ContainerInterface) {
                $this->fail('Container not set');
            }
        };

        $actual = $this->fixture->build($callback);

        $this->assertNotSame($callback, $actual[0]);
        $this->assertNull($actual[1]);
        $this->assertNull($actual[0]());
    }

    /**
     * @dataProvider sampleCallbacks
     */
    public function testBuildChecksContainer($callback, $class)
    {
        $this->container->expects($this->once())
            ->method('has')
            ->with($class)
            ->willReturn(false);

        $this->fixture->build($callback);
        // InvokableContainerAwareStubInterface
    }

    public function testBuildGetsFromContainer()
    {
        $callback = InvokableContainerAwareStubInterface::class;
        $object   = $this->createMock($callback);

        $this->container->method('has')->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with($callback)
            ->willReturn($object);

        $this->fixture->build($callback);
    }

    public function testBuildSetsContainerAware()
    {
        $callback = InvokableContainerAwareStubInterface::class;
        $object   = $this->createMock($callback);

        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn($object);

        $object->expects($this->once())
            ->method('setContainer')
            ->with($this->container);

        $this->fixture->build($callback);
    }

    /**
     * @dataProvider sampleCallbacks
     */
    public function testBuildInvokable($callback, $class, $method)
    {
        $actual = $this->fixture->build($callback);

        $this->assertInstanceOf($class, $actual[0]);
        $this->assertEquals($method, $actual[1]);
    }

    public function sampleCallbacks()
    {
        $method = uniqid('method');

        return [
            [InvokableResponseStub::class, InvokableResponseStub::class, null],
            [InvokableResponseStub::class.':'.$method, InvokableResponseStub::class, $method],
        ];
    }

    /**
     * @dataProvider sampleInvalidCallbacks
     */
    public function testInvalidCallbackThrowsException($invalid, $expected)
    {
        $message = 'Callback must be a string or instance of \Closure; '.$expected.' given.';
        $this->setExpectedException(RouterException::class, $message);

        $this->fixture->build($invalid);
    }

    public function sampleInvalidCallbacks()
    {
        return [
            [null, 'NULL'],
            [[], 'array'],
            [(object) [], 'object'],
        ];
    }

    public function testMalformedCallbackThrowsException()
    {
        $callback = uniqid().':'.uniqid().':'.uniqid();

        $message = 'Callback "'.$callback.'" is malformed.';
        $this->setExpectedException(RouterException::class, $message);

        $this->fixture->build($callback);
    }
}
