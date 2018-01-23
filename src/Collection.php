<?php

namespace Bitty;

use Bitty\CollectionInterface;

class Collection extends \ArrayIterator implements CollectionInterface
{
    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return iterator_to_array($this);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = '', $trim = true)
    {
        $value = $default;
        if ($this->offsetExists($key)) {
            $value = $this->offsetGet($key);
        }

        if (!$trim) {
            return $value;
        }

        return $this->trimValue($value);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($key)
    {
        if ($this->offsetExists($key)) {
            $this->offsetUnset($key);
        }
    }

    /**
     * Trims a value of padding, if applicable.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function trimValue($value)
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (!is_array($value)) {
            return $value;
        }

        array_walk_recursive($value, function (&$data) {
            if (is_string($data)) {
                $data = trim($data);
            }
        });

        return $value;
    }
}
