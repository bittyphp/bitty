<?php

namespace Bitty\Router;

use Bitty\Router\RouteInterface;

class Route implements RouteInterface
{
    /**
     * Route identifier.
     *
     * @var string
     */
    protected $identifier = null;

    /**
     * List of allowed request methods, e.g. GET, POST, etc.
     *
     * @var string[]
     */
    protected $methods = [];

    /**
     * Route path.
     *
     * @var string
     */
    protected $path = null;

    /**
     * Route callback.
     *
     * @var \Closure|string
     */
    protected $callback = null;

    /**
     * List of constraints for route variables.
     *
     * @var string[]
     */
    protected $constraints = [];

    /**
     * Route name.
     *
     * @var string
     */
    protected $name = null;

    /**
     * Parameters to pass to the route.
     *
     * @var string[]
     */
    protected $params = [];

    /**
     * @param string[]|string $methods
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     * @param int $identifier
     */
    public function __construct(
        $methods,
        $path,
        $callback,
        array $constraints = [],
        $name = null,
        $identifier = 0
    ) {
        $this->setMethods($methods);
        $this->setPath($path);
        $this->setCallback($callback);
        $this->setConstraints($constraints);
        $this->setName($name);
        $this->identifier = 'route_'.$identifier;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Sets the route methods.
     *
     * @param string[]|string $methods List of request methods to allow.
     */
    public function setMethods($methods)
    {
        $this->methods = array_map('strtoupper', (array) $methods);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Sets the route path.
     *
     * @param string $path Route path.
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the route callback.
     *
     * @param callable|string $callback Callback to call.
     *
     * @throws \InvalidArgumentException
     */
    public function setCallback($callback)
    {
        if (is_callable($callback) || is_string($callback)) {
            $this->callback = $callback;

            return;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Callback must be a callable or string; %s given.',
                gettype($callback)
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Sets the route constraints.
     *
     * @param string[] $constraints List of constraints for route variables.
     */
    public function setConstraints(array $constraints)
    {
        $this->constraints = $constraints;
    }

    /**
     * {@inheritDoc}
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * {@inheritDoc}
     */
    public function getPattern()
    {
        $pattern = $this->path;

        foreach ($this->constraints as $name => $regex) {
            $pattern = str_replace(
                '{'.$name.'}',
                '(?<'.$name.'>'.$regex.')',
                $pattern
            );
        }

        return $pattern;
    }

    /**
     * Sets the route name.
     *
     * @param string $name Route name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the route parameters.
     *
     * @param string[] $params Parameters to pass to the route.
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function getParams()
    {
        return $this->params;
    }
}
