<?php
class highwayController extends frontstageController {
	function index() {
		$url = parent::url(); $message = null;
		
		$type = isset($_GET['type']) ? $_GET['type'] : null;
		$type_id = isset($_GET['type_id']) ? $_GET['type_id'] : null;
		$is_cooperation = isset($_GET['is_cooperation']) ? $_GET['is_cooperation'] : null;
		$is_follow = isset($_GET['is_follow']) ? $_GET['is_follow'] : null;
		$sign = isset($_GET['sign']) ? $_GET['sign'] : null;
			
		if ($type === null || $type_id === null || $is_cooperation === null || $is_follow === null || $sign === null) {
			$message = _('Param error.'); goto _return;
		}
			
		if (encrypt(['type'=>$type, 'type_id'=>$type_id, 'is_cooperation'=>$is_cooperation, 'is_follow'=>$is_follow]) !== $sign) {
			$message = _('Sign error.'); goto _return;
		}
		
		$m_user = (new userModel())->getSession();
		
		if ($m_user) {
			switch ($type) {
				case 'album':
					
					if ($is_cooperation) {
						(new cooperationModel())->insertCooperation($type, $type_id, $m_user['user_id']);
						
						$url = parent::url('user', 'albumcontent', ['album_id'=>$type_id]);
					}
					
					if ($is_follow) {
						$m_album = (new albumModel())->column(['user_id'])->where([[[['album_id', '=', $type_id]], 'and']])->fetch();
						
						$param_followfrom = ['user_id'=>$m_album['user_id']];
						$param_followto = ['user_id'=>$m_user['user_id']];
						
						list ($result_0, $message_0) = array_decode_return( (new followModel())->ableToBuild($param_followfrom, $param_followto) );
						
						if ($result_0 != 1) {$message = $message_0; goto _return;}
						
						(new Model())->beginTransaction();
						
						(new followModel())->build($param_followfrom, $param_followto);
						
						(new Model())->commit();
					}
					
					break;
			}
		} else {
			$url = parent::url('user', 'login', ['redirect'=>parent::url('highway', 'index', query_string_parse())]);
		}
		
		_return: redirect($url, $message);
	}
}