<?php

/**
 * Boots
 *
 * @package Boots
 * @version 1.0.0
 * @license GPLv2
 *
 * Boots - The missing WordPress framework. http://wpboots.com
 *
 * Copyright (C) <2014>  <M. Kamal Khan>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

if(!class_exists('Boots')) :

    class Boots
    {
        /**
          * Application settings
          *
          * @var    $Settings
          * @since  1.0.0
          * @access private
          */
        private $Settings = array();

        /**
          * Application extensions
          *
          * @var    $Extensions
          * @since  1.0.0
          * @access private
          */
        private $Extensions = array();

        /**
          * Sets the app settings, paths, type (plugin, theme)
          * with some extra piece of information.
          *
          * @since  1.0.0
          * @access public
          * @param  string $type Application type
          * @param  array  $Args App information. Gets modified
          *                      with extra information
          * @return null
          */
        public function __construct($type, & $Args)
        {
            if($type != 'plugin' && $type != 'theme')
            {
                return null;
            }

            if(!array_key_exists('APP_ID', $Args))
            {
                trigger_error("APP_ID is required", E_USER_ERROR);
            }

            if(!array_key_exists('APP_NICK', $Args))
            {
                trigger_error("APP_NICK is required", E_USER_ERROR);
            }

            if(!array_key_exists('APP_VERSION', $Args))
            {
                trigger_error("APP_VERSION is required", E_USER_ERROR);
            }

            if(!array_key_exists('ABSPATH', $Args))
            {
                trigger_error("ABSPATH is required", E_USER_ERROR);
            }

            $abspath = $Args['ABSPATH'] = dirname($Args['ABSPATH']);
            $path = $Args['ABSPATH'] . '/boots';

            $this->Settings['BOOTS'] = 'Boots';

            $this->Settings['APP_MODE'] = 'live';
            $this->Settings['APP_TYPE'] = $type;
            $this->Settings['WP_ABSPATH'] = rtrim(ABSPATH, '/');
            $this->Settings['WP_DIR'] = $this->Settings['WP_ABSPATH'];
            $this->Settings['WP_AJAXURL'] = admin_url('admin-ajax.php'); // use the ajax extension instead
            $this->Settings['WP_VERSION'] = get_bloginfo('version');
            $this->Settings['WP_URL'] = get_bloginfo('wpurl');
            $this->Settings['WP_SITE_URL'] = site_url();
            $this->Settings['WP_ADMIN_URL'] = rtrim(admin_url(), '/');
            $this->Settings['WP_ADMIN_POSTS_URL'] = admin_url('edit.php');
            $this->Settings['WP_ADMIN_PAGES_URL'] = admin_url('edit.php?post_type=page');
            $this->Settings['WP_INCLUDES_URL'] = rtrim(includes_url(), '/');
            $this->Settings['WP_CONTENT_URL'] = content_url();
            $this->Settings['WP_PLUGINS_URL'] = plugins_url();
            $this->Settings['WP_UPLOAD_DIR'] = wp_upload_dir();
            $this->settings_helper('THEME');

            $this->Settings['APP_PREFIX'] = strtolower($this->Settings['BOOTS']);
            if($type=='plugin')
            {
                $this->Settings['APP_DIR'] = $abspath;
                $this->Settings['APP_URL'] = plugins_url('' , $path);
                $this->Settings['APP_PREFIX'] .= '_plugin_';
            }
            else if($type=='theme')
            {
                $this->settings_helper();
                $this->Settings['APP_PREFIX'] .= '_theme_';
            }
            $this->Settings['IS_CHILD_THEME'] = $this->Settings['THEME_DIR'] != $this->Settings['THEME_DIR_PARENT']
                                                 ? true : false;
            $this->Settings['APP_PREFIX'] .= $Args['APP_ID'] . '_';
            $Args['APP_LOGO'] = array_key_exists('APP_LOGO', $Args)
                              ? ($this->Settings['APP_URL'] . '/' . $Args['APP_LOGO'])
                              : false;
            $Args['APP_ICON'] = array_key_exists('APP_ICON', $Args)
                              ? ($this->Settings['APP_URL'] . '/' . $Args['APP_ICON'])
                              : false;

            $this->Settings['BOOTS_DIR'] = $path;
            $this->Settings['BOOTS_URL'] = $this->Settings['APP_URL'] . '/' . basename($path);
            $this->Settings['BOOTS_EXTEND_DIR'] = $path . '/extend';
            $this->Settings['BOOTS_EXTEND_URL'] = $this->Settings['BOOTS_URL'] . '/extend';
            $this->Settings['PHP_VERSION'] = phpversion();
            $this->Settings['PHP_VERSION_ID'] = PHP_VERSION_ID;

            $this->Settings = array_merge($this->Settings, $Args);
            ksort($this->Settings);

            $Args = $this->Settings;

            return null;
        }

        /**
          * Helps setting the theme and app paths
          *
          * @since  1.0.0
          * @access private
          * @param  string $str Prepend text for array keys.
          * @return void
          */
        private function settings_helper($str = 'APP')
        {
            $this->Settings[$str . '_DIR'] = get_stylesheet_directory();
            $this->Settings[$str . '_URL'] = get_stylesheet_directory_uri();
            $this->Settings[$str . '_DIR_PARENT'] = get_template_directory();
            $this->Settings[$str . '_URL_PARENT'] = get_template_directory_uri();
        }

        /**
          * Loads an extension from boots/extend/.
          * Creates an instance of the extension.
          *
          * @since  1.0.0
          * @access private
          * @param  string $ext Extension being called.
          * @return object
          */
        private function load_extension($ext)
        {
            $exdir = $this->Settings['BOOTS_EXTEND_DIR'];
            $exurl = $this->Settings['BOOTS_EXTEND_URL'];

            $fdir = $exdir . '/' . str_replace('-', '_', $ext);
            $furl = $exurl . '/' . str_replace('-', '_', $ext);

            $file = $fdir . '/' . 'api.php';

            $json_file = $fdir . '/' . 'boots.json';

            if(!file_exists($json_file))
            {
                $this->dump_error('File <strong>' . $json_file . '</strong> could not be found. Make sure you use composer to install extensions.');
            }

            $json = json_decode(file_get_contents($json_file), true);

            $class = $json['class'];

            if(!class_exists($class))
            {
                if(!file_exists($file))
                {
                    $this->dump_error('File <strong>' . $file . '</strong> could not be found.');
                }
                else
                {
                    include $file;
                    if(!class_exists($class))
                    {
                        $this->dump_error('Class <strong>' . $class . '</strong> could not be located in <strong>' . $file . '</strong>.');
                    }
                }
            }

            return new $class($this, $this->Settings, $fdir, $furl);
        }

        private function dump_error($error, $type = E_USER_ERROR)
        {
            if($this->Settings['APP_MODE'] == 'dev')
            {
                trigger_error($error, $type);
            }
            else
            {
                //die();
            }
        }

        /**
          * Checks to see whether the extension is
          * already instantiated. Loads it if not.
          *
          * @since  1.0.0
          * @uses   Boots::load_extension
          * @access private
          * @param  string $extension Extension being called.
          * @return object
          */
        private function extend($extension)
        {
            if(!array_key_exists($extension, $this->Extensions))
            {
                $this->Extensions[$extension] = $this->load_extension($extension);
            }
            return $this->Extensions[$extension];
        }

        /**
          * __get Magic Method
          * Returns the extension instance
          *
          * @since  1.0.0
          * @uses   Boots::extend
          * @access public
          * @param  string $extension Extension being called.
          * @return object
          */
        public function __get($extension)
        {
            $extension = strip_tags($extension);
            return $this->extend($extension);
        }
    }

endif;