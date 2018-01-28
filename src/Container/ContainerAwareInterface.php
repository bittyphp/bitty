<?php

namespace Bitty\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerAwareInterface
{
    /**
     * Sets the container.
     *
     * @param PsrContainerInterface $container
     */
    public function setContainer(PsrContainerInterface $container = null);

    /**
     * Gets the container.
     *
     * @return PsrContainerInterface
     */
    public function getContainer();
}
