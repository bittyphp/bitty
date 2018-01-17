<?php

namespace Bitty\Security\Context;

use Psr\Http\Message\ServerRequestInterface;

interface ContextInterface
{
    /**
     * Checks whether or not this should be the default context.
     *
     * @return bool
     */
    public function isDefault();

    /**
     * Sets context data.
     *
     * @param string $name
     * @param mixed $value
     */
    public function set($name, $value);

    /**
     * Gets context data.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * Removes context data.
     *
     * @param string $name
     */
    public function remove($name);

    /**
     * Clears all context data.
     */
    public function clear();

    /**
     * Checks if a request is shielded.
     *
     * @param ServerRequestInterface $request
     *
     * @return bool
     */
    public function isShielded(ServerRequestInterface $request);

    /**
     * Gets the first pattern and roles that match the request.
     *
     * TODO: This needs a better name and to return an object.
     *
     * @param ServerRequestInterface $request
     *
     * @return mixed[]
     */
    public function getPatternMatch(ServerRequestInterface $request);
}
