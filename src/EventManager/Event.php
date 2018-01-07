<?php

namespace Bizurkur\Bitty\EventManager;

use Bizurkur\Bitty\Collection;
use Bizurkur\Bitty\CollectionInterface;
use Bizurkur\Bitty\EventManager\EventInterface;

class Event implements EventInterface
{
    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var null|string|object
     */
    protected $target = null;

    /**
     * @var CollectionInterface
     */
    protected $params = null;

    /**
     * @var bool
     */
    protected $isPropagationStopped = false;

    /**
     * @param string|null $name
     * @param null|string|object $target
     * @param mixed[] $params
     */
    public function __construct($name = null, $target = null, array $params = [])
    {
        if (null !== $name) {
            $this->setName($name);
        }

        $this->setTarget($target);
        $this->setParams($params);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        if (!preg_match("/^[A-Za-z0-9_\.]+$/", $name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Event name "%s" is invalid. Only alpha-numeric characters, '
                    .'underscores, and periods allowed.',
                    $name
                )
            );
        }

        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritDoc}
     */
    public function setParams(array $params)
    {
        $this->params = new Collection($params);
    }

    /**
     * {@inheritDoc}
     */
    public function getParams()
    {
        return $this->params->all();
    }

    /**
     * {@inheritDoc}
     */
    public function getParam($name)
    {
        return $this->params->get($name, null, false);
    }

    /**
     * {@inheritDoc}
     */
    public function stopPropagation($flag)
    {
        $this->isPropagationStopped = (bool) $flag;
    }

    /**
     * {@inheritDoc}
     */
    public function isPropagationStopped()
    {
        return $this->isPropagationStopped;
    }
}
