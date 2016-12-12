<?php

namespace Boots;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Event
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

use stdClass;
use SplStack;
use ReflectionFunction;
use Boots\Contract\EventContract;
use Boots\Exception\InfiniteRecursionException;

/**
 * @package Boots
 * @subpackage Event
 */
class Event implements EventContract
{
    /**
     * Event listeners.
     * @var array
     */
    protected $events = [];

    /**
     * Event details baggage.
     * @var SplStack
     */
    protected $baggage;

    /**
     * Event delegations.
     * @var array
     */
    protected $delegates = [];

    /**
     * Initialize the baggage.
     */
    public function __construct()
    {
        $this->baggage = new SplStack;
    }

    /**
     * Determine current event name or if it matches the provided key.
     * @param  string $key    Identifier to match against.
     * @return string|boolean Name of the event | Is the provided key event?
     */
    public function name($key = null)
    {
        if ($this->baggage->isEmpty()) {
            return is_null($key) ? null : false;
        }
        $name = $this->baggage->top()->key;
        return is_null($key) ? $name : (strcmp($key, $name) === 0);
    }

    public function params($key = null)
    {
        if ($this->baggage->isEmpty()) {
            return;
        }
        $params = $this->baggage->top()->params;
        if (!is_null($key)) {
            if (!array_key_exists($key, $params)) {
                return;
            }
            return $params[$key];
        }
        return $params;
    }

    /**
     * Add an event delegation.
     * @param  EventContract $event Delegation
     * @return $this                Allow chaining
     */
    public function delegate(EventContract $event)
    {
        $this->delegates[] = $event;
        return $this;
    }

    /**
     * Add an event listener.
     * @param  string   $key    Identifier
     * @param  callable $action Action
     * @return $this            Allow chaining
     */
    public function add($key, callable $action)
    {
        // Add an event listenser
        if (!array_key_exists($key, $this->events)) {
            $this->events[$key] = [];
        }
        $this->events[$key][] = $action;
    }

    /**
     * Emit an event.
     * @param  string $key    Identifier
     * @param  array  $params Action parameters
     * @return array          Results of all actions
     */
    public function fire($key, array $params = [])
    {
        // prevent infinite recursion
        if ($this->name($key)) {
            throw new InfiniteRecursionException(sprintf(
                'Firing an event (%s) recursively within its action is not allowed.',
                $key
            ));
        }

        $event = new stdClass;
        $event->key = $key;
        $event->params = $params;
        $this->baggage->push($event);

        $results = [];
        $args = array_merge([$this], $params);
        if (array_key_exists($key, $this->events)) {
            foreach ($this->events[$key] as $action) {
                $reflectedAction = new ReflectionFunction($action);
                if (count($reflectedAction->getParameters()) == 0) {
                    $results[] = call_user_func($action);
                } else {
                    $results[] = call_user_func_array($action, $args);
                }
            }
        }

        // delegate the event
        foreach ($this->delegates as $delegate) {
            $results = array_merge($results, $delegate->fire($key, $params));
        }

        $this->baggage->pop();
        return $results;
    }

    /**
     * Magic property access.
     * @param  string $property Property name
     * @return mixed            Value based on property
     */
    public function __get($property)
    {
        switch ($property) {
            case 'name': return $this->name();
            case 'params': return $this->params();
        }
    }
}