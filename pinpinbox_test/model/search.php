<?php
class searchModel extends Model {
	protected $database = 'site';
	protected $table = 'search';
	protected $memcache = 'site';
	protected $join_table = [];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		return [
				['class'=>__CLASS__],
		];
	}
	
	function importData() {
		$m_searchstructure = Model('searchstructure')->column(['searchkey'])->group(['searchkey'])->fetchAll();
		if ($m_searchstructure) Model('search')->replace($m_searchstructure);
		
		//solr
		$param = [
				'clean'=>true,
				'command'=>'full-import',
				'commit'=>true,
				'debug'=>false,
				'indent'=>true,
				'optimize'=>true,
				'verbose'=>false,
				'wt'=>'json',
		];
		curl('http://localhost:8983/solr/search/dataimport', $param);
	}
}