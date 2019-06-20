<?php
class newsareaModel extends Model {
	protected $db = 'site';
	public static $mc = 'site';
	protected static $table = 'newsarea';
	protected $join_table = array('news', 'newsarea_news');
	
	function __construct() {
		parent::db($this->db);
		parent::mc(self::$mc);
	}
	
	function cachekeymap() {
		$return = array();
		$return[] = array('class'=>__CLASS__);
		$return[] = array('class'=>'newsModel');
		$return[] = array('class'=>'newsarea_newsModel');
	
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
		//static param
		$param += array('inserttime'=>inserttime());
		if (M_PACKAGE == 'admin' && !empty(Session::get('admin'))) $param['modifyadmin_id'] = Session::get('admin')['admin_id'];
		
		parent::$db_instance[$this->db]->exec(parent::sql_add_format(__CLASS__, $param));
		
		$newsarea_id = (int)parent::$db_instance[$this->db]->lastInsertId();
		
		//clean mc
		parent::cache_delete(__CLASS__, $this->cachekeymap());
		
		return $newsarea_id;
	}
	
	function edit($newsarea_id, array $param=null) {
		if (!empty($param)) {
			//static param
			if (M_PACKAGE == 'admin' && !empty(Session::get('admin'))) $param['modifyadmin_id'] = Session::get('admin')['admin_id'];
				
			parent::$db_instance[$this->db]->exec(parent::sql_edit_format(__CLASS__, $param, array('newsarea_id'=>$newsarea_id)));
		}
		
		//clean mc
		parent::cache_delete(__CLASS__, $this->cachekeymap());
		
		return true;
	}
	
	function delete($newsarea_id) {
		parent::$db_instance[$this->db]->exec(parent::sql_delete_format(__CLASS__, array(array('newsarea_id', '=', $newsarea_id))));
		
		//clean mc
		parent::cache_delete(__CLASS__, $this->cachekeymap());
		
		return true;
	}
}