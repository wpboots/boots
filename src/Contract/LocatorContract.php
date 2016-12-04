<?php

namespace Boots\Contract;

/**
 * Boots locator contract.
 *
 * @package Boots
 * @subpackage LocatorContract
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
 * @subpackage LocatorContract
 * @version 2.0.0
 */
interface LocatorContract
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
     * @return string           Versioned fully qualified class name
     */
    public function locate($filepath, $class, $version = '');

    /**
     * Store filepath for fluent api access.
     * @param  string $filepath Path to file where class exists
     * @return $this            Allow chaining
     */
    public function file($filepath);

    /**
     * Store version for fluent api access.
     * @param  string $version Version
     * @return $this           Allow chaining
     */
    public function version($version);

    /**
     * Locate a version class for fluent api access.
     * @throws Exception\FileNotFoundException
     *         If file does not exist.
     * @throws Exception\ClassNotFoundException
     *         If version class does not exist
     *         even after loading the file.
     * @param  string $class Fully qualified class name
     * @return string        Versioned fully qualified class name
     */
    public function find($class);
}
