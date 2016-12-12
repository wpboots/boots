<?php

namespace Boots;

/**
 * Boots container.
 *
 * @package Boots
 * @subpackage Container
 * @version 2.0.0
 * @see http://wpboots.com
 * @link https://github.com/wpboots/boots
 * @author Kamal Khan <shout@bhittani.com> https://bhittani.com
 * @license https://github.com/wpboots/boots/blob/master/LICENSE
 * @copyright Copyright (c) 2014-2016, Kamal Khan
 */

// Die if accessing this script directly.
if (!defined('ABSPATH')) {
    die(-1);
}

/**
 * @package Boots
 * @subpackage Container
 * @version 2.0.0
 */
class Container implements Contract\ContainerContract, \ArrayAccess
{
    /**
     * Container storage.
     * @var array
     */
    protected $container = [];

    /**
     * Shared entity keys.
     * @var array
     */
    protected $shared = [];

    /**
     * Container delegates
     * @var array
     */
    protected $delegates = [];

    /**
     * Resolve parameter bindings from the container.
     * @throws \Boots\Exception\BindingResolutionException
     *         If a parameter can not be resolved.
     * @param  array $bindings ReflectionParameter's
     * @param  array $params   Additional passed parameter values
     * @return array           Resolved binding values
     */
    protected function resolve(array $bindings, array $params = [])
    {
        $args = [];
        foreach ($bindings as $binding) {
            $key = is_null($bindingClass = $binding->getClass())
                ? $binding->getName()
                : $bindingClass->getName();
            if (array_key_exists($key, $params)) {
                $arg = $params[$key];
            } else {
                try {
                    $arg = $this->get($key, $params);
                } catch (\Exception $e) {
                    if (!$binding->isDefaultValueAvailable()) {
                        throw $e;
                    }
                    $arg = $binding->getDefaultValue();
                }
            }
            $args[] = $arg;
        }
        return $args;
    }

    /**
     * Resolve a class.
     * @param  string $class  Fully qualified class name
     * @param  array  $params Additional passed parameter values
     * @return Object         Resolved instance
     */
    protected function resolveClass($class, array $params = [])
    {
        $reflectedClass = new \ReflectionClass($class);
        $constructor = $reflectedClass->getConstructor();
        if (is_null($constructor)) {
            return new $class;
        }
        $args = $this->resolve($constructor->getParameters(), $params);
        return $reflectedClass->newInstanceArgs($args);
    }

    /**
     * Resolve a callable.
     * @param  callable $callable PHP Callable
     * @param  array    $params   Additional passed parameter values
     * @return mixed              Resolved value
     */
    protected function resolveCallable(callable $callable, array $params = [])
    {
        if ($callable instanceof \Closure) {
            $reflectedFunction = new \ReflectionFunction($callable);
        } else {
            $reflectedClass = new \ReflectionClass($callable);
            if (!$reflectedClass->hasMethod('__invoke')) {
                return $callable;
            }
            $reflectedFunction = $reflectedClass->getMethod('__invoke');
        }
        $args = $this->resolve($reflectedFunction->getParameters(), $params);
        return call_user_func_array($callable, $args);
    }

    /**
     * Find an entity including within delegates.
     * @param  string  $key   Identifier
     * @param  boolean $found Whether the entity was found or not
     * @return mixed          Entity or null if not found
     */
    protected function find($key, & $found = false, $unset = false)
    {
        if (array_key_exists($key, $this->container)) {
            $found = true;
            if ($unset) {
                unset($this->container[$key]);
                return null;
            }
            return $this->container[$key];
        }
        $delegateFound = false;
        foreach ($this->delegates as $delegate) {
            $entity = $delegate->find($key, $found, $unset);
            if ($found) {
                // To use as stack pop
                // return $entity;
                if (!$unset) {
                    return $entity;
                }
                $delegateFound = true;
            }
        }
        $found = $delegateFound;
    }

    /**
     * Resolve an entity by key.
     * @throws \Boots\Exception\NotFoundException
     *         If an entry is not found.
     * @throws \Boots\Exception\BindingResolutionException
     *         If an entity can not be resolved.
     * @param  string $key    Identifier
     * @param  array  $params Additional passed parameter values
     * @return mixed  Entity
     */
    public function get($key, array $params = [])
    {
        $entity = $this->find($key, $found);
        if ($found) {
            if (is_callable($entity)) {
                try {
                    $entity = $this->resolveCallable($entity, $params);
                } catch (\Exception $e) {
                    throw new Exception\BindingResolutionException(sprintf(
                        'Failed to resolve callable %s while resolving %s from the container.',
                        get_class($entity), $key
                    ), 1, $e);
                }
                if (in_array($key, $this->shared)) {
                    $this->container[$key] = $entity;
                }
            }
            return $entity;
        }
        if (class_exists($key)) {
            try {
                $entity = $this->resolveClass($key, $params);
            } catch (\Exception $e) {
                throw new Exception\BindingResolutionException(sprintf(
                    'Failed to resolve class %s from the container.', $key
                ), 1, $e);
            }
            return $entity;
        }
        if (interface_exists($key)) {
            throw new Exception\BindingResolutionException(sprintf(
                'Failed to resolve interface %s from the container.', $key
            ));
        }
        throw new Exception\NotFoundException(sprintf(
            '%s is not managed by the container.', $key
        ));
    }

    /**
     * Check whether an entity for a key exists.
     * @param  string  $key Identifier
     * @return boolean Exists or not
     */
    public function has($key)
    {
        $this->find($key, $has);
        return (bool) $has;
    }

    /**
     * Add an entry.
     * @param  string $key   Identifier
     * @param  mixed  $value Value
     * @return $this  Allow chaining
     */
    public function add($key, $value)
    {
        $this->container[$key] = $value;
        $this->shared = array_diff($this->shared, [$key]);
        return $this;
    }

    /**
     * Add a shared entry.
     * @param  string $key   Identifier
     * @param  mixed  $value Value
     * @return $this  Allow chaining
     */
    public function share($key, $value)
    {
        $this->container[$key] = $value;
        if (!in_array($key, $this->shared)) {
            $this->shared[] = $key;
        }
        return $this;
    }

    /**
     * Add a delegation.
     * @param  Contract\ContainerContract $container Container
     * @return $this Allow chaining
     */
    public function delegate(Contract\ContainerContract $container)
    {
        $this->delegates[] = $container;
        return $this;
    }

    /**
     * ArrayAccess: Alias of add.
     * @param  string $offset Identifier
     * @param  mixed $value   Value
     * @return $this          Allow chaining
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
        return $this;
    }

    /**
     * ArrayAccess: alias of has.
     * @param  string $offset Identifier
     * @return boolean        Exists or not
     */
    public function offsetExists($offset) {
        return $this->has($offset);
    }

    /**
     * ArrayAccess: unset a key binding.
     * @param  string $offset Identifier
     * @return void
     */
    public function offsetUnset($offset) {
        $this->find($offset, $found, true);
    }

    /**
     * ArrayAccess: alias of get.
     * @param  string $offset Identifier
     * @return mixed          Entity
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }
}
