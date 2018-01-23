<?php

namespace Bitty\Security\Context;

use Bitty\Security\Context\ContextMap;
use Bitty\Security\Context\ContextMapInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class ContextMapServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getFactories()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getExtensions()
    {
        return [
            'security.context' => function (ContainerInterface $container, ContextMapInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                return new ContextMap();
            },
        ];
    }
}
