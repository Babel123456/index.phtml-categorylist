<?php
class templatequeueModel extends Model {
	protected $database = 'site';
	protected $table = 'templatequeue';
	protected $memcache = 'site';
	protected $join_table = array('template', 'templatestatistics');
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'templateModel'),
				array('class'=>'templatestatisticsModel'),
		);
		
		return $return;
	}
}