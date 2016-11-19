<?php

namespace Boots;

/**
 * The boots api.
 *
 * @package Boots
 * @subpackage Boots_2_0_0
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
 * @subpackage Boots_2_0_0
 */
class Boots_2_0_0
{
    /**
     * Api instance
     * @var Boots\Boots
     */
    protected $api;

    public function __construct(Boots $api)
    {
        $this->setApi($api);
        $this->validateConfig();
    }

    public function setApi(Boots $api)
    {
        $this->api = $api;
        return $this;
    }

    protected function validateConfig()
    {
        $type = $this->api->getType();
        if(!in_array($type, ['plugin', 'theme'])) {
            throw new Exception\InvalidTypeException(
                'Only plugin or theme type is acceptable'
            );
        }
        $config = $this->api->getConfig();
        if(!array_key_exists('APP_ID', $config)) {
            throw new Exception\InvalidConfigException(
                'APP_ID config key is required'
            );
        }
        if(!array_key_exists('APP_NICK', $config)) {
            throw new Exception\InvalidConfigException(
                'APP_NICK config key is required'
            );
        }
        if(!array_key_exists('APP_VERSION', $config)) {
            throw new Exception\InvalidConfigException(
                'APP_VERSION config key is required'
            );
        }
        if(!array_key_exists('ABSPATH', $config)) {
            throw new Exception\InvalidConfigException(
                'ABSPATH config key is required'
            );
        }
    }
}