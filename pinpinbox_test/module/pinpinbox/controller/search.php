<?php
class searchController extends frontstageController {
	const key = 'search';
	
	function __construct() {}
	
	function index() {
		$searchkey = isset($_POST['searchkey'])? $_POST['searchkey'] : null;
		
		$return = [];
		
		if (!empty($_COOKIE[self::key])) {
			$return = json_decode($_COOKIE[self::key], true);
		}
		
		if (trim($searchkey)) {
			$a_searchkey = explode(' ', preg_replace('/\s+/', ' ', $searchkey));
			
			$where = [];
			foreach ($a_searchkey as $searchkey) {
				$where[] = [[['_text_', '=', $searchkey]], 'and'];
			}
			
			$s_search = Solr('search')->column(['searchkey'])->where($where)->limit('0,8')->fetchAll();
			if ($s_search) {
				foreach ($s_search as $v0) {
					if (!in_array($v0['searchkey'], $return)) {
						$return[] = $v0['searchkey'];
					}
				}
			}
		}
		
		asort($return);
		
		_die: die(json_encode(array_values($return), JSON_UNESCAPED_UNICODE));
	}

	function cooperate_search() {
		$searchkey = isset($_POST['searchkey'])? $_POST['searchkey'] : null;
		$album_id = isset($_POST['album_id'])? $_POST['album_id'] : null;
		$user_id = isset($_POST['user_id'])? $_POST['user_id'] : null;

		$a_user = [];

		$column = [
			'user.user_id',
			'user.name user_name',
		];

		$where = [[[['user.act', '=', 'open'], ['user.name', 'Like', '%'.$searchkey.'%'], ['user.user_id', '!=', $user_id]], 'and']];

		$join = [
			['left join', 'creative', 'using(`user_id`)'],
		];

		//搜尋使用者結果
		$m_user = (new userModel())->column($column)->where($where)->join($join)->fetchAll();

		//目前已有的協作者
		$m_cooperation = (new cooperationModel())->menu('album', $album_id)->fetchAll();
		foreach ($m_cooperation as $k0 => $v0) {
			if($v0['identity'] == 'admin') unset($m_cooperation[$k0]);
		}

		$a_cooperation_user_id = array_column($m_cooperation, 'user_id');

		foreach ($m_user as $k0 => $v0) {
			$cover = URL_STORAGE.\userModel::getPicture($v0['user_id']);
			$a_user[] = [
				'user_id' => $v0['user_id'],
				'name' => $v0['user_name'],
				'identity' => (in_array($v0['user_id'], $a_cooperation_user_id)) ? true : false,
				'cover' => $cover,
			];
		}

		$return = $a_user;

		_die: die(json_encode(array_values($return), JSON_UNESCAPED_UNICODE));
	}
}