<?php
class usersearchModel extends Model {
	protected $database = 'analysis';
	protected $table = 'usersearch';
	protected $memcache = 'analysis';
	protected $join_table = [];
	
	const type = 'searchtype';
	const key = 'searchkey';
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		return [
				['class'=>__CLASS__],
		];
	}
	
	/**
	 * 2017-01-12 Lion: 此函式由 crontab 運行
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
				//web + app
				$m_userlog = parent::$database_instance['userlog']->fetchAll(
						"SELECT user_id, `server`, `get`, post FROM `" . $tables . "`
						WHERE user_id != 0
						AND (
							`get` RLIKE '\"" . self::type . "\":\"album\"' OR
							`get` RLIKE '\"" . self::type . "\":\"template\"' OR
							`get` RLIKE '\"" . self::type . "\":\"user\"' OR
							post RLIKE '\"" . self::type . "\":\"album\"' OR
							post RLIKE '\"" . self::type . "\":\"template\"' OR
							post RLIKE '\"" . self::type . "\":\"user\"'
						)
						AND (
							`get` RLIKE '\"" . self::key . "\":\".+\"' OR
							post RLIKE '\"" . self::key . "\":\".+\"'
						)"
				);
				
				foreach ($m_userlog as $v0) {
					$array_0 = (strpos($v0['server'], '\/api\/') !== false) ? json_decode($v0['post'], true) : json_decode($v0['get'], true);
					
					$array_0['searchkey'] = trim(preg_replace('/[\s　]+/', ' ', $array_0['searchkey']));//2017-03-08 Lion: 取代全形、半形空白
					
					if ($array_0['searchkey'] === '') continue;
					
					if (urlencode(urldecode($array_0['searchkey'])) === $array_0['searchkey']) $array_0['searchkey'] = urldecode($array_0['searchkey']);//處理 urlencode
						
					if (!isset($a_count[$v0['user_id']][$array_0['searchtype']][$array_0['searchkey']])) $a_count[$v0['user_id']][$array_0['searchtype']][$array_0['searchkey']] = 0;
						
					++$a_count[$v0['user_id']][$array_0['searchtype']][$array_0['searchkey']];
				}
			}
		}
		
		if ($a_count) {
			$m_usersearch = (new usersearchModel)->column(['user_id', 'searchtype', 'searchkey', '`count`'])->where([[[['user_id', 'in', array_keys($a_count)]], 'and']])->fetchAll();
		
			foreach ($m_usersearch as $v0) {
				if (isset($a_count[$v0['user_id']][$v0['searchtype']][$v0['searchkey']])) $a_count[$v0['user_id']][$v0['searchtype']][$v0['searchkey']] += $v0['count'];
			}
				
			$a_usersearch = [];
		
			foreach ($a_count as $user_id => $a_searchtype) {
				foreach ($a_searchtype as $searchtype => $a_searchkey) {
					foreach ($a_searchkey as $searchkey => $count) {
						$a_usersearch[] = [
								'user_id'=>$user_id,
								'searchtype'=>$searchtype,
								'searchkey'=>$searchkey,
								'count'=>$count,
						];
					}
				}
			}
		
			(new usersearchModel)->replace($a_usersearch);
		}
		
		_return: return array_encode_return($result, $message);
	}
}