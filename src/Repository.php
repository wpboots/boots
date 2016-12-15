<?php

namespace Boots;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Repository
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

/**
 * @package Boots
 * @subpackage Repository
 */
class Repository implements Contract\RepositoryContract
{
    /**
     * The repository
     * @var array
     */
    protected $repo = [];

    /**
     * The delegates
     * @var array
     */
    protected $delegates = [];

    /**
     * Construct with an optional array.
     * @param array $repo The initial repository array
     */
    public function __construct(array $repo = [])
    {
        $this->repo = $repo;
    }

    /**
     * Recursive setter.
     * @param  string $key   Key string
     * @param  mixed  $value Value to set
     * @return $this
     */
    protected function setter($key, $value)
    {
        $keys = explode('.', $key);
        $key = array_shift($keys);
        if (count($keys) == 0) {
            return [$key => $value];
        }
        return [$key => $this->setter(implode('.', $keys), $value)];
    }

    /**
     * Resolve a dot notated key from the repository.
     * @param  string  $key   Key to resolve
     * @param  boolean $found Was the key found or not?
     * @return mixed   Value
     */
    protected function resolve($key, & $found = false)
    {
        $keys = explode('.', $key);
        $repo = $this->repo;
        foreach ($keys as $k) {
            if (!is_array($repo)
                || !array_key_exists($k, $repo)) {
                foreach ($this->delegates as $delegate) {
                    $repo = $delegate->resolve($key, $found);
                    if ($found) {
                        return $repo;
                    }
                }
                $found = false;
                return;
            }
            $repo = $repo[$k];
        }
        $found = true;
        return $repo;
    }

    /**
     * Get the entire repository as an array.
     * @param  boolean $withDelegates Include delegates?
     * @return array   Repository array with preferable delegates
     */
    public function all($withDelegates = false)
    {
        if ($withDelegates) {
            $repos = [];
            $repos[] = $this->repo;
            foreach ($this->delegates as $delegate) {
                $repos[] = $delegate->all();
            }
            return $repos;
        }
        return $this->repo;
    }

    /**
     * Get the entire repository array with delegates.
     * @return array Repository array with delegates
     */
    public function everything()
    {
        return $this->all(true);
    }

    /**
     * Check to see if the given key exists in the repository.
     * @param  string  $key Key string to check for
     * @return boolean Exists or not
     */
    public function has($key)
    {
        $has = false;
        $this->resolve($key, $has);
        return $has;
    }

    /**
     * Get the value for a given key.
     * @param  string $key     Key string
     * @param  mixed  $default Default value if key isn't found
     * @return mixed  Value for the given key
     */
    public function get($key, $default = null)
    {
        $found = false;
        $value = $this->resolve($key, $found);
        if (!$found) {
            return $default;
        }
        return $value;
    }

    /**
     * Set a value for a given key.
     * @param  string $key   Key string
     * @param  mixed  $value Value to set
     * @return $this Allow chaining
     */
    public function set($key, $value)
    {
        $repo = $this->setter($key, $value, null);
        $this->repo = array_replace_recursive($this->repo, $repo);
        return $this;
    }

    /**
     * Set a default value for a given key.
     * @param  string $key   Key string
     * @param  mixed  $value Value to set
     * @return $this Allow chaining
     */
    public function preset($key, $value)
    {
        if ($this->has($key)) {
            return $this;
        }
        return $this->set($key, $value);
    }

    /**
     * Append a value onto a key value.
     * @param  string $k     Key string
     * @param  mixed  $value Value to append
     * @return $this Allow chaining
     */
    public function append($key, $value)
    {
        $oldValue = $this->get($key, []);
        if (is_array($oldValue)) {
            $oldValue[] = $value;
            return $this->set($key, $oldValue);
        }
        $newValue = [$oldValue, $value];
        return $this->set($key, $newValue);
    }

    /**
     * Add a repository as a delegate.
     * @param  Contract\RepositoryContract $repository Repository interface
     * @return $this Allow chaining
     */
    public function delegate(Contract\RepositoryContract $repository)
    {
        $this->delegates[] = $repository;
        return $this;
    }
}
