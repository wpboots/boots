<?php

namespace Boots;

/**
 * This file is part of the Boots framework.
 *
 * @package    Boots
 * @subpackage WordPress
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    2.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/boots
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/boots/blob/master/LICENSE
 */

/**
 * @package Boots
 * @subpackage WordPress
 */
class WordPress
{
    /**
     * Boots instance
     * @var Boots
     */
    protected $boots;

    /**
     * Validate and make the configuration.
     * @param Boots $boots      Boots wrapper object
     * @param array $extensions Optional extensions to bind
     */
    public function __construct(Boots $boots)
    {
        $this->boots = $boots;
        $this->validateConfig();
        $this->makeConfig();
    }

    /**
     * Validate application configuration.
     * @return void
     */
    protected function validateConfig()
    {
        $config = $this->boots->config();

        if (!in_array($config->get('type'), ['plugin', 'theme'])) {
            throw new Exception\UnkownTypeException(
                'Only plugin or theme type is acceptable.'
            );
        }
        if (!$config->has('id')) {
            throw new Exception\InvalidConfigException(
                'id configuration key is required.'
            );
        }
        if (!$config->has('nick')) {
            throw new Exception\InvalidConfigException(
                'nick configuration key is required.'
            );
        }
        if (!$config->has('version')) {
            throw new Exception\InvalidConfigException(
                'version configuration key is required.'
            );
        }
    }

    /**
     * Make the configuration by setting widely used key values.
     * @return void
     */
    protected function makeConfig()
    {
        $config = $this->boots->config();
        $type = $config->get('type');
        $config->preset('env', 'production');
        $config->set('app.type', $type);
        if ($type == 'plugin') {
            $pluginDirName = basename($config->get('app.path'));
            $config->set('app.url', plugins_url($pluginDirName));
        } elseif ($type == 'theme') {
            $config->set('app.url', get_stylesheet_directory_uri());
            $config->set('app.parent_path', get_template_directory());
            $config->set('app.parent_url', get_template_directory_uri());
        }
        if ($config->has('logo')) {
            $config->set('app.logo', $config->get('app.url') . '/' . ltrim($config->get('logo'), '/'));
        }
        if ($config->has('icon')) {
            $config->set('app.icon', $config->get('app.url') . '/' . ltrim($config->get('icon'), '/'));
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
        $config->set('wp.theme.parent_path', get_template_directory());
        $config->set('wp.theme.parent_url', get_template_directory_uri());
        $config->set('wp.using_child_theme', $config->get('wp.theme.path') != $config->get('wp.theme.parent_path'));
        $config->set('boots.url', $config->get('app.url') . '/boots');
        $config->set('boots.extend_url', $config->get('boots.url') . '/extend');
        $config->set('php.version', phpversion());
        $config->set('php.version_id', PHP_VERSION_ID);
    }
}
