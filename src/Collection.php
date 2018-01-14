<?php

namespace Bitty;

use Bitty\CollectionInterface;

class Collection implements CollectionInterface
{
    /**
     * Array of key/value pairs.
     *
     * @var mixed[]
     */
    protected $data = [];

    /**
     * @param mixed[] $data Array of key/value pairs.
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function has($key)
    {
        if (isset($this->data[$key])
            || array_key_exists($key, $this->data)
        ) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function get($key, $default = '', $trim = true)
    {
        $value = $default;
        if ($this->has($key)) {
            $value = $this->data[$key];
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
        if ($this->has($key)) {
            unset($this->data[$key]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->data);
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
