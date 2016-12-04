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
if(!defined('ABSPATH')) die(-1);

/**
 * @package Boots
 * @subpackage Dispenser
 * @version 2.0.0
 */
class Dispenser implements Contract\DispenserContract
{
    /**
     * Dispense an extension by key.
     * @param  string $token Extension key
     * @return mixed  Extension
     */
    public function dispense($token)
    {
        //
    }
}
