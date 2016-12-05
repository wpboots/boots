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
     * Resolve a class.
     * @throws \Boots\Exception\BindingResolutionException
     *         If class can not be resolved.
     * @param  string $class Fully qualified class name
     * @return Object        Resolved instance
     */
    protected function resolve($class)
    {
        $reflectedClass = new \ReflectionClass($class);
        $constructor = $reflectedClass->getConstructor();
        if (is_null($constructor)) {
            return new $class;
        }
        $params = $constructor->getParameters();
        $values = [];
        foreach ($params as $param) {
            if (!is_null($paramClass = $param->getClass())) {
                $values[] = $this->get($paramClass->getName());
                continue;
            }
            $values[] = $this->get($param->getName());
        }
        return $reflectedClass->newInstanceArgs($values);
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
            return $this->container[$key];
        }
        if (class_exists($key)) {
            try {
                $entity = $this->resolve($key);
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
