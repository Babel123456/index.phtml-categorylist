<?php
class adminModel extends Model {
	protected $database = 'site';
	protected $table = 'admin';
	protected $memcache = 'site';
	protected $join_table = ['admin_admingroup', 'admingroup', 'admingroup_adminmenu', 'adminmenu'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		return [
				['class'=>__CLASS__],
				['class'=>'admingroupModel'],
				['class'=>'adminmenuModel'],
		];
	}
	
	static function login($account, $password) {
		$result = 1;
		$message = null;
		$redirect = null;
		
		$account = (trim($account) === '')? null : trim($account);
		$password = (trim($password) === '')? null : trim($password);
		
		if ($account === null || $password === null) {
			$result = 0;
			$message = _('Param error.');
			goto _return;
		}
		
		$m_admin = adminModel::newly()->column(['admin_id', '`password`', 'act'])->where([[[['account', '=', $account]], 'and']])->fetch();
		
		if (empty($m_admin)) {
			$result = 0;
			$message = _('Account does not exist.');
			goto _return;
		} else {
			if (!password_verify($password, $m_admin['password'])) {
				$result = 0;
				$message = _('Password is incorrect.');
				goto _return;
			} elseif ($m_admin['act'] == 'close') {
				$result = 0;
				$message = _('Account is closed.');
				goto _return;
			}
		}
		
		list ($result1, $message1) = array_decode_return(adminModel::setSession($m_admin['admin_id']));
		if ($result1 != 1) {
			$result = $result1;
			$message = $message1;
			goto _return;
		}
		
		adminModel::newly()->where([[[['admin_id', '=', $m_admin['admin_id']]], 'and']])->edit(['lastloginip'=>remote_ip(), 'lastlogintime'=>inserttime()]);
		
		$redirect = empty(query_string_parse()['redirect'])? backstageController::url('index', 'index', null, 'admin') : query_string_parse()['redirect'];
		
		_return: return array_encode_return($result, $message, $redirect);
	}
	
	static function getOne($admin_id) {
		return adminModel::newly()->where([[[['admin_id', '=', (int)$admin_id]], 'and']])->fetch();
	}
	
	static function getSession() {
		return Session::get('admin');
	}
	
	static function setSession($admin_id) {
		$result = 1;
		$message = null;
		
		if (empty($admin_id)) $admin_id = null;
		
		if ($admin_id === null) {
			$result = 0;
			$message = _('Param error.');
			goto _return;
		}
		
		$m_admin = Model('admin')->column(['admin_id', 'name', 'lastloginadmingroup_id'])->where([[[['admin_id', '=', $admin_id]], 'and']])->fetch();
		
		$a_admin = [
				'admin_id'=>$m_admin['admin_id'],
				'name'=>$m_admin['name'],
				'lastloginadmingroup_id'=>$m_admin['lastloginadmingroup_id'],
		];
		
		Session::set('admin', $a_admin);
		
		_return: return array_encode_return($result, $message);
	}
	
	function usable($admin_id) {
		$result = 1;
		$message = null;
	
		if (empty($admin_id)) $admin_id = null;
		
		if ($admin_id === null) {
			$result = 0;
			$message = _('Param error.');
			goto _return;
		}
	
		$m_admin = Model('admin')->column(['act'])->where([[[['admin_id', '=', $admin_id]], 'and']])->fetch();
		
		if (empty($m_admin)) {
			$result = 0;
			$message = _('Account does not exist.');
			goto _return;
		} else {
			if ($m_admin['act'] == 'close') {
				$result = 0;
				$message = _('Account is closed.');
				goto _return;
			}
		}
	
		_return: return array_encode_return($result, $message);
	}
}