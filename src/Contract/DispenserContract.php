<?php

namespace Boots\Contract;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Contract\DispenserContract
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

/**
 * @package Boots
 * @subpackage Contract\DispenserContract
 */
interface DispenserContract
{
    /**
     * Dispense a service by token.
     * @param  string $token Service token
     * @return mixed  Service
     */
    public function dispense($token);
}
