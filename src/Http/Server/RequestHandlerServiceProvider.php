<?php

namespace Bitty\Http\Server;

use Bitty\Http\Server\RequestHandler;
use Bitty\Http\Server\RequestHandlerInterface;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

class RequestHandlerServiceProvider implements ServiceProviderInterface
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
            'request.handler' => function (ContainerInterface $container, RequestHandlerInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                $router = $container->get('router');

                return new RequestHandler($router);
            },
        ];
    }
}
