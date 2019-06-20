<?php
class admingroup_adminmenuModel extends Model {
	protected $database = 'site';
	protected $table = 'admingroup_adminmenu';
	protected $memcache = 'site';
	protected $join_table = array();
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'adminModel'),
				array('class'=>'adminmenuModel'),
				array('class'=>'admingroupModel'),
		);
	
		return $return;
	}
}