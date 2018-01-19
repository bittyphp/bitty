<?php

namespace Bitty\Container;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Container\ContainerInterface;
use Bitty\Container\Exception\NotFoundException;
use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var callable[]
     */
    protected $callables = [];

    /**
     * @var mixed[]
     */
    protected $cache = [];

    /**
     * @param callable[] $callables
     * @param ServiceProviderInterface[] $providers
     */
    public function __construct(array $callables = [], array $providers = [])
    {
        $this->callables = $callables;
        $this->register($providers);
    }

    /**
     * {@inheritDoc}
     */
    public function set($id, $callable)
    {
        if (isset($this->cache[$id])) {
            unset($this->cache[$id]);
        }

        $this->callables[$id] = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function has($id)
    {
        return isset($this->callables[$id]);
    }

    /**
     * {@inheritDoc}
     */
    public function get($id)
    {
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        if (isset($this->callables[$id])) {
            $this->cache[$id] = $this->callables[$id]($this);
            if ($this->cache[$id] instanceof ContainerAwareInterface) {
                $this->cache[$id]->setContainer($this);
            }

            return $this->cache[$id];
        }

        throw new NotFoundException(
            sprintf('Service "%s" does not exist.', $id)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function extend($id, $callable)
    {
        if (!isset($this->callables[$id])) {
            $this->callables[$id] = $callable;

            return;
        }

        $factory = $this->callables[$id];

        $this->callables[$id] = function (PsrContainerInterface $container) use ($factory, $callable) {
            $previous = $factory($container);

            return $callable($container, $previous);
        };
    }

    /**
     * {@inheritDoc}
     */
    public function register(array $providers)
    {
        foreach ($providers as $provider) {
            $this->callables = array_merge(
                $this->callables,
                $provider->getFactories()
            );
        }

        foreach ($providers as $provider) {
            $extensions = $provider->getExtensions();
            foreach ($extensions as $id => $extension) {
                $this->extend($id, $extension);
            }
        }
    }
}
