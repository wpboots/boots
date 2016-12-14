<?php

namespace Boots\Mounter;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage Mounter\Mount
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

use Bhittani\PhpParser;
use Composer\Script\Event;

/**
 * @package Boots
 * @subpackage Mounter\Mount
 */
class Mount
{
    protected static function sanitize($str)
    {
        $re = '/(?<!^)([A-Z][a-z]|(?<=[a-z])[^a-z]|(?<=[A-Z])[0-9_])/';
        return strtolower(preg_replace($re, '.$1', str_replace(' ', '', lcfirst(ucwords(
            str_replace(['-', '_'], ' ', $str)
        )))));
    }

    public static function mountBoots(Event $event)
    {
        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $name = $package->getPrettyName();
        $version = $package->getPrettyVersion();
        $sanitizedVersion = static::sanitize($version);
        $installPath = $composer->getInstallationManager()->getInstallPath($package);

        dump($name);
    }
}
