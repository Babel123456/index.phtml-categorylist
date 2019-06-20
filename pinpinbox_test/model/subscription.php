<?php
class subscriptionModel extends Model {
	protected $database = 'site';
	protected $table = 'subscription';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	static function build($user_id, $type, $type_id, array $protocol=['application', 'webpush']) {
		if ($protocol) {
			$m_topic = (new topicModel)->column(['aws_sns_topicarn'])->where([[[['`type`', '=', $type], ['type_id', '=', $type_id]], 'and']])->fetch();
			
			if ($m_topic) {
				$array0 = [];
		
				foreach ($protocol as $v0) {
					switch ($v0) {
						case 'application':
							$m_device = (new deviceModel)
								->column(['aws_sns_endpointarn'])
								->where([[[['user_id', '=', $user_id], ['aws_sns_endpointarn', '!=', ''], ['enabled', '=', true]], 'and']])
								->fetchAll();

							foreach ($m_device as $v1) {
								$e_aws = Extension('aws\sns')->subscribe($m_topic['aws_sns_topicarn'], $v0, $v1['aws_sns_endpointarn']);
								
								$array0[] = [
										'protocol'=>$v0,
										'aws_sns_endpointarn'=>$v1['aws_sns_endpointarn'],
										'aws_sns_subscriptionarn'=>$e_aws['SubscriptionArn'],
								];
							}
							break;
							
						case 'webpush':
							$array0[] = [
									'protocol'=>$v0,
							];
							break;
							
						default:
							throw new \Exception('Unknown case');
							break;
					}
				}
				
				if ($array0) {
					$replace = [];
					
					foreach ($array0 as $v0) {
						$replace[] = [
								'user_id'=>$user_id,
								'`type`'=>$type,
								'type_id'=>$type_id,
								'protocol'=>$v0['protocol'],
								'aws_sns_endpointarn'=>isset($v0['aws_sns_endpointarn'])? $v0['aws_sns_endpointarn'] : '',
								'aws_sns_subscriptionarn'=>isset($v0['aws_sns_subscriptionarn'])? $v0['aws_sns_subscriptionarn'] : '',
						];
					}
					
					(new subscriptionModel)->replace($replace);//2016-11-03 Lion: 寫入的欄位數需一致
				}
			}
		}
		
		return true;
	}
	
	/**
	 * 2016-11-30 Lion: 此函式由 crontab 運行
	 * @param array $param
	 * @return array
	 */
	static function buildByDevice(array $param) {
		$result = 1;
		$message = null;
	
		/**
		 * 2016-12-01 Lion:
		 * @param int device_id
		 * @param string protocol
		 */
		$device_id = isset($param['device_id'])? $param['device_id'] : null;
		$protocol = isset($param['protocol'])? $param['protocol'] : null;
	
		if ($device_id === null) {
			$result = 0;
			$message = 'Param error of "device_id"';
			goto _return;
		}
	
		set_time_limit(0);
		
		$m_device = (new deviceModel)
		->column(['user_id', 'identifier', 'os', 'token'])
		->where([[[['device_id', '=', $device_id]], 'and']])
		->fetch();
		
		if (empty($m_device)) {
			$result = 0;
			$message = 'Data of "device" does not exist';
			goto _return;
		}
		
		//albumcooperation
		$a_albumcooperation = (new albumModel)
		->column(['album.album_id'])
		->join([['inner join', 'cooperation', 'on cooperation.user_id = '.(new albumModel)->quote($m_device['user_id']).' and cooperation.type = \'album\' and cooperation.type_id = album.album_id']])
		->where([[[['album.act', '!=', 'delete']], 'and']])
		->fetchAll();
	
		//albumqueue
		$a_albumqueue = (new albumModel)
		->column(['album.album_id'])
		->join([['inner join', 'albumqueue', 'on albumqueue.user_id = '.(new albumModel)->quote($m_device['user_id']).' and albumqueue.album_id = album.album_id']])
		->where([[[['album.act', '!=', 'delete']], 'and']])
		->fetchAll();
	
		//user
		$a_user = (new userModel)->column(['user_id'])->where([[[['user_id', '=', $m_device['user_id']]], 'and']])->fetch();
	
		//follow
		$a_follow = (new followtoModel)->column(['`to`'])->where([[[['user_id', '=', $m_device['user_id']]], 'and']])->fetchAll();
	
		$replace = [];
	
		switch ($protocol) {
			case 'application':
				if (empty($m_device['identifier'])) {$result = 0; $message = 'Param error of "identifier"'; goto _return;}
				if (empty($m_device['os'])) {$result = 0; $message = 'Param error of "os"'; goto _return;}
				if (empty($m_device['token'])) {$result = 0; $message = 'Param error of "token"'; goto _return;}
				
				$e_aws = Extension('aws\sns')->createPlatformEndpoint($m_device['identifier'], $m_device['os'], $m_device['token']);
				
				if (empty($e_aws['EndpointArn'])) {
					$result = 0;
					$message = 'Param error or "aws_sns_endpointarn"';
					goto _return;
				}
				
				$aws_sns_endpointarn = $e_aws['EndpointArn'];
				
				(new deviceModel)
				->where([[[['device_id', '=', $device_id]], 'and']])
				->edit([
						'aws_sns_endpointarn'=>$aws_sns_endpointarn,
						'enabled'=>true,
						'modifyadmin_id'=>0,//2016-11-30 Lion: 由於是系統變更, 因此改為 0
				]);
	
				//albumcooperation
				if ($a_albumcooperation) {
					$m_topic = (new topicModel)
					->column(['type_id', 'aws_sns_topicarn'])
					->where([[[['`type`', '=', 'albumcooperation'], ['type_id', 'in', array_column($a_albumcooperation, 'album_id')], ['aws_sns_topicarn', '!=', '']], 'and']])
					->fetchAll();
						
					foreach ($m_topic as $v0) {
						$e_aws = Extension('aws\sns')->subscribe($v0['aws_sns_topicarn'], $protocol, $aws_sns_endpointarn);
	
						$replace[] = [
								'user_id'=>$m_device['user_id'],
								'`type`'=>'albumcooperation',
								'type_id'=>$v0['type_id'],
								'protocol'=>$protocol,
								'aws_sns_endpointarn'=>$aws_sns_endpointarn,
								'aws_sns_subscriptionarn'=>$e_aws['SubscriptionArn'],
						];
					}
				}
	
				//albumqueue
				if ($a_albumqueue) {
					$m_topic = (new topicModel)
					->column(['type_id', 'aws_sns_topicarn'])
					->where([[[['`type`', '=', 'albumqueue'], ['type_id', 'in', array_column($a_albumqueue, 'album_id')], ['aws_sns_topicarn', '!=', '']], 'and']])
					->fetchAll();
						
					foreach ($m_topic as $v0) {
						$e_aws = Extension('aws\sns')->subscribe($v0['aws_sns_topicarn'], $protocol, $aws_sns_endpointarn);
	
						$replace[] = [
								'user_id'=>$m_device['user_id'],
								'`type`'=>'albumqueue',
								'type_id'=>$v0['type_id'],
								'protocol'=>$protocol,
								'aws_sns_endpointarn'=>$aws_sns_endpointarn,
								'aws_sns_subscriptionarn'=>$e_aws['SubscriptionArn'],
						];
					}
				}
	
				//user
				if ($a_user) {
					$m_topic = (new topicModel)
					->column(['type_id', 'aws_sns_topicarn'])
					->where([[[['`type`', '=', 'user'], ['type_id', '=', $a_user['user_id']], ['aws_sns_topicarn', '!=', '']], 'and']])
					->fetch();
						
					$e_aws = Extension('aws\sns')->subscribe($m_topic['aws_sns_topicarn'], $protocol, $aws_sns_endpointarn);
						
					$replace[] = [
							'user_id'=>$m_device['user_id'],
							'`type`'=>'user',
							'type_id'=>$m_topic['type_id'],
							'protocol'=>$protocol,
							'aws_sns_endpointarn'=>$aws_sns_endpointarn,
							'aws_sns_subscriptionarn'=>$e_aws['SubscriptionArn'],
					];
				}
	
				//follow
				if ($a_follow) {
					$m_topic = (new topicModel)
					->column(['type_id', 'aws_sns_topicarn'])
					->where([[[['`type`', '=', 'follow'], ['type_id', 'in', array_column($a_follow, 'to')], ['aws_sns_topicarn', '!=', '']], 'and']])
					->fetchAll();
	
					foreach ($m_topic as $v0) {
						$e_aws = Extension('aws\sns')->subscribe($v0['aws_sns_topicarn'], $protocol, $aws_sns_endpointarn);
	
						$replace[] = [
								'user_id'=>$m_device['user_id'],
								'`type`'=>'follow',
								'type_id'=>$v0['type_id'],
								'protocol'=>$protocol,
								'aws_sns_endpointarn'=>$aws_sns_endpointarn,
								'aws_sns_subscriptionarn'=>$e_aws['SubscriptionArn'],
						];
					}
				}
				break;
	
			case 'webpush':
				//albumcooperation
				if ($a_albumcooperation) {
					foreach ($a_albumcooperation as $v0) {
						$replace[] = [
								'user_id'=>$m_device['user_id'],
								'`type`'=>'albumcooperation',
								'type_id'=>$v0['album_id'],
								'protocol'=>$protocol,
						];
					}
				}
	
				//albumqueue
				if ($a_albumqueue) {
					foreach ($a_albumqueue as $v0) {
						$replace[] = [
								'user_id'=>$m_device['user_id'],
								'`type`'=>'albumqueue',
								'type_id'=>$v0['album_id'],
								'protocol'=>$protocol,
						];
					}
				}
	
				//user
				if ($a_user) {
					$replace[] = [
							'user_id'=>$m_device['user_id'],
							'`type`'=>'user',
							'type_id'=>$a_user['user_id'],
							'protocol'=>$protocol,
					];
				}
	
				//follow
				if ($a_follow) {
					foreach ($a_follow as $v0) {
						$replace[] = [
								'user_id'=>$m_device['user_id'],
								'`type`'=>'follow',
								'type_id'=>$v0['to'],
								'protocol'=>$protocol,
						];
					}
				}
				break;
		}
	
		if ($replace) (new subscriptionModel)->replace($replace);
	
		_return: return array_encode_return($result, $message);
	}
	
	function cachekeymap() {
		return [
				['class'=>__CLASS__],
		];
	}
	
	function destroy($user_id, $type, $type_id, array $protocol=['application', 'webpush']) {
		$m_subscription = (new subscriptionModel)
			->column(['protocol', 'aws_sns_subscriptionarn'])
			->where([[[['user_id', '=', $user_id], ['`type`', '=', $type], ['type_id', '=', $type_id], ['protocol', 'in', $protocol]], 'and']])
			->fetchAll();
		
		foreach ($m_subscription as $v0) {
			switch ($v0['protocol']) {
				case 'application':
					Extension('aws\sns')->unsubscribe($v0['aws_sns_subscriptionarn']);
					break;
			}
		}
		
		(new subscriptionModel)
			->where([[[['user_id', '=', $user_id], ['`type`', '=', $type], ['type_id', '=', $type_id], ['protocol', 'in', $protocol]], 'and']])
			->delete();
	}
	
	/**
	 * 2016-11-30 Lion: 此函式由 crontab 運行
	 * @param array $param
	 * @return array
	 */
	static function destroyByDevice(array $param) {
		$result = 1;
		$message = null;
		
		$device_id = isset($param['device_id'])? $param['device_id'] : null;
		$protocol = isset($param['protocol'])? $param['protocol'] : null;
		
		if ($device_id === null) {
			$result = 0;
			$message = 'Param error of "device_id"';
			goto _return;
		}
		
		set_time_limit(0);
		
		$m_device = (new deviceModel)
		->column(['aws_sns_endpointarn'])
		->where([[[['device_id', '=', $device_id]], 'and']])
		->fetch();
		
		if (empty($m_device)) {
			$result = 0;
			$message = 'Data of "device" does not exist';
			goto _return;
		}
		
		switch ($protocol) {
			case 'application':
				if (empty($m_device['aws_sns_endpointarn'])) {
					$result = 0;
					$message = 'Param error or "aws_sns_endpointarn"';
					goto _return;
				} 
				
				$m_subscription = (new subscriptionModel)
					->column(['aws_sns_subscriptionarn'])
					->where([[[['protocol', '=', 'application'], ['aws_sns_endpointarn', '=', $m_device['aws_sns_endpointarn']], ['aws_sns_subscriptionarn', '!=', '']], 'and']])
					->fetchAll();
				
				foreach ($m_subscription as $v0) {
					Extension('aws\sns')->unsubscribe($v0['aws_sns_subscriptionarn']);
				}
				
				(new subscriptionModel)
					->where([[[['protocol', '=', 'application'], ['aws_sns_endpointarn', '=', $m_device['aws_sns_endpointarn']]], 'and']])
					->delete();
				
				Extension('aws\sns')->deleteEndpoint($m_device['aws_sns_endpointarn']);
				break;
		}
		
		_return: return array_encode_return($result, $message);
	}
}