<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link https://vedathemes.com
 * @since 1.0.0
 * @package Simplified_Font_Manager
 *
 * @wordpress-plugin
 * Plugin Name: Simplified Font Manager
 * Description: Simplest way to add google fonts to any WordPress site. Use CSS selectors to apply custom fonts to any element of your website.
 * Version: 1.5.0
 * Author: vedathemes
 * Author URI: https://vedathemes.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: simplified-font-manager
 * Domain Path: /lang
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'SIMPLIFIED_FONT_MANAGER_DIR', plugin_dir_path( __FILE__ ) );

// Currently plugin version.
define( 'SIMPLIFIED_FONT_MANAGER_VERSION', '1.5.0' );

// Load plugin textdomain.
add_action( 'plugins_loaded', 'simplified_font_manager_plugins_loaded' );

// Load plugin's front-end functionality.
require SIMPLIFIED_FONT_MANAGER_DIR . '/frontend/class-frontend.php';

// Load plugin's admin functionality.
require SIMPLIFIED_FONT_MANAGER_DIR . '/backend/class-backend.php';

/**
 * Load plugin text domain.
 *
 * @since 1.0.0
 */
function simplified_font_manager_plugins_loaded() {

	// Add plugin text domain.
	load_plugin_textdomain( 'simplified_font_manager', false, SIMPLIFIED_FONT_MANAGER_DIR . 'lang/' );
}
