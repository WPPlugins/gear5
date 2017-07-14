<?php
/**
* Plugin Name: Gear5
* Plugin URI: http://wordpress.org/support/plugin/gear5
* Description: A simple plugin for Gear5 performance monitoring and alerting system. For signup and details plese visit <a href="http://www.gear5.me">www.gear5.me</a>
* Version: 1.4.2
* Author: Robert Gombash
* License: GPL2
*/

/*  Copyright 2014 Gear5

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    Credits: Shiba Example Plugin (http://shibashake.com/wordpress-theme/wordpress-example-plugin) 
*/

// don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

define( 'GEAR5_VERSION', '1.4.2' );
define( 'GEAR5_RELEASE_DATE', date_i18n( 'F j, Y', '1406912561' ) );
define( 'GEAR5_DIR', plugin_dir_path( __FILE__ ) );
define( 'GEAR5_URL', plugin_dir_url( __FILE__ ) );

if (!class_exists("Gear5")) :
class Gear5 {
	
        var $settings, $options_page;

	function __construct() {

                if (is_admin()) {
			// Load example settings page
			if (!class_exists("Gear5_Settings"))
				require(GEAR5_DIR . 'gear5-settings.php');
			$this->settings = new Gear5_Settings();	
		}
		
		add_action('init', array($this,'init') );		
                add_action('wp_head', array( &$this, 'wp_head' ) );
        
                register_activation_hook( __FILE__, array($this,'activate') );
                register_deactivation_hook( __FILE__, array($this,'deactivate') );
                
                //add setting link to plugin
                $plugin = plugin_basename( __FILE__ );
                add_filter( "plugin_action_links_$plugin", array($this, 'plugin_add_settings_link') );         
                                
        }
                        
        function plugin_add_settings_link( $links ) {
            $settings_link = '<a href="options-general.php?page=Gear5">Settings</a>';
            array_push( $links, $settings_link );            
            return $links;
        }

        /*
	Load language translation files (if any) for our plugin.
	*/
	function init() {
		load_plugin_textdomain( 'gear5', GEAR5_DIR . 'lang', 
							   basename( dirname( __FILE__ ) ) . '/lang' );
	}

        // triggered by activation hook
        function activate($networkwide) {}

	// triggered by deactivation hook
        function deactivate($networkwide) {}

        //insert gear5 script into header
        function wp_head() {                
            $api_key="";
            $options = get_option('gear5_options');
            if(!empty($options['text_api_id'])){
                $api_key = trim($options['text_api_id']);
                $script = "<!-- Gear5 tracking code -->\n<script data-cfasync=\"false\" src=\"//cdn.gear5.me/js/boomerang/boomerang.php?key=$api_key\" async></script>\n";

            }
            echo $script;       

        }

}
endif;

// Initialize our plugin object.
global $gear5;
if (class_exists("gear5") && !$gear5) {
    $gear5 = new Gear5();
}
