<?php

namespace Bitty\Http;

use Bitty\Http\Response;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseServiceProvider implements ServiceProviderInterface
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
            'response' => function (ContainerInterface $container, ResponseInterface $previous = null) {
                if ($previous) {
                    return $previous;
                }

                return new Response();
            },
        ];
    }
}
