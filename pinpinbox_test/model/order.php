<?php
class orderModel extends Model {
	protected $database = 'cashflow';
	protected $table = '`order`';
	protected $memcache = 'cashflow';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		return [
            ['class'=>__CLASS__],
        ];
	}
}