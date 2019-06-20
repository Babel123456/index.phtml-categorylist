<?php
class userregistrationModel extends Model {
	protected $database = 'analysis';
	protected $table = 'userregistration';
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
		$m_user = Model('user')->column(['DATE_FORMAT(inserttime, \'%Y-%m-%d %H:00:00\') `date`', 'way', 'COUNT(1) `count`'])->where([[[['DATE_FORMAT(inserttime, \'%Y-%m-%d %H:00:00\')', 'between', [$starttime, $endtime]]], 'and']])->group(['`date`', 'way'])->fetchAll();
		if ($m_user) Model('userregistration')->replace($m_user);
	}
	
	function getChartDataOfGrowth($datetime, $period='day') {
		switch ($period) {
			case 'hour':
				$format0 = 'Y-m-d H:00:00';
				$date_format = '`date`';
				$dateinterval = 'PT1H';
				$modify = '+1 hour';					
				break;
	
			case 'day':
				$format0 = 'Y-m-d';
				$format1 = 'DATE(`date`) `date`';
				$format2 = 'DATE(`date`)';
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
		$m_userregistration = Model('userregistration')->column([$format1, 'way', 'SUM(`count`) `count`'])->where([[[[$format2, 'between', [$starttime, $endtime]]], 'and']])->group([$format2, 'way'])->fetchAll();
		$array0 = [];
		$array1 = [];
		foreach ($m_userregistration as $v0) {
			//by date
			if (empty($array0[$v0['date']])) $array0[$v0['date']] = 0;
			$array0[$v0['date']] += (int)$v0['count'];
			
			//by way + date
			$array1[$v0['way']][$v0['date']] = (int)$v0['count'];
		}
		
		//by date
		$count = (int)Model('userregistration')->column(['SUM(`count`) `count`'])->where([[[[$format2, '<', $starttime]], 'and']])->fetchColumn();
		$array2 = ['name'=>'Grand Total', 'type'=>'line'];
		foreach ($a_time as $timestamp => $time) {
			$array2['data'][] = [(double)$timestamp, empty($array0[$time])? $count : $count += $array0[$time]];
		}
		$a_series[] = $array2;
		
		//by way + date
		$a_enum = Model('userregistration')->fetchEnum('way');
		foreach ($a_enum as $enum) {
			$array3 = ['name'=>ucfirst($enum), 'type'=>'column'];
			foreach ($a_time as $timestamp => $time) {
				$array3['data'][] = [(double)$timestamp, empty($array1[$enum][$time])? 0 : (int)$array1[$enum][$time]];
			}
			$a_series[] = $array3;
		}
		
		return json_encode($a_series);
	}
	
	function getChartDataOfWay() {
		//series
		$m_userregistration = Model('userregistration')->column(['way', 'SUM(`count`) `count`'])->group(['way'])->fetchAll();
		$array0 = [];
		foreach ($m_userregistration as $v0) {
			$array0[$v0['way']] = $v0['count'];
		}
		$a_enum = Model('userregistration')->fetchEnum('way');
		$a_series = [];
		$array1 = ['name'=>'Count'];
		foreach ($a_enum as $enum) {
			$array1['data'][] = [
					'name'=>ucfirst($enum),
					'y'=>(int)$array0[$enum],
			];
		}
		$a_series[] = $array1;
		
		return json_encode($a_series);
	}
	
	function getRegistrationAverage() {
		$sum = Model('userregistration')->column(['SUM(`count`)'])->fetchColumn();
		
		$divisor = count(Model('userregistration')->column(['COUNT(1)'])->group(['DATE(`date`)'])->fetchAll());
		
		return round($sum / $divisor);
	}
	
	function getRegistrationHighest() {
		$m_userregistration = Model('userregistration')->column(['DATE(`date`) as_date', 'SUM(`count`) as_count'])->group(['as_date'])->order(['as_count'=>'desc'])->limit('0,1')->fetch();
		
		return [$m_userregistration['as_date'], $m_userregistration['as_count']];
	}
}