<?php
class styleModel extends Model {
	protected $database = 'site';
	protected $table = 'style';
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
		$this->column([
				'style.style_id',
				'style.name',
		]);
		
		$this->where([[[['style.act', '=', 'open']], 'and']]);
		
		$this->order(['style.sequence'=>'asc']);
		
		return $this;
	}
}