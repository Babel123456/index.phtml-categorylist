<?php
class scriptModel extends Model {
	protected $database = 'site';
	protected $table = 'script';
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