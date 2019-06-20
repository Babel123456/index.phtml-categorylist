<?php
class indexelementModel extends Model {
	protected $database = 'site';
	protected $table = 'indexelement';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				['class'=>__CLASS__],
		);
	
		return $return;
	}
}