<?php
class templatestatisticsModel extends Model {
	protected $database = 'site';
	protected $table = 'templatestatistics';
	protected $memcache = 'site';
	protected $join_table = array('template', 'templatequeue', 'event_templatejoin');
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'templateModel'),
				array('class'=>'templatequeueModel'),
				array('class'=>'event_templatejoinModel'),
		);
	
		return $return;
	}
}