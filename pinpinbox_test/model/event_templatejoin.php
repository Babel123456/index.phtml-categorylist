<?php
class event_templatejoinModel extends Model {
	protected $database = 'site';
	protected $table = 'event_templatejoin';
	protected $memcache = 'site';
	protected $join_table = ['template', 'album', 'event', 'templatestatistics'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'eventModel'),				
				array('class'=>'templateModel'),
				array('class'=>'albumModel'),
				array('class'=>'templatestatisticsModel'),
		);
	
		return $return;
	}
}