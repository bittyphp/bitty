<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\Container;
use Bizurkur\Bitty\Container\ContainerAwareInterface;

abstract class AbstractContainerAware implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container = null;

    /**
     * {@inheritDoc}
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getContainer()
    {
        return $this->container;
    }
}
