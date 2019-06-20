<?php
class frame_resourceModel extends Model {
	protected $database = 'site';
	protected $table = 'frame_resource';
	protected $memcache = 'site';
	protected $join_table = array();
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
		);
	
		return $return;
	}
}