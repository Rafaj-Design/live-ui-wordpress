<?php
/*
Plugin Name: LiveUI
Plugin URI: http://wp.liveui.io/
Description: LiveUI plugin for www.liveui.io
Version: 0.1
Author: LiveUI
Author URI: http://www.liveui.io
License: MIT
*/


define('LIVEUI_VERSION', '0.1');
define('LIVEUI_CLIENT', 'WordPress LiveUI Plugin');
define('LIVEUI_MINIMUM_WP_VERSION', '4.3');
define('LIVEUI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LIVEUI_PLUGIN_DIR', plugin_dir_path(__FILE__));


// Translations

add_action('init', 'LUI');

function LUI($key) {
	return $key;
	
	if (get_option('liveui_debugging')) {
		
	}
}


// Colors

add_action('init', 'LUIColor');

function LUIColor($key) {
	return $key;
	
	if (get_option('liveui_debugging')) {
		
	}
}


// Images

add_action('init', 'LUIImage');

function LUIImage($key) {
	return $key;
	
	if (get_option('liveui_debugging')) {
		
	}
}


// Installation

register_activation_hook(__FILE__, 'liveui_install'); 

register_deactivation_hook( __FILE__, 'liveui_remove');

function liveui_install() {
	global $wpdb;
	
	// Check environment
	if ( version_compare( $GLOBALS['wp_version'], LIVEUI_MINIMUM_WP_VERSION, '<' ) ) {
		load_plugin_textdomain('liveui');
		
		$message = '<strong>'.sprintf(esc_html__( 'LiveUI %s requires WordPress %s or higher.' , 'liveui'), LIVEUI_VERSION, LIVEUI_MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'liveui'), 'https://codex.wordpress.org/Upgrading_WordPress');

		die($message);
	}
	
	// Setup default values
	add_option("liveui_translation_api_key", '', '', 'yes');
	add_option("liveui_translation_api_key_works", '0', '', 'yes');
	add_option("liveui_debugging", '0', '', 'yes');
	add_option("liveui_debugging_text_with_underscores", '0', '', 'yes');
	
	// Create tables
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	
	// Missing translations
	$table_name = $wpdb->prefix."liveui_missing_translations"; 
	$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `key` varchar(255) NOT NULL,
	  `added` datetime NOT NULL,
	  `lang_code` varchar(5) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `key` (`key`,`lang_code`)
	) ENGINE=InnoDB $charset_collate;";
	
	dbDelta($sql);
}

function liveui_remove() {
	global $wpdb;
	
	delete_option('liveui_translation_api_key');
	delete_option('liveui_translation_api_key_works');
	delete_option('liveui_debugging');
	delete_option('liveui_debugging_text_with_underscores');
	
	// Drop missing translations table
	$table_name = $wpdb->prefix . "liveui_missing_translations";
	$wpdb->query("DROP TABLE {$table_name}");
}


// Admin

if (is_admin()) {
	
	// Translations
	
	add_action('plugins_loaded', 'load_liveui_textdomain');
	
	function load_liveui_textdomain() {
		load_plugin_textdomain('liveui', false, LIVEUI_PLUGIN_DIR.'languages/' );
	}
	
	// Settings
	
	function register_my_setting() {
		register_setting('liveui_settings', 'liveui_translation_api_key'); 
		register_setting('liveui_settings', 'liveui_debugging', 'intval'); 
		register_setting('liveui_settings', 'liveui_debugging_text_with_underscores', 'intval'); 
	} 
	
	add_action('admin_init', 'register_my_setting');
	
	// Admin page
	
	add_action('admin_menu', 'liveui_admin_menu');
	
	function liveui_admin_menu() {
		add_options_page('LiveUI', 'LiveUI', 'administrator', 'liveui', 'liveui_html_page');
	}
	
	function liveui_html_page() {
		global $wpdb;
		
		add_option('liveui_translations', array('en' => array('test' => 'this is my test :)')));
		
		$table_name = $wpdb->prefix . "liveui_missing_translations";
		$missingTranslationsCount = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name};");
		
		include(LIVEUI_PLUGIN_DIR.'options.php');
	}

}


