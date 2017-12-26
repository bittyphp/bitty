<?php

namespace Bizurkur\Bitty\Tests;

use Bizurkur\Bitty\Container;
use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\Exception\NotFoundException;
use Bizurkur\Bitty\Container\ServiceBuilder;
use Bizurkur\Bitty\Container\ServiceBuilderInterface;
use Bizurkur\Bitty\ContainerInterface;
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

    public function testHasParameterTrue()
    {
        $name = uniqid();

        $this->fixture->setParameter($name, uniqid());
        $actual = $this->fixture->hasParameter($name);

        $this->assertTrue($actual);
    }

    public function testHasParameterFalse()
    {
        $name = uniqid();

        $actual = $this->fixture->hasParameter($name);

        $this->assertFalse($actual);
    }

    public function testGetParameter()
    {
        $name = uniqid();
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

    public function testHasIsTrue()
    {
        $name = uniqid();

        $this->fixture->set($name, new \stdClass());
        $actual = $this->fixture->has($name);

        $this->assertTrue($actual);
    }

    public function testHasContainerAlwaysTrue()
    {
        $actual = $this->fixture->has('container');

        $this->assertTrue($actual);
    }

    public function testHasIsFalse()
    {
        $name = uniqid();

        $actual = $this->fixture->has($name);

        $this->assertFalse($actual);
    }

    public function testGet()
    {
        $name = uniqid();
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

    public function testBuilderSetsContainerOnContainerAwareBuilder()
    {
        $builder = $this->createMock(ServiceBuilder::class);

        $spy = $this->once();
        $builder->expects($spy)->method('setContainer');

        $fixture = new Container([], [], $builder);

        $actual = $spy->getInvocations()[0]->parameters[0];
        $this->assertSame($fixture, $actual);
    }

    public function testGetCallsBuilder()
    {
        $name = uniqid();

        $builder = $this->createMock(ServiceBuilderInterface::class);
        $this->fixture = new Container([], [], $builder);

        $builder->expects($this->once())
            ->method('build')
            ->with($name);

        $this->fixture->get($name);
    }

    public function testGetReturnsBuilderResponse()
    {
        $builder = $this->createMock(ServiceBuilderInterface::class);
        $this->fixture = new Container([], [], $builder);

        $object = new \stdClass();
        $builder->method('build')->willReturn($object);

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
