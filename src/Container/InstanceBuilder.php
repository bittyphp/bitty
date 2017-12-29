<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\Container\InstanceBuilderInterface;

class InstanceBuilder implements InstanceBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function build($class, array $arguments = [])
    {
        if (empty($arguments)) {
            return new $class();
        }

        ksort($arguments);

        $reflection = new \ReflectionClass($class);

        return $reflection->newInstanceArgs($arguments);
    }
}
