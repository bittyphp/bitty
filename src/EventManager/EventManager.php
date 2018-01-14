<?php

namespace Bitty\EventManager;

use Bitty\EventManager\Event;
use Bitty\EventManager\EventInterface;
use Bitty\EventManager\EventManagerInterface;

class EventManager implements EventManagerInterface
{
    /**
     * @var array[]
     */
    protected $events = [];

    /**
     * {@inheritDoc}
     */
    public function attach($event, $callback, $priority = 0)
    {
        $events = [];
        if (isset($this->events[$event])) {
            $events = $this->events[$event];
        }

        $events[] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        usort($events, function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return 0;
            }

            return $a['priority'] < $b['priority'] ? 1 : -1;
        });

        $this->events[$event] = $events;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function detach($event, $callback)
    {
        if (!isset($this->events[$event])) {
            return false;
        }

        $found  = false;
        $events = [];
        foreach ($this->events[$event] as $data) {
            if ($callback === $data['callback']) {
                $found = true;
                continue;
            }

            $events[] = $data;
        }

        $this->events[$event] = $events;

        return $found;
    }

    /**
     * {@inheritDoc}
     */
    public function clearListeners($event)
    {
        if (isset($this->events[$event])) {
            unset($this->events[$event]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function trigger($event, $target = null, array $params = [])
    {
        $name = $event;
        if ($event instanceof EventInterface) {
            $name = $event->getName();
        } else {
            $event = new Event($name, $target, $params);
        }

        if (!isset($this->events[$name])) {
            return false;
        }

        $response = null;
        foreach ($this->events[$name] as $data) {
            $response = $data['callback']($event, $response);

            if ($event->isPropagationStopped()) {
                break;
            }
        }

        return $response;
    }
}
