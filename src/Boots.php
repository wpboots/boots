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
     * Boots api instance
     * @var Api
     */
    protected $api;

    /**
     * Setup and fire up the boots api instance.
     * @param array $config Configuration array
     */
    public function __construct(array $config)
    {
        $manifest = $this->extractManifest($config['abspath']);
        $version = $manifest['version'];
        $locator = new Locator;
        $repoClass = $locator->locate(__DIR__ . '/Repository.php', 'Boots\Repository', $version);
        $this->config = new $repoClass($config);
        $this->manifest = new $repoClass($manifest);
        $apiClass = $locator->locate(__DIR__ . '/Api.php', 'Boots\Api', $version);
        $this->api = new $apiClass($this);
    }

    /**
     * Factory for setting up the object.
     * @param  array $config Configuration
     * @return Boots Factory generated instance
     */
    public static function factory(array $config)
    {
        $boots = new static;
        $manifest = $boots->extractManifest($config['abspath']);
        $version = $manifest['version'];
        $locator = new Locator;
        $repoClass = $locator->locate(__DIR__ . '/Repository.php', 'Boots\Repository', $version);
        $boots->config = new $repoClass($config);
        $boots->manifest = new $repoClass($manifest);
        $apiClass = $locator->locate(__DIR__ . '/Api.php', 'Boots\Api', $version);
        $boots->api = new $apiClass($this);
        return $boots;
    }

    /**
     * Get a local class.
     * @param  string $prefix  Name of class
     * @param  string $version Version
     * @return string Fully qualified class name
     */
    protected function getLocalClass($prefix, $version = '')
    {
        $suffix = str_replace('.', '_', $version);
        $suffix = empty($suffix) ? '' : "_{$suffix}";
        $fqcn = 'Boots\\' . $prefix . $suffix;
        if (!class_exists($fqcn)) {
            require_once __DIR__ . "/{$prefix}.php";
        }
        return $fqcn;
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