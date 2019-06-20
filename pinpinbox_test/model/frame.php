<?php
class frameModel extends Model {
	protected $database = 'site';
	protected $table = 'frame';
	protected $memcache = 'site';
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
	
	function menu() {
		$column = [
				'user_id',
				'url',
				'blank',
		];
		$this->column($column);
		
		$where = [
				[[['act', '=', 'open']], 'and']
		];
		$this->where($where);
		
		return $this;
	}
}