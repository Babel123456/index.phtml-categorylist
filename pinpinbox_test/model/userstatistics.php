<?php
class userstatisticsModel extends Model {
	protected $database = 'site';
	protected $table = 'userstatistics';
	protected $memcache = 'site';
	protected $join_table = ['indexcreative'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
				['class'=>'userModel'],
				['class'=>'indexcreativeModel'],
		];
	
		return $return;
	}
}