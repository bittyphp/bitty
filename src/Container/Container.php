<?php

namespace Bitty\Container;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerInterface;
use Bitty\Container\Exception\NotFoundException;
use Bitty\Container\ServiceProviderInterface;

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
     * Service provider.
     *
     * @var ServiceProviderInterface
     */
    protected $provider = null;

    /**
     * @param array $services
     * @param array $parameters
     * @param ServiceProviderInterface|null $provider
     */
    public function __construct(
        array $services = [],
        array $parameters = [],
        ServiceProviderInterface $provider = null
    ) {
        $this->services   = $services;
        $this->parameters = $parameters;
        $this->provider   = $provider;

        if ($this->provider instanceof ContainerAwareInterface) {
            // hooray for circular references
            $this->provider->setContainer($this);
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

        if (null !== $this->provider) {
            $this->services[$id] = $this->provider->provide($id);

            return $this->services[$id];
        }

        throw new NotFoundException(
            sprintf('Service "%s" does not exist.', $id)
        );
    }
}
