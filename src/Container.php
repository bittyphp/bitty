<?php

namespace Bizurkur\Bitty;

use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\Exception\NotFoundException;
use Bizurkur\Bitty\Container\ServiceBuilderInterface;
use Bizurkur\Bitty\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * Array of services.
     *
     * @var mixed[]
     */
    protected $services = [];

    /**
     * Array of parameters.
     *
     * @var mixed[]
     */
    protected $parameters = [];

    /**
     * Service builder.
     *
     * @var ServiceBuilderInterface
     */
    protected $builder = null;

    /**
     * @param array $services
     * @param array $parameters
     * @param ServiceBuilderInterface|null $builder
     */
    public function __construct(
        array $services = [],
        array $parameters = [],
        ServiceBuilderInterface $builder = null
    ) {
        $this->services = $services;
        $this->parameters = $parameters;
        $this->builder = $builder;

        if ($this->builder instanceof ContainerAwareInterface) {
            // hooray for circular references
            $this->builder->setContainer($this);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function hasParameter($name)
    {
        return isset($this->parameters[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($name)
    {
        if (!isset($this->parameters[$name])) {
            throw new NotFoundException(
                sprintf('Parameter "%s" does not exist.', $name)
            );
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function set($id, $object)
    {
        $this->services[$id] = $object;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        if ('container' === $id) {
            return true;
        }

        return isset($this->services[$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if ('container' === $id) {
            return $this;
        }

        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (null !== $this->builder) {
            $this->services[$id] = $this->builder->build($id);

            return $this->services[$id];
        }

        throw new NotFoundException(
            sprintf('Service "%s" does not exist.', $id)
        );
    }
}
