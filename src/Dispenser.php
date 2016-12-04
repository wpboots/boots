<?php

namespace Boots;

/**
 * Boots dispenser.
 *
 * @package Boots
 * @subpackage Dispenser
 * @version 2.0.0
 * @see http://wpboots.com
 * @link https://github.com/wpboots/boots
 * @author Kamal Khan <shout@bhittani.com> https://bhittani.com
 * @license https://github.com/wpboots/boots/blob/master/LICENSE
 * @copyright Copyright (c) 2014-2016, Kamal Khan
 */

// Die if accessing this script directly.
if (!defined('ABSPATH')) {
    die(-1);
}

/**
 * @package Boots
 * @subpackage Dispenser
 * @version 2.0.0
 */
class Dispenser implements Contract\DispenserContract
{
    /**
     * Directory path containing the services/extensions.
     * @var string
     */
    protected $directory;

    /**
     * Class locator.
     * @var Contract\LocatorContract
     */
    protected $locator;

    /**
     * Dispenser storage.
     * @var Contract\RepositoryContract
     */
    protected $dispenser;

    /**
     * Construct the instance.
     * @param string                      $directory  Extensions directory path
     * @param Contract\LocatorContract    $locator    Class locator instance
     * @param Contract\RepositoryContract $repository Repository instance
     */
    public function __construct(
        $directory,
        Contract\LocatorContract $locator,
        Contract\RepositoryContract $repository
    ) {
        $this->locator = $locator;
        $this->directory = $directory;
        $this->repository = $repository;
    }

    protected function locate($token)
    {
        $dirpath = "{$this->directory}/{$token}";
        $filepath = "{$dirpath}/{$token}.php";
        $path2manifest = "{$dirpath}/{$token}.json";
        if (!is_file($path2manifest)) {
            throw new Exception\FileNotFoundException(sprintf(
                '%s could not be found.', $path2manifest
            ));
        }
        $manifestContent = file_get_contents($path2manifest);
        $mArr = json_decode($manifestContent, true);
        return $this->locator->locate($filepath, $mArr['class'], $mArr['version']);
    }

    /**
     * Dispense an extension by key.
     * @param  string $token Extension key
     * @return mixed  Extension
     */
    public function dispense($token)
    {
        if ($this->repository->has($token)) {
            return $this->repository->get($token);
        }
        $extension = $this->locate($token);
        if ($extension) {
            $extension = new $extension;
            $this->repository->set($token, $extension);
        }
        return $extension;
    }
}
