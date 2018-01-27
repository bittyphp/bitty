<?php

namespace Bitty\Router;

use Bitty\Container\ContainerAwareInterface;
use Bitty\Router\CallbackBuilderInterface;
use Bitty\Router\Exception\RouterException;
use Psr\Container\ContainerInterface;

class CallbackBuilder implements CallbackBuilderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function build($callback)
    {
        if ($callback instanceof \Closure) {
            $callback = $callback->bindTo($this->container);

            return [$callback, null];
        }

        if (!is_string($callback)) {
            throw new RouterException(
                sprintf(
                    'Callback must be a string or instance of \Closure; %s given.',
                    gettype($callback)
                )
            );
        }

        list($class, $method) = $this->getClassAndMethod($callback);

        if ($this->container->has($class)) {
            $object = $this->container->get($class);
        } else {
            $object = new $class();
        }

        $this->applyContainerIfAware($object);

        return [$object, $method];
    }

    /**
     * Gets the class name and optional method name from the callback string.
     *
     * @param string $callback
     *
     * @return mixed[]
     */
    protected function getClassAndMethod($callback)
    {
        $parts = explode(':', $callback);
        if (2 === count($parts)) {
            return $parts;
        }

        if (1 !== count($parts)) {
            throw new RouterException(
                sprintf('Callback "%s" is malformed.', $callback)
            );
        }

        return [$callback, null];
    }

    /**
     * Sets the container on the object, if it's container aware.
     *
     * @param object $object
     */
    protected function applyContainerIfAware($object)
    {
        if ($object instanceof ContainerAwareInterface) {
            $object->setContainer($this->container);
        }
    }
}
