<?php
class adarea_adModel extends Model {
	protected $database = 'site';
	protected $table = 'adarea_ad';
	protected $memcache = 'site';
	protected $join_table = array('ad', 'adarea');
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'adModel'),
				array('class'=>'adareaModel'),
		);
	
		return $return;
	}
}