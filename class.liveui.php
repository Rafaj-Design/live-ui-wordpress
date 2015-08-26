<?php

class liveui {
	
	public static $baseApiUrl = 'http://api.liveui.io';
	
	public static $baseImagesUrl = 'http://images.liveui.io';
	
	
	private static $translations = null;
	
	private static $images = null;
	
	private static $colors = null;
	
	
	// Public interface
	
	public static function load_translations() {
		self::$translations = get_option('liveui_translations');
		self::$images = get_option('liveui_images');
		self::$colors = get_option('liveui_colors');
	}
	
	public static function get_available_locales() {
		
	}
	
	public static function update_data($build='live') {
		$apiKey = get_option('liveui_translation_api_key');
		if (!$apiKey) {
			
			return false;
		}
		$translations = self::get_api('translations', $apiKey, $build);
		print_r($translations);
		$images = self::get_api('visuals/images', $apiKey, $build);
		print_r($images);
		$colors = self::get_api('visuals/colors', $apiKey, $build);
		print_r($colors);
		die();
	}
	
	public static function translation_for_key($key, $locale) {
		
	}
	
	public static function image_for_key($key, $locale) {
		
	}
	
	public static function color_for_key($key) {
		
	}
	
	public static function get_api($path, $apiKey, $build=0, $postData=null) {
		$url = self::$baseApiUrl.'/'.$path.'.json';
		return self::get($url, $apiKey, $build, $postData);
	}
	
	public static function get_image($path, $apiKey) {
		$url = self::$baseImagesUrl.'/'.$path;
		return self::get($url, $apiKey, -666);
	}
	
	// Private interface
	
	private static function get_cached_image($imgUrl) {
		
	}
	
	private static function reset_image_cache() {
		
	}
	
	private static function get($url, $apiKey, $build, $postData=null) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		if (!empty($postData)) {
			if (is_array($postData)) {
				$postData = json_encode($postData);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$headers = array();
		$headers[] = 'X-ApiKey: '.$apiKey;
		if ($build > -100) {
			$headers[] = 'X-AppBuild: '.$build;
		}
		$headers[] = 'X-ApiVersion: 1.0';
		$headers[] = 'X-Platform: '.LIVEUI_CLIENT;
		$headers[] = 'X-PluginVersion: '.LIVEUI_VERSION;
		$headers[] = 'X-WPVersion: '.$GLOBALS['wp_version'];
		$headers[] = 'X-MinWPVersion: '.LIVEUI_MINIMUM_WP_VERSION;
		$headers[] = 'X-OsName: '.$build;
		$headers[] = 'X-Url: '.get_option('siteurl');
		$headers[] = 'X-Home: '.get_option('home');
		$headers[] = 'X-AdminEmail: '.get_option('admin_email');
		$headers[] = 'X-BlogName: '.get_option('blogname');
		$headers[] = 'X-BlogDesc: '.get_option('blogdesc');
		if (!empty($postData)) {
			$headers[] = 'Content-Type: application/json';
			$headers[] = 'Content-Length: '.strlen($postData);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
	}
}