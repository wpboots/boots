<?php

namespace Boots;

/**
 * The boots api.
 *
 * @package Boots
 * @subpackage Api
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
 * @subpackage Api
 * @version 2.0.0
 */
class Api_2_0_0
{
    /**
     * Boots instance
     * @var Boots\Boots
     */
    protected $boots;

    public function __construct(Boots $boots)
    {
        $this->setBoots($boots);
    }

    protected function validateConfig()
    {
        $type = $this->boots->getType();
        if(!in_array($type, ['plugin', 'theme'])) {
            throw new Exception\InvalidTypeException(
                'Only plugin or theme type is acceptable'
            );
        }
        $config = $this->boots->getConfig();
        if(!$config->has('abspath')) {
            throw new Exception\InvalidConfigException(
                'abspath configuration key is required'
            );
        }
        if(!$config->has('id')) {
            throw new Exception\InvalidConfigException(
                'id configuration key is required'
            );
        }
        if(!$config->has('nick')) {
            throw new Exception\InvalidConfigException(
                'nick configuration key is required'
            );
        }
        if(!$config->has('version')) {
            throw new Exception\InvalidConfigException(
                'version configuration key is required'
            );
        }
    }

    protected function makeConfig()
    {
        $type = $this->boots->getType();
        $config = $this->boots->getConfig();
        $config->preset('env', 'production');
        $config->set('app.type', $this->boots->getType());
        $config->set('app.dir', basename($config->get('abspath')));
        $config->set('app.path', dirname($config->get('abspath')));
        if ($type == 'plugin') {
            $pluginDirName = basename($config->get('app.path'));
            $config->set('app.url', plugins_url($pluginDirName));
        } else if ($type == 'theme') {
            $config->set('app.path', get_stylesheet_directory());
            $config->set('app.url', get_stylesheet_directory_uri());
            $config->set('app.parent_path',  get_template_directory());
            $config->set('app.parent_url',  get_template_directory_uri());
        }
        if ($config->has('logo')) {
            $config->set('app.logo', $config->get('app.url') . '/' . $config->get('logo'));
        }
        if ($config->has('icon')) {
            $config->set('app.icon', $config->get('app.url') . '/' . $config->get('icon'));
        }
        $config->set('wp.path', rtrim(ABSPATH, '/'));
        $config->set('wp.url', get_bloginfo('wpurl'));
        $config->set('wp.ajax_url', admin_url('admin-ajax.php'));
        $config->set('wp.version', get_bloginfo('version'));
        $config->set('wp.site_url', home_url());
        $config->set('wp.includes_url', rtrim(includes_url(), '/'));
        $config->set('wp.content_url', content_url());
        $config->set('wp.plugins_url', plugins_url());
        $config->set('wp.uploads', wp_upload_dir());
        $config->set('wp.admin.url', rtrim(admin_url(), '/'));
        $config->set('wp.admin.posts_url', admin_url('edit.php'));
        $config->set('wp.admin.pages_url', admin_url('edit.php?post_type=page'));
        $config->set('wp.theme.path', get_stylesheet_directory());
        $config->set('wp.theme.url', get_stylesheet_directory_uri());
        $config->set('wp.theme.parent_path',  get_template_directory());
        $config->set('wp.theme.parent_url',  get_template_directory_uri());
        $config->set('wp.using_child_theme', $config->get('wp.theme.path') != $config->get('wp.theme.parent_path'));
        $config->set('boots.version', $this->boots->getVersion());
        $config->set('boots.path', $this->boots->getPath());
        $config->set('boots.extend_path', $config->get('boots.path') . '/extend');
        $config->set('boots.url', $config->get('app.url') . '/' . basename(dirname(dirname($config->get('boots.path')))));
        $config->set('boots.extend_url', $config->get('boots.url') . '/extend');
        $config->set('php.version', phpversion());
        $config->set('php.version_id', PHP_VERSION_ID);
    }

    public function setBoots(Boots $boots)
    {
        $this->boots = $boots;
        $this->validateConfig();
        $this->makeConfig();
        return $this;
    }
}