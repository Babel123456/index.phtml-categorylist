<?php
class user_facebookModel extends Model {
	protected $database = 'site';
	protected $table = 'user_facebook';
	protected $memcache = 'site';
	protected $join_table = array();
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'userModel'),
		);
	
		return $return;
	}
}