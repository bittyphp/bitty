<?php

namespace Bizurkur\Bitty\Container;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    /**
     * Sets the container.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Gets the container.
     *
     * @return ContainerInterface
     */
    public function getContainer();
}
