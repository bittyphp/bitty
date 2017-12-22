<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\Container;

interface ContainerAwareInterface
{
    /**
     * Sets the container.
     *
     * @param Container $container
     */
    public function setContainer(Container $container);

    /**
     * Gets the container.
     *
     * @return Container
     */
    public function getContainer();
}
