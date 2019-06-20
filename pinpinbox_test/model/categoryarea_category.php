<?php
class categoryarea_categoryModel extends Model {
	protected $database = 'site';
	protected $table = 'categoryarea_category';
	protected $memcache = 'site';
	protected $join_table = ['album', 'categoryarea'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = array(
				array('class'=>__CLASS__),
				array('class'=>'categoryModel'),
				array('class'=>'categoryareaModel'),
				array('class'=>'albumModel'),
				array('class'=>'albumstatisticsModel'),
		);
	
		return $return;
	}
}