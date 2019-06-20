<?php
class hobby2categoryModel extends Model {
	protected $database = 'site';
	protected $table = 'hobby2category';
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