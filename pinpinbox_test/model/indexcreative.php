<?php
class indexcreativeModel extends Model {
	protected $database = 'site';
	protected $table = 'indexcreative';
	protected $memcache = 'site';
	protected $join_table = ['user', 'userstatistics'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				['class'=>__CLASS__],
				['class'=>'userstatisticsModel'],
		);
	
		return $return;
	}
}