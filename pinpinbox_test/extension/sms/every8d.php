<?php
class every8d {
	static $sms;
	
	/**
	 * api param(必填)
	 *     UID: 帳號
	 *     PWD: 密碼
	 */
	private static $UID;
	private static $PWD;
	
	function __construct() {
		require PATH_SDK.'every8d'.DIRECTORY_SEPARATOR.'SMSHttp.php';
		
		$m_sms = Model('sms')->where(array(array(array(array('sms_id', '=', __CLASS__)), 'and')))->fetch();
		
		if (empty($m_sms)) throw new Exception("[".__METHOD__."] Setting error");
		
		$a_customize = json_decode($m_sms['customize'], true);
		
		foreach (array('UID', 'PWD') as $v0) {
			if (empty($tmp0 = array_multiple_search($a_customize, 'key', $v0))) {
				throw new Exception("[".__METHOD__."] Setting error");
			}
			self::$$v0 = $tmp0[0]['value'];
		}
		
		self::$sms = new SMSHttp();
	}
	
	function send($cellphone, $message) {
		if (self::$sms->sendSMS(self::$UID, self::$PWD, M_METHOD, $message, $cellphone, null)) {
			$r_result = true;
			$r_message = null;
			$return = array(
					'batchID'=>self::$sms->batchID,
					'credit'=>self::$sms->credit,
			);
		} else {
			$r_result = false;
			$r_message = self::$sms->processMsg;
			$return = array(
					'processMsg'=>self::$sms->processMsg
			);
		}
		$add = array(
				'sms_id'=>__CLASS__,
				'cellphone'=>$cellphone,
				'message'=>$message,
				'callback'=>self::$sms->sendSMSUrl,
				'request'=>json_encode(array(
						'UID'=>self::$UID,
						'PWD'=>self::$PWD,
						'SB'=>M_METHOD,
						'MSG'=>$message,
						'DEST'=>$cellphone,
						'ST'=>null,
				)),
				'`return`'=>json_encode($return),
		);
		Model('smslog')->add($add);
		
		return array_encode_return($r_result, $r_message);
	}
}