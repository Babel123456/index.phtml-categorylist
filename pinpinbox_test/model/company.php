<?php
class companyModel extends Model {
	protected $database = 'site';
	protected $table = 'company';
	protected $memcache = 'site';
	protected $join_table = ['event_companyjoin'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
				['class'=>'event_companyjoinModel'],
		];
		
		return $return;
	}
	

}