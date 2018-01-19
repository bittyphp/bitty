<?php

namespace Bitty\Container;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Sets a callable to build a service.
     *
     * @param string $id ID of service to build.
     * @param callable $callable Callable to build the service.
     */
    public function set($id, $callable);

    /**
     * Extends a callable.
     *
     * @param string $id
     * @param callable $callable
     */
    public function extend($id, $callable);

    /**
     * Registers a list of service providers.
     *
     * @param ServiceProviderInterface[] $providers
     */
    public function register(array $providers);
}
