<?php
class rateModel extends model {
	protected $db = 'cashflow';
	public static $mc = 'cashflow';
	protected static $table = 'rate';
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
	
	function get($fetch_type='fetch') {
		$sql = 'Select';
		$sql .= (parent::$column)? ' '.implode(',', array_map('trim', parent::$column)) : ' `'.self::$table.'`.*';
		$sql .= ' from '.DB_PREFIX.$this->db.'.'.self::$table;
		if (!empty(parent::$join)) $sql .= ' '.implode(' ', parent::$join);
		if (!empty(parent::$where)) $sql .= ' where '.implode(' and ', parent::$where);
		if (!empty(parent::$group)) $sql .= ' group by '.implode(',', array_map('trim', parent::$group));
		if (!empty(parent::$order)) $sql .= ' order by '.implode(',', parent::$order);
		if (!empty(parent::$limit)) $sql .= ' limit '.parent::$limit;
		parent::init();
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
		//static param
		$param += array('inserttime'=>inserttime());
		if (M_PACKAGE == 'admin' && !empty($_SESSION['admin'])) $param['modifyadmin_id'] = $_SESSION['admin']['admin_id'];
		
		parent::$db_instance[$this->db]->exec(parent::sql_add_format(__CLASS__, $param));
		
		$rate_id = (int)parent::$db_instance[$this->db]->lastInsertId();
		
		//clean mc
		parent::cache_delete(__CLASS__, $this->cachekeymap());
		
		return $rate_id;
	}
	
	function edit(array $param=null) {
		if (!empty($param)) {
			//static param
			if (M_PACKAGE == 'admin' && !empty($_SESSION['admin'])) $param['modifyadmin_id'] = $_SESSION['admin']['admin_id'];
			
			$sql = 'Update '.DB_PREFIX.$this->db.'.'.self::$table;
			$tmp1 = array();
			foreach ($param as $k1 => $v1) {
				if (is_null($v1)) $v1 = '';
				$tmp1[] = "`".$k1."`=".parent::$db_instance[$this->db]->quote($v1);
			}
			$sql .= ' set '.implode(',', $tmp1);
			if (!empty(parent::$where)) $sql .= ' where '.implode(' and ', parent::$where);
			parent::init();
			parent::$db_instance[$this->db]->exec($sql);
		}
		
		//clean mc
		parent::cache_delete(__CLASS__, $this->cachekeymap());
		
		return true;
	}
	
	function delete() {
		$sql = 'Delete from '.DB_PREFIX.$this->db.'.'.self::$table;
		if (!empty(parent::$where)) $sql .= ' where '.implode(' and ', parent::$where);
		parent::init();
		parent::$db_instance[$this->db]->exec($sql);
	
		//clean mc
		parent::cache_delete(__CLASS__, $this->cachekeymap());
	
		return true;
	}
}