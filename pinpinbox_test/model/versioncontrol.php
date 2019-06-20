<?php
class versioncontrolModel extends Model {
	protected $database = 'site';
	protected $table = 'versioncontrol';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		return [
				['class'=>__CLASS__],
		];
	}
	
	static function ableToUpdateVersion($platform, $version) {
		$result = 2;//不需更新
	
		switch ($platform) {
			case 'apple':
				$on_board_version = \Core\AppStore::getVersion(Model('settings')->getByKeyword('IOS_APP_ID'));
				break;
	
			case 'google':
				$result = 3;//2018-03-23 Lion: 臨時修改
				
				goto _return;
			
				$on_board_version = \Core\GooglePlay::getVersion(Model('settings')->getByKeyword('ANDROID_APP_URL'));
				break;
					
			default:
				throw new Exception('Unknown case');
				break;
		}
		
		if ($on_board_version) {
			$m_versioncontrol = versioncontrolModel::newly()
				->column(['`version`', '`type`', 'target'])
				->where([[[['platform', '=', $platform], ['`version`', '>', $version], ['`version`', '<=', $on_board_version]], 'and']])
				->order(['`version`'=>'desc'])
				->fetchAll();
			
			foreach ($m_versioncontrol as $v0) {
				switch ($v0['type']) {
					case 'all':
						$result = 1;
						break 2;
						break;
	
					case 'part':
						if ($v0['target'] && in_array($version, json_decode($v0['target'], true))) {
							$result = 1;
							break 2;
						}
						break;
				}
			}
		}
		
		_return: return array_encode_return($result);
	}
}