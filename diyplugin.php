<?php
/**
 * Plugin Name: Diy Plugin Framework
 * Plugin URI: http://github.com/onemanonelaptop/diy
 * Description: A Diy Plugin Framework for creating plugins
 * Version: 0.0.7
 * Author: Rob Holmes
 * Author URI: http://github.com/onemanonelaptop
 */
 
/* Copyright 2011 Rob Holmes ( email: rob@onemanonelaptop.com )

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


add_action( 'plugins_loaded', 'diy_init' );

/**
* Runs on plugins_loaded action and defines the Diy Class 
*   
* @since    0.1
* @access   public
* @return   void
*/
if (!function_exists('diy_init')) {
    function diy_init() {
        include_once('diy.php');
        do_action('diy_init');
    }
}
?>