<?php

namespace Bizurkur\Bitty\Tests;

use Bizurkur\Bitty\Container;
use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\Exception\NotFoundException;
use Bizurkur\Bitty\Container\ServiceProviderInterface;
use Bizurkur\Bitty\ContainerInterface;
use Bizurkur\Bitty\Tests\Stubs\ServiceProviderStubInterface;
use Bizurkur\Bitty\Tests\TestCase;
use Psr\Container\ContainerInterface as PsrContainerInterface;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    protected $fixture = null;

    protected function setUp()
    {
        parent::setUp();

        $this->fixture = new Container();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(ContainerInterface::class, $this->fixture);
        $this->assertInstanceOf(PsrContainerInterface::class, $this->fixture);
    }

    /**
     * @dataProvider sampleHasParameter
     */
    public function testHasParameter($parameters, $name, $expected)
    {
        $this->fixture = new Container([], $parameters);

        $actual = $this->fixture->hasParameter($name);

        $this->assertSame($expected, $actual);
    }

    public function sampleHasParameter()
    {
        $name = uniqid();

        return [
            'has true' => [
                'parameters' => [$name => uniqid()],
                'name' => $name,
                'expected' => true,
            ],
            'has false' => [
                'parameters' => [],
                'name' => uniqid(),
                'expected' => false,
            ],
        ];
    }

    public function testGetParameter()
    {
        $name  = uniqid();
        $value = uniqid();

        $this->fixture->setParameter($name, $value);
        $actual = $this->fixture->getParameter($name);

        $this->assertEquals($value, $actual);
    }

    public function testGetParameterThrowsException()
    {
        $name = uniqid();

        $message = 'Parameter "'.$name.'" does not exist.';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->getParameter($name);
    }

    /**
     * @dataProvider sampleHas
     */
    public function testHas($services, $name, $expected)
    {
        $this->fixture = new Container($services);

        $actual = $this->fixture->has($name);

        $this->assertSame($expected, $actual);
    }

    public function sampleHas()
    {
        $name = uniqid();

        return [
            'has true' => [
                'services' => [$name => new \stdClass()],
                'name' => $name,
                'expected' => true,
            ],
            'has false' => [
                'services' => [],
                'name' => uniqid(),
                'expected' => false,
            ],
            'container true' => [
                'services' => [],
                'name' => 'container',
                'expected' => true,
            ],
        ];
    }

    public function testGet()
    {
        $name   = uniqid();
        $object = new \stdClass();

        $this->fixture->set($name, $object);
        $actual = $this->fixture->get($name);

        $this->assertSame($object, $actual);
    }

    public function testGetContainer()
    {
        $actual = $this->fixture->get('container');

        $this->assertSame($this->fixture, $actual);
    }

    public function testContainerCannotBeOverwritten()
    {
        $this->fixture->set('container', new \stdClass());

        $actual = $this->fixture->get('container');

        $this->assertSame($this->fixture, $actual);
    }

    public function testBuilderSetsContainerOnContainerAwareProvider()
    {
        $provider = $this->createMock(ServiceProviderStubInterface::class);

        $spy = $this->once();
        $provider->expects($spy)->method('setContainer');

        $fixture = new Container([], [], $provider);

        $actual = $spy->getInvocations()[0]->parameters[0];
        $this->assertSame($fixture, $actual);
    }

    public function testGetCallsProvider()
    {
        $name     = uniqid();
        $provider = $this->createMock(ServiceProviderInterface::class);

        $this->fixture = new Container([], [], $provider);

        $provider->expects($this->once())
            ->method('provide')
            ->with($name);

        $this->fixture->get($name);
    }

    public function testGetReturnsProviderResponse()
    {
        $object   = new \stdClass();
        $provider = $this->createMock(ServiceProviderInterface::class);
        $provider->method('provide')->willReturn($object);

        $this->fixture = new Container([], [], $provider);

        $actual = $this->fixture->get(uniqid());

        $this->assertSame($object, $actual);
    }

    public function testGetThrowsException()
    {
        $name = uniqid();

        $message = 'Service "'.$name.'" does not exist.';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->get($name);
    }
}
