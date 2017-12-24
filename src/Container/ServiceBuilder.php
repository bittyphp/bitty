<?php

namespace Bizurkur\Bitty\Container;

use Bizurkur\Bitty\Container\ContainerAwareInterface;
use Bizurkur\Bitty\Container\ContainerAwareTrait;
use Bizurkur\Bitty\Container\ServiceBuilderInterface;

class ServiceBuilder implements ServiceBuilderInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Service configuration data.
     *
     * @var mixed[]
     */
    protected $services = null;

    /**
     * {@inheritDoc}
     */
    public function build($id)
    {
        if (!isset($this->services[$id])) {
            throw new NotFoundException(
                sprintf('Service "%s" does not exist.', $id)
            );
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
            list($class, $args) = $this->getParentData($id, $config['parent']);
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
            $this->addArguments($config['args'], $args);
        }

        if (empty($args)) {
            $service = new $class();
        } else {
            ksort($args);
            $reflection = new \ReflectionClass($class);
            $service = $reflection->newInstanceArgs($args);
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
    protected function getParentData($id, $parent)
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
            list($class, $args) = $this->getParentData($id, $parentConfig['parent']);
        }

        if (isset($parentConfig['class'])) {
            $class = $parentConfig['class'];
        }

        if (isset($parentConfig['args'])) {
            $this->addArguments($parentConfig['args'], $args);
        }

        return [$class, $args];
    }

    /**
     * Adds arguments to pass to a service constructor.
     *
     * @param mixed[] $args Arguments to add.
     * @param mixed[] $builtArgs Array to add arguments to.
     */
    protected function addArguments(array $args, array &$builtArgs)
    {
        foreach ($args as $index => $arg) {
            if (isset($arg['index'])) {
                $index = $arg['index'];
            }

            if (isset($arg['type']) && 'service' === $arg['type']) {
                if (isset($arg['id'])) {
                    $builtArgs[$index] = $this->container->get($arg['id']);
                } else {
                    $builtArgs[$index] = $this->buildService($arg['value']);
                }
            } else {
                $builtArgs[$index] = $arg['value'];
            }
        }
    }
}
