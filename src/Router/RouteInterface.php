<?php

namespace Bitty\Router;

interface RouteInterface
{
    /**
     * Gets the route identifier.
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Gets the route methods.
     *
     * @return string[]
     */
    public function getMethods();

    /**
     * Gets the route path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Gets the route callback.
     *
     * @return \Closure|string
     */
    public function getCallback();

    /**
     * Gets the route constraints.
     *
     * This should return an array of patterns keyed by the parameter they fill.
     *
     * For example, if the path was /products/{id} then you'd probably want a
     * constraint that restricts 'id' to be an integer: ['id' => '\d+']
     *
     * This example assumes regex constraints are allowed. Each implementation
     * may apply constraints however they want and are not required to use regex.
     *
     * @return string[]
     */
    public function getConstraints();

    /**
     * Gets a matchable pattern that combines that path and constraints.
     *
     * @return string
     */
    public function getPattern();

    /**
     * Gets the route name.
     *
     * @return string
     */
    public function getName();

    /**
     * Gets the route parameters.
     *
     * @return string[]
     */
    public function getParams();
}
