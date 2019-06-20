<?php
class userpageviewModel extends Model {
	protected $database = 'analysis';
	protected $table = 'userpageview';
	protected $memcache = 'analysis';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
		];
	
		return $return;
	}
	
	function crontabForAnalysis($starttime, $endtime) {
		set_time_limit(0);
		
		Model('userlog');
		
		$o_starttime = new \DateTime($starttime);
		$o_endtime = new \DateTime($endtime);
		$o_period = new \DatePeriod($o_starttime, new \DateInterval('P1D'), $o_endtime->modify('+1 day'));
		$replcae = [];
		foreach ($o_period as $time) {
			$tables = parent::$database_instance['userlog']->fetchColumn("SHOW TABLES FROM ".DB_PREFIX."userlog LIKE ".Model('userlog')->quote($time->format('Ymd')));
			if ($tables) {
				$m_userlog = parent::$database_instance['userlog']->fetchAll("SELECT DATE_FORMAT(inserttime, '%Y-%m-%d %H:00:00') `datetime`, COUNT(DISTINCT session_id) `user`, COUNT(1) `count` FROM `".$tables."` GROUP BY `datetime`");
				foreach ($m_userlog as $v0) {
					$replcae[] = $v0;
				}
			}
		}
		
		if ($replcae) Model('userpageview')->replace($replcae);
	}
	
	function getChartDataOfPageview($datetime, $period='day') {
		switch ($period) {
			case 'hour':
				$format0 = 'Y-m-d H:00:00';
				$date_format = '`date`';
				$dateinterval = 'PT1H';
				$modify = '+1 hour';					
				break;
	
			case 'day':
				$format0 = 'Y-m-d';
				$format1 = 'DATE(`datetime`) `datetime`';
				$format2 = 'DATE(`datetime`)';
				$dateinterval = 'P1D';
				$modify = '+1 day';
				break;
	
			case 'week':
				$format = '';
				$date_format = 'WEEK(`date`) `date`';
				break;
	
			case 'month':
				$format = '';
				$date_format = 'MONTH(`date`) `date`';
				break;
		}
	
		list($starttime, $endtime) = $datetime;
	
		//period
		$o_starttime = new \DateTime($starttime);
		$o_endtime = new \DateTime($endtime);
		$o_period = new \DatePeriod($o_starttime, new \DateInterval($dateinterval), $o_endtime->modify($modify));
		$a_time = [];
		foreach ($o_period as $time) {
			$a_time[(string)($time->getTimestamp() * 1000)] = $time->format('Y-m-d');
		}
		
		//series
		$a_series = [];
		$m_userpageview = Model('userpageview')->column([$format1, 'SUM(`user`) `user`', 'SUM(`count`) `count`'])->where([[[[$format2, 'between', [$starttime, $endtime]]], 'and']])->group([$format2])->fetchAll();
		$array0 = [];
		$array1 = [];
		foreach ($m_userpageview as $v0) {
			//by date
			$array0[$v0['datetime']] = (int)$v0['count'];
			
			//by user + date
			$array1[$v0['datetime']] = (int)$v0['user'];
		}
		
		//by date
		$array2 = ['name'=>'Grand Total', 'type'=>'line'];
		foreach ($a_time as $timestamp => $time) {
			$array2['data'][] = [(double)$timestamp, empty($array0[$time])? 0 : $array0[$time]];
		}
		$a_series[] = $array2;
		
		//by user + date
		$array3 = ['name'=>'User', 'type'=>'column'];
		foreach ($a_time as $timestamp => $time) {
			$array3['data'][] = [(double)$timestamp, empty($array1[$time])? 0 : $array1[$time]];
		}
		$a_series[] = $array3;
		
		return json_encode($a_series);
	}
	
	function getPageviewAverage() {
		$sum = Model('userpageview')->column(['SUM(`count`)'])->fetchColumn();
		
		$divisor = count(Model('userpageview')->column(['COUNT(1)'])->group(['DATE(`datetime`)'])->fetchAll());
		
		return round($sum / $divisor);
	}
	
	function getPageviewHighest() {
		$m_userpageview = Model('userpageview')->column(['DATE(`datetime`) as_datetime', 'SUM(`count`) as_count'])->group(['as_datetime'])->order(['as_count'=>'desc'])->limit('0,1')->fetch();
		
		return [$m_userpageview['as_datetime'], $m_userpageview['as_count']];
	}
	
	function getUserAverage() {
		$sum = Model('userpageview')->column(['SUM(`user`)'])->fetchColumn();
		
		$divisor = count(Model('userpageview')->column(['COUNT(1)'])->group(['DATE(`datetime`)'])->fetchAll());
		
		return round($sum / $divisor);
	}
	
	function getUserHighest() {
		$m_userpageview = Model('userpageview')->column(['DATE(`datetime`) as_datetime', 'SUM(`user`) as_user'])->group(['as_datetime'])->order(['as_user'=>'desc'])->limit('0,1')->fetch();
	
		return [$m_userpageview['as_datetime'], $m_userpageview['as_user']];
	}
}