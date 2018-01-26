<?php

namespace Bitty\Router;

use Bitty\CollectionInterface;

interface RouteCollectionInterface extends CollectionInterface
{
    /**
     * Adds a new route.
     *
     * @param string[]|string $methods
     * @param string $path
     * @param \Closure|string $callback
     * @param string[] $constraints
     * @param string|null $name
     */
    public function add(
        $methods,
        $path,
        $callback,
        array $constraints = [],
        $name = null
    );
}
