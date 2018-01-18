<?php

namespace Bitty\Http;

use Bitty\Http\Request;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestServiceProvider implements ServiceProviderInterface
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
            'request' => function (ContainerInterface $container, ServerRequestInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                return Request::createFromGlobals();
            },
        ];
    }
}
