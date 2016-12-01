<?php

namespace Boots;

/**
 * A wrapper class for the Boots API.
 * By using this approach, any version updates
 * will not enforce developers to modify the usage.
 *
 * @package Boots
 * @subpackage Boots
 * @version 0.1.0
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
 * @since 1.0.0
 */
class Boots
{
    /**
     * Type of application
     * @since 2.0.0
     * @var string
     */
    protected $type;

    /**
     * Configuration
     * @since 2.0.0
     * @var array
     */
    protected $config;

    /**
     * Manifest repository
     * @since 2.0.0
     * @var RepositoryInterface
     */
    protected $manifest;

    /**
     * Boots directory name
     * @since 2.0.0
     * @var string
     */
    protected $bootsDir = 'boots';

    /**
     * Manifest file name
     * @since 2.0.0
     * @var string
     */
    protected $manifestFile = 'boots.json';

    /**
     * Repository class
     * @since 2.0.0
     * @var string
     */
    protected $repositoryClass;

    /**
     * Boots api instance
     * @since 1.0.0
     * @var Boots
     */
    protected $api;

    /**
     * Instantiate the api.
     *
     * Setup the manifest and fire up the boots api instance.
     *
     * @since  2.0.0
     *         Refactor and cleaning.
     * @since  1.0.0
     * @uses   Boots
     * @access public
     * @param  string $extension Extension.
     * @return object
     */
    public function __construct($type, array $config)
    {
        $this->type = $type;
        $manifest = $this->extractManifest($config['abspath']);
        $this->loadRepository('Repository', $manifest['repository']['version']);
        $this->setupManifest($manifest);
        $this->setupConfig($config);
        $this->setupApi($this->getVersion());
    }

    protected function getLocal($prefix, $version)
    {
        $suffix = str_replace('.', '_', $version);
        $suffix = empty($suffix) ? '' : "_{$suffix}";
        $nsParts = explode('\\', get_class());
        $name = $prefix . $suffix;
        $nsParts[count($nsParts)-1] = $name;
        return [implode('\\', $nsParts), $name];
    }

    protected function getLocalClass($prefix, $version = '')
    {
        $fqcn = $this->getLocal($prefix, $version);
        if (!class_exists($fqcn[0])) {
            require_once __DIR__ . "/{$fqcn[1]}.php";
        }
        return $fqcn[0];
    }

    protected function getLocalInterface($prefix, $version = '')
    {
        $fqin = $this->getLocal($prefix, $version);
        if (!class_exists($fqin[0])) {
            require_once __DIR__ . "/{$fqin[1]}.php";
        }
        return $fqin[0];
    }

    /**
     * Extract the manifest.
     * @since 2.0.0
     */
    protected function extractManifest($abspath)
    {
        $path = "{$this->bootsDir}/{$this->manifestFile}";
        $jsonFile = dirname($abspath) . '/' . $path;
        $jsonContents = file_get_contents($jsonFile);
        return json_decode($jsonContents, true);
    }

    protected function loadRepository($prefix = 'Repository', $version = '')
    {
        $this->getLocalInterface("{$prefix}Interface");
        $this->repositoryClass = $this->getLocalClass($prefix, $version);
        return $this->repositoryClass;
    }

    protected function setupManifest(array $manifest)
    {
        $this->manifest = new $this->repositoryClass($manifest);
        return $this->manifest;
    }

    protected function setupConfig($config)
    {
        $this->config = new $this->repositoryClass($config);
        return $this->config;
    }

    /**
     * Instantiate the boots api.
     * @since 2.0.0
     * @param  string $version Api version
     * @return $this Allow chaining
     */
    protected function setupApi($version)
    {
        $class = $this->getLocalClass('Api', $version);
        $this->api = new $class($this);
        return $this;
    }

    /**
     * Get the manifest repository.
     * @since 2.0.0
     * @return RepositoryInterface Manifest repository
     */
    public function getManifest()
    {
        return $this->manifest;
    }

    /**
     * Get the configuration repository.
     * @since 2.0.0
     * @return RepositoryInterface Configuration repository
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the type of the application.
     * @since 2.0.0
     * @return string plugin or theme
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the boots api version.
     * @since 2.0.0
     * @return string Version
     */
    public function getVersion()
    {
        return $this->manifest->get('version');
    }

    /**
     * Get boots directory name.
     * @return string Directory name
     */
    public function getDirName()
    {
        return $this->bootsDir;
    }

    /**
     * Get the versioned boots api instance.
     * @since 2.0.0
     * @return API_x_x_x Boots api instance
     */
    public function getInstance()
    {
        return $this->api;
    }

    /**
     * __get Magic Method.
     * Returns an extension instance.
     *
     * @since  1.0.0
     * @uses   Boots
     * @access public
     * @param  string $extension Extension.
     * @return object
     */
    public function __get($extension)
    {
        return $this->api->$extension;
    }
}