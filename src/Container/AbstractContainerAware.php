<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\ContainerInterface;
use Bizurkur\Bitty\Container\ContainerAwareInterface;

abstract class AbstractContainerAware implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container)
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
