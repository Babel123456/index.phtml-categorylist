<?php
class bluenew_creditcard {
	function __construct() {}
	
	function index(array $param) {
		$order_id = !empty($param['order_id'])? $param['order_id'] : null;
		$total = !empty($param['total'])? $param['total'] : null;
		if ($order_id == null || $total == null) {
			throw new Exception("[".__METHOD__."] Parameters error");
		}
		
		$m_cashflow = Model('cashflow')->where(array(array(array(array('cashflow_id', '=', __CLASS__)), 'and')))->fetch();
		
		if (empty($m_cashflow)) throw new Exception("[".__METHOD__."] Setting error");
		
		$a_customize = json_decode($m_cashflow['customize'], true);
		
		//小數取 4 位, 且不要有千分位的字符, 因為會被視為字串而寫入有異
		$Amount = number_format($total, 4, '.', '');
		
		/**
		 * api param(必填)
		 *     MerchantNumber: 商店編號
		 *     DepositFlag: 1 為 自動請款，0 為 手動請款
		 *     Englishmode: 中英文版本指標，0為中文版，1為英文版
		 *     iphonepage: 手機刷卡頁版本指標，0 為一般電腦版， 1 為 iPhone版
		 *     op: 交易模式，固定：AcceptPayment
		 *     Code: 密鑰
		 *     callback: 處理地址
		 */
		foreach (array('MerchantNumber', 'DepositFlag', 'Englishmode', 'iphonepage', 'op', 'Code', 'callback') as $v0) {
			if (empty($tmp0 = array_multiple_search($a_customize, 'key', $v0))) {
				throw new Exception("[".__METHOD__."] Setting error");
			}
			$$v0 = $tmp0[0]['value'];
		}
		
		/**
		 * api param(選填)
		 */
		//藍新文件規範 amount 最低金額 50 元
		$amount_min = empty($tmp0 = array_multiple_search($a_customize, 'key', 'amount_min'))? 50 : $tmp0[0]['value'];
		if ($Amount < $amount_min) {
			throw new Exception("[".__METHOD__."] The amount must be greater than ".number_format($amount_min));
		}
		
		//order_id
		$OrgOrderNumber = $OrderNumber = $order_id;
		
		//更新 order.request
		$tmp1 = array(
				'MerchantNumber'=>$MerchantNumber,
				'OrderNumber'=>$OrderNumber,
				'Amount'=>$Amount,
				'OrgOrderNumber'=>$OrgOrderNumber,
				'DepositFlag'=>$DepositFlag,
				'Englishmode'=>$Englishmode,
				'iphonepage'=>SDK('Mobile_Detect')->isMobile()? 1 : $iphonepage,
				'OrderURL'=>$OrderURL = frontstageController::url('cashflow', 'feedback', array('cashflow_id'=>__CLASS__)),
				'ReturnURL'=>$ReturnURL = frontstageController::url('cashflow', 'receive', array('cashflow_id'=>__CLASS__)),
				'op'=>$op,
				'checksum'=>$checksum = md5($MerchantNumber.$OrderNumber.$Code.$Amount),
		);
		ksort($tmp1);
		$edit = array(
				'callback'=>$callback,
				'request'=>json_encode($tmp1),
		);
		Model('order')->where(array(array(array(array('order_id', '=', $order_id)), 'and')))->edit($edit);
		
		//form
		$form  = '<form action="'.$callback.'" method="post">';
		$form .= '<input type="hidden" name="MerchantNumber" value="'.$MerchantNumber.'">';
		$form .= '<input type="hidden" name="OrderNumber" value="'.$OrderNumber.'">';
		$form .= '<input type="hidden" name="Amount" value="'.$Amount.'">';
		$form .= '<input type="hidden" name="OrgOrderNumber" value="'.$OrgOrderNumber.'">';
		$form .= '<input type="hidden" name="DepositFlag" value="'.$DepositFlag.'">';
		$form .= '<input type="hidden" name="Englishmode" value="'.$Englishmode.'">';
		$form .= '<input type="hidden" name="iphonepage" value="'.$iphonepage.'">';
		$form .= '<input type="hidden" name="OrderURL" value="'.$OrderURL.'">';
		$form .= '<input type="hidden" name="ReturnURL" value="'.$ReturnURL.'">';
		$form .= '<input type="hidden" name="op" value="'.$op.'">';
		$form .= '<input type="hidden" name="checksum" value="'.$checksum.'">';
		$form .= '</form>';
		
		return array_encode_return(1, null, null, $form);
	}
	
	function feedback() {
		die;
	}
	
	function receive() {
		$final_result = isset($_POST['final_result'])? $_POST['final_result'] : null;
		$P_MerchantNumber = isset($_POST['P_MerchantNumber'])? $_POST['P_MerchantNumber'] : null;
		$P_OrderNumber = isset($_POST['P_OrderNumber'])? $_POST['P_OrderNumber'] : null;
		$P_Amount = isset($_POST['P_Amount'])? $_POST['P_Amount'] : null;
		$P_CheckSum = isset($_POST['P_CheckSum'])? $_POST['P_CheckSum'] : null;
		$final_return_PRC = isset($_POST['final_return_PRC'])? $_POST['final_return_PRC'] : null;
		$final_return_SRC = isset($_POST['final_return_SRC'])? $_POST['final_return_SRC'] : null;
		$final_return_ApproveCode = isset($_POST['final_return_ApproveCode'])? $_POST['final_return_ApproveCode'] : null;
		$final_return_BankRC = isset($_POST['final_return_BankRC'])? $_POST['final_return_BankRC'] : null;
		$final_return_BatchNumber = isset($_POST['final_return_BatchNumber'])? $_POST['final_return_BatchNumber'] : null;
		if ($P_MerchantNumber == null || $P_OrderNumber == null || $final_result == null || $final_return_PRC == null || $final_return_SRC == null || $P_Amount == null || $P_CheckSum == null) {
			throw new Exception("[".__METHOD__."] Parameter error");
		}
		
		$final_return_BankRC = isset($_POST['final_return_BankRC'])? $_POST['final_return_BankRC'] : null;
		
		$return = array();
		
		//交易成功 step-1
		if ($final_result == 1) {
			//交易成功 step-2
			$m_cashflow = Model('cashflow')->where(array(array(array(array('cashflow_id', '=', __CLASS__)), 'and')))->fetch();
			
			if (empty($m_cashflow)) throw new Exception("[".__METHOD__."] Setting error");
			
			$a_customize = json_decode($m_cashflow['customize'], true);
			
			if (empty($tmp0 = array_multiple_search($a_customize, 'key', 'Code'))) {
				throw new Exception("[".__METHOD__."] Setting error");
			}
			
			$Code = $tmp0[0]['value'];
			
			$checkstr = md5($P_MerchantNumber.$P_OrderNumber.$final_result.$final_return_PRC.$Code.$final_return_SRC.$P_Amount);
			if (strtolower($checkstr) != strtolower($P_CheckSum)) {
				$return = array_encode_return(0, _('Transaction fail, sign error.'), null, array('order_id'=>$P_OrderNumber, 'redirect'=>true));
			} else {
				$return = array_encode_return(1, _('Transaction success.'), null, array('order_id'=>$P_OrderNumber, 'redirect'=>true));
			}
		}
		
		//交易失敗
		elseif ($final_result == 0) {
			//交易失敗，有可能是交易失敗；有可能是交易成功，但通知商家失敗而做了取消，視為交易失敗
			if ($final_return_PRC == "8" && $final_return_SRC == "204") {
				$message = _('Transaction fail, order number repeat!');
			} elseif ($final_return_PRC == "34" && $final_return_SRC == "171") {
				switch (explode('/', $final_return_BankRC)[1]) {
					case '05':
						$message = _('Do not honour.');
						break;
						
					case '51':
						$message = _('Not sufficient funds.');
						break;

					case '54':
						$message = _('Expired card.');
						break;
						
					default:
						$message = _('Transaction fail, because of the financial problem!');
						break;
				}
			} else {
				$message = _('Transaction fail, please contact us!');
			}
			$return = array_encode_return(0, $message, null, array('order_id'=>$P_OrderNumber, 'redirect'=>true));
		}
		
		return $return;
	}
}