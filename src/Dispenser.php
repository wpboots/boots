<?php

namespace Boots;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Dispenser
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

use Boots\Contract\ContainerContract;
use Boots\Contract\DispenserContract;

/**
 * @package Boots
 * @subpackage Dispenser
 */
class Dispenser implements DispenserContract
{
    /**
     * Container instance.
     * @var ContainerContract
     */
    protected $container;

    /**
     * Dispenser storage.
     * @var array
     */
    protected $dispenser = [];

    /**
     * Entities manifest
     * @var array
     */
    protected $entities = [];

    /**
     * Construct the instance.
     * @param  string $directory Path to extensions directory
     * @param  array  $entities  Entities manifest
     * @return void
     */
    public function __construct($directory, array $entities = [])
    {
        $this->entities = $entities;
        $this->directory = rtrim($directory, '/');
        $this->registerAutoloaders();
    }

    protected function strToCamelCase($str)
    {
        return str_replace(' ', '', lcfirst(ucwords(
            str_replace(['-', '_'], ' ', $str)
        )));
    }

    /**
     * Cast a string to kebab-case.
     * @see http://php.net/manual/en/function.preg-replace.php#111695
     * @param  string $str String to cast
     * @return string kebab-cased
     */
    protected function strToKebabCase($str)
    {
        $re = '/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z]|(?<=[A-Z])[0-9_])/';
        return strtolower(preg_replace($re, '-$1', $str));
    }

    /**
     * Register psr-4 spl autoloader.
     * @return $this Allow chaining
     */
    protected function autoloadPsr4()
    {
        spl_autoload_register([$this, 'loadPsr4Class']);
        return $this;
    }

    /**
     * Register the autoloaders.
     * @return $this Allow chaining
     */
    public function registerAutoloaders()
    {
        $psr4 = [];
        foreach ($this->entities as $entity => $meta) {
            if (array_key_exists('autoload', $meta)
                && array_key_exists('psr-4', $meta['autoload'])
            ) {
                $maps = $meta['autoload']['psr-4'];
                $version = array_key_exists('version', $meta) ? $meta['version'] : '';
                $psr4[$entity] = [
                    'maps' => $maps,
                    'version' => $version
                ];
            }
        }

        $this->psr4 = $psr4;
        $this->autoloadPsr4();
        return $this;
    }

    /**
     * Unregister the autoloaders.
     * @return $this Allow chaining
     */
    public function unregisterAutoloaders()
    {
        spl_autoload_unregister([$this, 'loadPsr4Class']);
        return $this;
    }

    /**
     * Psr-4 class autoloader.
     * @param  string $class Class
     * @return boolean Loaded
     */
    public function loadPsr4Class($class)
    {
        foreach ($this->psr4 as $dir => $mappings) {
            $version = $mappings['version'];
            foreach ($mappings['maps'] as $prefix => $subDir) {
                $baseDir = "{$this->directory}/{$dir}/{$subDir}";
                $baseDir = rtrim($baseDir, '/') . '/';
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    continue;
                }
                $relativeClass = substr($class, $len);
                $suffix = str_replace('.', '_', $version);
                $suffix = empty($suffix) ? '' : "_{$suffix}";
                $search = '/'.preg_quote($suffix).'$/';
                $relativeClass = preg_replace($search, '', $relativeClass);
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($file)) {
                    require $file;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the container instance.
     * @param  ContainerContract $container Container instance
     * @return $this Allow chaining
     */
    public function setContainer(ContainerContract $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Dispense an entity by key.
     * @param  string $key Identifier
     * @return mixed Entity
     */
    public function dispense($key)
    {
        $key = $this->strToCamelCase($key);
        $key = $this->strToKebabCase($key);
        if (array_key_exists($key, $this->dispenser)) {
            return $this->dispenser[$key];
        }
        if (!array_key_exists($key, $this->entities)) {
            // TODO: throw
            return null;
        }
        $entity = $this->entities[$key];
        if (!array_key_exists('class', $entity)) {
            // TODO: throw
            return null;
        }
        $class = $entity['class'];
        $version = '';
        if (array_key_exists('version', $entity)) {
            $version = $entity['version'];
        }
        $suffix = str_replace('.', '_', $version);
        $class .= empty($suffix) ? '' : "_{$suffix}";
        if (is_null($this->container)) {
            $extension = new $class;
        } else {
            $extension = $this->container->get($class);
        }
        $this->dispenser[$key] = $extension;
        return $extension;
    }
}
