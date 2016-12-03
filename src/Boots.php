<?php

namespace Boots;

/**
 * A wrapper class for the Boots API.
 * By using this approach, any version updates
 * will not enforce developers to modify the usage.
 *
 * @package Boots
 * @subpackage Boots
 * @version 2.0.0
 * @see http://wpboots.com
 * @link https://github.com/wpboots/boots
 * @author Kamal Khan <shout@bhittani.com> https://bhittani.com
 * @license https://github.com/wpboots/boots/blob/master/LICENSE
 * @copyright Copyright (c) 2014-2016, Kamal Khan
 */

// Die if accessing this script directly.
if(!defined('ABSPATH')) die(-1);

/**
 * @package Boots
 * @subpackage Boots
 * @version 2.0.0
 */
class Boots
{
    /**
     * Configuration repository
     * @var RepositoryInterface
     */
    protected $config;

    /**
     * Manifest repository
     * @var RepositoryInterface
     */
    protected $manifest;

    /**
     * Repository class
     * @var string
     */
    protected $repositoryClass;

    /**
     * Boots api instance
     * @var Api
     */
    protected $api;

    /**
     * Setup and fire up the boots api instance.
     * @param array  $config Configuration array
     */
    public function __construct(array $config)
    {
        $manifest = $this->extractManifest($config['abspath']);
        $this->repositoryClass = $this->loadRepository('Repository', $manifest['version']);
        $this->config = new $this->repositoryClass($config);
        $this->manifest = new $this->repositoryClass($manifest);
        $this->api = $this->setupApi($this->manifest->get('version'));
    }

    /**
     * Get a local class or interface.
     * @param  string $prefix  Name of class or interface
     * @param  string $version Version
     * @return string Fully qualified class or interface name
     */
    protected function getLocal($prefix, $version)
    {
        $suffix = str_replace('.', '_', $version);
        $suffix = empty($suffix) ? '' : "_{$suffix}";
        $nsParts = explode('\\', get_class());
        $name = $prefix . $suffix;
        $nsParts[count($nsParts)-1] = $name;
        return implode('\\', $nsParts);
    }

    /**
     * Get a local class.
     * @param  string $prefix  Name of class
     * @param  string $version Version
     * @return string Fully qualified class name
     */
    protected function getLocalClass($prefix, $version = '')
    {
        $fqcn = $this->getLocal($prefix, $version);
        if (!class_exists($fqcn)) {
            require_once __DIR__ . "/{$prefix}.php";
        }
        return $fqcn;
    }

    /**
     * Get a local interface.
     * @param  string $prefix  Name of interface
     * @param  string $version Version
     * @return string Fully qualified interface name
     */
    protected function getLocalInterface($prefix, $version = '')
    {
        $fqin = $this->getLocal($prefix, $version);
        if (!interface_exists($fqin)) {
            require_once __DIR__ . "/{$prefix}.php";
        }
        return $fqin;
    }

    /**
     * Extract the manifest as an array.
     * @param  string $abspath Framework directory path
     * @return array Manifest array
     */
    protected function extractManifest($abspath)
    {
        $jsonFile = dirname($abspath) . '/boots/boots.json';
        $jsonContents = file_get_contents($jsonFile);
        return json_decode($jsonContents, true);
    }

    /**
     * Load the repostiory class and interface.
     * @param  string $prefix  Name of class and interface
     * @param  string $version Version
     * @return string Fully qualified class name
     */
    protected function loadRepository($prefix = 'Repository', $version = '')
    {
        $this->getLocalInterface("{$prefix}Interface");
        return $this->getLocalClass($prefix, $version);
    }

    /**
     * Instantiate the boots api.
     * @param  string $version Version
     * @return Api Boots api instance
     */
    protected function setupApi($version)
    {
        $class = $this->getLocalClass('Api', $version);
        return new $class($this);
    }

    /**
     * Get the manifest repository.
     * @return RepositoryInterface Manifest repository
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * Get the configuration repository.
     * @return RepositoryInterface Configuration repository
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the versioned boots api instance.
     * @return Api Boots api instance
     */
    public function getInstance()
    {
        return $this->api;
    }

    /**
     * Returns an extension instance.
     *
     * @param  string $extension Extension.
     * @return object
     */
    public function __get($extension)
    {
        return $this->api->$extension;
    }
}