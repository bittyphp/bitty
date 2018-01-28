<?php

namespace Bitty\Container;

use Psr\Container\ContainerInterface as PsrContainerInterface;

trait ContainerAwareTrait
{
    /**
     * @var PsrContainerInterface
     */
    protected $container = null;

    /**
     * {@inheritDoc}
     */
    public function setContainer(PsrContainerInterface $container = null)
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
