<?php
class follow2monthModel extends Model {
	protected $database = 'site';
	protected $table = 'follow2month';
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
	
	/**
	 * 2017-04-14 Lion: 此函式由 crontab 運行
	 * @return array
	 */
	function importData() {
		$result = 1;
		$message = null;
		
		$this->truncate();
		
		$m_followfrom = (new followfromModel)
		->column(['user_id', 'COUNT(`from`) `count_from`'])
		->where([[[['inserttime', '>=', date('Y-m-d 00:00:00', strtotime('last month'))]], 'and']])
		->group(['user_id'])
		->fetchAll();
		
		if ($m_followfrom) $this->add($m_followfrom);
		
		_return: return array_encode_return($result, $message);
	}
}