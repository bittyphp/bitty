<?php

namespace Bitty\Router;

use Bitty\Router\Exception\RouterException;

interface CallbackBuilderInterface
{
    /**
     * Builds an callback array containing an object and method to call.
     *
     * The callback string can be any of the following:
     *   - A container service.
     *   - A container service and method to call, separated by a colon.
     *   - A fully qualified class name.
     *   - A fully qualified class name and method to call, separated by a colon.
     *
     * For example:
     *   - 'Acme\MyClass:someMethod'
     *   - 'some.container.service'
     *   - 'some.container.service:someMethod'
     *
     * If using a class name, it will still check to see if the container knows
     * how to build it. Otherwise it assumes the class doesn't have a constructor.
     *
     * @param string $callback
     *
     * @return mixed[]
     *
     * @throws RouterException If unable to build the callback.
     */
    public function build($callback);
}
