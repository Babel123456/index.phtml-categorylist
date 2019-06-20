<?php
class albumexploreController extends backstageController {
	function __construct() {}
	
	function album_search() {
		if (is_ajax()) {
			$album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : null ;

			if($album_id === null) json_encode_return(0, _('未輸入相本id或輸入錯誤,請重新確認'), null, null);

			$m_album = Model('album')->column(['album.album_id', 'album.user_id', 'album.cover', 'album.name album_name', 'user.name user_name', 'album.act'])->join([['left join', 'user', 'using(user_id)']])->where([[[['album_id', '=', $album_id], ['album.act', 'in', ['open', 'close']]] ,'and']])->fetch();
			$a_album = [
				'album_id' => $m_album['album_id'],
				'album' => $m_album['album_name'],
				'user' => $m_album['user_name'],
				'cover' => URL_UPLOAD.$m_album['cover'],
			];

			if(!empty($m_album)) {
				($m_album['act'] == 'close') ? json_encode_return(0, _('相本狀態為"關閉中, 無法引入為首頁布局'), null, null) : json_encode_return(1, null, null, json_encode($a_album));
			} else {
				json_encode_return(0, _('找不到指定的相本,請重新確認'), null, null);
			}
		}
	}

	function elementlayout() {
		if (is_ajax()) {
			$value = $_POST['value'];  $data = '';
			$id = (!empty($_POST['id'])) ? $_POST['id'] : null ;
			$single = $multi = $unuse = $hot = $inserttime = $set_target= $keyword = $disable = $image = '';

			switch($value) {
				case 'category':
					list($html, $js) = parent::$html->selectKit(['id'=>'basis_id', 'name'=>'basis_id'], parent::get_form_select('category'), (int)$set_target);
					$data .= '<div>'.$html.'</div>';
					break;

				case 'categoryarea' :
					list($html, $js) = parent::$html->selectKit(['id'=>'basis_id', 'name'=>'basis_id'], parent::get_form_select('categoryarea'), (int)$set_target);
					$data .= '<div>'.$html.'</div>';
					break;

				case 'creative':
					//條件: user至少需要有相本, 相本數量先不設限
					$column = ['COUNT(album.album_id) as album_count', 'user.user_id', 'user.name as user_name'];
					$where = [[[['user.act', '=', 'open'], ['album.act', '=', 'open']], 'and']];
					$join = [['left join', 'user', 'using(user_id)']];
					$group = ['user_id'];
					$m_user = Model('album')->column($column)->where($where)->join($join)->group($group)->fetchAll();

					$data .= '<select id="basis_id"><option value="">'._('Please select').'</option> ';
					foreach($m_user as $k => $v0) {
						$selected = ($v0['user_id'] == substr($set_target, 0, strpos($set_target, ' -'))) ? 'selected="selected"' : null;
						$data .= '<option data-img-src="'.URL_STORAGE.Core::get_userpicture($v0['user_id']).'" value="'.$v0['user_id'].'" '.$selected.'>'.$v0['user_id'].' - '.$v0['user_name'].'</option> ';
					}
					$data .= '</select>';

					break;

				default: break;
			}
			json_encode_return(1, null, null, $data);
		}
		json_encode_return(0, _('Error'));
	}

	function index() {
		list($html0, $js0) = parent::$html->grid();
		list($html1, $js1) = parent::$html->browseKit(array('selector'=>'.grid-img'));
		parent::$data['index'] = $html0.$html1;
		parent::$html->set_js($js0.$js1);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function form() {
		if (is_ajax()) {
			//form
			$categoryarea2explore_id = $_POST['categoryarea2explore_id'];
			$name = $_POST['name'];
			$basis = $_POST['basis'];
			$basis_id = $_POST['basis_id'];
			$url = $_POST['url'];
			$sequence = $_POST['sequence'];
			$description = $_POST['description'];
			$act = $_POST['act'];

			// 20180109 非手動排列的書櫃, 引入的作品將自動失效
			$exhibit = ($basis == 'manual') ? $_POST['exhibit'] : json_encode([]);

			switch ($_GET['act']) {
				//新增
				case 'add':
					if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					$add = array(
							'name'=>$name,
							'basis'=>$basis,
							'basis_id'=>$basis_id,
							'categoryarea2explore_id'=>$categoryarea2explore_id,
							'url'=>$url,
							'sequence'=>$sequence,
							'description'=>$description,
							'act'=>$act,
							'exhibit'=>$exhibit,
							'inserttime'=>inserttime(),
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					$M_CLASS_id = Model(M_CLASS)->add($add);
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
				
				//修改
				case 'edit':
					$M_CLASS_id = $_POST[M_CLASS.'_id'];
					
					if (Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
						json_encode_return(0, _('Data already exists by : ').'Name');
					}
					
					//form
					$edit = array(
							'name'=>$name,
							'basis'=>$basis,
							'basis_id'=>$basis_id,
							'categoryarea2explore_id'=>$categoryarea2explore_id,
							'url'=>$url,
							'sequence'=>$sequence,
							'description'=>$description,
							'act'=>$act,
							'exhibit'=>$exhibit,
							'modifyadmin_id'=>adminModel::getSession()['admin_id'],
					);
					Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->edit($edit);
					
					json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
					break;
			}
		}
		
		//初始值-from
		$categoryarea2explore_id = 0;
		$name = null;
		$basis = 'manual';
		$basis_id = 0;
		$url = null;
		$sequence = null;
		$description = null;
		$act = 'close';
		$inserttime = null;
		$modifytime = null;
		$modifyadmin_name = null;
		$exhibit = null;

		//form
		$column = array();
		$extra = null;
		
		//tabs
		$a_tabs = array();
		
		//layout
		$layout = '<div id="elementlayout" class="albumexplore">
					<ul id="sort">
						<li data-status="unset" id="item1"><img></li>
						<li data-status="unset" id="item2"><img></li>
						<li data-status="unset" id="item3"><img></li>
						<li data-status="unset" id="item4"><img></li>
					</ul>
				</div>';

		//form for add or edit
		switch ($_GET['act']) {
			//新增
			case 'add':
				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'add'));
				break;
				
			//修改
			case 'edit':
				if (!empty($_GET)) {
					$M_CLASS_id = $_GET[M_CLASS.'_id'];
					$m_albumexplore = Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', $M_CLASS_id)), 'and')))->fetch();

					//form
					$categoryarea2explore_id = $m_albumexplore['categoryarea2explore_id'];
					$name = $m_albumexplore['name'];
					$basis = $m_albumexplore['basis'];
					$basis_id = $m_albumexplore['basis_id'];
					$act = $m_albumexplore['act'];
					$exhibit = json_decode( $m_albumexplore['exhibit'] );
					$url = $m_albumexplore['url'];
					$sequence = $m_albumexplore['sequence'];
					$description = $m_albumexplore['description'];
					$inserttime = $m_albumexplore['inserttime'];
					$modifytime = $m_albumexplore['modifytime'];
					$modifyadmin_name = adminModel::getOne($m_albumexplore['modifyadmin_id'])['name'];
				}
				$tmp = [];
				switch ($basis) {
					case 'manual' :
						//fetch album info
						if ($exhibit) {
							foreach ($exhibit as $k0 => $v0) {
								$m_album = Model('album')->column(['album.album_id', 'album.user_id', 'album.cover', 'album.name album_name', 'user.name user_name'])->join([['left join', 'user', 'using(user_id)']])->where([[[['album.act', '=', 'open'], ['album.album_id', '=', $v0]], 'and']])->fetch();

								$tmp[] = '<li id="item' . ($k0 + 1) . '" data-status="set" data-album_id="' . $v0 . '"><img src="' . URL_UPLOAD . $m_album['cover'] . '" onerror="this.src=  \'' . static_file('images/background02.png') . '\' "></li>';
							}
						} else {
							for ($i = 1; $i <= 4; $i++) {
								$tmp[] = '<li id="item' . ($i) . '" data-status="unset" data-album_id=""><img src="" onerror="this.src=\''.static_file('images/background02.png').'\'"></li>';
							}
						}
						break;

					case 'category' :
					case 'categoryarea' :
						$_call = 'getBy'.ucfirst($basis);
						$m_album = (new albumexploreModel())->$_call($basis_id);
						foreach ($m_album as $k0 => $v0) {
							$tmp[] = '<li id="item' . ($k0 + 1) . '" data-status="set" data-album_id="' . $v0['album_id'] . '"><img src="' . URL_UPLOAD . $v0['cover'] . '" onerror="this.src=\''.static_file('images/background02.png').'\'"></li>';
						}
						break;

					case 'creative' :
						$m_album = (new albumexploreModel())->getByCreative($categoryarea2explore_id, $basis_id);
						foreach ($m_album as $k0 => $v0) {
							$tmp[] = '<li id="item' . ($k0 + 1) . '" data-status="set" data-album_id="' . $v0['album_id'] . '"><img src="' . URL_UPLOAD . $v0['cover'] . '" onerror="this.src=\''.static_file('images/background02.png').'\'"></li>';
						}
						break;
				}


				$layout = '<div id="elementlayout" class="albumexplore"><ul id="sort">'.implode('', $tmp).'</ul></div>';

				list($html, $js) = parent::$html->hidden('id="'.M_CLASS.'_id" name="'.M_CLASS.'_id" value="'.$M_CLASS_id.'"');
				$extra .= $html;
				parent::$html->set_js($js);

				parent::$data['action'] = parent::url(M_CLASS, 'form', array('act'=>'edit'));
				break;
		}
		parent::$data['basis'] = $basis;
		parent::$data['basis_id'] = $basis_id;

		$a_categoryarea2explore_id = array_merge([['value'=>0, 'text'=>'熱門主題']], parent::get_form_select('categoryarea'));
		list($html, $js) = parent::$html->selectKit(['id'=>'categoryarea2explore_id', 'name'=>'categoryarea2explore_id'], $a_categoryarea2explore_id, $categoryarea2explore_id);
		$column[] = ['key'=>parent::get_adminmenu_name_by_class('albumexplore'), 'value'=>$html];
		parent::$html->set_js($js);

		$a_basis = [['value'=> 'manual', 'text'=>'手動排列'], ['value'=> 'creative', 'text'=> '創作人'], ['value'=> 'categoryarea', 'text'=>'熱門主分類'], ['value'=> 'category', 'text'=>'熱門子分類'],  ];
		list($html, $js) = parent::$html->selectKit(['id'=>'basis', 'name'=>'basis'], $a_basis, $basis);
		$column[] = ['key'=>'條件', 'value'=>$html];
		parent::$html->set_js($js);

		list($html, $js) = array('<div id="layout"></div>', null);
		$column[] = ['key'=>'關聯資料', 'value'=>$html];
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="name" name="name" value="'.$name.'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Name'), 'value'=>$html);
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="description" name="description" value="'.$description.'" size="64" maxlength="64" required');
		$column[] = array('key'=>_('Description'), 'value'=>$html);
		parent::$html->set_js($js);

		$urlKeyRemark = '若非以下格式，APP將開瀏覽器顯示<br>
						個人專區：/creative/content/?user_id=<font color="red">id</font><br>
						活動：/event/content/?event_id=<font color="red">id</font><br>
						主分類：/album/?categoryarea_id=<font color="red">id</font><br>
						子分類：<br>/album/rank_id=0&categoryarea_id=<font color="red">id</font>&category_id=<font color="red">id</font>';
		list($html, $js) = parent::$html->text('id="url" name="url" value="'.$url.'" size="64" maxlength="128" ');
		$column[] = array('key'=>_('Url'), 'value'=>$html, 'key_remark'=>$urlKeyRemark);
		parent::$html->set_js($js);	

		list($html, $js) = parent::$html->number('id="search_album" name="search_album" value="" size="10" min="1" max="99999999" ');
		$column[] = array('key'=>_('選擇相本'), 'value'=>$html);

		list($html, $js) = array($layout, null);
		$column[] = array('key'=>_('Exhibit'), 'value'=>$html, 'key_remark'=>_('首頁展示內容'));
		parent::$html->set_js($js);

		list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="'.$sequence.'" min="0" max="255" required');
		$column[] = array('key'=>_('Sequence'),'value'=>$html);
		parent::$html->set_js($js);
		
		$a_act = array();
		foreach (json_decode(Core::settings('EVENT_ACT'), true) as $k0 => $v0) {
			$a_act[] = array(
					'name'=>'act',
					'value'=>$k0,
					'text'=>$v0,
			);
		}
		list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
		$column[] = array('key'=>_('Act'), 'value'=>$html);
		parent::$html->set_js($js);
		
		$column[] = array('key'=>_('Insert Time'), 'value'=>$inserttime);
		
		$column[] = array('key'=>_('Modify Time'), 'value'=>$modifytime);
		
		$column[] = array('key'=>_('Modify Admin Name'), 'value'=>$modifyadmin_name);
		
		list($html0, $js0) = parent::$html->submit('value="'._('Submit').'"');
		list($html1, $js1) = parent::$html->back('value="'._('Back').'"');
		$column[] = array('key'=>'&nbsp;', 'value'=>$html0.'&emsp;'.$html1);
		parent::$html->set_js($js0.$js1);
		
		list($html, $js) = parent::$html->table('class="table"', $column, $extra);
		$a_tabs[0] = array('href'=>'#tabs-0', 'name'=>_('Form'), 'value'=>$html);
		parent::$html->set_js($js);
		
		
		list($html, $js) = parent::$html->tabs($a_tabs);
		$formcontent = $html;
		parent::$html->set_js($js);
		
		list($html, $js) = parent::$html->form('id="form"', $formcontent);
		parent::$data['form'] = $html;
		parent::$html->set_js($js);
		
		parent::headbar();
		parent::footbar();
		parent::jquery_set();
		parent::$html->set_css(static_file('js/Image-Select/css/ImageSelect.css'), 'href');
		parent::$html->set_js(static_file('js/Image-Select/js/ImageSelect.jquery.js'), 'src');

		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}
	
	function delete() {
		die;
	}
	
	function json() {

		$response = array();

		//column
		$column = array(
				M_CLASS.'_id',
				'albumexplore.name',
				'basis',
				'categoryarea2explore_id',
				'categoryarea.name categoryarea_name',
				'albumexplore.sequence',
				'albumexplore.act',
				'albumexplore.inserttime',
				'albumexplore.modifytime',
		);

		$join = [['left join', 'categoryarea', 'ON albumexplore.categoryarea2explore_id = categoryarea.categoryarea_id']];

		list($where, $group, $order, $limit) = parent::grid_request_encode();

		if($where) {
			foreach ($where[0][0] as $k0 => $v0) {
				if ($v0[0] == 'categoryarea_name') $where[0][0][$k0][0] = 'categoryarea.name';
			}
		}

		//data
		$fetchAll = Model(M_CLASS)->column($column)->where($where)->join($join)->group($group)->order($order)->limit($limit)->fetchAll();

		foreach ($fetchAll as $k0 => $v0) {
			if(is_null( $v0['categoryarea_name'] )) {
				$fetchAll[$k0]['categoryarea_name'] = '熱門主題';
			}
		}

		$response['data'] = $fetchAll;
		
		//total
		$response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->join($join)->group($group)->fetchColumn();

		die(json_encode($response));
	}
	
	function grid_edit() {
		if (!empty($_REQUEST)) {
			Model(M_CLASS)->where(array(array(array(array(M_CLASS.'_id', '=', (int)$_REQUEST[M_CLASS.'_id'])), 'and')))->edit(array('sequence'=>$_REQUEST['sequence'], 'modifyadmin_id'=>adminModel::getSession()['admin_id']));
			
			json_encode_return(1, 'Edit success.');
		}
		die;
	}

	function refreshlayout() {
		if (is_ajax()) {
			$basis = (!empty($_POST['basis'])) ? $_POST['basis'] : null ;
			$basis_id = (!empty($_POST['basis_id'])) ? $_POST['basis_id'] : null ;
			$categoryarea2explore_id = (!empty($_POST['categoryarea2explore_id'])) ? $_POST['categoryarea2explore_id'] : null ;

			$data = [];

			switch ($basis) {
				case 'category' :
				case 'categoryarea' :
					$_call = 'getBy'.ucfirst($basis);
					$m_album = (new albumexploreModel())->$_call($basis_id);
					foreach ($m_album as $k0 => $v0) {
						$data[] = '<li id="item' . ($k0 + 1) . '" data-status="set" data-album_id="' . $v0['album_id'] . '"><img src="' . URL_UPLOAD . $v0['cover'] . '" onerror="this.src=\''.static_file('images/background02.png').'\'"></li>';
					}
					break;

				case 'creative' :
					$m_album = (new albumexploreModel())->getByCreative($categoryarea2explore_id, $basis_id);
					foreach ($m_album as $k0 => $v0) {
						$data[] = '<li id="item' . ($k0 + 1) . '" data-status="set" data-album_id="' . $v0['album_id'] . '"><img src="' . URL_UPLOAD . $v0['cover'] . '" onerror="this.src=\''.static_file('images/background02.png').'\' "></li>';
					}
					break;

			}

			json_encode_return(1, null, null, $data);
		}
	}
}