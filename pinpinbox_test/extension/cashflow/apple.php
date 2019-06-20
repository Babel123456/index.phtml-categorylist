<?php
class apple {
	function __construct() {}
	
	function feedback() {
		$result = 0;
		$message = null;
		$data = null;
		
		$order_id = isset($_POST['order_id'])? $_POST['order_id'] : null;
		$password = isset($_POST['password'])? $_POST['password'] : null;
		$receipt_data = isset($_POST['receipt-data'])? $_POST['receipt-data'] : null;
		
		if ($order_id === null || $receipt_data === null) {
			$message = 'Param error';
			goto _return;
		}
		
		$is_loop = false;
		
		_again:
		
		$url = (SITE_EVN == 'production' && $is_loop == false)? 'https://buy.itunes.apple.com/verifyReceipt' : 'https://sandbox.itunes.apple.com/verifyReceipt';
		$param = ['receipt-data'=>$receipt_data];
		if ($password !== null) $param['password'] = $password;
		$a_return = json_decode(curl($url, json_encode($param)), true);
		
		$data = ['order_id'=>$order_id, 'verify'=>$url, 'verify_request'=>$param, 'verify_return'=>$a_return];
		
		if (!isset($a_return['status'])) {
			$message = 'Handshake error';
			goto _return;
		}
		
		if ($a_return['status'] == 0) {
			$result = 1;
			goto _return;
		} else {
			switch ($a_return['status']) {
				case 21000:
					$message = $a_return['status'].' - The App Store could not read the JSON object you provided';
					break;
						
				case 21002:
					$message = $a_return['status'].' - The data in the receipt-data property was malformed or missing';
					break;
						
				case 21003:
					$message = $a_return['status'].' - The receipt could not be authenticated';
					break;
						
				case 21004:
					$message = $a_return['status'].' - The shared secret you provided does not match the shared secret on file for your account';
					break;
						
				case 21005:
					$message = $a_return['status'].' - The receipt server is not currently available';
					break;
						
				case 21006:
					$message = $a_return['status'].' - This receipt is valid but the subscription has expired. When this status code is returned to your server, the receipt data is also decoded and returned as part of the response';
					break;
						
				case 21007:
					$message = $a_return['status'].' - This receipt is from the test environment, but it was sent to the production environment for verification. Send it to the test environment instead';
					
					//2016-03-18 Lion: 給 apple 送審所進行的操作會取得測試環境的資訊，為了完成流程因此處理
					if ($is_loop) {
						$message = 'Infinite loop';
						goto _return;
					} else {
						$is_loop = true;
						goto _again;
					}
					break;
						
				case 21008:
					$message = $a_return['status'].' - This receipt is from the production environment, but it was sent to the test environment for verification. Send it to the production environment instead';
					break;
						
				default:
					$message = 'Unknown case';
					break;
			}
			goto _return;
		}
		
		_return: return array_encode_return($result, $message, null, $data);
	}
}