<?php

namespace Bitty\EventManager;

use Bitty\EventManager\EventManager;
use Bitty\EventManager\EventManagerInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class EventManagerServiceProvider implements ServiceProviderInterface
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
            'event.manager' => function (ContainerInterface $container, EventManagerInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                return new EventManager();
            },
        ];
    }
}
