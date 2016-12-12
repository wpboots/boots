<?php

namespace Boots\Contract;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Contract\EventContract
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

/**
 * @package Boots
 * @subpackage Contract\EventContract
 */
interface EventContract
{
    /**
     * Emit an event.
     * @param  string $key    Identifier
     * @param  array  $params Action parameters
     * @return array          Results of all actions
     */
    public function fire($key, array $params = []);

    /**
     * Add an event listener.
     * @param  string   $key    Identifier
     * @param  callable $action Action
     * @return $this            Allow chaining
     */
    public function add($key, callable $action);
}
