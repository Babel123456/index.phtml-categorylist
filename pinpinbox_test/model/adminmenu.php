<?php
class adminmenuModel extends Model {
	protected $database = 'site';
	protected $table = 'adminmenu';
	protected $memcache = 'site';
	protected $join_table = array('admin', 'admin_admingroup', 'admingroup', 'admingroup_adminmenu');
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'adminModel'),
				array('class'=>'admingroupModel'),
		);
	
		return $return;
	}
}