<?php
/**
 * 2016-07-15 Lion: 推播機制需要重置時, 就執行這支
 */
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

set_time_limit(0);
		
$m_topic = (new topicModel)->column(['aws_sns_topicarn'])->fetchAll();
foreach ($m_topic as $v0) {
	Extension('aws\sns')->deleteTopic($v0['aws_sns_topicarn']);
}
(new topicModel)->truncate();

$m_album = (new albumModel)->column(['album_id'])->fetchAll();
foreach ($m_album as $v0) {
	(new topicModel)->build('albumcooperation', $v0['album_id']);
	(new topicModel)->build('albumqueue', $v0['album_id']);
}

$m_user = (new userModel)->column(['user_id'])->fetchAll();
foreach ($m_user as $v0) {
	(new topicModel)->build('user', $v0['user_id']);
	(new topicModel)->build('follow', $v0['user_id']);
}

$m_user = (new userModel)
	->column([
			'device.device_id',
			'device.os',
			'device.browser',
	])
	->join([['inner join', 'device', 'USING(user_id)']])
	->where([[[['user.act', '=', 'open'], ['device.enabled', '=', true]], 'and']])
	->fetchAll();

foreach ($m_user as $v0) {
	if (in_array($v0['os'], ['android', 'ios'])) {
		 (new cronjobModel)->add([
		 		'method'=>'subscriptionModel::buildByDevice',
		 		'param'=>json_encode([
		 				'device_id'=>$v0['device_id'],
		 				'protocol'=>'application',
		 		]),
		 		'state'=>'pretreat',
		 ]);
	}
	
	if (in_array($v0['browser'], ['chrome', 'firefox', 'safari'])) {
		(new cronjobModel)->add([
				'method'=>'subscriptionModel::buildByDevice',
				'param'=>json_encode([
						'device_id'=>$v0['device_id'],
						'protocol'=>'webpush',
				]),
				'state'=>'pretreat',
		]);
	}
}