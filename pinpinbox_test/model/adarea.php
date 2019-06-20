<?php
class adareaModel extends Model {
	protected $database = 'site';
	protected $table = 'adarea';
	protected $memcache = 'site';
	protected $join_table = array('ad', 'adarea_ad');
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'adModel'),
				array('class'=>'adarea_adModel'),
		);
	
		return $return;
	}
}