<?php

namespace Bitty\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Sets an instantiated service.
     *
     * @param string $id ID of service.
     * @param object $object Object representing the service.
     */
    public function set($id, $object);
}
