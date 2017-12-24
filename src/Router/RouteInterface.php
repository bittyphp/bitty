<?php

namespace Bizurkur\Bitty\Router;

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
     * @return callback
     */
    public function getCallback();

    /**
     * Gets the route constraints.
     *
     * @return string[]
     */
    public function getConstraints();

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
