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

    protected function resolveBindings(array $bindings)
    {
        $args = [];
        foreach ($bindings as $binding) {
            $key = is_null($bindingClass = $binding->getClass())
                ? $binding->getName()
                : $bindingClass->getName();
            $args[] = $this->get($key);
        }
        return $args;
    }

    /**
     * Resolve a class.
     * @throws \Boots\Exception\BindingResolutionException
     *         If class can not be resolved.
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
        $args = $this->resolveBindings($constructor->getParameters());
        return $reflectedClass->newInstanceArgs($args);
    }

    protected function resolveCallable(callable $callable)
    {
        $reflectedFunction = new \ReflectionFunction($callable);
        $args = $this->resolveBindings($reflectedFunction->getParameters());
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
                return $this->resolveCallable($entity);
            }
            return $entity;
        }
        if (class_exists($key)) {
            try {
                $entity = $this->resolveClass($key);
            } catch (\Exception $e) {
                if ($e instanceof Exception\BindingResolutionException) {
                    throw $e;
                }
                throw new Exception\BindingResolutionException(sprintf(
                    'Failed to resolve class %s from the container.', $key
                ));
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
        return $this;
    }
}
