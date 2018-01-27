<?php

namespace Bitty\Container;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
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
