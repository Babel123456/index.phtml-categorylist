<?php
class audioModel extends Model {
	protected $database = 'site';
	protected $table = 'audio';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		return [
				['class'=>__CLASS__],
				['class'=>'albumModel'],
		];
	}
}