<?php
class followtoModel extends Model {
	protected $database = 'site';
	protected $table = 'followto';
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
}