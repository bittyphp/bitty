<?php

namespace Bitty\Router;

use Bitty\Router\Router;
use Bitty\Router\RouterInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class RouterServiceProvider implements ServiceProviderInterface
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
            'router' => function (ContainerInterface $container, RouterInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                return new Router();
            },
        ];
    }
}
