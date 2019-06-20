<?php
/**
 * memcache
 * <p>v1.1 2015-01-05 Lion:
 *     mc key 的組成改為 class::function <#> sql <|> type
 * </p>
 * <p>v1.0 2014-03-13 Lion:
 *     以 server、port 之於 db 的想法設計，mc key 的組成為 class::function | param + operator + value [& param + operator + value] | type
 *     class 之於 table，function 之於 column，param + operator + value 之於 where 條件，type 為資料取得的形式
 *     不以 key 之於 db 的考量是，memcache 特性是 key - value，這樣在 key 上會有過多的設計
 * </p>
 */
class mc extends Memcache {
	private $mc = null;
	private $server = '127.0.0.1';
	private $port = '11211';
	public $expire = 5;
	private static $cachekey = 'CACHEKEY';
	
	function __construct($obj) {
		if (!class_exists('Memcache')) {
			throw new Exception('['.__METHOD__.'] Class \'Memcache\' not found');
		}
		
		if (!empty($obj['SERVER'])) $this->server = $obj['SERVER'];
		if (!empty($obj['PORT'])) $this->port = $obj['PORT'];
		if (!empty($obj['EXPIRE'])) $this->expire = $obj['EXPIRE'];
		
		parent::addserver($this->server, $this->port);
	}
	
	function __destruct() {
		return parent::close();
	}
	
	function get() {
		return parent::get(self::$cachekey);
	}
	
	function set($value) {
		return parent::set(self::$cachekey, $value, MEMCACHE_COMPRESSED, $this->expire);
	}
	
	function delete() {
		return parent::delete(self::$cachekey, 0);
	}
}