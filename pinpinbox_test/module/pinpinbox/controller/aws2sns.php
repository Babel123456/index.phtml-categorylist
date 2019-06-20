<?php
class aws2snsController extends frontstageController {
	function deliveryfailure() {
		$response = (new \Extension\aws\sns)->subscriptionConfirmation();
		
		if (isset($response['Type']) && $response['Type'] === 'Notification' && isset($response['Message']) && trim($response['Message']) !== '') {
			$a_message = json_decode($response['Message'], true);
			
			switch ($a_message['FailureType']) {
				case 'EndpointDisabled':
				case 'EndpointNotFound':
					if (isset($a_message['EndpointArn']) && trim($a_message['EndpointArn']) !== '') {
						deviceModel::newly()->where([[[['aws_sns_endpointarn', '=', $a_message['EndpointArn']]], 'and']])->edit([
								'enabled'=>false
						]);
					}
					break;
			}
		}
		
		die;
	}
}