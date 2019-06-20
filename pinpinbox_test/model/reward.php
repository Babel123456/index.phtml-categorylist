<?php
class rewardModel extends Model {
	protected $database = 'site';
	protected $table = 'reward';
	protected $memcache = 'site';
	protected $join_table = ['user', 'album', 'exchange'];
	
	function __construct() {
		parent::__construct_child();
	}
	
	function cachekeymap() {
		$return = [
				['class'=>__CLASS__],
		];
	
		return $return;
	}

	/**
	 * @param $user_id
	 * @return array $result
	 *
	 * 回傳使用者最後一次填寫的收件資料作為欄位的預設值
	 */
	function getLastRecord($user_id) {
		$return = $this->where([[[['user_id', '=', $user_id]] ,'and']])->order(['reward_id' => 'desc'])->limit(1)->fetch();

		return $return;
	}

	function getRecipient($user_id, $album_id) {
		$result = 1;
		$data = [];

		$m_album = (new albumModel())->where([[[['album.album_id', '=', $album_id]], 'and']])->fetch();

		if($m_album['user_id'] != $user_id) {
			$result = 0;
		} else {
			$column = ['reward.*', 'SUM(exchange.point+exchange.point_free) as point_use'];
			$where =[[[['reward.type', '=', 'album'], ['reward.type_id', '=', $album_id]], 'and']];
			$m_reward = $this->column($column)->where($where)->join([['left join', 'exchange', 'using(exchange_id)']])->group(['reward_id'])->fetchAll();
			$data = $m_reward;
		}

		return array_encode_return($result, null, null, $data) ;
	}

}