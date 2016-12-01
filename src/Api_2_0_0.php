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
     * @var Boots
     */
    protected $boots;

    /**
     * Extension storage
     * @var array
     */
    protected $extensions;

    /**
     * Manifest file name
     * @var string
     */
    protected $manifestFile = 'boots.json';

    /**
     * Name of the extension file
     * @var string
     */
    protected $extensionFile = 'index.php';

    public function __construct(Boots $boots, array $extensions = [])
    {
        $this->boots = $boots;
        $this->extensions = $extensions;
        $this->validateConfig();
        $this->makeConfig();
    }

    protected function validateConfig()
    {
        $type = $this->boots->getType();
        if(!in_array($type, ['plugin', 'theme'])) {
            throw new Exception\UnkownTypeException(
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
        $config->set('wp.theme.parent_path',  get_template_directory());
        $config->set('wp.theme.parent_url',  get_template_directory_uri());
        $config->set('wp.using_child_theme', $config->get('wp.theme.path') != $config->get('wp.theme.parent_path'));
        $config->set('boots.version', $this->boots->getVersion());
        $config->set('boots.path', dirname($config->get('abspath')) . '/' . $this->boots->getDirName());
        $config->set('boots.extend_path', $config->get('boots.path') . '/extend');
        $config->set('boots.url', $config->get('app.url') . '/' . $this->boots->getDirName());
        $config->set('boots.extend_url', $config->get('boots.url') . '/extend');
        $config->set('php.version', phpversion());
        $config->set('php.version_id', PHP_VERSION_ID);
    }

    /**
     * Load an extension.
     * @param  string $path    Exension file path
     * @param  string $fqcn    Fully qualified class name
     * @param  string $version Version
     * @return string Fully qualified class name with version
     */
    protected function loadExtension($path, $fqcn, $version)
    {
        $suffix = str_replace('.', '_', $version);
        $suffix = empty($suffix) ? '' : "_{$suffix}";
        $fqcn = $fqcn . $suffix;
        if (!class_exists($fqcn)) {
            if (!is_file($path)) {
                throw new \Exception(sprintf(
                    'File %s could not be located.', $path
                ));
            }
            require_once $path;
            if (!class_exists($fqcn)) {
                throw new \Exception(sprintf(
                    'Class %s could not be located in %s.', $fqcn, $path
                ));
            }
        }
        return $fqcn;
    }

    protected function resolveExtension($fqcn)
    {
        $class = new \ReflectionClass($fqcn);
        $constructor = $class->getConstructor();
        if (is_null($constructor)) {
            return new $fqcn;
        }
        $params = $constructor->getParameters();
        if (count($params) != 1) {
            throw new \Exception(sprintf(
                'Constructor for %s may only have one parameter.', $fqcn
            ));
        }
        // $reqType = get_class($this->boots);
        // $param = array_shift($params);
        // if ($param->getClass()->name != $reqType) {
        //     throw new \Exception(sprintf(
        //         'Constructor parameter for %s should be type hinted by %s.', $fqcn, $reqType
        //     ));
        // }
        return new $fqcn($this->boots);
    }

    protected function extend($extension)
    {
        if (array_key_exists($extension, $this->extensions)) {
            return $this->extensions[$extension];
        }
        $config = $this->boots->getConfig();
        $manifest = $this->boots->getManifest();
        $path2extend = $config->get('boots.extend_path');
        $path2extension = "{$path2extend}/{$extension}";
        if (!is_dir($path2extension)) {
            throw new \Exception(sprintf(
                'Path %s could not be located.', $path2extension
            ));
        }
        $path2manifest = "{$path2extension}/{$this->manifestFile}";
        if (!is_file($path2manifest)) {
            throw new \Exception(sprintf(
                'Manifest file %s could not be located.', $path2manifest
            ));
        }
        $jsonContents = file_get_contents($path2manifest);
        $mArr = json_decode($jsonContents, true);
        $manifest->set("extensions.{$extension}", $mArr);
        $path2file = "{$path2extension}/{$this->extensionFile}";
        $extensionFqcn = $this->loadExtension($path2file, $mArr['class'], $mArr['version']);
        $instance = $this->resolveExtension($extensionFqcn);
        $this->extensions[$extension] = $instance;
        return $instance;
    }

    public function __isset($extension)
    {
        return array_key_exists($extension, $this->extensions);
    }

    public function __unset($extension)
    {
        unset($this->extensions[$extension]);
    }

    public function __set($extension, $value)
    {
        $this->extensions[$extension] = $value;
    }

    public function __get($extension)
    {
        return $this->extend($extension);
    }
}