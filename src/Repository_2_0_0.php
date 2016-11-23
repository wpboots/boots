<?php

namespace Boots;

/**
 * Boots repository class.
 *
 * @package Boots
 * @subpackage Repository
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
 * @subpackage Repository
 * @version 2.0.0
 */
class Repository_2_0_0 implements RepositoryInterface
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
     * @param string     $key   Key string
     * @param mixed      $value Value to set
     * @param array|null $repo  For internal use
     * @return $this Allow chaining
     */
    public function set($key, $value, & $repo = null)
    {
        $keys = explode('.', $key);
        $key = array_shift($keys);
        if (count($keys) == 0) {
            if (is_null($repo)) {
                $repo = [$key => $value];
                $this->repo = array_replace_recursive($this->repo, $repo);
                return $this;
            }
            $repo[$key] = $value;
            return $this;
        }
        $repo[$key] = [];
        $this->set(implode('.', $keys), $value, $repo[$key]);
        $this->repo = array_replace_recursive($this->repo, $repo);
        return $this;
    }

    /**
     * Set a default value for a given key.
     * @param string     $key   Key string
     * @param mixed      $value Value to set
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
     * @param  string     $k     Key string
     * @param  mixed      $value Value to append
     * @param  array|null $repo  For internal use
     * @return $this Allow chaining
     */
    public function append($k, $value, & $repo = null)
    {
        $keys = explode('.', $k);
        $key = array_shift($keys);
        $val = $this->get(count($key) == 0 ? $key : $k, []);
        if (!is_array($val)) {
            $val = [$val];
        }
        if (is_array($value)) {
            $val = $value;
        } else {
            $val[] = $value;
        }
        if (count($keys) == 0) {
            if (is_null($repo)) {
                $repo = [$key => $val];
                $this->repo = array_replace_recursive($this->repo, $repo);
                return $this;
            }
            $repo[$key] = $val;
            return $this;
        }
        $repo[$key] = [];
        $this->set(implode('.', $keys), $val, $repo[$key]);
        $this->repo = array_replace_recursive($this->repo, $repo);
        return $this;
    }

    /**
     * Add a repository as a delegate.
     * @param  RepositoryInterface $repository Repository interface
     * @return $this Allow chaining
     */
    public function delegate(RepositoryInterface $repository)
    {
        $this->delegates[] = $repository;
        return $this;
    }
}