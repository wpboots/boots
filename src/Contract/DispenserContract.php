<?php

namespace Boots\Contract;

/**
 * Boots dispenser contract.
 *
 * @package Boots
 * @subpackage DispenserContract
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
 * @subpackage DispenserContract
 * @version 2.0.0
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
