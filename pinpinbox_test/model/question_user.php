<?php
class question_userModel extends Model {
	protected $database = 'site';
	protected $table = 'question_user';
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