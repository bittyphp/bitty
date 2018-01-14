<?php

namespace Bitty\EventManager;

use Bitty\EventManager\EventInterface;

/**
 * Interface for EventManager
 *
 * NOTE: This is a placeholder until PSR-14 is approved.
 */
interface EventManagerInterface
{
    /**
     * Attaches a listener to an event
     *
     * @param string $event the event to attach too
     * @param callback $callback a callable function
     * @param int $priority the priority at which the $callback executed
     *
     * @return bool true on success false on failure
     */
    public function attach($event, $callback, $priority = 0);

    /**
     * Detaches a listener from an event
     *
     * @param string $event the event to attach too
     * @param callback $callback a callable function
     *
     * @return bool true on success false on failure
     */
    public function detach($event, $callback);

    /**
     * Clear all listeners for a given event
     *
     * @param string $event
     */
    public function clearListeners($event);

    /**
     * Trigger an event
     *
     * Can accept an EventInterface or will create one if not passed
     *
     * @param string|EventInterface $event
     * @param null|object|string $target
     * @param mixed[] $params
     *
     * @return mixed
     */
    public function trigger($event, $target = null, array $params = []);
}
