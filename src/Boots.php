<?php

namespace Boots;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Boots
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

use Boots\Contract\EventContract;
use Boots\Contract\ContainerContract;
use Boots\Contract\DispenserContract;
use Boots\Contract\RepositoryContract;

/**
 * @package Boots
 * @subpackage Boots
 */
class Boots extends Container
{
    /**
     * Configuration repository.
     * @var RepositoryContract
     */
    protected $config;

    /**
     * Event dispatcher.
     * @var EventContract
     */
    protected $dispatcher;

    /**
     * Extension dispenser.
     * @var DispenserContract
     */
    protected $dispenser;

    /**
     * Name of the framework.
     */
    const NAME = 'boots';

    /**
     * Construct the instance.
     * @param DispenserContract       $dispenser Dispenser instance
     * @param RepositoryContract|null $config    Configuration repository instance
     * @param ContainerContract|null  $container Container instance
     */
    public function __construct(
        DispenserContract $dispenser,
        RepositoryContract $config = null,
        EventContract $dispatcher = null,
        ContainerContract $container = null
    ) {
        $this->dispenser = $dispenser;
        $this->config = $config ?: new Repository;
        $this->dispatcher = new Event;
        if (!is_null($dispatcher)) {
            $this->dispatcher->delegate($dispatcher);
        }
        if (!is_null($container)) {
            $this->delegate($container);
        }
        $this->share('boots', $this);
        $this->share(get_class(), $this);
    }

    /**
     * Factory to create an instance.
     * @param  string $appDir Base application directory
     * @param  array  $config Configuration array
     * @return Boots          The instance
     */
    public static function create($appDir, array $config = [])
    {
        $appDir = rtrim($appDir, '/');
        $baseDir = $appDir . '/' . static::NAME;
        $extendDir = $baseDir . '/extend';
        $manifest = ['version' => '', 'extensions' => []];
        $manifestFile = $baseDir . '/' . static::NAME . '.php';
        if (is_file($manifestFile)) {
            $manifest = require $manifestFile;
        }
        $version = $manifest['version'];
        $extensions = $manifest['extensions'];
        $repository = new Repository($config);
        $dispenser = new Dispenser($extendDir, $extensions);
        $instance = new static($dispenser, $repository);
        $dispenser->setContainer($instance);
        $instance->config('app.path', $appDir);
        $instance->config(static::NAME . '.path', $baseDir);
        $instance->config(static::NAME . '.extend_path', $extendDir);
        $instance->config(static::NAME . '.version', $version);
        $instance->config('extensions', $extensions);
        foreach (array_keys($extensions) as $ext) {
            $instance->config("extensions.{$ext}.path", "{$extendDir}/$ext");
        }
        new WordPress($instance);
        return $instance;
    }

    /**
     * Set or get a configuration key.
     * @param  string $key   Identifier to get or set
     * @param  mixed  $value Value to set
     * @return mixed         Repository instance or key value
     */
    public function config($key = null, $value = null)
    {
        if (is_null($key)) {
            return $this->config;
        }
        if (is_null($value)) {
            return $this->config->get($key);
        }
        $this->config->set($key, $value);
        return $value;
    }

    /**
     * Add an event listener. Proxy to Event::add().
     * @param  string   $key    Identifier
     * @param  callable $action Action
     * @return $this            Allow chaining
     */
    public function on($key, callable $action)
    {
        $this->dispatcher->add($key, $action);
        return $this;
    }

    /**
     * Emit an event. Proxy to Event::fire().
     * @param  string $key    Identifier
     * @param  array  $params Action parameters
     * @return array          Results of all actions
     */
    public function fire($key, array $params = [])
    {
        return $this->dispatcher->fire($key, $params);
    }

    /**
     * Get an extension by indentifier.
     * @param  string $extension Identifier
     * @return object            The extension instance
     */
    public function __get($extension)
    {
        return $this->dispenser->dispense($extension);
    }
}
