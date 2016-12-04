<?php

namespace Boots\Contract;

/**
 * Boots container contract.
 *
 * @package Boots
 * @subpackage ContainerContract
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
 * @subpackage ContainerContract
 * @version 2.0.0
 */
interface ContainerContract
{
    /**
     * Resolve an entity by key.
     * @throws \Boots\Exception\NotFoundException
     *         If an entry is not found.
     * @throws \Boots\Exception\BindingResolutionException
     *         If an entity can not be resolved.
     * @param  string $key Identifier
     * @return mixed  Entity
     */
    public function get($key);

    /**
     * Check whether an entity for a key exists.
     * @param  string  $key Identifier
     * @return boolean Exists or not
     */
    public function has($key);
}
