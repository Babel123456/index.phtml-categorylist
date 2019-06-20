<?php
class searchstructureModel extends Model {
	protected $database = 'analysis';
	protected $table = 'searchstructure';
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
			$tables = parent::$database_instance['userlog']->fetchColumn("SHOW TABLES FROM ".DB_PREFIX."userlog LIKE ".parent::$database_instance['userlog']->quote($time->format('Ymd')));
			
			if ($tables) {
				//web 和 app
				$m_userlog = parent::$database_instance['userlog']->fetchAll(
						"SELECT `server`, `get`, post, DATE_FORMAT(inserttime, '%Y-%m-%d %H:00:00') inserttime FROM `".$tables."`
						WHERE (
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
					
					$array_0['searchkey'] = trim($array_0['searchkey']);
					
					if ($array_0['searchkey'] === '') continue;
					
					if (urlencode(urldecode($array_0['searchkey'])) === $array_0['searchkey']) $array_0['searchkey'] = urldecode($array_0['searchkey']);//處理 urlencode
					
					if (empty($a_count[$v0['inserttime']][$array_0['searchtype']][$array_0['searchkey']])) $a_count[$v0['inserttime']][$array_0['searchtype']][$array_0['searchkey']] = 0;
					
					++$a_count[$v0['inserttime']][$array_0['searchtype']][$array_0['searchkey']];
				}
			}
		}
		
		if ($a_count) {
			$replcae = [];
			
			foreach ($a_count as $datetime => $v0) {
				foreach ($v0 as $searchtype => $v1) {
					foreach ($v1 as $searchkey => $count) {
						$replcae[] = [
								'`datetime`'=>$datetime,
								'searchtype'=>$searchtype,
								'searchkey'=>$searchkey,
								'`count`'=>$count,
						];
					}
				}
			}
			
			(new searchstructureModel)->replace($replcae);
				
			(new searchModel)->importData();
		}
		
		_return: return array_encode_return($result, $message);
	}
	
	function getChartDataOfSearchType() {
		//series
		$m_searchstructure = Model('searchstructure')->column(['searchtype', 'SUM(`count`) `count`'])->group(['searchtype'])->fetchAll();
		$array0 = [];
		foreach ($m_searchstructure as $v0) {
			$array0[$v0['searchtype']] = (int)$v0['count'];
		}
		$a_enum = Model('searchstructure')->fetchEnum('searchtype');
		$a_series = [];
		$array1 = ['name'=>'Count'];
		foreach ($a_enum as $enum) {
			if ($enum == 'none') continue;
			
			$array1['data'][] = [
					'name'=>ucfirst($enum),
					'y'=>empty($array0[$enum])? 0 : $array0[$enum],
			];
		}
		$a_series[] = $array1;
	
		return json_encode($a_series);
	}
}