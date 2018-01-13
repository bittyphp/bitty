<?php

namespace Bitty\EventManager;

/**
 * Representation of an event
 *
 * NOTE: This is a placeholder until PSR-14 is approved.
 */
interface EventInterface
{
    /**
     * Set the event name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get event name
     *
     * @return string
     */
    public function getName();

    /**
     * Set the event target
     *
     * @param null|string|object $target
     */
    public function setTarget($target);

    /**
     * Get target/context from which event was triggered
     *
     * @return null|string|object
     */
    public function getTarget();

    /**
     * Set event parameters
     *
     * @param array $params
     */
    public function setParams(array $params);

    /**
     * Get parameters passed to the event
     *
     * @return array
     */
    public function getParams();

    /**
     * Get a single parameter by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParam($name);

    /**
     * Indicate whether or not to stop propagating this event
     *
     * @param bool $flag
     */
    public function stopPropagation($flag);

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function isPropagationStopped();
}
