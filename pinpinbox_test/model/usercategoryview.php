<?php
class usercategoryviewModel extends Model {
	protected $database = 'analysis';
	protected $table = 'usercategoryview';
	protected $memcache = 'analysis';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		return [
				['class'=>__CLASS__],
		];
	}
	
	/**
	 * 2017-01-11 Lion: 此函式由 crontab 運行
	 * @param array $param
	 * @return array
	 */
	function importData(array $param) {
		$result = 1;
		$message = null;
		
		$starttime = isset($param['starttime'])? $param['starttime'] : null;
		$endtime = isset($param['endtime'])? $param['endtime'] : null;
		
		if ($starttime === null) {
			$result = 0; $message = 'Param error. "starttime" is required.';
			goto _return;
		}
		
		if ($endtime === null) {
			$result = 0; $message = 'Param error. "endtime" is required.';
			goto _return;
		}
		
		set_time_limit(0);
		
		new userlogModel;
		
		$o_starttime = new \DateTime($starttime);
		$o_endtime = new \DateTime($endtime);
		$o_period = new \DatePeriod($o_starttime, new \DateInterval('P1D'), $o_endtime->modify('+1 day'));
		
		$a_count = [];
		
		foreach ($o_period as $time) {
			$tables = parent::$database_instance['userlog']->fetchColumn("SHOW TABLES FROM " . DB_PREFIX . "userlog LIKE ".parent::$database_instance['userlog']->quote($time->format('Ymd')));
			
			if ($tables) {
				//web
				$m_userlog = parent::$database_instance['userlog']->fetchAll(
						"SELECT user_id, `get` FROM `" . $tables . "` WHERE user_id != 0 AND `server` RLIKE '\"REQUEST_URI\":\".+(index\\\\\\\\/album){1}.+\"' AND (`get` RLIKE '\"categoryarea_id\":\".+\"' OR `get` RLIKE '\"category_id\":\".+\"')"
				);
				
				foreach ($m_userlog as $v0) {
					$array_0 = json_decode($v0['get'], true);
					
					if (isset($array_0['category_id'])) {
						if (!isset($a_count[$v0['user_id']][$array_0['category_id']])) $a_count[$v0['user_id']][$array_0['category_id']] = 0;
						
						++$a_count[$v0['user_id']][$array_0['category_id']];
					} elseif (isset($array_0['categoryarea_id'])) {
						$a_category_id = array_column(
								(new categoryarea_categoryModel)->column(['category_id'])
								->where([[[['categoryarea_id', '=', $array_0['categoryarea_id']]], 'and']])
								->fetchAll(),
								'category_id');
						
						foreach ($a_category_id as $v1) {
							if (!isset($a_count[$v0['user_id']][$v1])) $a_count[$v0['user_id']][$v1] = 0;
							
							++$a_count[$v0['user_id']][$v1];
						}
					}
				}
				
				//app
				$m_userlog = parent::$database_instance['userlog']->fetchAll(
						"SELECT user_id, post FROM `" . $tables . "` WHERE user_id != 0 AND `server` RLIKE '\"REQUEST_URI\":\".+(api\\\\\\\\/retrievehotrank){1}.+\"' AND post RLIKE '\"categoryid\":\".+\"'"
				);
				
				foreach ($m_userlog as $v0) {
					$array_0 = json_decode($v0['post'], true);
					
					if (isset($array_0['categoryid'])) {
						$a_category_id = array_column(
								(new categoryarea_categoryModel)->column(['category_id'])
								->where([[[['categoryarea_id', '=', $array_0['categoryid']]], 'and']])
								->fetchAll(),
								'category_id');
						
						foreach ($a_category_id as $v1) {
							if (!isset($a_count[$v0['user_id']][$v1])) $a_count[$v0['user_id']][$v1] = 0;
								
							++$a_count[$v0['user_id']][$v1];
						}
					}
				}
			}
		}
		
		if ($a_count) {
			$m_usercategoryview = (new usercategoryviewModel)->column(['user_id', 'category_id', '`count`'])->where([[[['user_id', 'in', array_keys($a_count)]], 'and']])->fetchAll();
				
			foreach ($m_usercategoryview as $v0) {
				if (isset($a_count[$v0['user_id']][$v0['category_id']])) $a_count[$v0['user_id']][$v0['category_id']] += $v0['count'];
			}
				
			$a_usercategoryview = [];
				
			foreach ($a_count as $user_id => $a_category) {
				foreach ($a_category as $category_id => $count) {
					$a_usercategoryview[] = [
							'user_id'=>$user_id,
							'category_id'=>$category_id,
							'count'=>$count,
					];
				}
			}
				
			(new usercategoryviewModel)->replace($a_usercategoryview);
		}
		
		_return: return array_encode_return($result, $message);
	}
}