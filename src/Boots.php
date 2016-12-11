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

use Boots\Container;
use Boots\Dispenser;
use Boots\Repository;
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
     * Version of the framework.
     * @var string
     */
    protected $version;

    /**
     * Configuration repository.
     * @var RepositoryContract
     */
    protected $config;

    /**
     * Extension dispenser.
     * @var DispenserContract
     */
    protected $dispenser;

    /**
     * Construct the instance.
     * @param string                  $version   Framework version
     * @param DispenserContract       $dispenser Dispenser instance
     * @param RepositoryContract|null $config    Configuration repository instance
     * @param ContainerContract|null  $container Container instance
     */
    public function __construct(
        $version,
        DispenserContract $dispenser,
        RepositoryContract $config = null,
        ContainerContract $container = null
    ) {
        $this->version = $version;
        $this->dispenser = $dispenser;
        $this->config = $config ?: new Repository;

        $this->share(get_class(), $this);
        if (!is_null($container)) {
            $this->delegate($container);
        }
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
        $baseDir = $appDir . '/boots';
        $extendDir = $baseDir . '/extend';
        $manifest = require $baseDir . '/boots.php';
        $version = $manifest['version'];
        $repository = new Repository($config);
        $dispenser = new Dispenser($extendDir, $manifest['extensions']);
        $instance = new static($version, $dispenser, $repository);
        $dispenser->setContainer($instance);
        return $instance;
    }

    /**
     * Get the version of the framework.
     * @return string Version
     */
    public function version()
    {
        return $this->version;
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
     * Get an extension by indentifier.
     * @param  string $extension Identifier
     * @return object            The extension instance
     */
    public function __get($extension)
    {
        return $this->dispenser->dispense($extension);
    }
}
