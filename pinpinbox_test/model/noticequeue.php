<?php
class noticequeueModel extends Model {
	protected $database = 'site';
	protected $table = 'noticequeue';
	protected $memcache = 'site';
	protected $join_table = ['notice'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
				['class'=>'noticeModel'],
		];
	
		return $return;
	}
}