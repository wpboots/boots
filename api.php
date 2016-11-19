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
     * Type: plugin or theme
     * @var string
     */
    protected $type;

    /**
     * Configuration arguments
     * @var array
     */
    protected $args;

    /**
     * Manifest array
     * @var array
     */
    protected $manifest;

    /**
     * Boots directory name
     * @var string
     */
    protected $bootsDir;

    /**
     * Main boots api file name
     * @var string
     */
    protected $bootsFile = 'boots.php';

    /**
     * Manifest file name
     * @var string
     */
    protected $manifestFile = 'boots.json';

    /**
     * Boots framework instance
     * @var Boots
     */
    protected $boots;

    /**
     * Instantiate the api.
     * 
     * Extract the manifest and fire up the boots api instance.
     *
     * @since  2.0.0
     *         Refactor and cleaning.
     * @since  1.0.0
     * @uses   Boots
     * @access public
     * @param  string $extension Extension.
     * @return object
     */
    public function __construct($type, array $args)
    {
        $this->args = $args;
        $this->type = $type;
        $this->bootsDir = basename(__DIR__);
        $this->extractManifest();
        $this->renew();
    }

    protected function extractManifest()
    {
        $path = "{$this->bootsDir}/{$this->manifestFile}";
        $jsonFile = dirname($this->args['ABSPATH']) . '/' . $path;
        $jsonContents = file_get_contents($jsonFile);
        $this->manifest = json_decode($jsonContents, true);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function setArgs(array $args)
    {
        $this->args = $args;
        return $this;
    }

    public function getVersion()
    {
        return $this->manifest['version'];
    }

    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    public function renew()
    {
        $args = $this->getArgs();
        $type = $this->getType();
        $version = $this->getVersion();
        $classVersion = str_replace('.', '_', $version);
        $class = "Boots_{$classVersion}";
        if(!class_exists($class))
        {
            $path = "{$this->bootsDir}/{$this->bootsFile}";
            include dirname($args['ABSPATH']) . '/' . $path;
        }
        $this->boots = new $class($type, $args);
        $this->setArgs($args);
        return $this;
    }

    public function instance()
    {
        return $this->boots;
    }

    /**
     * __get Magic Method
     * Returns the extension instance
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