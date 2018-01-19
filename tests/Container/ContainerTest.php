<?php

namespace Bitty\Tests\Container;

use Bitty\Container\Container;
use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerInterface;
use Bitty\Container\Exception\NotFoundException;
use Bitty\Tests\TestCase;
use Interop\Container\ServiceProviderInterface;
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
     * @dataProvider sampleHas
     */
    public function testHas($callables, $name, $expected)
    {
        $this->fixture = new Container($callables);

        $actual = $this->fixture->has($name);

        $this->assertSame($expected, $actual);
    }

    public function sampleHas()
    {
        $name = uniqid();

        return [
            'has true' => [
                'callables' => [
                    $name => function () {
                    },
                ],
                'name' => $name,
                'expected' => true,
            ],
            'has false' => [
                'callables' => [
                    $name => function () {
                    },
                ],
                'name' => uniqid(),
                'expected' => false,
            ],
        ];
    }

    public function testGet()
    {
        $name   = uniqid();
        $object = new \stdClass();
        $this->fixture->set($name, function () use ($object) {
            return $object;
        });

        $actual = $this->fixture->get($name);

        $this->assertSame($object, $actual);
    }

    public function testGetCachesValue()
    {
        $name = uniqid();
        $this->fixture->set($name, function () {
            return new \stdClass();
        });

        $actualA = $this->fixture->get($name);
        $actualB = $this->fixture->get($name);

        $this->assertSame($actualA, $actualB);
    }

    public function testSetResetsCache()
    {
        $name   = uniqid();
        $object = new \stdClass();
        $this->fixture->set($name, function () use ($object) {
            return $object;
        });

        $actualA = $this->fixture->get($name);

        $this->fixture->set($name, function () {
            return new \stdClass();
        });

        $actualB = $this->fixture->get($name);

        $this->assertNotSame($actualA, $actualB);
    }

    public function testGetSetsContainerOnContainerAwareService()
    {
        $name    = uniqid();
        $service = $this->createMock(ContainerAwareInterface::class);

        $this->fixture->set($name, function () use ($service) {
            return $service;
        });

        $service->expects($this->once())
            ->method('setContainer')
            ->with($this->fixture);

        $this->fixture->get($name);
    }

    public function testGetThrowsException()
    {
        $name = uniqid();

        $message = 'Service "'.$name.'" does not exist.';
        $this->setExpectedException(NotFoundException::class, $message);

        $this->fixture->get($name);
    }

    public function testExtendNonExistentId()
    {
        $name   = uniqid();
        $object = new \stdClass();

        $this->fixture->extend(
            $name,
            function (PsrContainerInterface $container, $previous = null) use ($object) {
                $this->assertNull($previous);

                return $object;
            }
        );

        $actual = $this->fixture->get($name);

        $this->assertSame($object, $actual);
    }

    public function testExtendExistingId()
    {
        $name    = uniqid();
        $objectA = new \stdClass();
        $objectB = new \stdClass();

        $this->fixture->set($name, function () use ($objectA) {
            return $objectA;
        });

        $this->fixture->extend(
            $name,
            function (PsrContainerInterface $container, $previous = null) use ($objectA, $objectB) {
                $this->assertSame($objectA, $previous);

                return $objectB;
            }
        );

        $actual = $this->fixture->get($name);

        $this->assertSame($objectB, $actual);
    }

    public function testRegisterNoProviders()
    {
        $actual = $this->fixture->register([]);

        $this->assertNull($actual);
    }

    public function testRegisterSingleProviderMultipleFactories()
    {
        $nameA   = uniqid();
        $nameB   = uniqid();
        $objectA = new \stdClass();
        $objectB = new \stdClass();

        $provider = $this->createConfiguredMock(
            ServiceProviderInterface::class,
            [
                'getFactories' => [
                    $nameA => function (PsrContainerInterface $container) use ($objectA) {
                        return $objectA;
                    },
                    $nameB => function (PsrContainerInterface $container) use ($objectB) {
                        return $objectB;
                    },
                ],
                'getExtensions' => [],
            ]
        );

        $this->fixture->register([$provider]);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);

        $this->assertSame($objectA, $actualA);
        $this->assertSame($objectB, $actualB);
    }

    public function testRegisterSingleProviderMultipleExtensions()
    {
        $nameA   = uniqid();
        $nameB   = uniqid();
        $objectA = new \stdClass();
        $objectB = new \stdClass();

        $this->fixture->set($nameA, function () use ($objectB) {
            return $objectB;
        });

        $provider = $this->createConfiguredMock(
            ServiceProviderInterface::class,
            [
                'getFactories' => [],
                'getExtensions' => [
                    $nameA => function (PsrContainerInterface $container, $previous = null) use ($objectA, $objectB) {
                        $this->assertSame($objectB, $previous);

                        return $objectA;
                    },
                    $nameB => function (PsrContainerInterface $container, $previous = null) use ($objectB) {
                        $this->assertNull($previous);

                        return $objectB;
                    },
                ],
            ]
        );

        $this->fixture->register([$provider]);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);

        $this->assertSame($objectA, $actualA);
        $this->assertSame($objectB, $actualB);
    }

    public function testRegisterMultipleProvidersMultipleFactories()
    {
        $nameA   = uniqid();
        $nameB   = uniqid();
        $nameC   = uniqid();
        $objectA = new \stdClass();
        $objectB = new \stdClass();
        $objectC = new \stdClass();
        $objectD = new \stdClass();

        $providerA = $this->createConfiguredMock(
            ServiceProviderInterface::class,
            [
                'getFactories' => [
                    $nameA => function (PsrContainerInterface $container) use ($objectA) {
                        return $objectA;
                    },
                    $nameB => function (PsrContainerInterface $container) use ($objectB) {
                        return $objectB;
                    },
                ],
                'getExtensions' => [],
            ]
        );

        $providerB = $this->createConfiguredMock(
            ServiceProviderInterface::class,
            [
                'getFactories' => [
                    $nameC => function (PsrContainerInterface $container) use ($objectC) {
                        return $objectC;
                    },
                    $nameB => function (PsrContainerInterface $container) use ($objectD) {
                        return $objectD;
                    },
                ],
                'getExtensions' => [],
            ]
        );

        $this->fixture->register([$providerA, $providerB]);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);
        $actualC = $this->fixture->get($nameC);

        $this->assertSame($objectA, $actualA);
        $this->assertSame($objectD, $actualB);
        $this->assertSame($objectC, $actualC);
    }

    public function testRegisterMultipleProvidersMultipleExtensions()
    {
        $nameA   = uniqid();
        $nameB   = uniqid();
        $objectA = new \stdClass();
        $objectB = new \stdClass();
        $objectC = new \stdClass();
        $objectD = new \stdClass();

        $providerA = $this->createConfiguredMock(
            ServiceProviderInterface::class,
            [
                'getFactories' => [
                    $nameA => function (PsrContainerInterface $container) use ($objectA) {
                        return $objectA;
                    },
                ],
                'getExtensions' => [
                    $nameB => function (PsrContainerInterface $container, $previous = null) use ($objectB) {
                        $this->assertNull($previous);

                        return $objectB;
                    },
                ],
            ]
        );

        $providerB = $this->createConfiguredMock(
            ServiceProviderInterface::class,
            [
                'getFactories' => [],
                'getExtensions' => [
                    $nameA => function (PsrContainerInterface $container, $previous = null) use ($objectA, $objectC) {
                        $this->assertSame($objectA, $previous);

                        return $objectC;
                    },
                    $nameB => function (PsrContainerInterface $container, $previous = null) use ($objectB, $objectD) {
                        $this->assertSame($objectB, $previous);

                        return $objectD;
                    },
                ],
            ]
        );

        $this->fixture->register([$providerA, $providerB]);

        $actualA = $this->fixture->get($nameA);
        $actualB = $this->fixture->get($nameB);

        $this->assertSame($objectC, $actualA);
        $this->assertSame($objectD, $actualB);
    }
}
