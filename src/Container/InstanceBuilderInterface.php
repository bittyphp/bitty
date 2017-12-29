<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\Container\Exception\ContainerException;

interface InstanceBuilderInterface
{
    /**
     * Builds a new instance of an object.
     *
     * @param string $class Fully qualified classname to build.
     * @param array $arguments Arguments to call constructor with.
     *
     * @return object
     *
     * @throws ContainerException If unable to build.
     */
    public function build($class, array $arguments = []);
}
