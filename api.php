<?php if(!defined('ABSPATH')) die(-1);

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

if(!class_exists('Boots')) :

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
     * Configuration arguments
     * @since 2.0.0
     * @var array
     */
    protected $config;

    /**
     * Manifest array
     * @since 2.0.0
     * @var array
     */
    protected $manifest;

    /**
     * Boots directory name
     * @since 2.0.0
     * @var string
     */
    protected $bootsDir;

    /**
     * Main boots api file name
     * @since 2.0.0
     * @var string
     */
    protected $bootsFile = 'boots.php';

    /**
     * Manifest file name
     * @since 2.0.0
     * @var string
     */
    protected $manifestFile = 'boots.json';

    /**
     * Boots framework instance
     * @since 1.0.0
     * @var Boots
     */
    protected $boots;

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
        $this->config = $config;
        $this->type = $type;
        $this->bootsDir = basename(__DIR__);
        $this->setupManifest();
        $this->renew();
    }

    /**
     * Extract and setup the manifest.
     * @since 2.0.0
     */
    protected function setupManifest()
    {
        $path = "{$this->bootsDir}/{$this->manifestFile}";
        $jsonFile = dirname($this->config['ABSPATH']) . '/' . $path;
        $jsonContents = file_get_contents($jsonFile);
        $this->manifest = json_decode($jsonContents, true);
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
     * Set the type of the application.
     * @since 2.0.0
     * @param  string $type plugin or theme
     * @return $this Allow chaining
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the configuration arguments.
     * @since 2.0.0
     * @param  string|null $key     Get individual key value
     * @param  mixed|null  $default Default value
     * @return array Arguments
     */
    public function getConfig($key = null, $default = null)
    {
        if(is_null($key)) {
            return $this->config;
        }
        return array_key_exists($key, $this->config)
            ? $this->config[$key] : $default;
    }

    /**
     * Set and merge the configuration arguments.
     * @since 2.0.0
     * @param  array $config Configuration
     * @return $this Allow chaining
     */
    public function setConfig(array $config)
    {
        array_replace_recursive($this->config, $config);
        return $this;
    }

    /**
     * Get the boots api version.
     * @since 2.0.0
     * @return string Version
     */
    public function getVersion()
    {
        return $this->manifest['version'];
    }

    /**
     * Set the version of the boots api to be used.
     * @since 2.0.0
     * @param  string $version Version
     * @return $this Allow chaining
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Instantiate the boots api.
     * @since 2.0.0
     * @return $this Allow chaining
     */
    public function renew()
    {
        $type = $this->getType();
        $config = $this->getConfig();
        $version = $this->getVersion();
        $classVersion = str_replace('.', '_', $version);
        $class = "Boots_{$classVersion}";
        if(!class_exists($class))
        {
            $path = "{$this->bootsDir}/{$this->bootsFile}";
            include dirname($config['ABSPATH']) . '/' . $path;
        }
        $this->boots = new $class($type, $config);
        $this->setConfig($config);
        return $this;
    }

    /**
     * Get the versioned boots api instance.
     * @since 2.0.0
     * @return Boots_x_x_x Boots api instance
     */
    public function instance()
    {
        return $this->boots;
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
        return $this->boots->$extension;
    }
}

endif;