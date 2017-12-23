<?php

namespace Bizurkur\Bitty;

use Bizurkur\Bitty\CollectionInterface;

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

        if ($trim) {
            if (is_string($value)) {
                return trim($value);
            } elseif (is_array($value)) {
                return array_map('trim', $value);
            }
        }

        return $value;
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
}
