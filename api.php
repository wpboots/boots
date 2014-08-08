<?php

/**
 * Boots
 * This is a wrapper class for the Boots API.
 * By using this approach, any version updates
 * will not enforce developers to update anything
 * for migration.
 *
 * @package Boots
 * @subpackage API
 * @version 1.0.0
 * @license GPLv2
 *
 * Boots - The missing WordPress framework.
 *
 * Copyright (C) <2014>  <M. Kamal Khan> http://wpboots.com
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
        private $Boots = null;
        /**
          * Load the boots.json file
          * and fire up the class
          * with the desired version of Boots.
          *
          * @since  1.0.0
          * @uses   Boots
          * @access public
          * @param  string $extension Extension.
          * @return object
          */
        public function __construct($type, & $Args)
        {
            $json = json_decode(file_get_contents(dirname($Args['ABSPATH']) . '/boots/boots.json'), true);
            $version = $json['version'];
            $class = 'Boots_' . $version;
            if(!class_exists($class))
            {
                include dirname($Args['ABSPATH']) . '/boots/boots.php';
            }
            $this->Boots = new $class($type, $Args);
        }
        /**
          * __get Magic Method
          * Returns the extension instance
          *
          * @since  1.0.0
          * @uses   Boots
          * @access public
          * @param  string $extension Extension.
          * @return object
          */
        public function __get($extension)
        {
            return $this->Boots->$extension;
        }
    }

endif;