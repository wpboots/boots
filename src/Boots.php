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
    protected $bootsDir;

    /**
     * Source directory name
     * @since 2.0.0
     * @var string
     */
    protected $srcDir;

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
        $this->bootsDir = basename(dirname(dirname(__FILE__)));
        $this->srcDir = basename(__DIR__);
        $this->setupManifest($config['abspath']);
        $this->setupConfig($config);
        $this->setupApi($this->getVersion());
    }

    protected function getLocalClass($prefix, $version)
    {
        $classVersion = str_replace('.', '_', $version);
        $nsParts = explode('\\', get_class());
        $nsParts[count($nsParts)-1] = "{$prefix}_{$classVersion}";
        return implode('\\', $nsParts);
    }

    /**
     * Extract and setup the manifest.
     * @since 2.0.0
     */
    protected function setupManifest($abspath)
    {
        $path = "{$this->bootsDir}/{$this->manifestFile}";
        $jsonFile = dirname($abspath) . '/' . $path;
        $jsonContents = file_get_contents($jsonFile);
        $manifestArray = json_decode($jsonContents, true);
        $repositoryVersion = $manifestArray['repository']['version'];
        $this->repositoryClass = $this->getLocalClass('Repository', $repositoryVersion);
        $this->manifest = new $this->repositoryClass($manifestArray);
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
     * Get boots path.
     * @return string Path to boots directory
     */
    public function getPath()
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