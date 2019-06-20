<?php
class admingroupModel extends Model {
	protected $database = 'site';
	protected $table = 'admingroup';
	protected $memcache = 'site';
	protected $join_table = array('admin', 'admin_admingroup', 'admingroup_adminmenu', 'adminmenu');
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'adminModel'),
				array('class'=>'adminmenuModel'),
		);
	
		return $return;
	}
}