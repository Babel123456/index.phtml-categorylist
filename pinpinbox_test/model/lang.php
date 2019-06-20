<?php
class langModel extends Model {
	protected $database = 'site';
	protected $table = 'lang';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
		
		echo "<script>console.log(".json_encode("\model\lang.php:start(langModel>語言資料表等設定)".date('m/d/Y h:i:s a', time())).");</script>";

	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
		];
	
		return $return;
	}
	
	function getSession() {
		return \Session::get('lang');
	}
	
	function setSession($lang) {
		return \Session::set('lang', $lang);
	}
	
	function usable($lang_id) {
		$result = 1;
		$message = null;
	
		if (trim($lang_id) === '') {
			$result = 0;
			$message = _('Language ID is empty.');
			goto _return;
		}
	
		$m_lang = Model('lang')->column(['act'])->where([[[['lang_id', '=', $lang_id]], 'and']])->fetch();
		if (empty($m_lang)) {
			$result = 0;
			$message = _('Language does not exist.');
			goto _return;
		} else {
			if ($m_lang['act'] == 'close') {
				$result = 0;
				$message = _('Language is closed.');
				goto _return;
			}
		}
		
		_return: return array_encode_return($result, $message);
	}
	
	
}