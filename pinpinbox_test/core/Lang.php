<?php
namespace Core;
class Lang {
	public static $lang;
	public static $support;
	public static $default = 'zh_TW';
	public static $_i18n = [];
	
	static function get() {
		
		//echo '<span style=color:lavender>core/lang:$lang=</span>'.self::$lang.'xxx'.date("H:i:s:u");
		
        
		if (self::$lang === null) {
			if (!empty($_GET['lang'])) {
				self::set($_GET['lang']);
			} elseif (!empty(\Session::get('lang'))) {
				self::$lang = \Session::get('lang');
			} else {
				if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
					$http_accept_language = strtolower(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0]);
					
					switch (substr($http_accept_language, 0, 2)) {
						case 'en':
							$lang = 'en_US';
							break;
			
						case 'ja':
							$lang = 'ja_JP';
							break;
								
						case 'zh':
							foreach (['cn', 'hk', 'sg', 'tw'] as $v0) {
								if (strpos($http_accept_language, $v0) !== false) {
									$lang = 'zh_'.strtoupper($v0);
									break;
								}
							}
							break;
			
						default:
							$lang = self::$default;
							break;
					}
				} else {
					$lang = self::$default;
				}
				self::set($lang);
			}
		}
		
		return self::$lang;
	}
	
	static function i18n($keyword) {
		
		echo "<script>console.log(".json_encode("\core\lang.php:start(設語言變數)".date('m/d/Y h:i:s a', time())).");</script>";
	
		
		if ($keyword === null || trim($keyword) === '') {
			$return = null;
		} elseif (isset(self::$_i18n[$keyword])) {
			$return = self::$_i18n[$keyword];
		} else {
			$m_i18n = (new \i18nModel)->column(['lang_id', '`value`'])->where([[[['keyword', '=', $keyword], ['lang_id', '=', self::$lang]], 'and']])->fetch();
			$return = self::$_i18n[$keyword] = empty($m_i18n) ? $keyword : $m_i18n['value'];
		}
		
		return $return;
	}
	
	static function set($lang) {
				
		if (self::$support === null) {
			self::$support = [];
			
			$m_lang = \langModel::newly()->column(['lang_id'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
			
			self::$support = array_column($m_lang, 'lang_id');
		}
		
		if (!in_array($lang, self::$support)) $lang = self::$default;
		
		self::$lang = \Session::set('lang', $lang);
		
		return true;
	}
}