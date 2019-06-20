<?php
//^到時可能搬去 user or admin
class sign {
	static $session_key = 'sign_access';
	
	function access_set($url) {
		$_SESSION[self::$session_key][$url] = true;
		
		return true;
	}
	
	function access_pass($url) {
		$return = false;
		if (isset($_SESSION[self::$session_key][$url]) && $_SESSION[self::$session_key][$url]) {
			$return = true;
		}
		
		return $return;
	}
	
	function access_delete($url=null) {
		if (isset($url)) {
			unset($_SESSION[self::$session_key][$url]);
		} else {
			unset($_SESSION[self::$session_key]);
		}
		
		return true;
	}
}