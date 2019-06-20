<?php
class logsiteModel extends Model {
	protected $db = 'site';
	public static $mc = 'site';
	protected static $table = 'logsite';
	protected $join_table = array();
	
	function __construct() {
		parent::db($this->db);
		parent::mc(self::$mc);
	}
	
	function cachekeymap() {
		$return = array();
		$return[] = array('class'=>__CLASS__);
	
		return $return;
	}
	
	function get(array $param=null, $fetch_type='fetch') {
		$sql = parent::sql_select_format(__CLASS__, $param);
		
		$a_sql[self::$table] = array('sql'=>$sql, 'type'=>$fetch_type);
		
		$cachekey = parent::cachekey_encode(__METHOD__, $a_sql);
		$data = null;
		if (parent::cache_exist(__CLASS__, $cachekey)) {
			$data = parent::cache_get(__CLASS__, null, null, $cachekey);
		} else {
			$data = parent::$db_instance[$this->db]->$fetch_type($sql);
		
			parent::cache_set(__CLASS__, $cachekey, $data);
		}
			
		return $data;
	}
	
	function add(array $param=null) {
		$param['date'] = insertdate();
		$param['method'] = M_METHOD;
		
		//Lion 2014-12-26: 由於是走 add, 參數 $param 應該走 add 的格式, 在這裡處理成 get 的格式
		$where = array();
		foreach ($param as $k1 => $v1) {
			$where[] = array('`'.$k1.'`', '=', $v1);
		}
		$sql = parent::sql_select_format(__CLASS__, sql_select_encode(array('`count`'), null, $where));
		
		$a_sql[self::$table] = array('sql'=>$sql, 'type'=>'fetchColumn');
		
		$cachekey = parent::cachekey_encode(__METHOD__, $a_sql);
		
		//取得 count
		if (parent::cache_exist(__CLASS__, $cachekey)) {
			$data = parent::cache_get(__CLASS__, null, null, $cachekey);
			$count = $data['count'];
			$TRIGGER = $data['TRIGGER'];
		} else {
			$count = parent::$db_instance[$this->db]->fetchColumn($sql);
			$TRIGGER = inserttime();
			
			//insert
			if (empty($count)) {
				parent::$db_instance[$this->db]->exec(parent::sql_add_format(__CLASS__, $param));
				$count = 0;
			}
		}
		
		//累加 count, 並設置觸發時間, 緩衝寫入間隔 LOGSITE_DELAY 秒
		parent::cache_set(__CLASS__, $cachekey, array('count'=>++$count), 0, LOGSITE_DELAY);
		
		//更新log
		if (inserttime() >= $TRIGGER) {
			parent::$db_instance[$this->db]->exec(parent::sql_edit_format(__CLASS__, array('count'=>$count), $param));
		}
		
		return true;
	}
}