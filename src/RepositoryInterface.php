<?php

namespace Boots;

/**
 * Boots repository interface.
 *
 * @package Boots
 * @subpackage RepositoryInterface
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
 * @subpackage RepositoryInterface
 * @version 2.0.0
 */
interface RepositoryInterface
{
    /**
     * Get the entire repository as an array.
     * @param  boolean $withDelegates Include delegates?
     * @return array   Repository array with preferable delegates
     */
    public function all($withDelegates = false);

    /**
     * Get the entire repository array with delegates.
     * @return array Repository array with delegates
     */
    public function everything();

    /**
     * Check to see if the given key exists in the repository.
     * @param  string  $key Key string to check for
     * @return boolean Exists or not
     */
    public function has($key);

    /**
     * Get the value for a given key.
     * @param  string $key     Key string
     * @param  mixed  $default Default value if key isn't found
     * @return mixed  Value for the given key
     */
    public function get($key, $default = null);

    /**
     * Set a value for a given key.
     * @param  string $key   Key string
     * @param  mixed  $value Value to set
     * @return $this Allow chaining
     */
    public function set($key, $value);

    /**
     * Set a default value for a given key.
     * @param  string $key   Key string
     * @param  mixed  $value Value to set
     * @return $this Allow chaining
     */
    public function preset($key, $value);

    /**
     * Append a value onto a key value.
     * @param  string $k     Key string
     * @param  mixed  $value Value to append
     * @return $this Allow chaining
     */
    public function append($key, $value);

    /**
     * Add a repository as a delegate.
     * @param  RepositoryInterface $repository Repository interface
     * @return $this Allow chaining
     */
    public function delegate(RepositoryInterface $repository);
}