<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\ContainerInterface;

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
