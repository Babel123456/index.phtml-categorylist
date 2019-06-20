<?php
class admin_admingroupModel extends Model {
	protected $database = 'site';
	protected $table = 'admin_admingroup';
	protected $memcache = 'site';
	protected $join_table = array();
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'adminModel'),
				array('class'=>'admingroupModel'),
				array('class'=>'adminmenuModel'),
		);
	
		return $return;
	}
}