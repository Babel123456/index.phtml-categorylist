<?php
class google {
	function __construct() {}
	
	function feedback() {
		$result = 0;
		$message = null;
		$data = null;
		
		$order_id = isset($_POST['order_id'])? $_POST['order_id'] : null;
		$itemType = isset($_POST['itemType'])? $_POST['itemType'] : null;
		$jsonPurchaseInfo = isset($_POST['jsonPurchaseInfo'])? $_POST['jsonPurchaseInfo'] : null;
		$signature = isset($_POST['signature'])? $_POST['signature'] : null;
		
		if ($order_id === null || $jsonPurchaseInfo === null) {
			$message = 'Param error';
			goto _return;
		}
		
		$a_jsonPurchaseInfo = json_decode($jsonPurchaseInfo, true);
		
		$is_loop = false;
		
		_again:
		
		$url = 'https://www.googleapis.com/androidpublisher/v2/applications/'.$a_jsonPurchaseInfo['packageName'].'/purchases/products/'.$a_jsonPurchaseInfo['productId'].'/tokens/'.$a_jsonPurchaseInfo['purchaseToken'];
		$param = ['access_token'=>Core::settings('GOOGLE_API_OAUTH_ACCESS_TOKEN', true)];
		$a_return = json_decode(curl($url, $param, 'get'), true);
		
		$data = ['order_id'=>$order_id, 'verify'=>$url, 'verify_request'=>$param, 'verify_return'=>$a_return];
		
		if (isset($a_return['error'])) {
			//參考 https://developers.google.com/doubleclick-search/v2/standard-error-responses
			switch ($a_return['error']['code']) {
				case 401://Invalid Credentials
					if ($is_loop) {
						$message = 'Infinite loop';
						goto _return;
					} else {
						$is_loop = true;
					}
						
					$param = [
							'grant_type'=>'refresh_token',
							'client_id'=>'1068418586957-f87vsaf0097m35hk3sbo71df06k9436j.apps.googleusercontent.com',//From Google Developers Console
							'client_secret'=>'6q5bH7FVbKd5K3xOIlJjWOa2',//From Google Developers Console
							'refresh_token'=>Core::settings('GOOGLE_API_OAUTH_REFRESH_TOKEN')
					];
					$a_return = json_decode(curl('https://accounts.google.com/o/oauth2/token', $param), true);
						
					Model('settings');
					Model('settings_lang');
					Model()->beginTransaction();
		
					$keyword = 'GOOGLE_API_OAUTH_ACCESS_TOKEN';
						
					//settings
					Model('settings')->where([[[['keyword', '=', $keyword]], 'and']])->edit(['modifytime'=>inserttime()]);
		
					//settings_lang
					Model('settings_lang')->where([[[['keyword', '=', $keyword]], 'and']])->edit(['`value`'=>$a_return['access_token']]);
		
					Model()->commit();
						
					goto _again;
					break;
					
				default:
					$message = $a_return['error']['code'].' - '.$a_return['error']['message'];
					goto _return;
					break;
			}
		}
		
		//參考 https://developers.google.com/android-publisher/api-ref/purchases/products
		switch ($a_return['purchaseState']) {
			case 0://purchased
				$result = 1;
				break;
				
			case 1://canceled
				$result = 0;
				break;
				
			case 2://refunded
				$result = 3;
				break;
		}
		
		_return: return array_encode_return($result, $message, null, $data);
	}
}