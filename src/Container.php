<?php

namespace Bizurkur\Bitty;

use Bizurkur\Bitty\Container\Exception\ContainerException;
use Bizurkur\Bitty\Container\Exception\NotFoundException;
use Bizurkur\Bitty\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * Array of service configuration settings.
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
     * Cached instantiated services.
     *
     * @var object[]
     */
    protected $cache = [];

    /**
     * @param array $services Array of service configuration settings.
     * @param array $parameters Array of parameters.
     */
    public function __construct(array $services = [], array $parameters = [])
    {
        $this->services = $services;
        $this->parameters = $parameters;
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
        $this->cache[$id] = $object;
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

        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        if (!isset($this->services[$id])) {
            throw new NotFoundException(
                sprintf('Service "%s" does not exist.', $id)
            );
        }

        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }

        $config = $this->services[$id];

        return $this->buildService($config, $id);
    }

    /**
     * Builds a new instance of a service.
     *
     * @param array $config Service configuration settings.
     * @param string|null $id ID of service.
     *
     * @return object
     *
     * @throws ContainerException
     */
    protected function buildService($config, $id = null)
    {
        $class = null;
        $args = [];

        if ($id && !empty($config['abstract'])) {
            throw new ContainerException(
                sprintf('Unable to build service "%s": it is an abstract.', $id)
            );
        }

        if (isset($config['parent'])) {
            list($class, $args) = $this->getServiceParent($id, $config['parent']);
        }

        if (isset($config['class'])) {
            $class = $config['class'];
        }

        if (!$class) {
            if ($id) {
                throw new ContainerException(
                    sprintf('Unable to build service "%s": no class defined.', $id)
                );
            }

            throw new ContainerException(
                'Unable to build anonymous service: no class defined.'
            );
        }

        if (isset($config['args'])) {
            $this->addServiceArguments($config['args'], $args);
        }

        if (empty($args)) {
            $service = new $class();
        } else {
            ksort($args);
            $reflection = new \ReflectionClass($class);
            $service = $reflection->newInstanceArgs($args);
        }

        if ($id && (!isset($config['cache']) || !empty($config['cache']))) {
            $this->cache[$id] = $service;
        }

        return $service;
    }

    /**
     * Gets a service's parent parameters.
     *
     * @param string $id
     * @param string $parent
     *
     * @return mixed[]
     *
     * @throws ContainerException
     */
    protected function getServiceParent($id, $parent)
    {
        $class = null;
        $args = [];

        if (!isset($this->services[$parent])) {
            if ($id) {
                throw new ContainerException(
                    sprintf(
                        'Unable to build service "%s": parent service "%s" does not exist.',
                        $id,
                        $parent
                    )
                );
            }

            throw new ContainerException(
                sprintf(
                    'Unable to build anonymous service: parent service "%s" does not exist.',
                    $parent
                )
            );
        }

        $parentConfig = $this->services[$parent];
        if (isset($parentConfig['parent'])) {
            list($class, $args) = $this->getServiceParent($id, $parentConfig['parent']);
        }

        if (isset($parentConfig['class'])) {
            $class = $parentConfig['class'];
        }

        if (isset($parentConfig['args'])) {
            $this->addServiceArguments($parentConfig['args'], $args);
        }

        return [$class, $args];
    }

    /**
     * Adds arguments to pass to a service constructor.
     *
     * @param mixed[] $args Arguments to add.
     * @param mixed[] $builtArgs Array to add arguments to.
     */
    protected function addServiceArguments(array $args, array &$builtArgs)
    {
        foreach ($args as $index => $arg) {
            if (isset($arg['index'])) {
                $index = $arg['index'];
            }

            if (isset($arg['type']) && 'service' === $arg['type']) {
                if (isset($arg['id'])) {
                    $builtArgs[$index] = $this->get($arg['id']);
                } else {
                    $builtArgs[$index] = $this->buildService($arg['value']);
                }
            } else {
                $builtArgs[$index] = $arg['value'];
            }
        }
    }
}
