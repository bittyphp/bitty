<?php

namespace Bizurkur\Bitty\Tests;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Creates a mock without calling original constructor and clone methods.
     *
     * This matches up more similarly to newer PHPUnit versions.
     *
     * @param string $className Name of class to mock.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMock($className)
    {
        return $this->getMock($className, [], [], '', false, false);
    }

    /**
     * Creates a mock with preset method return values.
     *
     * This matches up more similarly to newer PHPUnit versions.
     *
     * @param string $className Name of class to mock.
     * @param mixed[] $methods List of return values keyed by method name.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createConfiguredMock($className, array $methods)
    {
        $mock = $this->createMock($className);

        foreach ($methods as $method => $value) {
            $mock->method($method)->willReturn($value);
        }

        return $mock;
    }
}
