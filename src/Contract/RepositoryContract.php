<?php

namespace Boots\Contract;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Contract\RepositoryContract
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

/**
 * @package Boots
 * @subpackage Contract\RepositoryContract
 */
interface RepositoryContract
{
    /**
     * Get the entire repository as an array.
     * @return array   Repository array with preferable delegates
     */
    public function all();

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
}
