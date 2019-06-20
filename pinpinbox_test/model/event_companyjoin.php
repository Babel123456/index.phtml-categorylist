<?php
class event_companyjoinModel extends Model {
	protected $database = 'site';
	protected $table = 'event_companyjoin';
	protected $memcache = 'site';
	protected $join_table = ['event', 'company'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
				['class'=>'eventModel'],
				['class'=>'companyModel'],
		];
		
		return $return;
	}
	

}