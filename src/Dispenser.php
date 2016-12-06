<?php

namespace Boots;

/**
 * Boots dispenser.
 *
 * @package Boots
 * @subpackage Dispenser
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
 * @subpackage Dispenser
 * @version 2.0.0
 */
class Dispenser implements Contract\DispenserContract
{
    /**
     * Directory path containing the services/extensions.
     * @var string
     */
    protected $directory;

    /**
     * Class locator.
     * @var Contract\LocatorContract
     */
    protected $locator;

    /**
     * Dispenser storage.
     * @var array
     */
    protected $dispenser = [];

    /**
     * Name of the index file.
     * @var string
     */
    protected $indexFile;

    /**
     * Name of the manifest file.
     * @var string
     */
    protected $manifestFile;

    /**
     * Construct the instance.
     * @param string                      $directory  Path to extensions directory
     * @param Contract\LocatorContract    $locator    Class locator instance
     */
    public function __construct(
        $directory,
        Contract\LocatorContract $locator
    ) {
        $this->locator = $locator;
        $this->directory = $directory;
    }

    /**
     * Locate the extension class.
     * @param  string $token Extension key
     * @return string        Extension class
     */
    protected function locate($token)
    {
        $dirpath = "{$this->directory}/{$token}";
        $filepath = $dirpath . '/' . ($this->indexFile ?: "{$token}.php");
        $path2manifest = $dirpath . '/' . ($this->manifestFile ?: "{$token}.json");
        if (!is_file($path2manifest)) {
            throw new Exception\FileNotFoundException(sprintf(
                '%s is required to load the %s extension.', $path2manifest, $token
            ));
        }
        $manifestContent = file_get_contents($path2manifest);
        $mArr = json_decode($manifestContent, true);
        return $this->locator->locate($filepath, $mArr['class'], $mArr['version']);
    }

    /**
     * Modify the index file name to look for.
     * @param  string $name Index file name
     * @return $this        Allow chaining
     */
    public function indexAt($name)
    {
        $this->indexFile = $name;
        return $this;
    }

    /**
     * Modify the manifest file name to look for.
     * @param  string $name Manifest file name
     * @return $this        Allow chaining
     */
    public function manifestAt($name)
    {
        $this->manifestFile = $name;
        return $this;
    }

    /**
     * Dispense an extension by key.
     * @param  string $token Extension key
     * @return mixed  Extension
     */
    public function dispense($token)
    {
        if (array_key_exists($token, $this->dispenser)) {
            return $this->dispenser[$token];
        }
        $class = $this->locate($token);
        $extension = new $class;
        $this->dispenser[$token] = $extension;
        return $extension;
    }
}
