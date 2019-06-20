<?php
class cht {
	static $server_ip = '202.39.54.130';
	static $server_port = 8000;
	static $TimeOut = 10;
	static $sms2;
	static $ret_code;
	static $ret_msg;
	
	function __construct() {
		include PATH_SDK.'cht'.DIRECTORY_SEPARATOR.'sms2_Big5.inc';
		
		self::$sms2 = new sms2();

		self::$ret_code = self::$sms2->create_conn(self::$server_ip, self::$server_port, self::$TimeOut, Core::settings('SMS_CHT_ACCOUNT'), Core::settings('SMS_CHT_PASSWORD'));
		self::$ret_msg = self::$sms2->get_ret_msg();
	}
	
	function __destruct() {
		self::$sms2->close_conn();
	}
	
	/**
	 * $ret_query_code
	 *     0: 訊息已送達對方(包含送達時間)
	 *     1: 手機未開或在受訊範圍外(系統會Retry)
	 *     17: 訊息無法送達對方
	 * @param unknown $sms_id
	 * @return Ambigous <multitype:unknown, multitype:unknown string >
	 */
	function query($sms_id) {
		if (self::$ret_code == 0) {
			$ret_query_code = self::$sms2->query_text($sms_id);
			$ret_msg = self::$sms2->get_ret_msg();
			if ($ret_query_code == 0) {
				$result = true;
			} else {
				$result = false;
			}
		} else {
			$result = false;
			$ret_query_code = null;
			$ret_msg = self::$ret_msg;
		}
		
		log_file(array('sms', __CLASS__, __FUNCTION__), array('sms_id'=>$sms_id, 'ret_code'=>self::$ret_code, 'ret_query_code'=>$ret_query_code, 'ret_msg'=>$ret_msg));
		
		return array_encode_return($result, $ret_msg);
	}
	
	function receive() {
		if (self::$ret_code == 0) {
			$result = true;
			$ret_msg = self::$ret_msg;
			$data = self::$sms2->get_send_tel();
		} else {
			$result = false;
			$ret_msg = self::$ret_msg;
			$data = null;
		}
	
		return array_encode_return($result, $ret_msg, null, $data);
	}
	
	function send($cellphone, $message) {
		if (self::$ret_code == 0) {
			$ret_send_code = self::$sms2->send_text($cellphone, iconv('UTF-8', 'Big5', $message));
			$result = ($ret_send_code == 0)? true : false;
			$sms_id = $ret_msg = self::$sms2->get_ret_msg();
		} else {
			$ret_send_code = null;
			$result = false;
			$ret_msg = self::$ret_msg;
			$sms_id = null;
		}
		
		log_file(array('sms', __CLASS__, __FUNCTION__), array('sms_id'=>$sms_id, 'cellphone'=>$cellphone, 'message'=>$message, 'ret_code'=>self::$ret_code, 'ret_send_code'=>$ret_send_code, 'ret_msg'=>$ret_msg));
		
		return array_encode_return($result, $ret_msg);
	}
}