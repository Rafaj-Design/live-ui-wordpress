<?php

if (!function_exists('debug')) {

	function debug($data, $file=__FILE__) {
		echo '<p>'.$file.'</p><pre>';
		var_dump($data);
		echo '</pre>';
	}

}

class liveui {
	
	public static $baseApiUrl = 'http://api.liveui.io';
	
	public static $baseImagesUrl = 'http://images.liveui.io';
	
	
	private static $translations = null;
	
	private static $images = null;
	
	private static $colors = null;
	
	
	// Public interface
	
	public static function get_available_locales() {
		self::init_translations();
		if (is_array(self::$translations) && !empty(self::$translations)) {
			return array_keys(self::$translations);
		}
		else {
			return array();
		}
	}
	
	public static function update_data($build='live') {
		$apiKey = get_option('liveui_translation_api_key');
		if (!$apiKey) {
			return false;
		}
		$translations = self::get_api('translations', $apiKey, $build);
		if ($translations) {
			$translations = json_decode($translations, true);
			if (isset($translations['data'])) {
				self::save_cache('translations', $translations['data']);
			}
		}
		$images = self::get_api('visuals/images', $apiKey, $build);
		if ($images) {
			$images = json_decode($images, true);
			if (isset($images['data'])) {
				$arr = array();
				foreach ($images['data'] as $img) {
					if (isset($img['key'])) {
						$arr[$img['key']] = $img;
					}
				}
				self::save_cache('images', $arr);
			}
		}
		$colors = self::get_api('visuals/colors', $apiKey, $build);
		if ($colors) {
			$colors = json_decode($colors, true);
			if (isset($colors['data'])) {
				self::save_cache('colors', $colors['data']);
			}
		}
	}
	
	public static function report_missing_translations() {
		global $wpdb;
		
		$table_name = $wpdb->prefix.'liveui_missing_translations';
		
		// Get translations
		$translationData = $wpdb->get_results("SELECT * FROM {$table_name} WHERE `reported` = 0");
		if (count($translationData) == 0) {
			return;
		}
		$translations = array();
		foreach ($translationData as $t) {
			if (!isset($translations[$t->table])) {
				$translations[$t->table] = array();
			}
			$translations[$t->table][] = $t->key;
		}
		
		// Report missing translations back to the LiveUI
		$apiKey = get_option('liveui_translation_api_key');
		if (!$apiKey) {
			return false;
		}
		
		$res = self::get_api('translations/debug', $apiKey, null, $translations);
		
		// Truncate the table
		$wpdb->query("UPDATE {$table_name} SET `reported` = 1 WHERE 1;");
	}
	
	public static function translation_for_key($key, $locale) {
		if (get_option('liveui_debugging_text_with_underscores')) {
			$out = '';
			$x = 0;
			for ($i = 0; $i < strlen($key); $i++) {
				if ($x == 7) {
					$x = 0;
					$out .= ' ';
				}
				else {
					$out .= '_';
				}
				
				$x++;
			}
			return $out;
		}
		self::init_translations();
		if (isset(self::$translations[$locale]['translations'][$key])) {
			return self::$translations[$locale]['translations'][$key];
		}
		else {
			self::add_missing_translation($key, $locale);
		}
		return $key;
	}
	
	private static function get_image_url($path, $img) {
		$exists = file_exists(ABSPATH.$path);
		if (!$exists) {
			$data = self::get_image('image/get/'.$img['file'], get_option('liveui_translation_api_key'));
			if (!empty($data)) {
				if (!file_put_contents(ABSPATH.$path, $data)) {
					return false;
				}
			}
			else {
				return false;
			}
		}
		return get_site_url().'/'.$path;
	}
	
	public static function image_url_for_key($key, $locale) {
		self::init_images();
		
		if (isset(self::$images[$key])) {
			$extension = '';
			
			$path = false;
			$hash = false;
			$exists = false;
			
			$img = self::$images[$key];
			if ($img['single']) {
				if (isset($img['image']['hash'])) {
					$hash = $img['image']['hash'];
					$path = get_option('liveui_image_temp_folder').$hash.$extension;
					$url = self::get_image_url($path, $img['image']);
					if ($url) {
						return $url;
					}
				}
			}
			else {
				if (isset($img['images'][$locale]['hash'])) {
					$hash = $img['images'][$locale]['hash'];
					$path = get_option('liveui_image_temp_folder').$hash.$extension;
					$url = self::get_image_url($path, $img['images'][$locale]);
					if ($url) {
						return $url;
					}
				}
			}
		}
		return '#?error=missing_liveui_image&key='.htmlspecialchars($key);
	}
	
	public static function color_for_key($key) {
		self::init_colors();
		if (isset(self::$colors[$key])) {
			return self::$colors[$key];
		}
		else return false;
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
	
	private static function add_missing_translation($key, $locale) {
		if (get_option('liveui_debugging')) {
			global $wpdb;
			
			$table_name = $wpdb->prefix.'liveui_missing_translations';
			
			$missingTranslationsCount = $wpdb->get_var("SELECT COUNT(`id`) FROM {$table_name} WHERE `key` = '{$key}' AND `lang_code` = '{$locale}';");
			if ($missingTranslationsCount == 0) {
				$res = $wpdb->insert($table_name, array(
				   "key" => $key,
				   "table" => "general",
				   "added" => current_time('mysql', 1),
				   "lang_code" => $locale
				));
			}
		}
	}
	
	private static function init_translations($loopCheck=false) {
		if (!self::$translations) {
			self::$translations = self::get_cache('translations');
			if (!self::$translations && !$loopCheck) {
				self::update_data();
				self::init_translations(true);
			}
		}
	}
	
	private static function init_images($loopCheck=false) {
		if (!self::$images) {
			self::$images = self::get_cache('images');
			if (!self::$images && !$loopCheck) {
				self::update_data();
				self::init_images(true);
			}
		}
	}
	
	private static function init_colors($loopCheck=false) {
		if (!self::$colors) {
			self::$colors = self::get_cache('colors');
			if (!self::$colors && !$loopCheck) {
				self::update_data();
				self::init_colors(true);
			}
		}
	}
	
	private function save_cache($file, $data) {
		self::$$file = null;
		$minutes = (int)get_option('liveui_data_cache_expiry_time');
		if ($minutes < 5) {
			$minutes = 5;
		}
		return set_transient('liveui_data_cache_'.$file, $data, (60 * $minutes));
	}
	
	private static function get_cache($file) {
		$data = get_transient('liveui_data_cache_'.$file);
		if ($data === false) {
			return null;
		}
		return $data;
	}
	
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