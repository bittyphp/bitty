<?php

namespace Bizurkur\Bitty\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Sets a parameter value.
     *
     * @param string $name Name of parameter.
     * @param mixed $value Value of parameter.
     */
    public function setParameter($name, $value);

    /**
     * Checks if a parameter exists.
     *
     * @param string $name Name of parameter.
     *
     * @return bool
     */
    public function hasParameter($name);

    /**
     * Gets a parameter value.
     *
     * @param string $name Name of parameter.
     *
     * @return mixed
     *
     * @throws NotFoundExceptionInterface
     */
    public function getParameter($name);

    /**
     * Sets an instantiated service.
     *
     * @param string $id ID of service.
     * @param object $object Object representing the service.
     */
    public function set($id, $object);
}
