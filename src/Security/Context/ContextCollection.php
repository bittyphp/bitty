<?php

namespace Bitty\Security\Context;

use Bitty\Security\Context\ContextInterface;
use Psr\Http\Message\ServerRequestInterface;

class ContextCollection implements ContextInterface
{
    /**
     * @var ContextInterface[]
     */
    protected $contexts = null;

    /**
     * @var ContextInterface
     */
    protected $activeContext = null;

    /**
     * Adds a context to the collection.
     *
     * @param ContextInterface $context
     */
    public function add(ContextInterface $context)
    {
        $this->contexts[] = $context;
    }

    /**
     * Clears the active context, if one is set.
     */
    public function clearActiveContext()
    {
        $this->activeContext = null;
    }

    /**
     * {@inheritDoc}
     */
    public function isDefault()
    {
        $this->activeContext = null;

        foreach ($this->contexts as $context) {
            if ($context->isDefault()) {
                $this->activeContext = $context;

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value)
    {
        $this->activeContext = null;

        foreach ($this->contexts as $context) {
            $context->set($name, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $default = null)
    {
        if ($this->activeContext) {
            return $this->activeContext->get($name, $default);
        }

        foreach ($this->contexts as $context) {
            $value = $context->get($name);
            if (null !== $value) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($name)
    {
        $this->activeContext = null;

        foreach ($this->contexts as $context) {
            $context->remove($name);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->activeContext = null;

        foreach ($this->contexts as $context) {
            $context->clear();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isShielded(ServerRequestInterface $request)
    {
        $this->activeContext = null;

        foreach ($this->contexts as $context) {
            if ($context->isShielded($request)) {
                $this->activeContext = $context;

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getPatternMatch(ServerRequestInterface $request)
    {
        $this->activeContext = null;

        foreach ($this->contexts as $context) {
            $match = $context->getPatternMatch($request);
            if (!empty($match)) {
                $this->activeContext = $context;

                return $match;
            }
        }

        return [];
    }
}
