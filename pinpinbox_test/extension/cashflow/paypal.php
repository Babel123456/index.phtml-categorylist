<?php
class paypal {
	/**
	 * api param(必填)
	 *     callback: 處理地址
	 *     business: ID 或 Email
	 *     cmd: 按鈕類型
	 *     rm: 表單回傳方式
	 *         0：使用GET傳遞所有參數
	 *         1：使用GET但不傳遞參數
	 *         2：使用POST傳遞所有參數
	 */
	private static $callback;
	private static $business;
	private static $cmd;
	private static $rm;
	
	function __construct() {
		$m_cashflow = Model('cashflow')->where(array(array(array(array('cashflow_id', '=', __CLASS__)), 'and')))->fetch();
		
		if (empty($m_cashflow)) throw new Exception("[".__METHOD__."] Setting error");
		
		$a_customize = json_decode($m_cashflow['customize'], true);
		
		foreach (array('callback', 'business', 'cmd', 'rm') as $v0) {
			if (empty($tmp0 = array_multiple_search($a_customize, 'key', $v0))) {
				throw new Exception("[".__METHOD__."] Setting error");
			}
			self::$$v0 = $tmp0[0]['value'];
		}
	}
	
	function index(array $param) {
		$order_id = !empty($param['order_id'])? $param['order_id'] : null;
		$total = !empty($param['total'])? $param['total'] : null;
		if ($order_id == null || $total == null) {
			throw new Exception("[".__METHOD__."] Parameters error");
		}
		
		//更新 order.request
		$tmp1 = array(
				'invoice'=>$order_id,
				'amount'=>$total,
				'business'=>self::$business,
				'cmd'=>self::$cmd,
				'notify_url'=>$notify_url = frontstageController::url('cashflow', 'feedback', array('cashflow_id'=>__CLASS__)),
				'return'=>$return = frontstageController::url('cashflow', 'receive', array('cashflow_id'=>__CLASS__)),
				'rm'=>self::$rm,
		);
		if (!empty($param['buy'])) {
			$tmp1['currency_code'] = $param['buy']['currency'];
			$tmp1['item_name'] = $param['buy']['name'];
		}
		if (!empty($param['user'])) {
			$tmp1['email'] = $param['user']['email'];
		}
		ksort($tmp1);
		$edit = array(
				'callback'=>self::$callback,
				'request'=>json_encode($tmp1),
		);
		Model('order')->where(array(array(array(array('order_id', '=', $order_id)), 'and')))->edit($edit);
		
		//form
		$form  = '<form action="'.self::$callback.'" method="post">';
		$form .= '<input type="hidden" name="invoice" value="'.$order_id.'">';
		$form .= '<input type="hidden" name="amount" value="'.$total.'">';
		$form .= '<input type="hidden" name="business" value="'.self::$business.'">';
		$form .= '<input type="hidden" name="cmd" value="'.self::$cmd.'">';
		$form .= '<input type="hidden" name="notify_url" value="'.$notify_url.'">';
		$form .= '<input type="hidden" name="return" value="'.$return.'">';
		$form .= '<input type="hidden" name="rm" value="'.self::$rm.'">';
		if (!empty($param['buy'])) {
			$form .= '<input type="hidden" name="currency_code" value="'.$param['buy']['currency'].'">';
			$form .= '<input type="hidden" name="item_name" value="'.$param['buy']['name'].'">';
		}
		if (!empty($param['user'])) {
			$form .= '<input type="hidden" name="email" value="'.$param['user']['email'].'">';
		}
		$form .= '</form>';
		
		return array_encode_return(1, null, null, $form);
	}
	
	function feedback() {
		$result = 0;
		$data = array('order_id'=>$_POST['invoice'], 'return'=>$_POST);
		$tmp0 = file_get_contents(self::$callback.'?cmd=_notify-validate&'.http_build_query($_POST));
		if (strpos($tmp0, 'VERIFIED') !== false) {
			if ($_POST['receiver_email'] == self::$business) {
				switch ($_POST['payment_status']) {
					case 'Completed':
						$result = 1;
						break;
						
					case 'Pending':
						$result = 2;
						break;
						
					case 'Refunded':
						$result = 3;
						break;
						
					default:
						$result = 0;
						break;
				}
			} else {
				$result = 0;
			}
		} elseif (strpos($tmp0, 'INVALID') !== false) {
			$result = 0;
		}
		
		return array_encode_return($result, null, null, $data);
	}
	
	function receive() {
		return $this->feedback();
	}
}