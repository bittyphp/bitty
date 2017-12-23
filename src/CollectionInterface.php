<?php

namespace Bizurkur\Bitty;

interface CollectionInterface
{
    /**
     * Returns all the data.
     *
     * @return mixed[]
     */
    public function all();

    /**
     * Sets a key/value pair.
     *
     * @param string $key The key to set.
     * @param mixed $value The value to set.
     */
    public function set($key, $value);

    /**
     * Checks if a key exists.
     *
     * @param string $key The key to check for.
     *
     * @return bool
     */
    public function has($key);

    /**
     * Gets a value for a key.
     *
     * @param string $name The key to get.
     * @param mixed $default Value to return when key not set.
     * @param bool $trim Call trim; defaults to true.
     *
     * @return mixed
     */
    public function get($key, $default = '', $trim = true);

    /**
     * Removes a key.
     *
     * @param string $key The key to remove.
     */
    public function remove($key);

    /**
     * Gets the data count.
     *
     * @return int
     */
    public function count();
}
