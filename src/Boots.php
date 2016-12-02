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
     * Type of application
     * @var string
     */
    protected $type;

    /**
     * Configuration repository
     * @var array
     */
    protected $config;

    /**
     * Manifest repository
     * @var RepositoryInterface
     */
    protected $manifest;

    /**
     * Boots directory name
     * @var string
     */
    protected $bootsDir = 'boots';

    /**
     * Manifest file name
     * @var string
     */
    protected $manifestFile = 'boots.json';

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
     * @param string $type   Type of application
     * @param array  $config Configuration array
     */
    public function __construct($type, array $config)
    {
        $this->type = $type;
        $manifest = $this->extractManifest($config['abspath']);
        $this->loadRepository('Repository', $manifest['version']);
        $this->setupManifest($manifest);
        $this->setupConfig($config);
        $this->setupApi($this->getVersion());
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
        return [implode('\\', $nsParts), $name];
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
        if (!class_exists($fqcn[0])) {
            require_once __DIR__ . "/{$prefix}.php";
        }
        return $fqcn[0];
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
        if (!interface_exists($fqin[0])) {
            require_once __DIR__ . "/{$prefix}.php";
        }
        return $fqin[0];
    }

    /**
     * Extract the manifest as an array.
     * @param  string $abspath Framework directory path
     * @return array Manifest array
     */
    protected function extractManifest($abspath)
    {
        $path = "{$this->bootsDir}/{$this->manifestFile}";
        $jsonFile = dirname($abspath) . '/' . $path;
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
        $this->repositoryClass = $this->getLocalClass($prefix, $version);
        return $this->repositoryClass;
    }

    /**
     * Setup the manifest repository.
     * @param  array $manifest Manifest array
     * @return RepositoryInterface Manifest repository
     */
    protected function setupManifest(array $manifest)
    {
        $this->manifest = new $this->repositoryClass($manifest);
        return $this->manifest;
    }

    /**
     * Setup the configuration repository.
     * @param  array $config Configuration array
     * @return RepositoryInterface Configuration repository
     */
    protected function setupConfig(array $config)
    {
        $this->config = new $this->repositoryClass($config);
        return $this->config;
    }

    /**
     * Instantiate the boots api.
     * @param  string $version Version
     * @return Api Boots api instance
     */
    protected function setupApi($version)
    {
        $class = $this->getLocalClass('Api', $version);
        $this->api = new $class($this);
        return $this->api;
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
     * Get the type of the application.
     * @return string plugin or theme
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the boots api version.
     * @return string Version
     */
    public function getVersion()
    {
        return $this->manifest->get('version');
    }

    /**
     * Get the framework directory name.
     * @return string Directory name
     */
    public function getDirName()
    {
        return $this->bootsDir;
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