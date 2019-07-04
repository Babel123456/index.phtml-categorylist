<?php
class special_exchangeModel extends Model {
	protected $database = 'site';
	protected $table = 'special_exchange';
	protected $memcache = 'site';
	protected $join_table = ['special'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
				['class'=>'specialModel'],
		];
	
		return $return;
	}
}