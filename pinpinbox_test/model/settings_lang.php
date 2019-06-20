<?php
class settings_langModel extends Model {
	protected $database = 'site';
	protected $table = 'settings_lang';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
		
		//echo "<script>console.log(".json_encode("\model\settings_lang.php:start(資料表settings_lang)".date ("Y-m-d H:i:s" , mktime(date('H')+6, date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";

	}
	
	function cachekeymap() {
		
		return [
				['class'=>__CLASS__],
		];
	}
}