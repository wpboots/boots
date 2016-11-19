<?php if(!defined('ABSPATH')) die(-1);

/**
 * A wrapper class for the Boots API.
 * By using this approach, any version updates
 * will not enforce developers to modify the usage.
 *
 * @package Boots
 * @subpackage API
 * @version 0.1.0
 * @see http://wpboots.com
 * @link https://github.com/wpboots/boots
 * @author Kamal Khan <shout@bhittani.com> https://bhittani.com
 * @license https://github.com/wpboots/boots/blob/master/LICENSE
 * @copyright Copyright (c) 2014-2016, Kamal Khan
 */

if(!class_exists('Boots')) :

    class Boots
    {
        protected $type;

        protected $args;

        protected $manifest;

        protected $bootsDir; // = 'boots';

        protected $bootsFile = 'boots.php';

        protected $manifestFile = 'boots.json';

        /**
         * Boots framework instance
         * @var Boots
         */
        protected $boots;

        /**
         * Load the boots.json file
         * and fire up the class
         * with the desired version of Boots.
         *
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