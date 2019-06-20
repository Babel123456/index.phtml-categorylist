<?php
class userstructureModel extends Model {
	protected $database = 'analysis';
	protected $table = 'userstructure';
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
	
	function crontabForAnalysis() {
		$m_user = Model('user')->column(['birthday', 'TIMESTAMPDIFF(YEAR, birthday, CURDATE()) age', 'gender', 'relationship', 'COUNT(1) `count`'])->group(['age', 'gender', 'relationship'])->fetchAll();
		$replace = [];
		foreach ($m_user as $v0) {
			$replace[] = [
					'age'=>(in_array($v0['birthday'], ['0000-00-00', '1900-01-01']))? -1 : $v0['age'],
					'gender'=>$v0['gender'],
					'relationship'=>$v0['relationship'],
					'count'=>$v0['count'],
			];
		}
		if ($replace) Model('userstructure')->replace($replace);
	}
	
	function getAgeAverage() {
		return Model('userstructure')->column(['ROUND(SUM(age * `count`) / SUM(`count`))'])->where([[[['age', '>=', 0]], 'and']])->fetchColumn();
	}
	
	function getAgeHighest() {
		return Model('userstructure')->column(['MAX(age)'])->where([[[['age', '>=', 0]], 'and']])->fetchColumn();
	}
	
	function getAgeLowest() {
		return Model('userstructure')->column(['MIN(age)'])->where([[[['age', '>=', 0]], 'and']])->fetchColumn();
	}
	
	function getChartDataOfAge() {
		//period
		$a_period0 = [['unknown', 'unknown'], [0, 17], [18, 24], [25, 34], [35, 44], [45, 54], [55, 64], [65, '+']];
		$a_period1 = [];
		foreach ($a_period0 as $v0) {
			list($startage, $endage) = $v0;
			if ($startage === 'unknown') {
				$a_period1[] = 'Unknown';
			} elseif ($endage === '+') {
				$a_period1[] = $startage.' '.$endage;
			} else {
				$a_period1[] = $startage.' ~ '.$endage;
			}
		}
		
		//series
		$a_series = [];
		$m_userstructure = Model('userstructure')->column(['age', 'gender', 'relationship', '`count`'])->fetchAll();
		$array0 = [];
		$array1 = [];
		foreach ($m_userstructure as $v0) {
			if (empty($array0[$v0['gender']][$v0['age']])) $array0[$v0['gender']][$v0['age']] = 0;
			$array0[$v0['gender']][$v0['age']] += (int)$v0['count'];
			
			if (empty($array1[$v0['relationship']][$v0['age']])) $array1[$v0['relationship']][$v0['age']] = 0;
			$array1[$v0['relationship']][$v0['age']] += (int)$v0['count'];
		}
		
		//series - gender
		$a_enum = Model('userstructure')->fetchEnum('gender');
		foreach ($a_enum as $enum) {
			$array2 = [
					'name'=>ucfirst($enum),
					'stack'=>'gender'
			];
			
			$array3 = [];
			foreach ($a_period0 as $v1) {
				$sum = 0;
				list($startage, $endage) = $v1;
				if ($startage === 'unknown') {
					$sum = empty($array0[$enum][-1])? 0 : $array0[$enum][-1];
				} else {
					if (empty($array0[$enum])) {
						$sum = 0;
					} else {
						foreach ($array0[$enum] as $age => $v2) {
							if (is_numeric($startage) && $endage == '+' && $age >= $startage) {
								$sum += $v2;
							} elseif ($age >= $startage && $age <= $endage) {
								$sum += $v2;
							}
						}
					}
				}
				$array3[] = $sum;
			}
			$array2['data'] = $array3;
			
			$a_series[] = $array2;
		}
		
		//series - relationship
		$a_enum = Model('userstructure')->fetchEnum('relationship');
		foreach ($a_enum as $enum) {
			$array2 = [
					'name'=>ucfirst($enum),
					'stack'=>'relationship'
			];
				
			$array3 = [];
			foreach ($a_period0 as $v1) {
				$sum = 0;
				list($startage, $endage) = $v1;
				if ($startage === 'unknown') {
					$sum = empty($array1[$enum][-1])? 0 : $array1[$enum][-1];
				} else {
					if (empty($array1[$enum])) {
						$sum = 0;
					} else {
						foreach ($array1[$enum] as $age => $v2) {
							if (is_numeric($startage) && $endage == '+' && $age >= $startage) {
								$sum += $v2;
							} elseif ($age >= $startage && $age <= $endage) {
								$sum += $v2;
							}
						}
					}
				}
				$array3[] = $sum;
			}
			$array2['data'] = $array3;
				
			$a_series[] = $array2;
		}
		
		return [json_encode($a_period1), json_encode($a_series)];
	}
	
	function getChartDataOfGender() {
		//series
		$m_userstructure = Model('userstructure')->column(['gender', 'SUM(`count`) `count`'])->group(['gender'])->fetchAll();
		$array0 = [];
		foreach ($m_userstructure as $v0) {
			$array0[$v0['gender']] = $v0['count'];
		}
		$a_enum = Model('userstructure')->fetchEnum('gender');
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
	
	function getChartDataOfRelationship() {
		//series
		$m_userstructure = Model('userstructure')->column(['relationship', 'SUM(`count`) `count`'])->group(['relationship'])->fetchAll();
		$array0 = [];
		foreach ($m_userstructure as $v0) {
			$array0[$v0['relationship']] = (int)$v0['count'];
		}
		$a_enum = Model('userstructure')->fetchEnum('relationship');
		$a_series = [];
		$array1 = ['name'=>'Count'];
		foreach ($a_enum as $enum) {
			$array1['data'][] = [
					'name'=>ucfirst($enum),
					'y'=>empty($array0[$enum])? 0 : $array0[$enum],
			];
		}
		$a_series[] = $array1;
		
		return json_encode($a_series);
	}
}