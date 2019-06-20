<?php
class bluenew_webatm {
	function __construct() {}
	
	function index(array $param) {
		$order_id = !empty($param['order_id'])? $param['order_id'] : null;
		$total = !empty($param['total'])? $param['total'] : null;
		if ($order_id == null || $total == null) {
			throw new Exception("[".__METHOD__."] Parameters error");
		}
		
		$amount = $total;
		
		$m_cashflow = Model('cashflow')->where(array(array(array(array('cashflow_id', '=', __CLASS__)), 'and')))->fetch();
		
		if (empty($m_cashflow)) throw new Exception("[".__METHOD__."] Setting error");
		
		$a_customize = json_decode($m_cashflow['customize'], true);
		
		/**
		 * api param(必填)
		 *     merchantnumber: 商店編號
		 *     code: 密鑰
		 *     paymenttype: 付款方式
		 *     bankid: 銀行代碼，僅支援[007]
		 *     callback: 處理地址
		 */
		foreach (array('merchantnumber', 'code', 'paymenttype', 'bankid', 'callback') as $v0) {
			if (empty($tmp0 = array_multiple_search($a_customize, 'key', $v0))) {
				throw new Exception("[".__METHOD__."] Setting error");
			}
			$$v0 = $tmp0[0]['value'];
		}
		$paytitle = isset($_POST['paytitle'])? $_POST['paytitle'] : null;//^ 未來這裡應該要依循一致的參數名稱
		$paymemo = isset($_POST['paymemo'])? $_POST['paymemo'] : null;
		$payname = isset($_POST['payname'])? $_POST['payname'] : null;
		$payphone = isset($_POST['payphone'])? $_POST['payphone'] : null;
		$nexturl = frontstageController::url('cashflow', 'process', array('order_id'=>$order_id, 'sign'=>encrypt(array('order_id'=>$order_id))));
		
		/**
		 * api param(選填)
		 */
		//藍新文件規範 amount 最低金額 35 元, 最高金額 30000 元
		$amount_min = empty($tmp0 = array_multiple_search($a_customize, 'key', 'amount_min'))? 35 : $tmp0[0]['value'];
		$amount_max = empty($tmp0 = array_multiple_search($a_customize, 'key', 'amount_max'))? 30000 : $tmp0[0]['value'];
		if ($amount < $amount_min) {
			throw new Exception("[".__METHOD__."] The amount must be greater than ".number_format($amount_min));
		} elseif ($amount > $amount_max) {
			throw new Exception("[".__METHOD__."] The amount must be less than ".number_format($amount_max));
		}
		
		//order_id
		$ordernumber = $order_id;
		
		//更新 order.request
		$tmp1 = array(
				'merchantnumber'=>$merchantnumber,
				'ordernumber'=>$ordernumber,
				'amount'=>$amount,
				'paymenttype'=>$paymenttype,
				'paytitle'=>$paytitle,
				'paymemo'=>$paymemo,
				'bankid'=>$bankid,
				'payname'=>$payname,
				'payphone'=>$payphone,
				'hash'=>$hash = md5($merchantnumber.$code.$amount.$ordernumber),
				'nexturl'=>$nexturl,
		);
		ksort($tmp1);
		$edit = array(
				'callback'=>$callback,
				'request'=>json_encode($tmp1),
		);
		Model('order')->where(array(array(array(array('order_id', '=', $order_id)), 'and')))->edit($edit);
		
		//form
		$form  = '<form action="'.$callback.'" method="post">';
		$form .= '<input type="hidden" name="merchantnumber" value="'.$merchantnumber.'">';
		$form .= '<input type="hidden" name="ordernumber" value="'.$ordernumber.'">';
		$form .= '<input type="hidden" name="amount" value="'.$amount.'">';
		$form .= '<input type="hidden" name="paymenttype" value="'.$paymenttype.'">';
		$form .= '<input type="hidden" name="paytitle" value="'.$paytitle.'">';
		$form .= '<input type="hidden" name="paymemo" value="'.$paymemo.'">';
		$form .= '<input type="hidden" name="bankid" value="'.$bankid.'">';
		$form .= '<input type="hidden" name="payname" value="'.$payname.'">';
		$form .= '<input type="hidden" name="payphone" value="'.$payphone.'">';
		$form .= '<input type="hidden" name="hash" value="'.$hash.'">';
		$form .= '<input type="hidden" name="nexturl" value="'.$nexturl.'">';
		$form .= '</form>';
		
		return array_encode_return(1, null, null, $form);
	}
	
	function feedback() {
		die;
	}
	
	function receive() {
		$merchantnumber = isset($_POST['merchantnumber'])? $_POST['merchantnumber'] : null;
		$ordernumber = isset($_POST['ordernumber'])? $_POST['ordernumber'] : null;
		$amount = isset($_POST['amount'])? $_POST['amount'] : null;
		$paymenttype = isset($_POST['paymenttype'])? $_POST['paymenttype'] : null;
		$serialnumber = isset($_POST['serialnumber'])? $_POST['serialnumber'] : null;
		$writeoffnumber = isset($_POST['writeoffnumber'])? $_POST['writeoffnumber'] : null;
		$timepaid = isset($_POST['timepaid'])? $_POST['timepaid'] : null;
		$tel = isset($_POST['tel'])? $_POST['tel'] : null;
		$hash = isset($_POST['hash'])? $_POST['hash'] : null;
		if ($merchantnumber == null || $ordernumber == null || $amount == null || $paymenttype == null || $serialnumber == null || $writeoffnumber == null || $timepaid == null || $tel == null || $hash == null) {
			throw new Exception("[".__METHOD__."] Parameter error");
		}
		
		$return = array();
		
		$m_cashflow = Model('cashflow')->where(array(array(array(array('cashflow_id', '=', __CLASS__)), 'and')))->fetch();
		
		if (empty($m_cashflow)) throw new Exception("[".__METHOD__."] Setting error");
		
		$a_customize = json_decode($m_cashflow['customize'], true);
		
		if (empty($tmp0 = array_multiple_search($a_customize, 'key', 'code'))) {
			throw new Exception("[".__METHOD__."] Setting error");
		}
		
		$code = $tmp0[0]['value'];
		
		$verify = md5("merchantnumber=".$merchantnumber.
				"&ordernumber=".$ordernumber.
				"&serialnumber=".$serialnumber.
				"&writeoffnumber=".$writeoffnumber.
				"&timepaid=".$timepaid.
				"&paymenttype=".$paymenttype.
				"&amount=".$amount.
				"&tel=".$tel.
				$code);
		
		if (strtolower($hash) != strtolower($verify)) {
			$return = array_encode_return(0, _('Transaction fail.'), null, array('order_id'=>$ordernumber, 'redirect'=>false));
		} else {
			$return = array_encode_return(1, _('Transaction success.'), null, array('order_id'=>$ordernumber, 'redirect'=>false));
		}
		
		return $return;
	}
}