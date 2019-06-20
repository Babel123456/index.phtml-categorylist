<?php
class noticeModel extends Model {
	protected $database = 'site';
	protected $table = 'notice';
	protected $memcache = 'site';
	protected $join_table = ['noticequeue'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
				['class'=>'noticequeueModel'],
		];
	
		return $return;
	}
	
	function countFollow($user_id) {
		$join = [
				['inner join', 'noticequeue', 'using(notice_id)'],
		];
		$where = [
				[[['notice.act', '=', 'open'], ['noticequeue.user_id', '=', $user_id]], 'and']
		];
		$c_notice = Model('notice')->column(['count(1)'])->join($join)->where($where)->fetchColumn();
		
		return array_encode_return(1, null, null, $c_notice);
	}
	
	function follow($user_id) {
		$column = [
				'notice.type',
				'notice.id',
				'notice.inserttime',
		];
		$this->column($column);
		
		$join = [
				['inner join', 'noticequeue', 'using(notice_id)'],
		];
		$this->join($join);
		
		$where = [
				[[['notice.act', '=', 'open'], ['noticequeue.user_id', '=', $user_id]], 'and']
		];
		$this->where($where);
		
		$order = array('notice.inserttime'=>'desc');
		$this->order($order);
		
		return $this;
	}
}