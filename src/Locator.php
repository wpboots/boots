<?php

namespace Boots;

/**
 * Boots locator.
 *
 * @package Boots
 * @subpackage Locator
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
 * @subpackage Locator
 * @version 2.0.0
 */
class Locator implements Contract\LocatorContract
{
    /**
     * Locate a versioned class.
     * @throws Exception\FileNotFoundException
     *         If file does not exist.
     * @throws Exception\ClassNotFoundException
     *         If version class does not exist
     *         even after loading the file.
     * @param  string $filepath Path to file where class exists
     * @param  string $class    Fully qualified class name
     * @param  string $version  Version
     * @return string           Version fully qualified class name
     */
    public function locate($filepath, $class, $version = '')
    {
        $suffix = str_replace('.', '_', $version);
        $suffix = empty($suffix) ? '' : "_{$suffix}";
        $versionedClass = $class . $suffix;
        if (!class_exists($versionedClass)) {
            if (!is_file($filepath)) {
                throw new Exception\FileNotFoundException('File not found.');
            }
            require_once $filepath;
            if (!class_exists($versionedClass)) {
                throw new Exception\ClassNotFoundException('Class not found.');
            }
        }
        return $versionedClass;
    }
}
