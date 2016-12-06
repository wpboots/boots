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
class Container implements Contract\ContainerContract
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
     * Resolve parameter bindings from the container.
     * @throws \Boots\Exception\BindingResolutionException
     *         If a parameter can not be resolved.
     * @param  array $bindings ReflectionParameter's
     * @return array           Resolved binding values
     */
    protected function resolve(array $bindings)
    {
        $args = [];
        foreach ($bindings as $binding) {
            $key = is_null($bindingClass = $binding->getClass())
                ? $binding->getName()
                : $bindingClass->getName();
            try {
                $arg = $this->get($key);
            } catch (\Exception $e) {
                if (!$binding->isDefaultValueAvailable()) {
                    throw $e;
                }
                $arg = $binding->getDefaultValue();
            }
            $args[] = $arg;
        }
        return $args;
    }

    /**
     * Resolve a class.
     * @param  string $class Fully qualified class name
     * @return Object        Resolved instance
     */
    protected function resolveClass($class)
    {
        $reflectedClass = new \ReflectionClass($class);
        $constructor = $reflectedClass->getConstructor();
        if (is_null($constructor)) {
            return new $class;
        }
        $args = $this->resolve($constructor->getParameters());
        return $reflectedClass->newInstanceArgs($args);
    }

    /**
     * Resolve a callable.
     * @param  callable $callable PHP Callable
     * @return mixed              Resolved value
     */
    protected function resolveCallable(callable $callable)
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
        $args = $this->resolve($reflectedFunction->getParameters());
        return call_user_func_array($callable, $args);
    }

    /**
     * Resolve an entity by key.
     * @throws \Boots\Exception\NotFoundException
     *         If an entry is not found.
     * @throws \Boots\Exception\BindingResolutionException
     *         If an entity can not be resolved.
     * @param  string $key Identifier
     * @return mixed  Entity
     */
    public function get($key)
    {
        if ($this->has($key)) {
            if (is_callable($entity = $this->container[$key])) {
                try {
                    $entity = $this->resolveCallable($entity);
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
                $entity = $this->resolveClass($key);
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
        return array_key_exists($key, $this->container);
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
}
