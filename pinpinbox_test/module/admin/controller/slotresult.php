<?php
class slotresultController extends backstageController {
	function __construct() {}
	
	function index() {
		list($html0, $js0) = parent::$html->grid();
		list($html1, $js1) = parent::$html->browseKit(array('selector'=>'.grid-img'));
		parent::$data['index'] = $html0.$html1;
		parent::$html->set_js($js0.$js1);
		
		$search_key = [
			0 => ['name' => '用戶', 'value'=>'user'],
			1 => ['name'=> '獎項', 'value'=> 'photousefor'],
			2 => ['name'=> '相片', 'value'=> 'photo'],
		];

		$search_sub_key = [
			'user' => [
				0 => ['name' => 'id', 'value'=>'user_id'],
				1 => ['name' => '名稱', 'value'=>'name'],
				2 => ['name' => '信箱', 'value'=>'email'],
				3 => ['name' => '帳號', 'value'=>'account'],
			],
			'photousefor' => [
				0 => ['name' => 'id', 'value'=>'photousefor_id'],
				1 => ['name' => '獎項名稱', 'value'=>'photousefor_name'],
			],
			'photo' => [
				0 => ['name' => 'id', 'value'=>'photo_id'],
			],
		];

		parent::$data['search_key'] = $search_key ;
		parent::$data['search_sub_key'] = $search_sub_key ;

		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function FetchByPhoto($key, $sub_key, $value) {
		$response = [];

		switch ($sub_key) {
			case 'photo_id':
				$active0 = $active1 =  null;
				if($sub_key == 'photousefor_name') $active0 = 'search_key_highlight';
				if($sub_key == 'photousefor_id') $active1 = 'search_key_highlight';

				$column = array(
						'photousefor_id',
						'photo_id',
						'photousefor.name photousefor_name',
						'photousefor.image photousefor_image',
						'photousefor.amount photousefor_amount',
						'photousefor.`count` photousefor_count',
						'photousefor.`inserttime` photousefor_inserttime',
						'photousefor.`modifytime` photousefor_modifytime',
				);

				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				$where = [[[['photo_id', '=', $value]], 'and']];

				//data
				$fetchAll = Model('photousefor')->column($column)->where($where)->group($group)->limit($limit)->fetchAll();
				
				foreach ($fetchAll as &$v0) {				
					$v0['image'] = '<img width="120" height="120" src="'.URL_UPLOAD.$v0['photousefor_image'].'">';
					$v0['features'] = '<a href="javascript:void(0)" class="list" onclick="get_data(\'photousefor\', \'photousefor_id\', '.$v0['photousefor_id'].')">中獎名單</a>';
				}		

				break;
			
			default:
				# code...
				break;
		}
		

		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model($key)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();

		return $response;
	}

	function FetchByPhotousefor($key, $sub_key, $value) {
		$response = [];
		switch ($sub_key) {
			/* 獎項id / 名稱 */
			case 'photousefor_name':
			case 'photousefor_id':
				$active0 = $active1 =  null;
				if($sub_key == 'photousefor_name') $active0 = 'search_key_highlight';
				if($sub_key == 'photousefor_id') $active1 = 'search_key_highlight';

				$column = array(
						'photousefor_user_id',
						'photousefor_id',
						'photo_id',
						'photousefor.name photousefor_name',
						'photousefor_user.inserttime photousefor_user_inserttime',
						'photousefor_user.modifytime photousefor_user_modifytime',
						'photousefor_user.user_id photousefor_user_user_id',
						'photousefor_user.state state',
						'user.name user_name',
						'user.account user_account',
						'user.email user_email',
						'user.way way',
						'device.os os',
				);

				$join =[['left join', 'user', 'using(`user_id`)'], ['left join', 'device', 'using(device_id)'], ['left join', 'photousefor', 'using(photousefor_id)']];
				list($where, $group, $order, $limit) = parent::grid_request_encode();
				
				if($sub_key == 'photousefor_id') {
					$where = [[[['photousefor_id', '=', $value]], 'and']];
				} elseif($sub_key == 'photousefor_name') {
					$where = [[[['photousefor.name', 'LIKE', '%'.$value.'%']], 'and']];
				}

				//data
				$fetchAll = Model('photousefor_user')->column($column)->where($where)->join($join)->group($group)->limit($limit)->fetchAll();
				
				foreach ($fetchAll as &$v0) {				
					$v0['photousefor'] = '<span class="'.$active1.'">id : '.$v0['photousefor_id'].'</span><br>'.'<span class="'.$active0.'">name : '.$v0['photousefor_name'].'</span>';
					$v0['photousefor_user'] = $v0['photousefor_user_id'];
					$v0['user'] = 'id : '.$v0['photousefor_user_user_id'].'<br>'.'name : '.$v0['user_name'].'<br>'.'account : '.$v0['user_account'].'<br>'.'email : '.$v0['user_email'].'<br>'.'way : '.$v0['way'];
				}
				break;
			
			/* 全部獎項 */
			default:
				//column
				$column = array(
						'photousefor_id',
						'photo_id',
						'photousefor.name photousefor_name',
						'amount',
						'count',
						'photousefor.inserttime photousefor_inserttime',
						'photousefor.modifytime photousefor_modifytime',
						'photo.name photo_name',
						'album.album_id album_id',
						'album.name album_name'
				);

				$join =[['left join', 'photo', 'using(`photo_id`)'], ['left join', 'album', 'using(`album_id`)']];

				list($where, $group, $order, $limit) = parent::grid_request_encode();
				//data
				$fetchAll = Model($key)->column($column)->where($where)->join($join)->group($group)->limit($limit)->fetchAll();
				
				foreach ($fetchAll as &$v0) {
					$v0['features'] = '<a href="javascript:void(0)" class="list" onclick="get_data(\'photousefor\', \'photousefor_id\', '.$v0['photousefor_id'].')">中獎名單</a>';
					$v0['album'] = 'id : '.$v0['album_id'].'<br>name : '.$v0['album_name'];
				}
				break;
		}

		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model($key)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
		
		return $response;
	}

	function FetchByUser($key, $sub_key, $value) {
		$response = [];
		switch ($sub_key) {
			case 'account':
			case 'email':
			case 'name':
			case 'user_id':
				$active0 = $active1 = $active2 = $active3 = null;
				if($sub_key == 'account') $active0 = 'search_key_highlight';
				if($sub_key == 'email') $active1 = 'search_key_highlight';
				if($sub_key == 'name') $active2 = 'search_key_highlight';
				if($sub_key == 'user_id') $active3 = 'search_key_highlight';

				list($where, $group, $order, $limit) = parent::grid_request_encode();
				$a_user_id = [];
				if($sub_key != 'user_id') {
					$user_id = Model('user')->column(['`user_id`'])->where([[[[$sub_key, 'LIKE', '%'.$value.'%']] ,'and']])->fetchAll();
					foreach ($user_id as $k0 => $v0) { $a_user_id[] = $v0['user_id']; }
				} else {
					$a_user_id[] = $value;
				}

				$where = [[[['photousefor_user.user_id', 'in', $a_user_id]], 'and']];
			
				$column =	[
						'photousefor_user.user_id user_id',
						'photousefor_user_id',
						'photousefor_id',
						'photo_id',
						'photousefor_user.count count',
						'user.name user_name',
						'user.way way',
						'user.account user_account',
						'user.email user_email',
						'photousefor.name photousefor_name',
						'photousefor_user.state state',
						'photousefor_user.inserttime photousefor_user_inserttime',
						'photousefor_user.modifytime photousefor_user_modifytime',
						'device.os os',
						'album.album_id album_id',
						'album.name album_name'
				];

				$join =[['left join', 'photousefor', 'using(`photousefor_id`)'],
						['left join', 'photo', 'using(`photo_id`)'], 
						['left join', 'album', 'using(`album_id`)'],
						['left join', 'user', 'ON photousefor_user.user_id=user.user_id'],
						['left join', 'device', 'ON photousefor_user.device_id=device.device_id']];
	
				//data
				$fetchAll = Model('photousefor_user')->column($column)->where($where)->join($join)->group($group)->limit($limit)->fetchAll();
				
				foreach ($fetchAll as &$v0) {
					$v0['photousefor'] = 'id : '.$v0['photousefor_id'].'<br>'.'name : '.$v0['photousefor_name'];
					$v0['user'] = '<span class="'.$active3.'">id : '.$value.'</span><br>'.'<span class="'.$active2.'">name : '.$v0['user_name'].'</span><br>'.'<span class="'.$active0.'">account : '.$v0['user_account'].'</span><br>'.'<span class="'.$active1.'">email : '.$v0['user_email'].'</span><br>'.'way : '.$v0['way'];
					$v0['album'] = 'id : '.$v0['album_id'].'<br>name : '.$v0['album_name'];
					$v0['photousefor_user_inserttime'] = $v0['photousefor_user_inserttime'];
					$v0['photousefor_user_modifytime'] = $v0['photousefor_user_modifytime'];
				}
				break;
			
			default:
				$column = array(
						'DISTINCT(`user_id`)',
						'user.name user_name',
						'user.account',
						'user.email',
						'user.way way',
				);

				$join =[['left join', 'user', 'using(`user_id`)']];

				list($where, $group, $order, $limit) = parent::grid_request_encode();
				//data
				$fetchAll = Model('photousefor_user')->column($column)->where($where)->join($join)->group($group)->limit($limit)->fetchAll();
				foreach ($fetchAll as &$v0) {
					$v0['features'] = '<a href="javascript:void(0)" class="list" onclick="get_data(\'user\', \'user_id\', '.$v0['user_id'].')">中獎清單</a>';
				}
				break;
		}
		
		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model('photousefor_user')->column(array('count(1)'))->where($where)->group($group)->fetchColumn();		
		return $response;
	}

	function json() {
		$response = array();
		$key = isset($_POST['key'])? $_POST['key'] : null;
		$sub_key = isset($_POST['sub_key'])? $_POST['sub_key'] : null;
		$value = isset($_POST['value'])? $_POST['value'] : null;
		$column = [] ;
		
		$func = 'FetchBy'.ucfirst($key);
		$response = $this->$func($key, $sub_key, $value);
		
		die(json_encode($response));
	}

}