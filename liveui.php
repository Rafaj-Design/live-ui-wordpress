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


require_once(LIVEUI_PLUGIN_DIR.'class.liveui.php');


// Translations

function LUI($key, $locale='en') {
	return liveui::translation_for_key($key, $locale);
}

// Images

function LUIImage($key, $locale='en') {
	return liveui::image_url_for_key($key, $locale);
}


// Colors

function LUIColor($key) {
	$color = liveui::color_for_key($key);
	if ($color) {
		return $color['value'];
	}
	else return '000000';
}

function LUIColorAlpha($key) {
	$color = liveui::color_for_key($key);
	if ($color) {
		return (int)$color['alpha'];
	}
	else return 100;
}


// Installation

// 2AB3D033-10F8-48E8-9A1E-50A952CB0831

register_activation_hook(__FILE__, 'liveui_install'); 

register_deactivation_hook( __FILE__, 'liveui_remove');

function liveui_install() {
	global $wpdb;
	
	// Check environment
	if ( version_compare($GLOBALS['wp_version'], LIVEUI_MINIMUM_WP_VERSION, '<' ) ) {
		load_plugin_textdomain('liveui');
		
		$message = '<strong>'.sprintf(esc_html__( 'LiveUI %s requires WordPress %s or higher.' , 'liveui'), LIVEUI_VERSION, LIVEUI_MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version.', 'liveui'), 'https://codex.wordpress.org/Upgrading_WordPress');

		die($message);
	}
	
	// Setup default values
	add_option("liveui_translation_api_key", '', '', 'yes');
	add_option("liveui_translation_api_key_works", '0', '', 'yes');
	add_option("liveui_image_temp_folder", 'wp-content/images/', '', 'yes');
	add_option("liveui_data_cache_expiry_time", '180', '', 'yes');
	add_option("liveui_data_cache_last_refresh", false, '', 'yes');
	add_option("liveui_debugging", '0', '', 'yes');
	add_option("liveui_debugging_text_with_underscores", '0', '', 'yes');
	
	// Create tables
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	$charset_collate = $wpdb->get_charset_collate();
	
	// Missing translations
	$table_name = $wpdb->prefix.'liveui_missing_translations';
	$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `key` varchar(255) NOT NULL,
	  `table` varchar(255) NOT NULL,
	  `added` datetime NOT NULL,
	  `lang_code` varchar(5) NOT NULL,
	  `reported` tinyint(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`),
	  KEY `key` (`key`,`lang_code`, `reported`)
	) ENGINE=InnoDB $charset_collate;";
	
	dbDelta($sql);
}

function liveui_remove() {
	global $wpdb;
	
	delete_option('liveui_translation_api_key');
	delete_option('liveui_translation_api_key_works');
	delete_option('liveui_image_temp_folder');
	delete_option('liveui_data_cache_expiry_time');
	delete_option('liveui_data_cache_last_refresh');
	delete_option('liveui_debugging');
	delete_option('liveui_debugging_text_with_underscores');
	
	delete_transient('liveui_data_cache_translations');
	delete_transient('liveui_data_cache_images');
	delete_transient('liveui_data_cache_colors');
	
	// Drop missing translations table
	$table_name = $wpdb->prefix.'liveui_missing_translations';
	$wpdb->query("DROP TABLE {$table_name};");
}


// Admin

if (is_admin()) {
	
	// Initialization
	
	add_action('init', 'check_actions');
	
	function check_actions() {
		if (isset($_POST['liveui_data_cache_expiry_time'])) {
			$exp = (int)$_POST['liveui_data_cache_expiry_time'];
			if ($exp < 5) {
				$exp = 5;
			}
			$_POST['liveui_data_cache_expiry_time'] = $exp;
		}
		if (isset($_POST['liveui_image_temp_folder'])) {
			$url = trim($_POST['liveui_image_temp_folder'], '/');
			$url .= '/';
			$_POST['liveui_image_temp_folder'] = $url;
		}
		
	    if (isset($_POST['reload'])) {
	        liveui::update_data();
	        wp_redirect('?page=liveui&done=reload');
	    }
	    else if (isset($_POST['remove'])) {
	        liveui::remove_image_cache();
	        wp_redirect('?page=liveui&done=remove');
	    }
	    else if (isset($_POST['report'])) {
	        liveui::report_missing_translations();
	        wp_redirect('?page=liveui&done=report');
	    }
	}

	// Updating data
	
	add_action('update_data', 'update_data');
	
	function update_data() {
		// Updating remote values
		liveui::update_data();
	}
	
	// Translations
	
	add_action('plugins_loaded', 'load_liveui_textdomain');
	
	function load_liveui_textdomain() {
		load_plugin_textdomain('liveui', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	// Settings
	
	add_action('admin_init', 'register_my_setting');
	
	function register_my_setting() {
		register_setting('liveui_settings', 'liveui_translation_api_key'); 
		register_setting('liveui_settings', 'liveui_image_temp_folder'); 
		register_setting('liveui_settings', 'liveui_data_cache_expiry_time', 'intval'); 
		register_setting('liveui_settings', 'liveui_debugging', 'intval'); 
		register_setting('liveui_settings', 'liveui_debugging_text_with_underscores', 'intval'); 
	} 
	
	// Admin page
	
	add_action('admin_menu', 'liveui_admin_menu');
	
	function liveui_admin_menu() {
		add_options_page('LiveUI', 'LiveUI', 'administrator', 'liveui', 'liveui_html_page');
	}
	
	function liveui_html_page() {
		global $wpdb;
		
		add_option('liveui_translations', array('en' => array('test' => 'this is my test :)')));
		
		$table_name = $wpdb->prefix."liveui_missing_translations";
		$missingTranslationsCount = $wpdb->get_var("SELECT COUNT(id) FROM {$table_name} WHERE `reported` = 0;");
		$reportedMissingTranslationsCount = $wpdb->get_var("SELECT COUNT(id) FROM {$table_name} WHERE `reported` = 1;");
		
		$tempImageFolderWritable = (bool)is_writable(ABSPATH.get_option('liveui_image_temp_folder'));
		
		include(LIVEUI_PLUGIN_DIR.'options.php');
	}

}



