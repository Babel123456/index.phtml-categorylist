<?php

class userController extends frontstageController
{

	function __construct()
	{
		$user = parent::user_get();
		if (is_ajax()) {

		} else {
			if (empty($user)) {
				$tmp0 = [];
				if (!empty(query_string_parse()['redirect'])) $tmp0['redirect'] = query_string_parse()['redirect'];

				if (!in_array(M_METHOD, ['user::albumcontent', 'user::forgot', 'user::login', 'user::fb_callback', 'user::login_facebook', 'user::logout', 'user::register', 'user::send_smspwd', 'user::point'])) {
					redirect(parent::url('user', 'login', $tmp0), _('Please login first.'));
				}
			}
		}
	}

	function album_editor()
	{
		if (is_ajax()) {
			$user = parent::user_get();
			$album_id = isset($_POST['album_id']) ? $_POST['album_id'] : null;
			if (empty($album_id)) json_encode_return(0, _('Abnormal process, please try again.'));
			if (empty($user)) json_encode_return(2, null, parent::url('user', 'login', ['redirect' => parent::url('template', 'content', ['template_id' => $template_id])]));

			list($result, $message, $redirect, $r_album_id) = array_decode_return(Model('album')->process2($user['user_id']));
			Model('album')->save($r_album_id);

			json_encode_return(1, null, parent::url('diy', 'index', ['album_id' => $album_id]), null);
		}
		die();
	}

	function album()
	{
		parent::$data['user'] = $user = parent::user_get();

		//取得新舊的排序條件
		$rank = empty($_GET['rank']) ? 1 : $_GET['rank'];
		if (!in_array($rank, [1, 2, 3])) $rank = 1;
		parent::$data['rank'] = $rank;

		$sort = empty($_GET['sort']) ? 1 : $_GET['sort'];
		$sort_type = ($sort == 1) ? 'desc' : 'asc';
		parent::$data['sort'] = $sort;

		//若取得 searchkey 1.填入 where 子句  2.修改 ias 的 GET 內容
		$searchkey = (isset($_GET['searchkey']) && $_GET['searchkey'] !== '') ? urldecode($_GET['searchkey']) : null;
		parent::$data['searchkey'] = htmlspecialchars($searchkey);

		$page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
		$num_of_per_page = 8;  //每一頁幾個

		$where = null;
		if ($searchkey !== null) {
			$where = [[[['album.name', 'like', str_replace(['%', '_'], ['\%', '\_'], $searchkey) . '%']], 'and']];
		}

		//作品數統計
		$c_my_albums = count(Model('album')->mine($user['user_id'])->where($where)->fetchAll());
		$c_my_collect = count(Model('album')->other($user['user_id'])->where($where)->fetchAll());
		$c_my_cooperation = count(Model('album')->cooperation($user['user_id'])->where($where)->fetchAll());
		$start = ($num_of_per_page * ($page - 1));
		switch ($rank) {
			case 1:
				$m_album = (new \albumModel)
					->mine($user['user_id'])
					->where($where)
					->limit($start . ',' . $num_of_per_page)
					->order(['album.inserttime' => $sort_type])
					->fetchAll();

				$c_album = $c_my_albums;
				break;

			case 2:
				$m_album = (new \albumModel)
					->other($user['user_id'])
					->where($where)
					->limit($start . ',' . $num_of_per_page)
					->order(['album.inserttime' => $sort_type])
					->fetchAll();

				$c_album = $c_my_collect;
				break;

			case 3:
				$m_album = (new \albumModel)
					->cooperation($user['user_id'])
					->where($where)
					->limit($start . ',' . $num_of_per_page)
					->order(['album.inserttime' => $sort_type])
					->fetchAll();

				$c_album = $c_my_cooperation;
				break;
		}

		//相簿資料
		if (!empty($m_album)) {
			foreach ($m_album as $k => $v) {
				$album[$k] = [
					'album' => [
						'album_id' => $v['album_id'],
						'name' => $v['album_name'],
						'cover' => URL_UPLOAD . getimageresize($v['cover'], 160, 240),
						'description' => strip_tags(nl2br(($v['description']))),
						'point' => $v['point'],
						'cover_url' => ($rank != 2) ? parent::url('user', 'albumcontent', ['album_id' => $v['album_id'], 'click' => 'cover']) : parent::url('album', 'content', ['album_id' => $v['album_id'], 'categoryarea_id' => $v['categoryarea_id'], 'click' => 'cover']),
						'name_url' => ($rank != 2) ? parent::url('user', 'albumcontent', ['album_id' => $v['album_id'], 'click' => 'name']) : parent::url('album', 'content', ['album_id' => $v['album_id'], 'categoryarea_id' => $v['categoryarea_id'], 'click' => 'name']),
						'viewed' => $v['viewed'],
					],
					'user' => [
						'name' => $v['user_name'],
						'picture' => URL_STORAGE . Core::get_userpicture($v['user_id']),
						'url' => Core::get_creative_url($v['user_id']),
					],
					'collect' => ($rank == 2) ? '<div class="info_icon"><i class="add_love"></i></div>' : null,
					'cooperation' => ($rank == 3) ? '<a href="javascript:void(0)" class="share_setting"><img src="' . static_file('images/setting_album.jpg') . '" height="46" width="42" alt=""></a>' : null,
				];
			}
		}
		$num_of_item = $c_album;
		$num_of_max_page = ceil($num_of_item / $num_of_per_page);
		parent::$data['album'] = !empty($album) ? $album : null;

		//more
		$more = null;
		if ($page != $num_of_max_page) {
			$array0 = ['sort' => $sort, 'rank' => $rank];
			if ($searchkey !== null) $array0['searchkey'] = $searchkey;
			$more = parent::url('user', 'album', $array0);
		}
		parent::$data['more'] = $more;

		//info
		$info = ['my_albums' => 0, 'my_collect' => 0, 'my_cooperation' => 0];
		$info['my_albums'] = $c_my_albums;        //已建立作品數
		$info['my_collect'] = $c_my_collect;    //已收藏作品數
		$info['my_cooperation'] = $c_my_cooperation;    //已收藏作品數
		parent::$data['info'] = $info;

		//seo
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('My Album'),
			[_('My Album')]
		);

		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		$this->member_nav();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');

		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function albumcontent()
	{
		parent::$data['user'] = $user = parent::user_get();

		$album_id = empty($_GET['album_id']) ? null : $_GET['album_id'];

		if ($album_id === null) redirect_php(parent::url('user', 'album'));

		//album + albumstatistics + category + categoryarea_category + categoryarea + template + audio
		$column = array(
			'album.album_id',
			'album.user_id',
			'album.template_id',
			'album.audio_id',
			'album.name',
			'album.description',
			'album.cover',
			'album.preview',
			'album.photo',
			'album.location',
			'album.weather',
			'album.mood',
			'album.rating',
			'album.point',
			'album.act',
			'album.inserttime',
			'album.publishtime',
			'albumstatistics.count',
			'albumstatistics.viewed',
			'category.category_id',
			'category.name category_name',
			'categoryarea.categoryarea_id',
			'categoryarea.name categoryarea_name',
			'template.template_id',
			'template.width',
			'template.height',
			'audio.name audio_name',
		);
		$join = array(
			array('left join', 'albumstatistics', 'using(album_id)'),
			array('left join', 'category', 'using(category_id)'),
			array('left join', 'categoryarea_category', 'using(category_id)'),
			array('left join', 'categoryarea', 'using(categoryarea_id)'),
			array('left join', 'template', 'using(template_id)'),
			array('left join', 'audio', 'using(audio_id)'),
		);
		$where = array(
			array(array(array('album.album_id', '=', $album_id), array('album.act', 'in', ['close', 'open']), array('album.state', 'in', ['pretreat', 'process', 'success'])), 'and'),
		);
		$m_album = (new \albumModel)
			->column($column)
			->join($join)
			->where($where)
			->fetch();

		$m_cooperation = Model('album')->diyable($album_id, $user['user_id']);
		$owner = Model('user')->column(['name'])->where([[[['user_id', '=', $m_album['user_id']]], 'and']])->fetchColumn();
		/**
		 *  找不到相簿或開啟非本人相簿
		 *  7/24 避免訪客用user給的後台作品網址進入頁面時被導致登入頁，故將頁面導至公開的 album::content
		 *  1/20 取得 cooperation 資格，若符合則可進入 user::albumcontent
		 */
		if (empty($m_album)) {
			redirect(empty($user) ? parent::url() : parent::url('user', 'album'), _('Album does not exist.'));
		} elseif (empty($user) || ($m_album['user_id'] != $user['user_id'] && !$m_cooperation['result'])) {
			redirect_php(parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => $m_album['categoryarea_id']]));
		}

		$a_album = array(
			'album' => array(
				'album_id' => $m_album['album_id'],
				'name' => htmlspecialchars($m_album['name']),
				'description' => nl2br(htmlspecialchars($m_album['description'])),
				'preview' => json_decode($m_album['preview'], true),
				'photo' => json_decode($m_album['photo'], true),
				'location' => $m_album['location'],
				'weather' => $m_album['weather'],
				'mood' => $m_album['mood'],
				'rating' => ($m_album['rating'] == 'general') ? _('Suitable for all ages') : _('Restricted album'),
				'point' => $m_album['point'],
				'act' => ($m_album['act'] == 'open') ? _('Public') : _('Private'),
				'inserttime' => date('Y/m/d', strtotime($m_album['inserttime'])),
				'publishtime' => date('Y/m/d', strtotime($m_album['publishtime'])),
				'display_time' => ($m_album['publishtime'] == '0000-00-00 00:00:00') ? date('Y/m/d', strtotime($m_album['inserttime'])) : date('Y/m/d', strtotime($m_album['publishtime'])),
				'cover' => $m_album['cover'],
				'page' => count(json_decode($m_album['photo'], true)),
				'status' => ($m_album['act'] == 'open' && $m_album['point'] > 0) ? _('Restricted  P points') : _('Off the shelves'),
			),
			'albumstatistics' => array(
				'count' => $m_album['count'],
				'viewed' => $m_album['viewed'],
			),
			'audio' => array(
				'name' => $m_album['audio_name'],
			),
			'category' => array(
				'category_id' => $m_album['category_id'],
				'name' => \Core\Lang::i18n($m_album['category_name']),
			),
			'categoryarea' => array(
				'categoryarea_id' => $m_album['categoryarea_id'],
				'name' => \Core\Lang::i18n($m_album['categoryarea_name']),
			),
			'template' => array(
				'template_id' => $m_album['template_id'],
				'width' => $m_album['width'],
				'height' => $m_album['height'],
			),
			'user' => array(
				'user_id' => $m_album['user_id'],
				'name' => $owner,
			)
		);
		parent::$data['album'] = $a_album;

		//cooperation.identity
		$user_identity = Model('cooperation')->column(['identity'])->where([[[['type', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user['user_id']]], 'and']])->fetchColumn();
		$nav_right = '';
		$nav_right_tmp = [];
		$nav_right_tmp = [
			//編輯作品資訊
			'edit' => '<a href="' . parent::url('user', 'albumcontent_setting', ['album_id' => $album_id]) . '" class="e_edit p1">' . _('Edit info') . '</a>',
			//編輯作品
			'diy' => '<a href="javascript:void(0)" onclick="album_editor()" class="e_edit p2">' . _('Album editor') . '</a>',
			//印刷
			'print' => '<a href="javascript:void(0)" onclick="print()" class="e_edit p5">' . _('Print') . '</a>',
			//瀏覽作品
			'view' => '<a href="javascript:void(0)" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})" class="e_edit p8">' . _('View album') . '</a>',
			//刪除作品
			'delete' => '<a href="javascript:void(0)" onclick="delete_album()" class="e_edit p6">' . _('Delete') . '</a>',
			//QRcode
			'qrcode' => '<a href="' . URL_STORAGE . storagefile(SITE_LANG . '/user/' . $user['user_id'] . '/album/' . $album_id . '/qrcode.jpg') . '" class="e_edit p7 imagepop">' . _('QR code') . '</a>'
		];

		switch ($user_identity) {
			case 'admin' :
				$nav_right = $nav_right_tmp['edit'] . $nav_right_tmp['diy'] . $nav_right_tmp['print'] . $nav_right_tmp['view'] . $nav_right_tmp['delete'] . $nav_right_tmp['qrcode'];
				break;
			case 'approver' :
				$nav_right = $nav_right_tmp['diy'] . $nav_right_tmp['print'] . $nav_right_tmp['view'] . $nav_right_tmp['qrcode'];
				break;
			case 'editor' :
				$nav_right = $nav_right_tmp['diy'] . $nav_right_tmp['print'] . $nav_right_tmp['view'] . $nav_right_tmp['qrcode'];
				break;
			case 'viewer' :
				$nav_right = $nav_right_tmp['print'] . $nav_right_tmp['view'] . $nav_right_tmp['qrcode'];
				break;
		}
		parent::$data['nav_right'] = $nav_right;

		//手機web用 URL Scheme
		$mobile_diy_bit = '<a href="#" data-uri="' . parent::deeplink('diy', 'content', ['album_id' => $album_id, 'template_id' => (int)$m_album['template_id'], 'identity' => $user_identity]) . '" onclick="clickHandler(this.dataset.uri)" class="adminmo_btn p2">' . _('Album editor') . '</a>';
		parent::$data['mobile_diy_bit'] = $mobile_diy_bit;

		//相片數 / 總數
		$amount = 0;
		$m_album_count = Model('album')->column(array('photo'))->where(array(array(array(array('user_id', '=', $user['user_id'])), 'and')))->fetchAll();
		foreach ($m_album_count as $v) {
			$amount += count(json_decode($v['photo'], true));
		}
		switch (Core::get_usergrade($user['user_id'])) {
			default:
			case 'free':
				$amount = $amount . ' / 1000';
				break;

			case 'plus':
				$amount = $amount . ' / 8000';
				break;

			case 'profession':
				$amount = $amount . ' / ' . _('Unrestricted');
				break;
		}
		parent::$data['amount'] = $amount;

		//disqus
		parent::$data['disqus'] = parent::disqus('album', $m_album['album_id']);

		//seo
		$this->seo(
			empty($m_album['name']) ? null : $m_album['name'] . ' | ' . Core::settings('SITE_TITLE'),
			empty($m_album['name']) ? null : array($m_album['name']),
			$m_album['description'],
			URL_UPLOAD . $m_album['cover']
		);

		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		$this->member_nav();

		//lightgallery
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
		parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');

		//mediaelement
		parent::$html->set_css(static_file('js/mediaelement-2.22.0/mediaelementplayer.min.css'), 'href');
		parent::$html->set_js(static_file('js/mediaelement-2.22.0/mediaelement-and-player.min.js'), 'src');

		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('js/intl-tel-input-master/css/intlTelInput.css'), 'href');
		parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
		parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');

		parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
		parent::$html->set_js(static_file('js/intl-tel-input-master/js/intlTelInput.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
		parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.show-more.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function albumcontent_setting()
	{
		if (is_ajax()) {
			$action = empty($_POST['action']) ? null : $_POST['action'];

			if ($action == 'select') {
				$categoryarea_id = empty($_POST['categoryarea_id']) ? 0 : $_POST['categoryarea_id'];

				if ($categoryarea_id) {
					$column = array('category_id', 'name');
					$join = array(
						array('left join', 'categoryarea_category', 'using(category_id)'),
					);
					$where = array(
						array(array(array('categoryarea_category.categoryarea_id', '=', $categoryarea_id), array('categoryarea_category.act', '=', 'open')), 'and'),
					);
					$order = array('categoryarea_category.sequence' => 'asc');
					$m_category = (new \categoryModel)
						->column($column)
						->join($join)
						->where($where)
						->order($order)
						->fetchAll();

					$a_category = array();
					foreach ($m_category as $v0) {
						$a_category[] = array(
							'category_id' => $v0['category_id'],
							'name' => \Core\Lang::i18n($v0['name']),
						);
					}
				} else {
					$a_category[] = [
						'category_id' => 0,
						'name' => '',
					];
				}

				json_encode_return(1, null, null, $a_category);
			}

			if ($action == 'form') {
				$user = parent::user_get();
				$album_id = $_POST['album_id'];
				$event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
				$join_event = !empty($_POST['join_event']) ? $_POST['join_event'] : null;

				list ($result, $message) = array_decode_return((new \albumModel)->settingsable($album_id, $user['user_id']));
				if (!$result) json_encode_return(0, $message);

				$user_grade = Core::get_usergrade($user['user_id']);

				$album_location = (!empty($_POST['album_location'])) ? $_POST['album_location'] : null;
				$album_title = (!empty($_POST['album_title'])) ? $_POST['album_title'] : null;
				$album_description = (!empty($_POST['album_description'])) ? $_POST['album_description'] : null;
				$album_privacy = (!empty($_POST['album_privacy'])) ? $_POST['album_privacy'] : null;
				$album_categoryarea = (!empty($_POST['album_categoryarea'])) ? $_POST['album_categoryarea'] : null;
				$album_category = (!empty($_POST['album_category'])) ? $_POST['album_category'] : null;
				$album_index = (!empty($_POST['album_index'])) ? $_POST['album_index'] : null;
				$album_point = (!empty($_POST['album_point'])) ? $_POST['album_point'] : 0;

				$display_num_of_collect = (!empty($_POST['display_num_of_collect'])) ? $_POST['display_num_of_collect'] : 0;
				$reward_after_collect = (!empty($_POST['reward_after_collect']) || ($album_point > 3) ) ? $_POST['reward_after_collect'] : 0;
				$reward_description = (!empty($_POST['reward_description']) || ($reward_after_collect == 1) ) ? htmlentities($_POST['reward_description'] , ENT_QUOTES) : '';

				if (!empty($album_index)) {
					// 檢查index是否有重複輸入[album]
					$index_unique = array_unique($album_index);
					$tmp = array();
					if (count($album_index) != count($index_unique)) {
						json_encode_return(0, _('Re-type photo album index'));
					}

					//[other album]
					$where = array(
						array(array(array('album_id', '!=', $album_id)), 'and'),
					);
					$all_index = Model('albumindex')->where($where)->fetchAll();
					foreach ($all_index as $k => $v) {
						$tmp[] = $v['index'];
					}

					foreach ($album_index as $v) {
						if (in_array($v, $tmp)) json_encode_return(0, _('A repetition of the other album index'));
					}
				}

				//檢查必填 [title][description]
				if ($album_privacy == 'open' && empty($album_title)) {
					json_encode_return(0, _('Please enter your data.'));
				}

				//檢查必填 [category_area][category]
				if ($album_privacy == 'open' && empty($album_categoryarea) && empty($album_category)) {
					json_encode_return(0, _('請為作品選個分類。'));
				}

				//檢查 P點值 (至少需為3P)
				if (in_array($album_point, [1, 2])) {
					json_encode_return(0, _('贊助條件最少為3p, 請重新設定.'));
				}

				//檢查 P點值 (至多為 9999)
				if ($album_point > 50000) {
					json_encode_return(0, _('贊助條件最多設定為50000p, 請重新設定.'));
				}

				$param = [
					'act' => $album_privacy,
					'category_id' => $album_category,
					'description' => $album_description,
					'location' => $album_location,
					'name' => $album_title,
					'point' => $album_point,
					'user_id' => $user['user_id'],
					'display_num_of_collect' => $display_num_of_collect,
					'reward_after_collect' => $reward_after_collect,
					'reward_description' => $reward_description,
				];

				(new albumModel)->updateSettings($album_id, $param);

				if ($album_index == "" && $user_grade == 'profession') {
					(new \albumindexModel)
						->where(array(array(array(array('album_id', '=', $album_id)), 'and')))
						->delete();
				}

				if ($album_index != null && $user_grade == 'profession') {
					(new \albumindexModel)
						->where(array(array(array(array('album_id', '=', $album_id)), 'and')))
						->delete();

					$param = array();
					foreach ($album_index as $v) {
						$param[] = array(
							'album_id' => $album_id,
							'`index`' => $v,
						);
					}
					(new \albumindexModel)->add($param);
				}

				//notice_switch
				if (!Core::notice_switch(array('type' => 'album', 'id' => $album_id, 'act' => $album_privacy))) json_encode_return(0, _('Abnormal process, please try again.'));

				if ($album_privacy == 'open' && $join_event != null) {
					/**
					 * 取得作品的template_id與event_templatejoin比對是否符合跳轉條件
					 * 有兌換頁=>event.special 無兌換頁=>event.content
					 */
					$m_template_id = (new \albumModel)
						->column(['template_id'])
						->where([[[['album_id', '=', $album_id]], 'and']])
						->fetchColumn();

					if ($m_template_id === 0) {
						//快速建立投稿時需參考GET event_id 才可直接進行投稿動作, 若未正確取得event_id 則視為一般編輯相本動作
						$refer_event_id = (isset($_POST['event_id'])) ? $_POST['event_id'] : json_encode_return(1, _('Edit success.'), parent::url('user', 'albumcontent', array('album_id' => $album_id)));

						$eventExchangePage = (new eventModel())
							->column(['exchange_page'])
							->where([[[['event_id', '=', $event_id]], 'and']])
							->fetchColumn();
					} else {
						$event = (new \event_templatejoinModel)
							->column(['event_templatejoin.*', 'event.exchange_page'])
							->join([['left join', 'event', 'using(event_id)']])
							->where([[[['event_templatejoin.template_id', '=', $m_template_id], ['event.act', '=', 'open'], ['event.starttime', '<', date('Y-m-d H:i:s', time())], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])
							->fetch();

						$refer_event_id = (!empty($event)) ? $event['event_id'] : 0;
						$eventExchangePage = $event['exchange_page'];
					}

					if (encrypt(['user_id' => $user['user_id'], 'event_id' => $refer_event_id]) != $join_event) json_encode_return(1, _('Edit success.'), Core::get_creative_url($user['user_id']));

					(new \eventjoinModel)->add([
						'event_id' => $refer_event_id,
						'album_id' => $album_id
					]);

					($eventExchangePage)
						? json_encode_return(1, _('Edit success.'), parent::url('event', 'special', ['event_id' => $refer_event_id]))
						: json_encode_return(1, _('Edit success.'), parent::url('event', 'content', ['event_id' => $refer_event_id]));
				}

				if ($album_privacy == 'close') {
					$m_eventjoin = (new \eventjoinModel)
						->column(['count(1)'])
						->where([[[['album_id', '=', $album_id]], 'and']])
						->fetchColumn();

					if ($m_eventjoin) {
						(new \eventjoinModel)
							->where([[[['album_id', '=', $album_id]], 'and']])
							->delete();
					}
				}

				json_encode_return(1, _('Edit success.'), Core::get_creative_url($user['user_id']));
			}

			json_encode_return(0, _('Abnormal process, please try again.'));
		}

		$user = parent::user_get();
		$amount = 0;

		//取得相簿ID
		$g_album_id = (!empty($_GET['album_id'])) ? $_GET['album_id'] : redirect_php(parent::url('user', 'album'));
		$where = array();
		$where[] = array(array(array('album_id', '=', $g_album_id)), 'and');
		$m_album = Model('album')->where($where)->fetch();

		//找不到相簿或開啟非登入者相簿
		if (empty($m_album) || ($user['user_id'] != $m_album['user_id'])) {
			$identity = Model('cooperation')->getCooeration('album', $m_album['album_id'], $user['user_id']);
			if(!empty($identity)) {
				redirect_php( Core::get_creative_url($user['user_id']) );
			} else {
				redirect_php(parent::url('user', 'album'));
			}
		}

		//取得categoryarea_id -- 判斷是否有album_id
		(!empty($m_album['category_id']))
			? $m_album['categoryarea_id'] = Model('categoryarea_category')->column(array('categoryarea_id'))->where(array(array(array(array('category_id', '=', $m_album['category_id'])), 'and')))->fetchColumn()
			: $m_album['categoryarea_id'] = null;

		//取得categoryarea_name -- 判斷是否有categoryarea_id
		(!empty($m_album['category_id']))
			? $m_album['categoryarea_name'] = \Core\Lang::i18n(Model('categoryarea')->column(array('name'))->where(array(array(array(array('categoryarea_id', '=', $m_album['categoryarea_id'])), 'and')))->fetchColumn())
			: $m_album['categoryarea_name'] = null;

		//取得category_name -- 判斷是否有category_id
		(!empty($m_album['category_id']))
			? $m_album['category_name'] = \Core\Lang::i18n(Model('category')->column(array('name'))->where(array(array(array(array('category_id', '=', $m_album['category_id'])), 'and')))->fetchColumn())
			: $m_album['category_name'] = null;

		//取得albumstatistics資訊
		$column = array('count');
		$where = array();
		$where[] = array(array(array('album_id', '=', $m_album['album_id'])), 'and');
		$m_albumstatistics = Model('albumstatistics')->column($column)->where($where)->fetchColumn();

		//算出此ID的相簿內相片總和
		$m_album_count = Model('album')->column(array('photo'))->where(array(array(array(array('user_id', '=', $user['user_id'])), 'and')))->fetchAll();
		foreach ($m_album_count as $k => $v) {
			$amount += count(json_decode($v['photo'], true));
		}

		//取得template資訊
		$column = array('width', 'height');
		$where = array();
		$where[] = array(array(array('template_id', '=', $m_album['template_id'])), 'and');
		$m_template = Model('template')->column($column)->where($where)->fetchAll();
		if (empty($m_template)) $m_template = null;

		//修改相簿資料
		$m_album['download_count'] = $m_albumstatistics;
		$m_album['inserttime'] = date('Y/m/d', strtotime($m_album['inserttime']));
		$m_album['publishtime'] = date('Y/m/d', strtotime($m_album['publishtime']));
		$m_album['display_time'] = ($m_album['publishtime'] == '1970/01/01') ? date('Y/m/d', strtotime($m_album['inserttime'])) : date('Y/m/d', strtotime($m_album['publishtime']));
		$m_album['width'] = $m_template[0]['width'];
		$m_album['height'] = $m_template[0]['height'];
		$m_album['page'] = count(json_decode($m_album['photo']));
		$m_album['rating'] = ($m_album['rating'] == 'general') ? _('Suitable for all ages') : _('Restricted album');
		$m_album['preview'] = json_decode($m_album['preview']);
		$m_album['photo'] = (empty($m_album['photo'])) ? 'empty' : $m_album['photo'];
		$m_album['status'] = ($m_album['act'] == 'open' && $m_album['point'] > 0) ? _('Restricted  P points') : _('Off the shelves');
		$m_album['act'] = $m_album['act'];

		//相片數與總數
		switch (Core::get_usergrade($user['user_id'])) {
			default:
			case 'free':
				$amount = $amount . ' / 1000';
				break;
			case 'plus':
				$amount = $amount . ' / 8000';
				break;

			case 'profession':
				$amount = $amount . ' / ' . _('Unrestricted') . '';
				break;
		}

		//此相簿的分類及子分類=>select optine用
		$m_categoryarea = Model('categoryarea')->column(array('categoryarea_id', 'name'))->where(array(array(array(array('act', '=', 'open')), 'and')))->fetchAll();
		$m_categoryarea_category = Model('categoryarea_category')->column(array('category_id', 'categoryarea_id'))->where(array(array(array(array('act', '=', 'open')), 'and')))->fetchAll();

		foreach ($m_categoryarea as $k => $v) {
			foreach ($m_categoryarea_category as $k2 => $v2) {
				if ($v['categoryarea_id'] == $v2['categoryarea_id']) {
					$m_categoryarea[$k]['group'][]['category_id'] = $v2['category_id'];
				}
			}
		}
		foreach ($m_categoryarea as $k => $v) {
			foreach ($v['group'] as $k2 => $v2) {
				$m_category = Model('category')->column(array('name'))->where(array(array(array(array('act', '=', 'open'), array('category_id', '=', $v2['category_id'])), 'and')))->fetchColumn();
				$m_categoryarea[$k]['group'][$k2]['name'] = \Core\Lang::i18n($m_category);
			}
		}

		// 取得音樂id
		$where = array();
		$where[] = array(array(array('act', '=', 'open')), 'and');
		$m_audio = Model('audio')->column(array('audio_id', 'name'))->where($where)->fetchAll();

		//取得登入者身分grade
		$where = array();
		$where[] = array(array(array('user_id', '=', $user['user_id'])), 'and');
		$m_album['usergrade'] = Model('usergrade')->column(array('grade'))->where($where)->fetchColumn();

		//取得相簿索引編號index
		$where = array();
		$where[] = array(array(array('album_id', '=', $m_album['album_id'])), 'and');
		$m_albumindex = Model('albumindex')->column(array('`index`'))->where($where)->fetchAll();

		//取得是否參加投稿活動
		$m_eventjoin = Model('eventjoin')->column(['count(1)'])->where([[[['album_id', '=', $m_album['album_id']]], 'and']])->fetchColumn();
		parent::$data['event_join'] = (!empty($m_eventjoin)) ? 'true' : 'false';

		$g_event_id = (!empty($_GET['event_id'])) ? $_GET['event_id'] : null;
		$albumNamePrefix = _('作品名稱');
		if (!is_null($g_event_id)) {
			$event_prefix_text = Model('event')->column(['prefix_text'])->where([[[['event_id', '=', $g_event_id]], 'and']])->fetchColumn();
			$albumNamePrefix = ($event_prefix_text) ? $event_prefix_text : _('作品名稱');
		}
		parent::$data['albumNamePrefix'] = $albumNamePrefix;

		//seo
		$this->seo(
			empty($m_album['name']) ? null : $m_album['name'] . ' | ' . Core::settings('SITE_TITLE'),
			empty($m_album['name']) ? null : array($m_album['name']),
			$m_album['description'],
			URL_UPLOAD . $m_album['cover']
		);

		//取得album_id
		$m_album['album']['album_id'] = $m_album['album_id'];

		parent::$data['categoryarea'] = $m_categoryarea;
		parent::$data['audio'] = $m_audio;
		parent::$data['album'] = $m_album;
		parent::$data['albumindex'] = $m_albumindex;
		parent::$data['user'] = $user;
		parent::$data['amount'] = $amount;
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		$this->member_nav();

		//lightgallery
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
		parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');

		//mediaelement
		parent::$html->set_css(static_file('js/mediaelement-2.22.0/mediaelementplayer.min.css'), 'href');
		parent::$html->set_js(static_file('js/mediaelement-2.22.0/mediaelement-and-player.min.js'), 'src');

		parent::$html->set_css(static_file('js/sweet-alert/css/sweet-alert.css'), 'href');
		parent::$html->set_js(static_file('js/sweet-alert/js/sweet-alert.min.js'), 'src');

		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('js/intl-tel-input-master/css/intlTelInput.css'), 'href');
		parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
		parent::$html->set_css(static_file('js/jquery.ddslick/css/service.ddlist.jquery.css'), 'href');
		parent::$html->set_css(static_file('js/trip/css/trip.css'), 'href');

		parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
		parent::$html->set_js(static_file('js/intl-tel-input-master/js/intlTelInput.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
		parent::$html->set_js(static_file('js/trip/js/trip.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function albumpreview_setting()
	{
		if (is_ajax()) {
			$user = parent::user_get();
			$album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : null;
			$album_preview = (!empty($_POST['album_preview'])) ? $_POST['album_preview'] : null;

			if (count($album_preview) < 1) json_encode_return(0, _('至少需要選擇一張預覽圖'));

			$where = array(
				array(array(array('user_id', '=', $user['user_id']), array('album_id', '=', $album_id)), 'and'),
			);
			$param = array();
			$param['preview'] = $album_preview;
			if (Model('album')->where($where)->edit($param)) {
				json_encode_return(1, _('Edit success.'), parent::url('user', 'albumcontent', array('album_id' => $album_id)));
			}
		}

		$user = parent::user_get();

		//取得相簿ID
		$g_album_id = (!empty($_GET['album_id'])) ? $_GET['album_id'] : redirect_php(parent::url('user', 'album'));
		$where = array();
		$where[] = array(array(array('album_id', '=', $g_album_id)), 'and');
		$m_album = Model('album')->where($where)->fetch();

		//找不到相簿或開啟非登入者相簿
		if (empty($m_album) || ($user['user_id'] != $m_album['user_id'])) redirect_php(parent::url('user', 'album'));

		$m_album['photo'] = json_decode($m_album['photo']);
		$m_album['preview'] = (empty($m_album['preview'])) ? 'empty' : json_decode($m_album['preview']);

		$all_photo = array();

		foreach ($m_album['photo'] as $k => $v) {
			$all_photo[$k]['source'] = $v;
			$all_photo[$k]['src'] = URL_UPLOAD . getimageresize($v, 160, 240);
			$all_photo[$k]['check'] = 'none';
			if ($m_album['preview'] != 'empty') {
				if (in_array($v, $m_album['preview'])) {
					$all_photo[$k]['check'] = 'checked';
				}
			}
		}

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Edit thumbnail preview'),
			array(_('Edit thumbnail preview'))
		);

		$preview_limit = (count($all_photo) > Core::settings('ALBUM_PREVIEW_LIMIT')) ? Core::settings('ALBUM_PREVIEW_LIMIT') : count($all_photo);
		parent::$data['preview_limit'] = $preview_limit;

		parent::$data['all_photo'] = $all_photo;
		parent::$data['album'] = $m_album;
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		$this->member_nav();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_js(static_file('js/jquery.ui.touch-punch.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');

		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function albumindex_verify()
	{
		if (is_ajax()) {
			$value = (!empty($_POST['value'])) ? $_POST['value'] : null;
			$album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : null;

			//排除本身相簿外的相同index值
			$where = array();
			$where[] = array(array(array('`index`', '=', $value), array('`album_id`', '!=', $album_id)), 'and');
			$m_albumindex = Model('albumindex')->where($where)->fetch();
			// 已有相同的值
			if (!empty($m_albumindex)) {
				json_encode_return(0, null, 'javascript:void(0)', null);
			}
			json_encode_return(1, null, 'javascript:void(0)', null);
		}
	}

	function creative_edit()
	{
		$user = parent::user_get();

		if (is_ajax()) {
			if (empty($user)) json_encode_return(0, _('Please login first.'), parent::url('user', 'login', ['redirect' => parent::url('creative', 'apply')]), 'Modal');

			//驗證拉桿
			$captcha = (isset($_POST['captcha']) && !empty($_POST['captcha'])) ? $_POST['captcha'] : null;
			if (Session::get('captcha') != $captcha) {
				json_encode_return(0, _('Slider validate fail!'));
			}

			//申請身分
			$apply = empty($_POST['apply']) ? null : $_POST['apply'];
			$param = [];

			switch ($apply) {
				case 'personal':
					$personal_email = !empty($_POST['personal_email']) ? $_POST['personal_email'] : null;
					$personal_country = !empty($_POST['personal_country']) ? $_POST['personal_country'] : null;
					$personal_zipcode = !empty($_POST['personal_zipcode']) ? $_POST['personal_zipcode'] : null;
					$personal_address = !empty($_POST['personal_address']) ? $_POST['personal_address'] : null;
					$personal_career = !empty($_POST['personal_career']) ? $_POST['personal_career'] : null;
					$personal_website = !empty($_POST['personal_website']) ? $_POST['personal_website'] : null;
					$personal_idcardnumber = !empty($_POST['personal_idcardnumber']) ? $_POST['personal_idcardnumber'] : null;
					$company_vatnumber = !empty($_POST['company_vatnumber']) ? $_POST['company_vatnumber'] : null;
					$email_country = $personal_country;
					if ($personal_email === null || $personal_country === null || $personal_zipcode === null || $personal_address === null || $personal_career === null || $personal_website === null) {
						json_encode_return(0, _('Please enter your data.'), null, 'Modal');
					}
					if ($personal_country == 'TW' && $personal_idcardnumber === null) {
						json_encode_return(0, _('Please enter your id card number.'), null, 'Modal');
					}

					$param['personal_email'] = $user_email = $personal_email;
					$param['personal_country'] = $personal_country;
					$param['personal_zipcode'] = $personal_zipcode;
					$param['personal_address'] = $personal_address;
					$param['personal_career'] = $personal_career;
					$param['personal_website'] = $personal_website;
					$param['personal_idcardnumber'] = $personal_idcardnumber;
					$param['company_vatnumber'] = $company_vatnumber;
					break;

				case 'company':
					$company_email = !empty($_POST['company_email']) ? $_POST['company_email'] : null;
					$company_country = !empty($_POST['company_country']) ? $_POST['company_country'] : null;
					$company_name_zh_TW = !empty($_POST['company_name_zh_TW']) ? $_POST['company_name_zh_TW'] : null;
					$company_name_en_US = !empty($_POST['company_name_en_US']) ? $_POST['company_name_en_US'] : null;
					$company_vatnumber = !empty($_POST['company_vatnumber']) ? $_POST['company_vatnumber'] : null;
					$company_telephone = !empty($_POST['company_telephone']) ? $_POST['company_telephone'] : null;
					$company_zipcode = !empty($_POST['company_zipcode']) ? $_POST['company_zipcode'] : null;
					$company_address = !empty($_POST['company_address']) ? $_POST['company_address'] : null;
					$company_website = !empty($_POST['company_website']) ? $_POST['company_website'] : null;
					$email_country = $company_country;
					if ($company_email === null || $company_country === null || $company_name_zh_TW === null || $company_name_en_US === null || $company_telephone === null || $company_zipcode === null || $company_address === null || $company_website === null) {
						json_encode_return(0, _('Please enter your data.'), null, 'Modal');
					}
					if ($company_country == 'TW' && $company_vatnumber === null) {
						json_encode_return(0, _('Please enter your Vat Number.'), null, 'Modal');
					}

					$param['company_email'] = $user_email = $company_email;
					$param['company_country'] = $company_country;
					$param['company_name_zh_TW'] = $company_name_zh_TW;
					$param['company_name_en_US'] = $company_name_en_US;
					$param['company_vatnumber'] = $company_vatnumber;
					$param['company_telephone'] = $company_telephone;
					$param['company_zipcode'] = $company_zipcode;
					$param['company_address'] = $company_address;
					$param['company_website'] = $company_website;
					break;

				default:
					json_encode_return(0, _('Unknown case, please try again.'));
					break;
			}

			//#1327 同步修改使用者聯絡信箱
			(new userModel)->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['email' => $user_email]);

			//匯款方式
			$remittance = empty($_POST['remittance']) ? null : $_POST['remittance'];
			if ($remittance == null) json_encode_return(0, _('Please select remittance way.'));
			switch ($remittance) {
				case 'paypal':
					$paypal_account = !empty($_POST['paypal_account']) ? $_POST['paypal_account'] : null;
					$paypal_currency = !empty($_POST['paypal_currency']) ? $_POST['paypal_currency'] : null;
					if ($paypal_account == null || $paypal_currency == null) {
						json_encode_return(0, _('Please enter your PayPal info.'), null, 'Modal');
					}

					$remittance_info = json_encode(array(
						'paypal_account' => $paypal_account,
						'paypal_currency' => $paypal_currency
					));
					break;

				case 'other':
					$name = !empty($_POST['name']) ? $_POST['name'] : null;
					$bank = !empty($_POST['bank']) ? $_POST['bank'] : null;
					$branch = !empty($_POST['branch']) ? $_POST['branch'] : null;
					$account = !empty($_POST['account']) ? $_POST['account'] : null;
					$remark = !empty($_POST['remark']) ? $_POST['remark'] : null;
					if ($name === null || $bank === null || $branch === null || $account === null) {
						json_encode_return(0, _('Please enter your bank info.'), null, 'Modal');
					}

					$remittance_info = json_encode(array(
						'name' => $name,
						'bank' => $bank,
						'branch' => $branch,
						'account' => $account,
						'remark' => $remark
					));

					break;

				default:
					json_encode_return(0, _('Unknown case, please try again.'));
					break;
			}

			$param['applyfor'] = $apply;
			$param['user_id'] = $user['user_id'];
			$param['remittance'] = $remittance;
			$param['remittance_info'] = $remittance_info;
			$param['inserttime'] = inserttime();

			(new creativeModel)->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit($param);

			json_encode_return(1, _('Edited'), parent::url('user', 'creative_edit'));
		}

		$creative = (new userModel)->column(['creative'])->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetchColumn();

		if (!$creative) redirect(parent::url('creative', 'apply'), _('您尚未開通販售功能，系統將導引至申請頁面。'));//2017-03-28 Lion: 不從 session 判斷, 避免沒有覆寫到確切的 session 造成誤判的情況

		$m_creative = (new creativeModel)->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetch();

		$a_applyfor = [
			'applyfor' => [
				'personal' => ($m_creative['applyfor'] == 'personal') ? 'active' : null,
				'company' => ($m_creative['applyfor'] == 'company') ? 'active' : null,
			],
		];

		$m_creative['applyfor'] = $a_applyfor['applyfor'];

		foreach ($m_creative as $k0 => $v0) {
			if ($k0 == 'remittance_info') continue;
			$a_creative[$k0] = $v0;
		}

		$decode_remittance_info = json_decode($m_creative['remittance_info'], true);

		$npa = null;
		if ($a_creative['remittance'] == 'paypal') {
			$pa = strpos($decode_remittance_info['paypal_account'], '@');
			$npa = substr_replace($decode_remittance_info['paypal_account'], str_repeat("*", $pa - 4), 0, ($pa - 4));
		}
		$remittance_info = [
			'name' => (isset($decode_remittance_info['name'])) ? $decode_remittance_info['name'] : null,
			'bank' => (isset($decode_remittance_info['bank'])) ? $decode_remittance_info['bank'] : null,
			'branch' => (isset($decode_remittance_info['branch'])) ? $decode_remittance_info['branch'] : null,
			'account' => (isset($decode_remittance_info['account'])) ? $decode_remittance_info['account'] : null,
			'remark' => (isset($decode_remittance_info['remark'])) ? $decode_remittance_info['remark'] : null,
			'paypal_account' => $npa,
			'paypal_currency' => (isset($decode_remittance_info['paypal_currency'])) ? $decode_remittance_info['paypal_currency'] : null,
		];

		$a_creative['remittance'] = [
			'remittance' => $m_creative['remittance'],
			'remittance_info' => $remittance_info,
		];

		parent::$data['creative'] = $a_creative;

		// career
		$a_career = [];
		$column = ['career_id', 'name',];
		$where = [[[['act', '=', 'open']], 'and']];
		$order = ['sequence' => 'asc'];
		$m_career = (new careerModel)->column($column)->where($where)->order($order)->fetchAll();
		foreach ($m_career as $v0) {
			$a_career[] = [
				'value' => $v0['career_id'],
				'text' => $v0['name'],
			];
		}
		parent::$data['career'] = $a_career;

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Purchasing history query'),
			[_('Purchasing history query')]
		);

		parent::$data['max'] = Session::set('captcha', rand(1, 100));
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui-1.10.4.custom.min.css'), 'href');
		parent::$html->set_js(static_file('js/jquery-ui-1.10.4.custom.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.ui.touch-punch.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function delete_album()
	{
		if (is_ajax()) {
			sleep(2);
			$user = parent::user_get();
			$album_id = isset($_POST['album_id']) ? $_POST['album_id'] : null;

			if (empty($user) || empty($album_id)) json_encode_return(0, _('Abnormal process, please try again.'));

			//[album].act => delete
			$where = array(array(array(array('user_id', '=', $user['user_id']), array('album_id', '=', $album_id)), 'and'));
			$param = array();
			$param['act'] = 'delete';
			if (Model('album')->where($where)->edit($param)) {
				/**
				 *  待加入其他影響的table
				 */

				//notice_switch
				if (!Core::notice_switch(array('type' => 'album', 'id' => $album_id, 'act' => 'delete'))) json_encode_return(0, _('Abnormal process, please try again.'));

				//[eventjoin][eventvote] delete
				$where = array(
					array(array(array('album_id', '=', $album_id)), 'and'),
				);
				Model('eventjoin')->where($where)->delete();
				Model('eventvote')->where($where)->delete();

				json_encode_return(1, _('Delete success.'), Core::get_creative_url($user['user_id']));
			}
		}
		die;
	}

	function exchange()
	{
		$user = parent::user_get();

		$m_exchange = (new \exchangeModel)
			->column(['exchange_id', 'platform', 'type', 'id', 'point_before', 'point', 'point_free_before', 'point_free', 'inserttime'])
			->where([[[['user_id', '=', $user['user_id']]], 'and']])
			->order(['inserttime' => 'desc'])
			->fetchAll();

		if (!empty($m_exchange)) {
			//格式化取得的交易資料
			foreach ($m_exchange as $k => $v) {
				$m_exchange[$k]['inserttime'] = date('Y/m/d', strtotime($m_exchange[$k]['inserttime']));
				$m_exchange[$k]['point_before_total'] = (int)($m_exchange[$k]['point_before'] + $m_exchange[$k]['point_free_before']);
				$m_exchange[$k]['point_pay'] = (int)($m_exchange[$k]['point'] + $m_exchange[$k]['point_free']);
				$m_exchange[$k]['surplus'] = $m_exchange[$k]['point_before_total'] - $m_exchange[$k]['point_pay'];

				switch ($m_exchange[$k]['platform']) {
					case 'web':
						$m_exchange[$k]['platform'] = 'Web';
						break;

					case 'apple':
						$m_exchange[$k]['platform'] = 'Apple';
						break;

					case 'google':
						$m_exchange[$k]['platform'] = 'Google';
						break;
				}

				switch ($m_exchange[$k]['type']) {
					case 'album':
						$m_exchange[$k]['type'] = _('Album');
						$m_album = Model('album')->column(array('name'))->where(array(array(array(array('album_id', '=', $v['id'])), 'and')))->fetchColumn();
						$m_exchange[$k]['memo'] = '<span style="color:#444">' . _('Exchange No:') . '<span style="color:#9d9d9d">' . $m_exchange[$k]['exchange_id'] . '</span></span><br>
												<span style="color:#444">' . _('Purchase Platform:') . '<span style="color:#9d9d9d">' . $m_exchange[$k]['platform'] . '</span></span><br>
												<span style="color:#444">' . _('Product Type:') . '<span style="color:#9d9d9d">' . _('Album') . '</span></span><br>
												<span style="color:#444">' . _('Product Name:') . '<span style="color:#9d9d9d"><a href="' . parent::url('album', 'content', ['album_id' => $v['id'], 'categoryarea_id' => \albumModel::getCategoryAreaId($v['id'])]) . '">' . $m_album . '</a></span></span>';
						break;

					case 'template':
						$m_exchange[$k]['type'] = _('Template');
						$m_template = Model('template')->column(array('name'))->where(array(array(array(array('template_id', '=', $v['id'])), 'and')))->fetchColumn();
						$m_exchange[$k]['memo'] = '<span style="color:#444">' . _('Exchange No:') . '<span style="color:#9d9d9d">' . $m_exchange[$k]['exchange_id'] . '</span></span><br>
												<span style="color:#444">' . _('Purchase Platform:') . '<span style="color:#9d9d9d">' . $m_exchange[$k]['platform'] . '</span></span><br>
												<span style="color:#444">' . _('Product Type:') . '<span style="color:#9d9d9d">' . _('Template') . '</span></span><br>
												<span style="color:#444">' . _('Product Name:') . '<span style="color:#9d9d9d"><a href="' . parent::url('template', 'content', array('template_id' => $v['id'])) . '">' . $m_template . '</a></span></span>';
						break;
				}

			}
			parent::$data['exchange'] = $m_exchange;
		}

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('P Point consumption query'),
			array(_('P Point consumption query'))
		);

		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.core.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.standalone.css'), 'href');
		parent::$html->set_js(static_file('js/footable/js/footable.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.paginate.js'), 'src');
		parent::$view[] = $view;
	}

	function forgot()
	{
		if (is_ajax()) {
			$result = 1;
			$message = _('Success.');
			$redirect = parent::url('user', 'login');

			$account = isset($_POST['account']) ? $_POST['account'] : null;
			$cellphone = isset($_POST['cellphone']) ? $_POST['cellphone'] : null;

			list($result0, $message0, $redirect0) = array_decode_return(Model('user')->forgotPassword($account, $cellphone));
			if ($result0 != 1) {
				$result = 0;
				$message = $message0;
				$redirect = $redirect0;
				goto _return;
			} else {
				$message = _('已發送簡訊至您的手機, 請至手機確認新密碼。');
			}

			_return:
			json_encode_return($result, $message, $redirect);
		}

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Forgot my password'),
			[_('Forgot my password')]
		);

		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('js/intl-tel-input-master/css/intlTelInput.css'), 'href');
		parent::$html->set_js(static_file('js/intl-tel-input-master/js/intlTelInput.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function grade()
	{
		//#1494 此頁暫不接受訪問
		redirect(parent::url('index'));

		$user = parent::user_get();

		if (is_ajax()) {
			//驗證登入狀態
			if ($user == null) json_encode_return(2, _('Please login first.'), parent::url('user', 'login', array('redirect' => parent::url('user', 'grade'))));

			$buy_id = empty($_POST['buy_id']) ? null : $_POST['buy_id'];
			$assets_item = empty($_POST['assets_item']) ? null : $_POST['assets_item'];
			$total = empty($_POST['total']) ? null : $_POST['total'];
			$currency = empty($_POST['currency']) ? null : $_POST['currency'];
			$obtain = empty($_POST['obtain']) ? null : $_POST['obtain'];

			if ($buy_id == null || $assets_item == null || $total == null || $currency == null || $obtain == null) {
				json_encode_return(0, _('Abnormal process, please try again.'));
			}

			//檢查是否存在於 buy
			$m_buy = Model('buy')->where(array(array(array(array('buy_id', '=', $buy_id), array('act', '=', 'open')), 'and')))->fetch();
			if (empty($m_buy) || $m_buy['platform'] != 'web' || $m_buy['assets'] != 'usergrade' || $m_buy['assets_item'] != $assets_item || $m_buy['total'] != $total || $m_buy['currency'] != $currency || $m_buy['obtain'] != $obtain) {
				json_encode_return(0, _('Abnormal process, please try again.'));
			}

			/* 9/17 改用pay2go(智付寶) */
			$cashflow_id = 'pay2go';

			Model('order');
			Model()->beginTransaction();
			$add = array(
				'cashflow_id' => $cashflow_id,
				'user_id' => $user['user_id'],
				'platform' => 'web',
				'assets' => 'usergrade',
				'assets_info' => json_encode(array('assets_item' => $assets_item, 'obtain' => $obtain)),
				'total' => $total,
				'currency' => $currency,
				'state' => 'pretreat',
				'fulfill' => 'pretreat',
				'remote_ip' => remote_ip(),
				'inserttime' => inserttime(),
			);
			$order_id = Model('order')->add($add);
			if (!$order_id) {
				Model()->rollBack();
				json_encode_return(0, _('[Order] occurs exception, please contact us.'));
			}

			//金流
			$tmp0 = array(
				'order_id' => $order_id,
				'total' => $total,
				'buy' => array(
					'currency' => $currency,
					'name' => 'pinpinbox - User Grade',
				),
				'user' => array(
					'email' => $user['email']
				),
				'assets_info' => $assets_item . '-' . $obtain,
			);
			list($result, $message, $redirect, $data) = array_decode_return(Core::extension('cashflow', $cashflow_id)->index($tmp0));

			if ($result) {
				Model()->commit();
				json_encode_return(1, null, null, $data);
			} else {
				Model()->rollBack();
				json_encode_return(0, $message);
			}
		}

		//取得使用者的 usergradequeue 內最後的身分時間
		$m_usergradequeue = Model('usergradequeue')->where(array(array(array(array('user_id', '=', $user['user_id'])), 'and')))->order(array('endtime' => 'desc'))->fetch();

		//有 usergradequeue 紀錄
		if (!empty($m_usergradequeue)) {
			if ($m_usergradequeue['endtime'] < date('Y-m-d 00:00:00')) {
				//紀錄已過期，此筆訂單的身分從今天開始算
				$starttime = date('Y/n/j');
			} else {
				//有未生效的身分，從未生效的身分之後開始算此筆訂單的身分時間
				$starttime = date('Y/n/j', strtotime($m_usergradequeue['endtime'] . '+1 day'));
			}
		} else {
			//沒有 usergradequeue 紀錄，此筆訂單的身分從今天開始算
			$starttime = date('Y/n/j');
		}
		parent::$data['starttime'] = $starttime;

		//取得 plus 及 pro 販售價格
		$where = array(
			array(array(array('platform', '=', 'web'), array('assets', '=', 'usergrade'), array('currency', '=', 'TWD'), array('act', '=', 'open')), 'and'),
		);
		$m_buy = Model('buy')->where($where)->order(array('obtain' => 'asc'))->fetchAll();
		$a_buy = array();
		foreach ($m_buy as $v0) {
			switch ($v0['assets_item']) {
				case 'plus':
					$a_buy[$v0['assets_item']][] = array(
						'buy_id' => $v0['buy_id'],
						'total' => number_format($v0['total']),
						'currency' => $v0['currency'],
						'obtain' => number_format($v0['obtain']),
						'original_total' => number_format(599 * ($v0['obtain'] / 30)),
					);
					break;

				case 'profession':
					$a_buy[$v0['assets_item']][] = array(
						'buy_id' => $v0['buy_id'],
						'total' => number_format($v0['total']),
						'currency' => $v0['currency'],
						'obtain' => number_format($v0['obtain']),
						'original_total' => number_format(1399 * ($v0['obtain'] / 30)),
					);
					break;
			}
		}
		parent::$data['buy'] = $a_buy;
		parent::$data['user_creative'] = $user['creative'];
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Member Update'),
			array(_('Member Update'))
		);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/sweet-alert/css/sweet-alert.css'), 'href');
		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/sweet-alert/js/sweet-alert.min.js'), 'src');
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function getRecipient() {
		if (is_ajax()) {
			$user = parent::user_get();
			$album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : null;

			$result = 0;
			$message = null;
			$data = [];
			extract((new rewardModel())->getRecipient($user['user_id'], $album_id));

			if(!$result) {
				$message = _('['.__FUNCTION__.'] occur exception, please contact us.');
			}

			json_encode_return($result, $message, null, $data);
		}
	}

	function img_save_to_file()
	{
		$user = parent::user_get();

		//upload路徑 -- 建立今天的dir
		$str = M_PACKAGE . '/' . M_CLASS . '/' . date('Ymd') . '/';
		mkdir_p(PATH_UPLOAD, $str);

		//上傳圖片在日期dir 下
		$imagePath = PATH_UPLOAD . $str;

		$allowedExts = array("jpeg", "jpg", "png", "JPEG", "JPG", "PNG");
		$temp = explode(".", $_FILES["img"]["name"]);
		$extension = end($temp);

		if (in_array($extension, $allowedExts)) {
			if ($_FILES['img']['error'] > 0) {
				$response = array(
					"status" => 'error',
					"message" => _('Exceeded upload size limit : ') . ini_get('upload_max_filesize') . 'B',
				);
			} else {
				$filename = $_FILES['img']['tmp_name'];
				list($width, $height) = getimagesize($filename);
				$save_filename = uniqid('tmp_') . '.' . $extension;
				move_uploaded_file($filename, $imagePath . $save_filename);

				$image = new Core\Image;

				$file = $image->set(PATH_UPLOAD . $str . $save_filename)->save(null, true);

				if (is_file($file)) {
					\Extension\aws\S3::upload($file);
				}

				$response = array(
					"status" => 'success',
					"url" => URL_UPLOAD . $str . $save_filename,
					"width" => $image->getWidth(),
					"height" => $image->getHeight()
				);
			}
		} else {
			$response = array(
				"status" => 'error',
				"message" => _('Upload file type only can be JPEG / JPG / PNG.'),
			);
		}

		print json_encode($response);
		die();
	}

	function img_crop_to_file()
	{
		$imgUrl = $_POST['imgUrl'];
		$imgInitW = $_POST['imgInitW'];
		$imgInitH = $_POST['imgInitH'];
		$imgW = $_POST['imgW'];
		$imgH = $_POST['imgH'];
		$imgY1 = $_POST['imgY1'];
		$imgX1 = $_POST['imgX1'];
		$cropW = $_POST['cropW'];
		$cropH = $_POST['cropH'];
		$angle = $_POST['rotation'];

		switch (strtolower(getimagesize($imgUrl)['mime'])) {
			case 'image/png':
				$img_r = imagecreatefrompng($imgUrl);
				$source_image = imagecreatefrompng($imgUrl);
				$type = '.png';
				break;

			case 'image/jpeg':
				$img_r = imagecreatefromjpeg($imgUrl);
				$source_image = imagecreatefromjpeg($imgUrl);
				$type = '.jpeg';
				break;

			case 'image/gif':
				$img_r = imagecreatefromgif($imgUrl);
				$source_image = imagecreatefromgif($imgUrl);
				$type = '.gif';
				break;

			default:
				die('image type not supported');
		}

		$sub = M_PACKAGE . '/' . M_CLASS . '/' . date('Ymd') . '/' . uniqid('tmp_') . $type;
		$output_path = PATH_UPLOAD . $sub;
		$output_url = URL_UPLOAD . $sub;

		$resizedImage = imagecreatetruecolor($imgW, $imgH);
		imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
		$rotated_image = imagerotate($resizedImage, -$angle, 0);

		$rotated_width = imagesx($rotated_image);
		$rotated_height = imagesy($rotated_image);

		$dx = $rotated_width - $imgW;
		$dy = $rotated_height - $imgH;

		$cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
		imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
		imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
		$final_image = imagecreatetruecolor($cropW, $cropH);
		imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
		imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);

		imagejpeg($final_image, $output_path, 100);

		$response = array(
			'status' => 'success',
			'url' => $output_url
		);
		die(json_encode($response));
	}

	function income()
	{
		if (is_ajax()) {
			$user = parent::user_get();

			list ($result, $message) = array_decode_return(\incomeModel::ableToTurnSettlementToIncome($user['user_id']));
			if ($result != \Lib\Result::SYSTEM_OK) {
				json_encode_return(0, $message);
			}

			//未結算收益 => album + template
			$where = array();
			$where = array(array(array(array('user_id', '=', $user['user_id']), array('income_id', '=', '0')), 'and'));
			$m_settlement = Model('settlement')->where($where)->fetchAll();
			$unsettled_pointsplit = 0;
			$settlement_id_pool = array();
			foreach ($m_settlement as $k => $v) {
				$unsettled_pointsplit += $v['point_album'] + $v['point_template'];
				$settlement_id_pool[] = $v['settlement_id'];
			}

			/** 判斷條件
			 *  沒有settlement
			 */
			if (count($m_settlement) < 1) json_encode_return(0, _('You have no settlement record yet.'));

			/** 判斷條件
			 *  本月已結算
			 */
			$_this_month = date('Y-m');
			$where = array(array(array(array('user_id', '=', $user['user_id']), array('inserttime', 'like', $_this_month . '%')), 'and'));
			$m_income = Model('income')->where($where)->fetchAll();
			if ($m_income) json_encode_return(0, _('This month has been settled.'));

			//不同幣別的結算收益底限
			$where = array(
				array(array(array('user_id', '=', $user['user_id'])), 'and'),
			);
			$m_creative = Model('creative')->where($where)->fetch();

			if ($m_creative) {
				//稅率
				$country = ($m_creative['personal_country'] !== 'none') ? $m_creative['personal_country'] : $m_creative['company_country'];
				$tmp_tax = json_decode(Core::settings('SETTLEMENT_TAX'), true);
				$tax = ($country === 'TW') ? $tmp_tax['TWD'] : $tmp_tax['USD'];

				switch ($m_creative['remittance']) {
					case 'paypal' :
						$currency = json_decode($m_creative['remittance_info'], true)['paypal_currency'];
						//幣別
						if ($currency == 'TWD') {
							$datum = Core::settings('SETTLEMENT_TWD_DATUM');
						} else {
							$exchange_rate = json_decode(Core::settings('EXCHANGE_RATE'), true);
							$datum = Core::settings('SETTLEMENT_USD_DATUM') * $exchange_rate['TWD_USD'];
						}
						//手續費
						$fee = 0;
						break;

					default:
						$currency = 'TWD';
						$datum = Core::settings('SETTLEMENT_TWD_DATUM');
						$tmp_fee = json_decode(Core::settings('SETTLEMENT_FEE'), true);
						//手續費
						$fee = ($country == 'TW') ? $tmp_fee['TWD'] : $tmp_fee['USD'];
						break;
				}
			}

			/** 判斷條件
			 *  P點未超過領取底限
			 */
			if ($unsettled_pointsplit < $datum) json_encode_return(0, _('Your P Point is below the bottom line of the settlement.'));

			//轉換P點為收益金額
			$transform = Core::settings('TRANSFORM_RATE');
			switch ($currency) {
				case 'USD':
					//現金收益 [美金]  = ((P點 / 虛擬貨幣轉換率) * 稅率) * 匯率
					$total = (($unsettled_pointsplit / $transform) * (100 - $tax) / 100) / $exchange_rate['TWD_USD'];
					break;

				default:
					//現金收益 [台幣]  = (((P點 / 虛擬貨幣轉換率) * 稅率) * 匯率 ) - 手續費
					$total = floor(((($unsettled_pointsplit / $transform) * (100 - $tax) / 100) - $fee));
					break;
			}

			$add = array(
				'user_id' => $user['user_id'],
				'total' => $total,
				'currency' => $currency,
				'remittance' => $m_creative['remittance'],
				'remittance_info' => $m_creative['remittance_info'],
				'country' => $country,
				'state' => 'pretreat',
				'inserttime' => inserttime(),
			);

			$income_id = Model('income')->add($add);

			$edit = array(
				'income_id' => $income_id,
				'state' => 'success',
			);
			$where = array();
			$where[] = array(array(array('settlement_id', 'in', $settlement_id_pool)), 'and');
			if (Model('settlement')->where($where)->edit($edit)) {
				json_encode_return(1, _('The application is successful. When the remittance is completed we will be notified by email.'), parent::url('user', 'settlement'), 'Modal');
			}
		}
		die;
	}

	function login()
	{
		if (is_ajax()) {
			$result = 1;
			$message = _('Login success.');
			$redirect = empty(query_string_parse()['redirect']) ? parent::url() : query_string_parse()['redirect'];
			$data = null;

			$account = isset($_POST['account']) ? $_POST['account'] : null;
			$password = isset($_POST['password']) ? $_POST['password'] : null;

			list($result0, $message0) = array_decode_return(Model('user')->ableToLogin($account, $password));
			if ($result0 != 1) {
				$result = $result0;
				$message = $message0;
				$redirect = null;
				goto _return;
			}

			Model('user')->login($account, $password);

			/**
			 *  0704 - 執行任務-首次登入
			 */
			$data = Model('task')->doTask('firsttime_login', Model('user')->getSession()['user_id'], 'web');

			_return:
			json_encode_return($result, $message, $redirect, $data);
		}

		//seo
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Log in'),
			[_('Log in')]
		);

		$fb = new Facebook\Facebook([
			'app_id' => Core::settings('FACEBOOK_APP_ID'),
			'app_secret' => Core::settings('FACEBOOK_APP_SECRET'),
			'default_graph_version' => Core::settings('FACEBOOK_API_VERSION'),
		]);

		$helper = $fb->getRedirectLoginHelper();
		$redirect = (!empty($_GET['redirect'])) ? ['redirect' => $_GET['redirect']] : [];
		$loginUrl = $helper->getReRequestUrl(parent::url('user', 'fb_callback', $redirect), json_decode((new settingsModel)->getByKeyword('FACEBOOK_APP_SCOPE'), true));
		parent::$data['url'] = htmlspecialchars($loginUrl);

		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function fb_callback()
	{
		Session::__start();
		$fb = new Facebook\Facebook([
			'app_id' => Core::settings('FACEBOOK_APP_ID'),
			'app_secret' => Core::settings('FACEBOOK_APP_SECRET'),
			'default_graph_version' => Core::settings('FACEBOOK_API_VERSION'),
		]);

		$helper = $fb->getRedirectLoginHelper();

		if (isset($_GET['state'])) {
			$helper->getPersistentDataHandler()->set('state', $_GET['state']);
		}


		try {
			$accessToken = $helper->getAccessToken();
		} catch (Facebook\Exceptions\FacebookResponseException $e) {
			echo 'Graph returned an error: ' . $e->getMessage();
			exit;
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			echo 'Facebook SDK returned an error: ' . $e->getMessage();
			exit;
		}

		if (!isset($accessToken)) {
			if ($helper->getError()) {
				header('HTTP/1.0 401 Unauthorized');
				echo "Error: " . $helper->getError() . "\n";
				echo "Error Code: " . $helper->getErrorCode() . "\n";
				echo "Error Reason: " . $helper->getErrorReason() . "\n";
				echo "Error Description: " . $helper->getErrorDescription() . "\n";
			} else {
				header('HTTP/1.0 400 Bad Request');
				echo 'Bad request';
			}
			exit;
		}

		$oAuth2Client = $fb->getOAuth2Client();
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);
		$tokenMetadata->validateAppId(Core::settings('FACEBOOK_APP_ID')); // Replace {app-id} with your app id
		$tokenMetadata->validateExpiration();
		if (!$accessToken->isLongLived()) {
			try {
				$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
			} catch (Facebook\Exceptions\FacebookSDKException $e) {
				echo "<p>Error getting long-lived access token: " . $helper->getMessage() . "</p>\n\n";
				exit;
			}
		}
		$_SESSION['fb_access_token'] = (string)$accessToken;
		//
		$result = 1;
		$message = _('Login success.');
		$redirect = null;
		$data = [];
		if (empty((string)$accessToken)) {
			$result = 0;
			$message = _('Abnormal process, please try again.');
			goto _return;
		}

		try {
			$response = $fb->get('/me?fields=birthday,email,gender,id,name', (string)$accessToken);//Returns a `Facebook\FacebookResponse` object //2016-03-29 Lion: 以 helper 方式取得的 token 經常性的拋出異常(原因不明), 改從前端接收
			$user = $response->getGraphUser();
			$birthday = $user->getField('birthday');
			$account = $user->getField('email');
			$gender = $user->getField('gender');
			$way_id = $user->getField('id');
			$name = $user->getField('name');

			Model('user');
			Model('user_facebook');
			Model('userstatistics');
			Model('follow');
			Model('subscription');
			Model('token');
			Model('topic');
			Model()->beginTransaction();

			$param = [
				'account' => $account,
				'name' => $name,
				'gender' => $gender,
				'birthday' => $birthday,
				'way' => 'facebook',
				'way_id' => $way_id,
			];
			list($result1, $message1, $redirect1, $data1) = array_decode_return(Model('user')->register($param));
			if ($result1 != 1) {
				Model()->rollBack();

				$result = $result1;
				$message = $message1;
				goto _return;
			}
			$redirect = $redirect1;

			Model()->commit();

			Model('user')->setSession($data1['id']);
		} catch (Facebook\Exceptions\FacebookResponseException $e) {
			//When Graph returns an error
			//'Graph returned an error: ' . $e->getMessage()
			Model('userlog')->setException($e);

			$result = 0;
			$message = _('Abnormal process, please try again.');
			goto _return;
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			//'Facebook SDK returned an error: ' . $e->getMessage()
			Model('userlog')->setException($e);

			$result = 0;
			$message = _('Abnormal process, please try again.');
			goto _return;
		}

		/**
		 *  0713 - 執行任務-首次登入
		 */
		if ($result) {
			$user = parent::user_get();
			$user_id = $user['user_id'];
			$task_for = 'firsttime_login';
			$data = model('task')->doTask($task_for, $user_id, 'web');
		}
		_return:

		$redirect = (!empty($_GET['redirect'])) ? urldecode(urldecode($_GET['redirect'])) : $redirect;

		Session::__end();
		redirect_php($redirect);
	}

	function login_facebook()
	{
		Session::__start();

		$result = 1;
		$message = _('Login success.');
		$redirect = null;
		$data = [];
		if (empty($_POST['accessToken'])) {
			$result = 0;
			$message = _('Abnormal process, please try again.');
			goto _return;
		}

		try {
			$fb = new Facebook\Facebook([
				'app_id' => Core::settings('FACEBOOK_APP_ID'),
				'app_secret' => Core::settings('FACEBOOK_APP_SECRET'),
				'default_graph_version' => Core::settings('FACEBOOK_API_VERSION'),
			]);

			$response = $fb->get('/me?fields=birthday,email,gender,id,name', $_POST['accessToken']);//Returns a `Facebook\FacebookResponse` object //2016-03-29 Lion: 以 helper 方式取得的 token 經常性的拋出異常(原因不明), 改從前端接收

			$user = $response->getGraphUser();

			$birthday = $user->getField('birthday')->format('Y-m-d');
			$account = $user->getField('email');
			$gender = $user->getField('gender');
			$way_id = $user->getField('id');
			$name = $user->getField('name');

			Model('user');
			Model('user_facebook');
			Model('userstatistics');
			Model('follow');
			Model('subscription');
			Model('token');
			Model('topic');
			Model()->beginTransaction();

			$param = [
				'account' => $account,
				'name' => $name,
				'gender' => $gender,
				'birthday' => $birthday,
				'way' => 'facebook',
				'way_id' => $way_id,
			];
			list($result1, $message1, $redirect1, $data1) = array_decode_return(Model('user')->register($param));
			if ($result1 != 1) {
				Model()->rollBack();

				$result = $result1;
				$message = $message1;
				goto _return;
			}
			$redirect = $redirect1;

			Model()->commit();

			Model('user')->setSession($data1['id']);
		} catch (Facebook\Exceptions\FacebookResponseException $e) {
			//When Graph returns an error
			//'Graph returned an error: ' . $e->getMessage()
			Model('userlog')->setException($e);

			$result = 0;
			$message = _('Abnormal process, please try again.');
			goto _return;
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			//'Facebook SDK returned an error: ' . $e->getMessage()
			Model('userlog')->setException($e);

			$result = 0;
			$message = _('Abnormal process, please try again.');
			goto _return;
		}

		/**
		 *  0713 - 執行任務-首次登入
		 */
		if ($result) {
			$user = parent::user_get();
			$user_id = $user['user_id'];
			$task_for = 'firsttime_login';
			$data = model('task')->doTask($task_for, $user_id, 'web');
		}
		_return:

		Session::__end();

		json_encode_return($result, $message, $redirect, $data);
	}

	function logout()
	{
		parent::$data['way'] = parent::user_get()['way'];

		Model('user')->logout();

		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function member_nav()
	{
		$user = parent::user_get();

		parent::$data['user'] = Model('user')->where(array(array(array(array('user_id', '=', $user['user_id'])), 'and')))->fetch();

		Core::get_usergrade($user['user_id']);//這行的位置需要在 Model('usergrade') 之上, 因為有 time 的處理

		$m_usergrade = Model('usergrade')->where(array(array(array(array('user_id', '=', $user['user_id'])), 'and')))->fetch();

		parent::$data['usergrade'] = array(
			'grade' => $m_usergrade['grade'],
			'endtime' => $m_usergrade['endtime'],
		);
	}

	function order()
	{
		$user = parent::user_get();
		$m_order = Model('order')->column(['order_id', 'assets', 'total', 'state', 'assets_info', 'inserttime'])
			->where([[[['user_id', '=', $user['user_id']], ['state', '=', 'success']], 'and']])
			->order(['inserttime' => 'desc'])->fetchAll();

		if (!empty($m_order)) {
			//格式化取得的訂單資料
			foreach ($m_order as $k => $v) {
				$m_order[$k]['inserttime'] = date('Y/m/d', strtotime($m_order[$k]['inserttime']));
				$m_order[$k]['total'] = 'NT$' . number_format($m_order[$k]['total']);
				$m_order[$k]['state'] = _('Success');
				$a_assets_info = json_decode($v['assets_info'], true);

				switch ($m_order[$k]['assets']) {
					case 'userpoint':
						$m_order[$k]['assets'] = _('P Point');
						$m_order[$k]['memo'] = '<span style="color:#444">' . _('Product Name:') . '</span><span style="color:#9d9d9d">' . $v['order_id'] . '</span><br>
												<span style="color:#444">' . _('Obtained P points:') . '</span><span style="color:#9d9d9d">' . (isset($a_assets_info['obtain']) ? number_format($a_assets_info['obtain']) : 0) . '</span>';
						break;

					case 'usergrade':
						$m_grade = Model('usergradequeue')->column(array('starttime', 'endtime'))->where(array(array(array(array('order_id', '=', $v['order_id'])), 'and')))->fetch();
						$m_order[$k]['assets'] = _('Grade');
						$m_order[$k]['memo'] = '<span style="color:#444">' . _('Product Name:') . '</span><span style="color:#9d9d9d">' . $v['order_id'] . '</span><br>
												<span style="color:#444">' . _('Level Name:') . '</span><span style="color:#9d9d9d">' . (isset($a_assets_info['assets_item']) ? $a_assets_info['assets_item'] : '') . '</span><br>
												<span style="color:#444">' . _('Validity period') . '：</span><span style="color:#9d9d9d">' . (isset($a_assets_info['obtain']) ? $a_assets_info['obtain'] . _('days') : '') . '</span><br>
												<span style="color:#444">' . _('Validity Period:') . '</span><span style="color:#9d9d9d">' . date('Y/m/d', strtotime($m_grade['starttime'])) . '～' . date('Y/m/d', strtotime($m_grade['endtime'])) . '</span>';
						break;
				}


			}
			parent::$data['order'] = $m_order;
		}

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Purchasing history query'),
			array(_('Purchasing history query'))
		);

		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.core.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.standalone.css'), 'href');
		parent::$html->set_js(static_file('js/footable/js/footable.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.paginate.js'), 'src');
		parent::$view[] = $view;
	}

	function point()
	{
		$user = parent::user_get();
		if (empty($user)) redirect(parent::url('user', 'login', ['redirect' => parent::url('user', 'point')]), _('Please login first.'));

		if (is_ajax()) {
			if ($user == null) {
				json_encode_return(2, _('Please login first.'), parent::url('user', 'login', array('redirect' => parent::url('user', 'point'))));
			}

			$buy_id = !empty($_POST['buy_id']) ? $_POST['buy_id'] : null;
			$assets = !empty($_POST['assets']) ? $_POST['assets'] : null;
			$assets_item = !empty($_POST['assets_item']) ? $_POST['assets_item'] : null;
			$total = !empty($_POST['total']) ? $_POST['total'] : null;
			$currency = !empty($_POST['currency']) ? $_POST['currency'] : null;
			$obtain = !empty($_POST['obtain']) ? $_POST['obtain'] : null;
			$redirectAlbumId = !empty($_POST['redirectAlbumId']) ? $_POST['redirectAlbumId'] : null;
			if ($buy_id == null || $assets == null || $assets_item == null || $total == null || $currency == null || $obtain == null) {
				json_encode_return(0, _('Abnormal process, please try again.'));
			}

			//檢查是否存在於 buy
			$m_buy = Model('buy')->where(array(array(array(array('buy_id', '=', $buy_id), array('act', '=', 'open')), 'and')))->fetch();
			if (empty($m_buy) || $m_buy['platform'] != 'web' || $m_buy['assets'] != $assets || $m_buy['assets_item'] != $assets_item || $m_buy['total'] != $total || $m_buy['currency'] != $currency || $m_buy['obtain'] != $obtain) {
				json_encode_return(0, _('Abnormal process, please try again.'));
			}

			/* 9/17 改用pay2go(智付寶) */
			$cashflow_id = 'pay2go';

			Model('order');
			Model()->beginTransaction();
			$tmp0 = array(
				'cashflow_id' => $cashflow_id,
				'user_id' => $user['user_id'],
				'platform' => 'web',
				'assets' => $assets,
				'assets_info' => json_encode(array('obtain' => $obtain)),
				'total' => $total,
				'currency' => $currency,
				'state' => 'pretreat',
				'fulfill' => 'pretreat',
				'remote_ip' => remote_ip(),
				'inserttime' => inserttime(),
			);
			$order_id = Model('order')->add($tmp0);
			if (!$order_id) {
				Model()->rollBack();
				json_encode_return(0, _('[Order] occurs exception, please contact us.'));
			}

			//金流
			$tmp0 = array(
				'order_id' => $order_id,
				'total' => $total,
				'buy' => array(
					'buy_id' => $buy_id,
					'currency' => $currency,
					'name' => 'pinpinbox - P Point',
				),
				'user' => array(
					'email' => $user['email'],
					'user_id' => $user['user_id']
				),
				'assets_info' => $assets . '-' . $obtain,
				'redirectAlbumId' => $redirectAlbumId,
			);
			list($result, $message, $redirect, $data) = array_decode_return(Core::extension('cashflow', $cashflow_id)->index($tmp0));

			if ($result) {
				Model()->commit();
				json_encode_return(1, null, null, $data);
			} else {
				Model()->rollBack();
				json_encode_return(0, $message);
			}
		}

		parent::$data['point'] = number_format(Core::get_userpoint($user['user_id'], 'web') + Core::get_userpoint($user['user_id'], 'web', 'point_free'));

		$where = array(
			array(array(array('platform', '=', 'web'), array('assets', '=', 'userpoint'), array('assets_item', '=', 'point'), array('currency', '=', 'TWD'), array('act', '=', 'open')), 'and'),
		);
		$m_buy = Model('buy')->where($where)->order(array('sequence' => 'asc'))->fetchAll();
		$a_buy = array();
		foreach ($m_buy as $v0) {
			$a_buy[] = array(
				'buy_id' => $v0['buy_id'],
				'assets' => $v0['assets'],
				'assets_item' => $v0['assets_item'],
				'total' => number_format($v0['total']),
				'currency' => $v0['currency'],
				'obtain' => number_format($v0['obtain']),
			);
		}
		parent::$data['buy'] = $a_buy;

		//Mars 161124 : 若由album::content呼叫的儲值註冊視窗, 則帶入此參數
		parent::$data['redirectAlbumId'] = isset($_GET['album_id']) ? $_GET['album_id'] : false;

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Buy P Points'),
			array(_('Buy P Points'))
		);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->jbox();
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function password()
	{
		if (is_ajax()) {
			$result = 1;
			$message = _('Your passwords modify completed.') . '<br>' . _('You may use your new password to log in next time.');
			$redirect = parent::url('user', 'login');

			$user = parent::user_get();

			if (empty($user)) {
				$result = 2;
				$message = _('Please login first.');
				$redirect = parent::url('user', 'login', ['redirect' => parent::url('user', 'settings')]);
				goto _return;
			}

			$old_pass = isset($_POST['old_pass']) ? $_POST['old_pass'] : null;
			$new_pass = isset($_POST['new_pass']) ? $_POST['new_pass'] : null;
			$new_pass_check = isset($_POST['new_pass_check']) ? $_POST['new_pass_check'] : null;

			if ($old_pass === null || $new_pass === null || $new_pass_check === null) {
				$result = 0;
				$message = _('Please enter Password.');
				$redirect = null;
				goto _return;
			} elseif (strlen($new_pass) < 8 || strlen($new_pass) > 16) {
				$result = 0;
				$message = _('密碼需輸入8至16個位元(英/數).');
				$redirect = null;
				goto _return;
			} elseif ($new_pass != $new_pass_check) {
				$result = 0;
				$message = _('The password that you entered twice do not match.');
				$redirect = null;
				goto _return;
			}

			list($result0, $message0, $redirect0) = array_decode_return(Model('user')->updatePassword($user['user_id'], $old_pass, $new_pass));
			if ($result0 != 1) {
				$result = $result0;
				$message = $message0;
				$redirect = null;
				goto _return;
			}

			_return:
			json_encode_return($result, $message, $redirect);
		}

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Password changed'),
			[_('Password changed')]
		);

		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		$this->member_nav();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function settings_phone()
	{
		if (is_ajax()) {
			$usefor = (!empty($_POST['usefor'])) ? $_POST['usefor'] : null;

			if ($usefor == null || !in_array($usefor, ['sms_send', 'editcellphone'])) json_encode_return(0, _('[Request] occur exception, please contact us.'), null, 'Modal');

			$user = (new \userModel)->getSession();

			$cellphone = (isset($_POST['cellphone']) && trim($_POST['cellphone']) !== '') ? trim($_POST['cellphone']) : null;

			switch ($usefor) {
				//發送驗證簡訊
				case 'sms_send' :
					list ($result, $message) = array_decode_return(\smspasswordModel::ableToRequestSMSPasswordForUpdateCellphone($user['user_id'], $cellphone));
					if ($result != \Lib\Result::SYSTEM_OK) {
						json_encode_return(0, $message);
					}

					\smspasswordModel::requestSMSPasswordForUpdateCellphone($user['user_id'], $cellphone);

					json_encode_return(1, _('Validation code has been sent.'));
					break;

				case 'editcellphone':
					$smspassword = (isset($_POST['smspassword']) && trim($_POST['smspassword']) !== '') ? trim($_POST['smspassword']) : null;

					list ($result, $message) = array_decode_return(\userModel::ableToUpdateCellphone($user['user_id'], $cellphone, $smspassword));
					if ($result != \Lib\Result::SYSTEM_OK) {
						json_encode_return(0, $message);
					}

					(new Model)->beginTransaction();

					\userModel::updateCellphone($user['user_id'], $cellphone);

					(new Model)->commit();

					json_encode_return(1, _('驗證成功，手機號碼更新完成'), parent::url('user', 'settings'));
					break;
			}

			json_encode_return(0, _('[Request] occur exception, please contact us.'), null, 'Modal');
		}
	}

	function question()
	{
		if (is_ajax()) {
			$user = parent::user_get();
			$question_name_id = (!empty($_POST['question_name_id'])) ? $_POST['question_name_id'] : null;
			$question_content = (!empty($_POST['question_content'])) ? $_POST['question_content'] : null;
			$captcha = (isset($_POST['captcha']) && !empty($_POST['captcha'])) ? $_POST['captcha'] : null;
			if (Session::get('captcha') != $captcha) {
				json_encode_return(0, _('Slider validate fail!'));
			}

			if ($question_name_id === null || $question_name_id == 'no') {
				json_encode_return(0, _('Please select a question intent.'));
			} elseif ($question_content === null) {
				json_encode_return(0, _('Please enter your question.'));
			}

			$m_question = Model('question')->where(array(array(array(array('question_id', '=', $question_name_id)), 'and')))->fetch();

			$add = array(
				'question_id' => $question_name_id,
				'user_id' => $user['user_id'],
				'question' => $question_content,
				'inserttime' => inserttime(),
			);

			Model('question_user')->add($add);

			$m_admin = Model('admin')->column(array('email'))->where(array(array(array(array('admin_id', 'in', json_decode($m_question['feedback'], true))), 'and')))->fetchAll();
			$a_email = array();
			foreach ($m_admin as $k => $v) {
				$a_email[] = $v['email'];
			}
			if (!empty($a_email)) {
				$tmp1 = array();
				$tmp1[] = _('Intended question') . '：' . $m_question['name'];
				$tmp1[] = _('Content') . '：' . $question_content;
				$tmp1[] = _('Questioner') . '：' . $user['name'];
				$body = implode('<br>', $tmp1);
				email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $a_email, _('pinpinbox - question report(feedback)'), $body);
			}

			json_encode_return(1, _('Success'), parent::url('user', 'question_record'));
		}

		//取得問題意向
		$m_question = Model('question')->column(array('question_id', 'name'))->where(array(array(array(array('act', '=', 'open')), 'and')))->fetchAll();
		$a_question = array();
		foreach ($m_question as $k => $v) {
			$a_question[$k]['question_id'] = $v['question_id'];
			$a_question[$k]['name'] = \Core\Lang::i18n($v['name']);
		}

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Feedback system'),
			array(_('Feedback system'))
		);
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		parent::$data['question'] = $a_question;
		parent::$data['max'] = Session::set('captcha', rand(1, 100));
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui-1.10.4.custom.min.css'), 'href');
		parent::$html->set_js(static_file('js/jquery-ui-1.10.4.custom.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.ui.touch-punch.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function question_record()
	{
		$user = parent::user_get();

		$m_question_user = Model('question_user')->order(array('inserttime' => 'desc'))->where(array(array(array(array('user_id', '=', $user['user_id'])), 'and')))->fetchAll();
		$m_question = Model('question')->column(array('question_id', 'name'))->fetchAll();
		$question_list = array();

		foreach ($m_question_user as $k => $v) {
			$question_list[$k] = array('inserttime' => date('Y/m/d', strtotime($v['inserttime'])));
			$question_list[$k]['question_user_id'] = $v['question_user_id'];
			foreach ($m_question as $k2 => $v2) {
				if ($v['question_id'] == $v2['question_id']) $question_list[$k]['name'] = \Core\Lang::i18n($v2['name']);
			}
			$question_list[$k]['content'] = nl2br(htmlspecialchars($v['question']));
			$question_list[$k]['answer'] = nl2br(htmlspecialchars($v['answer']));
			//若未有回覆內容不填入回覆日期
			$question_list[$k]['modifytime'] = (!empty($v['answer'])) ? _('Reply date：') . date('Y/m/d', strtotime($v['modifytime'])) : null;
		}
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Feedback records'),
			array(_('Feedback records'))
		);
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$data['question_list'] = $question_list;
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.core.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.standalone.css'), 'href');
		parent::$html->set_js(static_file('js/footable/js/footable.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.paginate.js'), 'src');
		parent::$view[] = $view;
	}

	function register()
	{
		if (is_ajax()) {
			$result = 1;
			$message = null;
			$redirect = null;
			$data = null;

			$account = isset($_POST['account']) ? $_POST['account'] : null;
			$password = isset($_POST['password']) ? $_POST['password'] : null;
			$repassword = isset($_POST['repassword']) ? $_POST['repassword'] : null;
			$name = isset($_POST['name']) ? $_POST['name'] : null;
			$cellphone = isset($_POST['cellphone']) ? $_POST['cellphone'] : null;
			$smspassword = isset($_POST['smspwd']) ? $_POST['smspwd'] : null;
			$newsletter = isset($_POST['newsletter']) ? $_POST['newsletter'] : null;

			if ($account === null || $password === null || $repassword === null || $name === null || $cellphone === null) {
				$result = 0;
				$message = _('Please enter your data.');

				goto _return;
			} elseif ($smspassword === null) {
				$result = 0;
				$message = _('Please enter SMS-password.');

				goto _return;
			} elseif ($password != $repassword) {
				$result = 0;
				$message = _('The password that you entered twice do not match.');

				goto _return;
			}

			$param = [
				'account' => $account,
				'password' => $password,
				'name' => $name,
				'cellphone' => $cellphone,
				'way' => 'none',
				'smspassword' => $smspassword,
				'newsletter' => $newsletter,
			];

			list ($result, $message, $redirect) = array_decode_return((new \userModel)->ableToRegister($param));
			if ($result != 1) {
				goto _return;
			}

			(new Model)->beginTransaction();

			$data_1 = (new userModel)->register_v2($param);

			(new Model)->commit();

			/**
			 *  0707 - 執行任務-登入贈點(註冊完成)
			 */
			$data = (new taskModel)->doTask('firsttime_login', $data_1['id'], 'web');

			_return:
			json_encode_return($result, $message, $redirect, $data);
		}

		//seo
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Register'),
			[_('Register')]
		);

		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('js/intl-tel-input-master/css/intlTelInput.css'), 'href');
		parent::$html->set_js(static_file('js/intl-tel-input-master/js/intlTelInput.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function resend_sms()
	{
		if (is_ajax()) {
			$account = empty($_POST['account']) ? null : $_POST['account'];
			$cellphone = empty($_POST['cellphone']) ? null : $_POST['cellphone'];

			//檢查是否已有驗證密碼的資料
			$m_smspassword = Model('smspassword')->where(array(array(array(array('user_account', '=', $account), array('user_cellphone', '=', \Core\I18N::cellphone($cellphone))), 'and')))->fetch();

			if (empty($m_smspassword)) {
				$return = _('您尚未完成第一次寄送驗證碼程序, 請點擊"寄送驗證碼"');
				json_encode_return(0, $return, null, 'Modal');
			} else {
				//sms
				$message = 'pinpinbox SMS password : ' . $m_smspassword['smspassword'];
				list($sms_result, $sms_message) = array_decode_return(Core::extension('sms', 'every8d')->send($cellphone, $message));

				if (!$sms_result) {
					json_encode_return(0, _('[SMS] occur exception, please contact us.'), null, 'Modal');
				}
				json_encode_return(1, _('Validation code has been sent.'), null, 'Modal');
			}
		}
	}

	function redirect_to_diy() {
	    $user = parent::user_get();
        $redirect = parent::url();

        list($result, $message, $redirect, $album_id) = array_decode_return((new \albumModel)->process2($user['user_id']));
        if ($result) {
            $redirect = parent::url('diy', 'index', ['album_id' => $album_id]);
            goto _return;
        }

        list($result, $message, $redirect, $album_id) = array_decode_return((new \albumModel)->pretreat($user['user_id'], 0));
        if ($result) {
            $redirect = parent::url('diy', 'index', ['album_id' => $album_id]);
            goto _return;
        }

        _return :
        redirect_php($redirect);
    }

	function sale_album()
	{
		$user = parent::user_get();
		$interval = (!empty($_GET['interval'])) ? $_GET['interval'] : 0;    //日期區間
		$info = array();                                                    //資訊
		$info['option'] = $interval;                                        //區間選項
		$info['unsettled_pointsplit'] = 0;
		$info['sum_pointsplit'] = 0;

		/**
		 *  轉換日期區間
		 */
		switch ($interval) {
			case 1:
				(!empty($_POST['start']))
					? $start_time = mb_substr($_POST['start'], 0, 4) . '-' . mb_substr($_POST['start'], 5, 2, 'utf-8') . '-' . mb_substr($_POST['start'], 8, 2, 'utf-8')
					: $start_time = base64_decode($_GET['starttime']);

				(!empty($_POST['end']))
					? $end_time = mb_substr($_POST['end'], 0, 4) . '-' . mb_substr($_POST['end'], 5, 2, 'utf-8') . '-' . mb_substr($_POST['end'], 8, 2, 'utf-8')
					: $end_time = base64_decode($_GET['endtime']);

				$time_interval = array($start_time, date('Y-m-d', strtotime($end_time . "+1 days")));

				$info['timeinterval'] = $start_time . '~' . $end_time;
				$info['start_time'] = base64_encode($start_time);
				$info['end_time'] = base64_encode($end_time);

				break;

			case 3:
				$time_interval = array(date("Y-m-d", strtotime("-3 month")), date("Y-m-d", strtotime("+1 days")));
				$info['timeinterval'] = date("Y-m-d", strtotime("-3 month")) . '~' . date("Y-m-d");
				$info['start_time'] = base64_encode(date("Y-m-d", strtotime("-3 month")));
				$info['end_time'] = base64_encode(date("Y-m-d"));
				break;

			default:
				$time_interval = array(date("Y-m-d", strtotime("-1 month")), date("Y-m-d", strtotime("+1 days")));
				$info['timeinterval'] = date("Y-m-d", strtotime("-1 month")) . '~' . date("Y-m-d");
				$info['start_time'] = base64_encode(date("Y-m-d", strtotime("-1 month")));
				$info['end_time'] = base64_encode(date("Y-m-d"));
				break;
		}

		/**
		 *  User 要引入的所有作品 從function::album_id_pool拿
		 *  album可能因先販售再關閉造成收益統計錯誤，故搜尋作品時先搜出除delete外的全部作品
		 */
		$all_album_id = $this::album_id_pool($user['user_id'], $time_interval);

		//符合條件的作品數大於0本才進行資料蒐集
		if (count($all_album_id) > 0) {
			/**
			 *  頁面預載作品 含Ias要求引入的作品 因做limit處理故無法一次引入所有作品
			 *  album可能因先販售再關閉造成收益統計錯誤，故搜尋作品時搜出除delete外的全部作品
			 */
			$page = (!empty($_GET['page'])) ? $_GET['page'] : 1;
			$start_page = ($page - 1) * 4;
			$page_range = $start_page . ',4';

			$column = [
				'album.cover',
				'album.point',
				'album.name',
				'album.album_id',
				'album.title',
				'album.description',
				'albumstatistics.count',
				'albumstatistics.viewed',
				'categoryarea_category.categoryarea_id',
				'user.user_id',
				'user.name user_name',
			];
			$where = [[[['album.act', '!=', 'delete'], ['album.state', '=', 'success'], ['album.zipped', '=', 1], ['album.album_id', 'in', $all_album_id]], 'and']];//2017-11-13 Lion: 由於經紀制度，產生收益的作品可能不是由自己帳號建立，故不向 album.user_id 進行撈取
			$join = [
				['LEFT JOIN', 'albumstatistics', 'USING(album_id)'],
				['INNER JOIN', 'user', 'ON user.user_id = album.user_id'],
				['LEFT JOIN', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
			];

			$m_album = (new \albumModel)
				->order(['albumstatistics.count' => 'desc'])
				->join($join)
				->limit($page_range)
				->column($column)
				->where($where)
				->fetchAll();

			$c_album = (new \albumModel)
				->join($join)
				->column(['count(1)'])
				->where($where)
				->fetchColumn();

			$a_album = [];

			foreach ($m_album as $k0 => $v0) {
				$a_album[$k0] = [
					'type' => 'album',
					'album' => [
						'album_id' => $v0['album_id'],
						'cover_url' => parent::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id'], 'click' => 'cover']),
						'name_url' => parent::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id'], 'click' => 'name']),
						'user_id' => $v0['user_id'],
						'name' => htmlspecialchars($v0['name']),
						'title' => htmlspecialchars($v0['title']),
						'point' => $v0['point'],
						'description' => strip_tags(nl2br(($v0['description']))),
						'cover' => URL_UPLOAD . $v0['cover'],
						'viewed' => $v0['viewed'],
					],
					'info' => [
						'count' => $v0['count'],
						'sum' => 0,
						'unsettled' => 0,
					],
					'user' => [
						'name' => $v0['user_name'],
						'url' => Core::get_creative_url($v0['user_id']),
						'picture' => URL_STORAGE . Core::get_userpicture($v0['user_id']),
					],
				];

				$albumqueue_pool[] = $v0['album_id'];
			}

			/**
			 *  個別相簿總收益
			 */
			$m_exchange_sum = [];

			if (count($all_album_id) > 0) {
				$m_exchange_sum = (new \exchangeModel)
					->column([
						'exchange.id',
						'SUM(split.point) AS sum',
					])
					->join([
						[
							'INNER JOIN',
							'split',
							\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
								'ON split.exchange_id = exchange.exchange_id'
								:
								'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
						]
					])
					->where([[[['exchange.type', '=', 'album'], ['exchange.id', 'IN', $all_album_id], ['exchange.inserttime', 'BETWEEN', $time_interval]], 'and']])
					->group(['exchange.id'])
					->fetchAll();

				foreach ($a_album as $k0 => $v0) {
					foreach ($m_exchange_sum as $v2) {
						if ($v0['album']['album_id'] == $v2['id']) {
							$a_album[$k0]['info']['sum'] = $v2['sum'];
							break;
						}
					}
				}
			}

			/**
			 *  個別相簿未結算收益
			 */
			$m_exchange_unsettled = [];

			if (count($all_album_id) > 0) {
				$m_exchange_unsettled = (new \exchangeModel)
					->column([
						'exchange.id',
						'SUM(split.point) AS unsettled',
					])
					->join([
						[
							'INNER JOIN',
							'split',
							\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
								'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL'
								:
								'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
						]
					])
					->where([[[['exchange.type', '=', 'album'], ['exchange.id', 'IN', $all_album_id], ['exchange.inserttime', 'BETWEEN', $time_interval]], 'and']])
					->group(['exchange.id'])
					->fetchAll();

				foreach ($a_album as $k0 => $v0) {
					foreach ($m_exchange_unsettled as $v2) {
						if ($v0['album']['album_id'] == $v2['id']) {
							$a_album[$k0]['info']['unsettled'] = $v2['unsettled'];
							break;
						}
					}
				}
			}

			/**
			 *  總收益
			 */
			foreach ($m_exchange_sum as $t0 => $v0) {
				$info['sum_pointsplit'] += $v0['sum'];
			}

			/**
			 *  未結算收益
			 */
			foreach ($m_exchange_unsettled as $t0 => $v0) {
				$info['unsettled_pointsplit'] += $v0['unsettled'];
			}

			//more
			$num_of_item = $c_album;
			$num_of_max_page = ceil($num_of_item / 4);
			$num_of_now_page = (1 <= $page && $page <= $num_of_max_page) ? $page : 1;
			$more = ($page >= $num_of_max_page) ? null : parent::url('user', 'sale_album', ['interval' => $interval, 'starttime' => $info['start_time'], '$endtime' => $info['end_time']]);
		}
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Profit settlement') . ' | ' . _('Sponsored albums'),
			array(_('Sponsored albums'), _('Profit settlement'))
		);

		/**
		 *  拆分比[album]
		 */
		$businessuserModel = (new \businessuser\Model())
			->column([
				'businessuser.mode',
				'businessuser.user_id',
			])
			->join([
				['INNER JOIN', 'user', 'ON user.businessuser_id = businessuser.businessuser_id']
			])
			->where([[[['user.user_id', '=', $user['user_id']]], 'and']])
			->fetch();

		switch ($businessuserModel['mode']) {
			case 'company':
				$info['pointsplitrate_album'] = _('依所屬經紀公司規定');
				break;

			case 'personal':
				$info['pointsplitrate_album'] = (\Model\split::getRatioForBusinessuserOfPersonalOfHimself($user['user_id'], 'album') * 100) . '%';
				break;

			default:
				$info['pointsplitrate_album'] = (\Model\split::getRatioForUser($user['user_id'], 'album') * 100) . '%';
				break;
		}

		parent::$data['more'] = (!empty($more)) ? $more : null;
		parent::$data['album'] = (!empty($a_album)) ? $a_album : null;
		parent::$data['info'] = $info;
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/datepicker/css/bootstrap-datepicker.min.css'), 'href');
		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.zh-TW.js'), 'src');
		parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');

		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function sale_template()
	{
		$user = parent::user_get();
		$interval = (!empty($_GET['interval'])) ? $_GET['interval'] : 0;    //日期區間
		$info = array();                                                    //資訊
		$info['option'] = $interval;                                        //區間選項

		/**
		 *  User 要引入的所有版型 從function::template_id_pool拿
		 *  同作品條件，搜尋時先搜出除delete外的全部版型
		 */
		$all_template_id = $this::template_id_pool($user['user_id']);

		/**
		 *  頁面預載版型 含Ias要求引入的版型 因做limit處理故無法一次引入所有版型
		 *  template可能因先販售再關閉造成收益統計錯誤，故搜尋時搜出除delete外的全部版型
		 */
		$page = (!empty($_GET['page'])) ? $_GET['page'] : 1;
		$start_page = ($page - 1) * 4;
		$page_range = $start_page . ',4';

		$join = [
			['left join', 'templatestatistics', 'using(template_id)'],
			['INNER JOIN', 'user', 'ON user.user_id = template.user_id'],
		];
		$where = [[[['template.user_id', '=', $user['user_id']], ['template.act', '!=', 'delete'], ['template.state', '=', 'success']], 'and']];

		$m_template = (new \templateModel)
			->order(['templatestatistics.count' => 'desc'])
			->join($join)
			->limit($page_range)
			->column([
				'template.image',
				'template.point',
				'template.name',
				'template.description',
				'template.template_id',
				'template.user_id',
				'templatestatistics.count',
				'templatestatistics.viewed',
				'user.name user_name',
			])
			->where($where)
			->fetchAll();

		$c_template = (new \templateModel)
			->column(['count(1)'])
			->join($join)
			->where($where)
			->fetchColumn();

		$a_template = [];

		foreach ($m_template as $k0 => $v0) {
			$a_template[$k0] = array(
				'type' => 'template',
				'template' => array(
					'template_id' => $v0['template_id'],
					'cover_url' => parent::url('template', 'content', ['template_id' => $v0['template_id'], 'click' => 'cover']),
					'name_url' => parent::url('template', 'content', ['template_id' => $v0['template_id'], 'click' => 'name']),
					'user_id' => $v0['user_id'],
					'name' => htmlspecialchars($v0['name']),
					'description' => htmlspecialchars($v0['description']),
					'point' => $v0['point'],
					'image' => URL_UPLOAD . getimageresize(M_PACKAGE . $v0['image'], 160, 240),
					'viewed' => $v0['viewed'],
				),
				'info' => array(
					'count' => $v0['count'],
					'sum' => 0,
					'unsettled' => 0,
				),
				'user' => array(
					'name' => $v0['user_name'],
					'url' => Core::get_creative_url($v0['user_id']),
					'picture' => URL_STORAGE . Core::get_userpicture($v0['user_id']),
				),
			);
		}

		/**
		 *  轉換日期區間
		 */
		switch ($interval) {
			case 1:
				(!empty($_POST['start']))
					? $start_time = mb_substr($_POST['start'], 0, 4) . '-' . mb_substr($_POST['start'], 5, 2, 'utf-8') . '-' . mb_substr($_POST['start'], 8, 2, 'utf-8')
					: $start_time = base64_decode($_GET['starttime']);

				(!empty($_POST['end']))
					? $end_time = mb_substr($_POST['end'], 0, 4) . '-' . mb_substr($_POST['end'], 5, 2, 'utf-8') . '-' . mb_substr($_POST['end'], 8, 2, 'utf-8')
					: $end_time = base64_decode($_GET['endtime']);

				$time_interval = array($start_time, date('Y-m-d', strtotime($end_time . "+1 days")));

				$info['timeinterval'] = $start_time . '~' . $end_time;
				$info['start_time'] = base64_encode($start_time);
				$info['end_time'] = base64_encode($end_time);

				break;

			case 3:
				$time_interval = array(date("Y-m-d", strtotime("-3 month")), date("Y-m-d", strtotime("+1 days")));
				$info['timeinterval'] = date("Y-m-d", strtotime("-3 month")) . '~' . date("Y-m-d");
				$info['start_time'] = base64_encode(date("Y-m-d", strtotime("-3 month")));
				$info['end_time'] = base64_encode(date("Y-m-d"));
				break;

			default:
				$time_interval = array(date("Y-m-d", strtotime("-1 month")), date("Y-m-d", strtotime("+1 days")));
				$info['timeinterval'] = date("Y-m-d", strtotime("-1 month")) . '~' . date("Y-m-d");
				$info['start_time'] = base64_encode(date("Y-m-d", strtotime("-1 month")));
				$info['end_time'] = base64_encode(date("Y-m-d"));
				break;
		}

		/**
		 *  個別版型總收益
		 */
		$m_exchange_sum = [];

		if (count($all_template_id) > 0) {
			$m_exchange_sum = (new \exchangeModel)
				->column([
					'exchange.id',
					'SUM(split.point) AS sum',
				])
				->join([
					[
						'INNER JOIN',
						'split',
						\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
							'ON split.exchange_id = exchange.exchange_id'
							:
							'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
					]
				])
				->where([[[['exchange.type', '=', 'template'], ['exchange.id', 'IN', $all_template_id], ['exchange.inserttime', 'BETWEEN', $time_interval]], 'and']])
				->group(['exchange.id'])
				->fetchAll();

			foreach ($a_template as $v) {
				foreach ($m_exchange_sum as $v2) {
					if ($v['template']['template_id'] == $v2['id']) {
						$v['info']['sum'] = $v2['sum'];
						break;
					}
				}
			}
		}

		/**
		 *  個別版型未結算收益
		 */
		$m_exchange_unsettled = [];

		if (count($all_template_id) > 0) {
			$m_exchange_unsettled = (new \exchangeModel)
				->column([
					'exchange.id',
					'SUM(split.point) AS unsettled',
				])
				->join([
					[
						'INNER JOIN',
						'split',
						\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
							'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL'
							:
							'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
					]
				])
				->where([[[['exchange.type', '=', 'template'], ['exchange.id', 'IN', $all_template_id], ['exchange.inserttime', 'BETWEEN', $time_interval]], 'and']])
				->group(['exchange.id'])
				->fetchAll();

			foreach ($a_template as $v) {
				foreach ($m_exchange_unsettled as $v2) {
					if ($v['template']['template_id'] == $v2['id']) {
						$v['info']['unsettled'] = $v2['unsettled'];
						break;
					}
				}
			}
		}

		/**
		 *  總收益
		 */
		$info['sum_pointsplit'] = 0;
		foreach ($m_exchange_sum as $t0 => $v0) {
			$info['sum_pointsplit'] += $v0['sum'];
		}

		/**
		 *  未結算收益
		 */
		$info['unsettled_pointsplit'] = 0;
		foreach ($m_exchange_unsettled as $t0 => $v0) {
			$info['unsettled_pointsplit'] += $v0['unsettled'];
		}

		/**
		 *  拆分比[album]
		 */
		$info['pointsplitrate_template'] = (\Model\split::getRatioForUser($user['user_id'], 'template') * 100) . '%';

		//more
		$num_of_item = $c_template;
		$num_of_max_page = ceil($num_of_item / 4);
		$more = ($page >= $num_of_max_page) ? null : parent::url('user', 'sale_template', ['interval' => $interval, 'starttime' => $info['start_time'], '$endtime' => $info['end_time']]);
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Profit settlement') . ' | ' . _('Sponsored style'),
			array(_('Sponsored style'), _('Profit settlement'))
		);
		parent::$data['more'] = $more;
		parent::$data['template'] = $a_template;
		parent::$data['info'] = $info;
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/datepicker/css/bootstrap-datepicker.min.css'), 'href');
		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.zh-TW.js'), 'src');
		parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function salechart()
	{
		$user = parent::user_get();
		$album_id = (!empty($_GET['album_id'])) ? $_GET['album_id'] : null;
		$template_id = (!empty($_GET['template_id'])) ? $_GET['template_id'] : null;
		$start_time = (!empty($_GET['start_time'])) ? mb_substr(base64_decode($_GET['start_time']), 0, 10) : null;
		$end_time = (!empty($_GET['end_time'])) ? mb_substr(base64_decode($_GET['end_time']), 0, 10) : null;
		$info = array();

		//圖型資訊
		if (!empty($_GET['album_id'])) {
			$info['referer'] = 'sale_album';
			$info['title'] = _('Album name');
			$chart_type = 'album';
			$chart_id = $album_id;
		};

		if (!empty($_GET['template_id'])) {
			$info['referer'] = 'sale_template';
			$info['title'] = _('Name of your Template');
			$chart_type = 'template';
			$chart_id = $template_id;
		}

		if ((empty($album_id) && empty($template_id)) || empty($start_time) || empty($end_time)) {
			redirect(parent::url('user', $info['referer']), _('Abnormal process, please try again.'));
		}

		$m_chart = Model($chart_type)
			->where([[[[$chart_type . '_id', '=', $chart_id]], 'and']])
			->fetch();

		//找不到[相簿.版型]或開啟非登入者[相簿.版型]
		if (empty($m_chart) || ($user['user_id'] != $m_chart['user_id'])) {
			$array_object_id = [];

			switch ($chart_type) {
				case 'album':
					$array_object_id = $this->album_id_pool($user['user_id'], [$start_time, $end_time]);
					break;

				case 'template':
					$array_object_id = $this->template_id_pool($user['user_id']);
					break;
			}

			if (!in_array($chart_id, $array_object_id)) {
				redirect_php(parent::url('user', $info['referer']));
			}
		}

		$info['chart_name'] = $m_chart['name'];

		//期間天數
		$info['days'] = $days = round((strtotime($end_time) - strtotime($start_time)) / 3600 / 24);
		$all_day = array();                //逐日
		$total_day_split = array();        //創作者小計
		$album_day_split = array();        //作品小計
		$info['time_interval'] = array($start_time, $end_time);
		$time_interval = array($start_time, date('Y-m-d', strtotime($end_time . "+1 days")));

		/**
		 *  相簿逐天收益
		 */
		$m_exchange_sum = (new exchangeModel)
			->column([
				'SUM(split.point) AS sum',
				'exchange.id',
				'DATE_FORMAT(exchange.inserttime, \'%Y-%m-%d\') AS day',
			])
			->join([
				[
					'INNER JOIN',
					'split',
					\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
						'ON split.exchange_id = exchange.exchange_id'
						:
						'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
				]
			])
			->where([[[['exchange.type', '=', $chart_type], ['exchange.id', '=', $chart_id], ['exchange.inserttime', 'between', $time_interval]], 'and']])
			->group(['DATE_FORMAT(exchange.inserttime, \'%Y-%m-%d\')'])
			->fetchAll();

		/**
		 *  會員逐天收益[全部作品][全部版型]
		 *  User 要引入的所有作品 從function::album_id_pool拿
		 *  User 要引入的所有版型 從function::template_id_pool拿
		 */
		switch ($chart_type) {
			case 'album':
				$all_id = $this::album_id_pool($user['user_id']);
				break;

			case 'template':
				$all_id = $this::template_id_pool($user['user_id']);
				break;

			default:
				break;
		}

		$m_exchange_day_sum = [];

		if (count($all_id) > 0) {
			$m_exchange_day_sum = (new exchangeModel)
				->column([
					'SUM(split.point) AS sum',
					'exchange.id',
					'DATE_FORMAT(exchange.inserttime, \'%Y-%m-%d\') AS day',
				])
				->join([
					[
						'INNER JOIN',
						'split',
						\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
							'ON split.exchange_id = exchange.exchange_id'
							:
							'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
					]
				])
				->where([[[['exchange.type', '=', $chart_type], ['exchange.id', 'in', $all_id], ['exchange.inserttime', 'between', $time_interval]], 'and']])
				->group(['DATE_FORMAT(exchange.inserttime, \'%Y-%m-%d\')'])
				->fetchAll();
		}

		/**
		 *  處理每一天的相簿收益及總收益
		 */
		for ($i = 0; $i <= $days; $i++) {
			$all_day[$i] = date('Y-m-d', strtotime($start_time . '+' . $i . 'days'));
			$chart_day_split[$i] = 0;
			foreach ($m_exchange_sum as $k => $v) {
				if ($all_day[$i] == $v['day']) {
					$chart_day_split[$i] = $v['sum'];
				}
			}

			$total_day_split[$i] = 0;
			foreach ($m_exchange_day_sum as $k2 => $v2) {
				if ($all_day[$i] == $v2['day']) {
					$total_day_split[$i] = $v2['sum'];
				}
			}
			$all_day[$i] = '\'' . date('m/d', strtotime($all_day[$i])) . '\'';
		}

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Earnings string diagram'),
			array(_('Earnings string diagram'))
		);
		parent::$data['info'] = $info;
		parent::$data['all_day'] = implode(',', $all_day);
		parent::$data['chart_day_split'] = implode(',', $chart_day_split);
		parent::$data['total_day_split'] = implode(',', $total_day_split);

		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/datepicker/css/bootstrap-datepicker.min.css'), 'href');
		parent::$html->set_css(static_file('js/jquery.ias/css/jquery.ias.css'), 'href');

		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.zh-TW.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.ias/js/jquery-ias.min.js'), 'src');
		parent::$html->set_js(static_file('js/highcharts.js'), 'src');
		parent::$html->set_js(static_file('js/exporting.js'), 'src');
		parent::$html->set_js(static_file('js/grid-light.js'), 'src');

		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function save_album()
	{
		if (is_ajax()) {
			$user = parent::user_get();
			$album_id = isset($_POST['album_id']) ? $_POST['album_id'] : null;
			if ($album_id == null) json_encode_return(0, _('Abnormal process, please try again.'));

			if (!Model('album')->save($album_id)) {
				json_encode_return(0, _('Abnormal process, please try again.') . '[Wrong album id]');
			}
			json_encode_return(1, _('儲存完成, 請繼續編輯此作品'));
		}
	}

	function send_smspwd()
	{
		if (is_ajax()) {
			$account = empty($_POST['account']) ? null : $_POST['account'];
			$password = empty($_POST['password']) ? null : $_POST['password'];
			$repassword = empty($_POST['repassword']) ? null : $_POST['repassword'];
			$cellphone = empty($_POST['cellphone']) ? null : $_POST['cellphone'];
			if ($account === null || $password === null || $repassword === null || $cellphone === null) {
				json_encode_return(0, _('Please enter your data.'));
			} elseif (strlen($repassword) < 8 || strlen($repassword) > 16) {
				json_encode_return(0, _('密碼需輸入8至16個位元(英/數).'));
			} elseif ($password != $repassword) {
				json_encode_return(0, _('The password that you entered twice do not match.'));
			}

			//檢查 account 重複
			$m_user = Model('user')->where(array(array(array(array('account', '=', $account), array('way', '=', 'none')), 'and')))->fetch();
			if (!empty($m_user)) {
				json_encode_return(2, _('The account already exists, please use another.'));
			}

			//檢查是否與已註冊會員手機號碼相同
			$m_user = Model('user')->where(array(array(array(array('cellphone', '=', \Core\I18N::cellphone($cellphone))), 'and')))->fetch();
			if (!empty($m_user)) {
				json_encode_return(2, _('The cellphone number already exists, please use another.'));
			}

			//檢查是否已有驗證密碼的資料
			$m_smspassword = Model('smspassword')->where(array(array(array(array('user_account', '=', $account), array('user_cellphone', '=', \Core\I18N::cellphone($cellphone))), 'and')))->fetch();
			if (empty($m_smspassword)) {
				$smspassword = random_password(4, 's');
				$tmp0 = array(
					'user_account' => $account,
					'user_cellphone' => \Core\I18N::cellphone($cellphone),
					'smspassword' => $smspassword,
				);
				Model('smspassword')->add($tmp0);
			} else {
				$smspassword = $m_smspassword['smspassword'];
			}

			//sms
			$message = 'pinpinbox SMS password : ' . $smspassword;
			list($sms_result, $sms_message) = array_decode_return(Core::extension('sms', 'every8d')->send(\Core\I18N::cellphone($cellphone), $message));

			if (!$sms_result) {
				json_encode_return(0, _('[SMS] occur exception, please contact us.'), null, 'Modal');
			}

			json_encode_return(1, _('Validation code has been sent.'), null, 'Modal');
		}
	}

	function settings()
	{
		$user = parent::user_get();

		if (is_ajax()) {
			$user_hobby = array();

			$user_creative_code = !empty($_POST['user_creative_code']) ? $_POST['user_creative_code'] : null;
			$user_email = !empty($_POST['user_email']) ? $_POST['user_email'] : null;
			// $user_cellphone = !empty($_POST['user_cellphone'])? $_POST['user_cellphone'] : null;
			$user_gender = !empty($_POST['user_gender']) ? $_POST['user_gender'] : null;
			$user_relationship = !empty($_POST['user_relationship']) ? $_POST['user_relationship'] : null;
			$user_birthday = !empty($_POST['user_birthday']) ? $_POST['user_birthday'] : null;
			$user_discuss = !empty($_POST['user_discuss']) ? $_POST['user_discuss'] : null;
			$user_newsletter = !empty($_POST['user_newsletter']) ? $_POST['user_newsletter'] : null;
			$user_hobby[0] = !empty($_POST['user_hobby_0']) ? $_POST['user_hobby_0'] : null;
			$user_hobby[1] = !empty($_POST['user_hobby_1']) ? $_POST['user_hobby_1'] : null;
			$user_hobby[2] = !empty($_POST['user_hobby_2']) ? $_POST['user_hobby_2'] : null;
			$user_address_id_1st = isset($_POST['user_address_id_1st']) ? $_POST['user_address_id_1st'] : 0;
			$user_address_id_2nd = isset($_POST['user_address_id_2nd']) ? $_POST['user_address_id_2nd'] : 0;

			/**
			 * 0503 - 調整創作人代號可為空故不檢查
			 */
			// if ($user_creative_code == null) json_encode_return(0, _('Please enter creative-code.'));

			if (!empty($user_creative_code)) {
				if (!preg_match('/^[a-zA-Z0-9]+$/', $user_creative_code)) json_encode_return(0, _('The creative-code enter only letters and numbers.'));
				if (preg_match('/^index$/i', $user_creative_code)) json_encode_return(0, _('"index" is Reserved word, please use another Author code name.'));
				if (!empty(Model('user')->where(array(array(array(array('user_id', '!=', $user['user_id']), array('creative_code', '=', $user_creative_code)), 'and')))->fetch())) {
					json_encode_return(0, _('The creative-code already exists, please use another.'));
				}
			}

			//檢查未選擇興趣
			if (count(array_filter($user_hobby)) == 0) json_encode_return(0, _('Select at least one interest.'));

			foreach ($user_hobby as $k0 => $v0) {
				if (is_null($v0)) unset($user_hobby[$k0]);
			}

			//檢查重複選取興趣
			$count_hobby = array_unique($user_hobby);
			if (count($user_hobby) != count($count_hobby)) json_encode_return(0, _('Can\'t select same hobby, please select again.'));

			//檢查居住地, 台灣地區需加填城市, address_id_1st = 1 為"台灣地區"
			if (($user_address_id_1st == 1 && (!$user_address_id_2nd))) json_encode_return(0, _('請選擇您的居住城市'));

			$newsletter = ($user_newsletter == 'open') ? 1 : 0;

			//更新table : user
			$edit = [
				'creative_code' => $user_creative_code,
				'email' => $user_email,
				'gender' => $user_gender,
				'relationship' => $user_relationship,
				'birthday' => $user_birthday,
				'discuss' => $user_discuss,
				'newsletter' => $newsletter,
				'address_id_1st' => $user_address_id_1st,
				'address_id_2nd' => $user_address_id_2nd,
			];
			$where = array(
				array(array(array('user_id', '=', $user['user_id'])), 'and'),
			);
			Model('user')->where($where)->edit($edit);

			hobby_userModel::setHobbyToUser($user['user_id'], $user_hobby);

			Model('user')->setSession($user['user_id']);

			//#1327 同步修改使用者聯絡信箱
			(new creativeModel)->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['personal_email' => $user_email]);

			/**
			 *  0704 - 執行任務-編輯資料完成
			 */
			$user_id = $user['user_id'];
			$task_for = 'firsttime_edit_profile';
			$r_data = model('task')->doTask($task_for, $user_id, 'web');
			json_encode_return(1, _('Update Success.'), parent::url('user', 'settings'), $r_data);
		}

		$column = [
			'user_id',
			'account',
			'`name`',
			'cellphone',
			'email',
			'gender',
			'birthday',
			'relationship',
			'description',
			'level',
			'creative',
			'creative_name',
			'creative_code',
			'sociallink',
			'discuss',
			'newsletter',
			'address_id_1st',
			'address_id_2nd',
			'way',
		];
		$m_user = Model('user')->column($column)->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetch();

		$n_cellphone = strlen($m_user['cellphone']);

		$cellphone = ($n_cellphone) ? substr_replace($m_user['cellphone'], str_repeat("*", ($n_cellphone - 4)), 0, ($n_cellphone - 4)) : null;

		$a_user_data = [
			'user_id' => $m_user['user_id'],
			'account' => $m_user['account'],
			'name' => $m_user['name'],
			'cellphone' => $cellphone,
			'email' => $m_user['email'],
			'picture' => URL_STORAGE . Core::get_userpicture($m_user['user_id']),
			'gender' => $m_user['gender'],
			'birthday' => $m_user['birthday'],
			'relationship' => $m_user['relationship'],
			'description' => htmlspecialchars($m_user['description']),
			'level' => $m_user['level'],
			'creative_name' => $m_user['creative_name'],
			'creative_code' => $m_user['creative_code'],
			'sociallink' => json_decode($m_user['sociallink'], true),
			'discuss' => $m_user['discuss'],
			'newsletter' => $m_user['newsletter'],
			'address_id_1st' => $m_user['address_id_1st'],
			'address_id_2nd' => $m_user['address_id_2nd'],
			'way' => $m_user['way'],
		];

		//追蹤人數
		$m_follow = Model('follow')->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetch();
		$a_user_data['count_from'] = $m_follow['count_from'];

		//總下載
		$where = [
			[[['album.user_id', '=', $user['user_id']], ['album.act', '!=', 'delete']], 'and']
		];
		$c_album = Model('album')->column(['sum(albumstatistics.count)'])->join([['left join', 'albumstatistics', 'using(album_id)']])->where($where)->fetchColumn();
		$a_user_data['download_count'] = empty($c_album) ? 0 : $c_album;

		//user的hobby清單
		$m_hobby_user = Model('hobby_user')->column(['hobby_id'])->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetchAll();
		$a_hobby_user = [];
		foreach ($m_hobby_user as $v0) {
			$a_hobby_user[] = $v0['hobby_id'];
		}
		$a_user_data['hobby_user'] = $a_hobby_user;

		//取得所有興趣清單
		$a_hobby = [];
		$m_hobby = Model('hobby')->column(['hobby_id', 'name'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
		foreach ($m_hobby as $k => $v) {
			$a_hobby[$k]['hobby_id'] = $v['hobby_id'];
			$a_hobby[$k]['name'] = \Core\Lang::i18n($v['name']);
		}
		parent::$data['hobby'] = $a_hobby;

		//折%數
		if ($m_user['creative'] == 1) {
			$a_user_data['pointsplitrate']['album'] = (\Model\split::getRatioForUser($m_user['user_id'], 'album') * 100) . '%';
			$a_user_data['pointsplitrate']['template'] = (\Model\split::getRatioForUser($m_user['user_id'], 'template') * 100) . '%';
		} else {
			$a_user_data['pointsplitrate']['album'] = _('Not launched');
			$a_user_data['pointsplitrate']['template'] = _('Not launched');
		}

		parent::$data['user_data'] = $a_user_data;

		parent::$data['address_id_1st'] = Model('address')->column(['address_id', '`name`'])->where([[[['`level`', '=', 0]], 'and']])->fetchAll();

		parent::$data['address_id_2nd'] = Model('address')->column(['address_id', '`name`'])->where([[[['`level`', '=', 1]], 'and']])->fetchAll();

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Member Information'),
			[_('Member Information')]
		);

		/*
        //居住區域
        $a_area = [
            'TW' => '台灣地區',
            'HKMO' => '港澳地區',
            'CN' => '中國地區',
            'AS' => '亞洲地區',
            'EU' => '歐洲地區',
            'AF' => '非洲地區',
            'NA' => '北美洲地區',
            'SA' => '中南美地區',
            'OA' => '大洋洲地區',
        ];
        parent::$data['area'] = $a_area;

        //台灣縣市
        $a_city = [
            'TPE' => '臺北市',
            'NTC' => '新北市',
            'KLU' => '基隆市',
            'TYC' => '桃園市',
            'ILN' => '宜蘭縣',
            'HSC' => '新竹市',
            'HSH' => '新竹縣',
            'MAC' => '苗栗縣',
            'TXG' => '臺中市',
            'CWH' => '彰化縣',
            'NTO' => '南投縣',
            'YUN' => '雲林縣',
            'HWA' => '花蓮縣',
            'TTT' => '臺東縣',
            'CHI' => '嘉義市',
            'CHY' => '嘉義縣',
            'TNN' => '臺南市',
            'KHH' => '高雄市',
            'PCH' => '屏東縣',
            'PEH' => '澎湖縣',
            'KMN' => '金門縣',
            'LNN' => '連江縣',
        ];
        parent::$data['city'] = $a_city;
        */

		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.core.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.standalone.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/intl-tel-input-master/css/intlTelInput.css'), 'href');
		parent::$html->set_css(static_file('js/datepicker/css/bootstrap-datepicker.min.css'), 'href');

		parent::$html->set_js(static_file('js/intl-tel-input-master/js/intlTelInput.min.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.paginate.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.zh-TW.js'), 'src');
		parent::$html->set_js(URL_ROOT . 'js/php.js', 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function settlement()
	{
		$user = parent::user_get();
		$account_info = array();

		$account_info['name'] = $user['name'];
		$account_info['level'] = $user['level'];
		$account_info['register_time'] = date('Y/m/d', strtotime($user['inserttime']));

		$businessuserModel = (new \businessuser\Model())
			->column([
				'businessuser.mode',
				'businessuser.user_id',
			])
			->join([
				['INNER JOIN', 'user', 'ON user.businessuser_id = businessuser.businessuser_id']
			])
			->where([[[['user.user_id', '=', $user['user_id']]], 'and']])
			->fetch();

		switch ($businessuserModel['mode']) {
			case 'company':
				$account_info['album_pointsplitrate'] = _('依所屬經紀公司規定');
				$account_info['template_pointsplitrate'] = _('依所屬經紀公司規定');
				break;

			case 'personal':
				$account_info['album_pointsplitrate'] = (\Model\split::getRatioForBusinessuserOfPersonalOfHimself($user['user_id'], 'album') * 100) . '%';
				$account_info['template_pointsplitrate'] = (\Model\split::getRatioForBusinessuserOfPersonalOfHimself($user['user_id'], 'template') * 100) . '%';
				break;

			default:
				$account_info['album_pointsplitrate'] = (\Model\split::getRatioForUser($user['user_id'], 'album') * 100) . '%';
				$account_info['template_pointsplitrate'] = (\Model\split::getRatioForUser($user['user_id'], 'template') * 100) . '%';
				break;
		}

		$where = array(
			array(array(array('user_id', '=', $user['user_id'])), 'and'),
		);
		$m_creative = (new \creativeModel)->where($where)->fetch();
		$m_user = (new \userModel)->where($where)->fetch();
		$tmp_tax = json_decode(Core::settings('SETTLEMENT_TAX'), true);

		//creative 資訊
		if ($m_user['creative']) {
			$account_info['remittance'] = ($m_creative['remittance'] != 'paypal') ? _('Bank/ Post office') : $m_creative['remittance'];
			$tmp = json_decode($m_creative['remittance_info'], true);

			//稅率
			$country = ($m_creative['personal_country'] !== 'none') ? $m_creative['personal_country'] : $m_creative['company_country'];
			$account_info['tax'] = ($country === 'TW') ? $tmp_tax['TWD'] : $tmp_tax['USD'];

			switch ($account_info['remittance']) {
				case 'paypal' :
					$account_info['remittance_info'] =
						'<li>
							<div class="tdtitle02">Paypal ' . _('account') . '</div>
							<div class="tdtxt02">' . $tmp['paypal_account'] . '</div>
						</li>';
					$account_info['currency'] = $tmp['paypal_currency'];
					$account_info['fee'] = 0;
					break;

				default:
					$account_info['remittance_info'] =
						'<li>
							<div class="tdtitle02">' . _('Bank Transfer') . '</div>
							<div class="tdtxt02"> 
								' . _('Account Name') . '：' . $tmp['name'] . '<br>
								' . _('Bank') . '' . (isset($tmp['bank']) ? $tmp['bank'] : null) . '<br>
								' . _('Branch') . '' . $tmp['branch'] . '<br>
								' . _('Account No.') . '' . $tmp['account'] . '							
							</div>
						</li>';
					$account_info['currency'] = 'TWD';

					//手續費
					$fee = json_decode(Core::settings('SETTLEMENT_FEE'), true);
					$account_info['fee'] = $fee[$account_info['currency']];
					break;
			}
		} else {
			$account_info['remittance'] = _('未填寫');
			$account_info['remittance_info'] = '';
			$account_info['currency'] = _('未填寫');
			$account_info['fee'] = 0;
			$account_info['tax'] = $tmp_tax['TWD'];

			// redirect_php(parent::url('creative', 'recruit'));
		}

		/**
		 *  累積總收益、未結算收益、可領取收益
		 */
		$all_album_id = $this::album_id_pool($user['user_id']);
		$all_template_id = $this::template_id_pool($user['user_id']);
		$account_info['total_sum_pointsplit'] = 0;
		$account_info['total_unsettled_pointsplit'] = 0;

		//[album]
		if (count($all_album_id) > 0) {
			//累積總收益
			$tmp0 = (new exchangeModel)
				->column(['SUM(split.point) AS sum'])
				->join([
					[
						'INNER JOIN',
						'split',
						\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
							'ON split.exchange_id = exchange.exchange_id'
							:
							'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
					]
				])
				->where([[[['exchange.type', '=', 'album'], ['exchange.id', 'IN', $all_album_id]], 'and']])
				->fetch();

			if (!empty($tmp0['sum'])) $account_info['total_sum_pointsplit'] = $tmp0['sum'];

			//未結算收益
			$tmp2 = (new exchangeModel)
				->column(['SUM(split.point) AS unsettled'])
				->join([
					[
						'INNER JOIN',
						'split',
						\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
							'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL'
							:
							'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
					]
				])
				->where([[[['exchange.type', '=', 'album'], ['exchange.id', 'IN', $all_album_id]], 'and']])
				->fetch();

			if (!empty($tmp2['unsettled'])) $account_info['total_unsettled_pointsplit'] = $tmp2['unsettled'];
		}

		//template
		if (count($all_template_id) > 0) {
			//累積總收益
			$tmp1 = (new exchangeModel)
				->column(['SUM(split.point) AS sum'])
				->join([
					[
						'INNER JOIN',
						'split',
						\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
							'ON split.exchange_id = exchange.exchange_id'
							:
							'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
					]
				])
				->where([[[['exchange.type', '=', 'template'], ['exchange.id', 'IN', $all_template_id]], 'and']])
				->fetch();

			if (!empty($tmp1['sum'])) $account_info['total_sum_pointsplit'] += $tmp1['sum'];

			//未結算收益
			$tmp3 = (new exchangeModel)
				->column(['SUM(split.point) AS unsettled'])
				->join([
					[
						'INNER JOIN',
						'split',
						\userModel::isDownlineOfBusinessUserOfCompany($user['user_id']) ?
							'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL'
							:
							'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL AND split.user_id = ' . (new \exchangeModel)->quote($user['user_id'])
					]
				])
				->where([[[['exchange.type', '=', 'template'], ['exchange.id', 'IN', $all_template_id]], 'and']])
				->fetch();

			if (!empty($tmp3['unsettled'])) $account_info['total_unsettled_pointsplit'] += $tmp3['unsettled'];
		}

		//可領取收益 => album + template
		$m_settlement = \settlementModel::getSettlementList($user['user_id']);

		$account_info['total_settled_pointsplit'] = 0;

		$settlement = [];

		foreach ($m_settlement as $v) {
			switch ($v['state']) {
				case 'fail':
					$state = _('過期');
					break;

				case 'success':
					$state = _('已結算');
					break;

				default:
					$state = _('未結算');
					break;
			}

			$settlement[] = [
				'starttime' => date('Y/m', strtotime($v['starttime'])),
				'point' => number_format($v['point']),
				'state' => $state,
			];

			if ($v['state'] === 'pretreat') $account_info['total_settled_pointsplit'] += $v['point'];
		}

		/**
		 *  不同幣別的結算收益底限
		 */
		switch ($account_info['currency']) {
			case 'USD' :
				//USD =>取得底限(100美金)後轉換成台幣
				$exchange_rate = json_decode(Core::settings('EXCHANGE_RATE'), true);
				$datum = Core::settings('SETTLEMENT_USD_DATUM');
				$account_info['datum'] = Core::settings('SETTLEMENT_USD_DATUM') * $exchange_rate['TWD_USD'];
				break;

			default:
				$datum = Core::settings('SETTLEMENT_TWD_DATUM');
				$account_info['datum'] = Core::settings('SETTLEMENT_TWD_DATUM');
				break;

		}

		//轉換P點為收益金額
		$tax = $account_info['tax'];
		$fee = $account_info['fee'];
		$transform = Core::settings('TRANSFORM_RATE');
		switch ($account_info['currency']) {
			case 'USD':
				//現金收益 [美金]  = ((P點 / 虛擬貨幣轉換率) * 稅率) * 匯率
				$total = (($account_info['total_settled_pointsplit'] / $transform) * (100 - $tax) / 100) / $exchange_rate['TWD_USD'];
				break;

			default:
				//現金收益 [台幣]  = (((P點 / 虛擬貨幣轉換率) * 稅率) * 匯率 ) - 手續費
				$total = floor(((($account_info['total_settled_pointsplit'] / $transform) * (100 - $tax) / 100) - $fee));
				break;
		}

		switch ($account_info['remittance']) {
			case 'paypal':
				$text1 = _('1. 結帳金額為未稅金額,詳細資料請參考下方列表。');
				break;

			default:
				$text1 = _('1. 結帳金額為未稅金額, 已包含手續費NT:30元 , 詳細資料請參考下方列表。');
				break;
		}

		//結算的按鈕及事件
		$transform = Core::settings('TRANSFORM_RATE');

		/**
		 *  不同狀態下會產生的前台內容
		 */
		$stateButtonValue = [
			'fill' => '<li><a href="javascript:void(0)" onclick="check_settlement()" class="used">' . _('Settlement') . '</a></li>',
			'unFill' => '<li><a href="javascript:void(0)" onclick="check_settlement()" class="used">' . _('填寫匯款資料') . '</a></li>',
			'emailValidate' => '<li><a href="javascript:void(0)" onclick="check_settlement()" class="used">' . _('重新填寫匯款資料') . '</a></li>',
		];

		$stateTipText = [
			'over' => '<li><div class="tdtitle02"></div>
                            <div class="tdtxt02"><span class="settled">' . _('*已達到收益門檻 :') . number_format($account_info['datum'] * $transform) . '</span>P</div>
                       </li>',

			'under' => '<li><div class="tdtitle02"></div>
                            <div class="tdtxt02"><span class="settled">*' . _('Profit settlement threshold: ') . number_format($account_info['datum'] * $transform) . '</span>P</div>
                       </li>',

			'emailValidate' => '<li><div class="tdtitle02"></div>
                            <div class="tdtxt02"><span style="color:#ee5b8d;">*請至Email收取驗證信</span></div>
                       </li>',
		];

		$stateScript = [
			'settled' => '//frontside validate
                    var settled = $(\'.settled\').html();
                    var count_settlement = $(\'#tab01 tbody>tr\').length;
                    
                    if (settled < ' . $account_info['datum'] . ') {
                        var r = {"message":"' . _('Your P Point is below the bottom line of the settlement.') . '"};
                        site_jBox(r, \'error\');
                        return false;
                    } else if(count_settlement < 1) {
                        var r = {"message":"' . _('You have no settlement record yet.') . '"};
                        site_jBox(r, \'error\');
                        return false;
                    } else {
                        var myConfirm = new jBox(\'Confirm\', {
                            cancelButton: \'' . _('No') . '\',
                            confirmButton: \'' . _('Yes') . '\',
                            confirm: function() {               
                                $.post(\'' . self::url('user', 'income') . '\', {   
                                }, function(r) {
                                    r = $.parseJSON(r);
                                    if (r.result == 1) {
                                        site_jBox(r, \'success\');
                                    } else {
                                        site_jBox(r, \'error\');
                                    }
                                });
                            },
                            onCloseComplete: function() {
                                myConfirm.destroy();
                            }
                        }).setContent(
                            "<div class=\"content\">" +
                            "' . _('Sum of settlement：') . '<span class=\"red\">$' . number_format($total) . '</span><br>" +
                            "' . _('Currency for settlement：') . '<span class=\"red\">' . $account_info['currency'] . '</span><br>" +
                            "' . _('Method of settlement：') . '<span class=\"red\">' . $account_info['remittance'] . '</span><br>" +
                            "' . _('Tips：') . '<br>" +
                            "' . $text1 . '<br>" +
                            "' . _('2. The system makes a settlement once a month, and the follow-up P Points gained will be settled the following month.') . '" +
                            "</div>"
                        ).open();
                    }',
			'unSettled' => 'location.href="' . parent::url('creative', 'apply', ['redirect' => parent::url('user', 'settlement')]) . '"',
		];

		if (empty($m_creative)) {
			//未填寫匯款資料
			$account_info['btn_value'] = $stateButtonValue['unFill'];
			$account_info['script'] = $stateScript['unSettled'];
			$account_info['tipText'] = (($account_info['total_settled_pointsplit'] / $transform) > $account_info['datum']) ? $stateTipText['over'] : $stateTipText['under'];
		} elseif (!empty($m_creative) && !$m_user['creative']) {
			//已填寫匯款資料但未進行Email驗證
			$account_info['btn_value'] = $stateButtonValue['emailValidate'];
			$account_info['script'] = $stateScript['unSettled'];
			$account_info['tipText'] = $stateTipText['emailValidate'];
		} else {
			//已驗證Email
			if (($account_info['total_settled_pointsplit'] / $transform) > $account_info['datum']) {
				//已達到結算門檻
				$account_info['btn_value'] = $stateButtonValue['fill'];
				$account_info['script'] = $stateScript['settled'];
				$account_info['tipText'] = null;
			} else {
				//未達到結算門檻
				$account_info['btn_value'] = null;
				$account_info['script'] = null;
				$account_info['tipText'] = $stateTipText['under'];
			}
		}

		/**
		 *  歷史結算紀錄
		 */
		$m_income = (new \incomeModel)
			->order(array('inserttime' => 'desc'))
			->where(array(array(array(array('user_id', '=', $user['user_id'])), 'and')))
			->fetchAll();

		if (!empty($m_income)) {
			foreach ($m_income as $k => $v) {
				switch ($v['state']) {
					case 'pretreat':
						$m_income[$k]['state'] = _('Pretreat');
						break;

					case 'fail':
						$m_income[$k]['state'] = _('Fail');
						break;

					case 'success' :
						$m_income[$k]['state'] = _('Success');
						break;
				}
			}
		}
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Profit settlement'),
			array(_('Profit settlement'))
		);

		parent::$data['account_info'] = $account_info;
		parent::$data['settlement'] = $settlement;
		parent::$data['income'] = $m_income;

		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.core.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.standalone.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/datepicker/css/bootstrap-datepicker.min.css'), 'href');
		parent::$html->set_css(static_file('js/sweet-alert/css/sweet-alert.css'), 'href');

		parent::$html->set_js(static_file('js/footable/js/footable.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.paginate.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.zh-TW.js'), 'src');
		parent::$html->set_js(static_file('js/sweet-alert/js/sweet-alert.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function template_bgdemo()
	{
		if (is_ajax()) {
			$user = parent::user_get();
			$bg_num = !empty($_POST['bg_num']) ? $_POST['bg_num'] : null;
			if ($bg_num != null) {
				$where = array();
				$where[] = array(array(array('frame_id', '=', $bg_num)), 'and');
				$m_frame_resource = Model('frame_resource')->where($where)->fetch();
				if (!empty($m_frame_resource)) {
					json_encode_return(1, static_file('images/' . $m_frame_resource['url']));
				}
				json_encode_return(2, _('Frame does not exist, please contact us.'));
			}
		}
		die;
	}

	function template_info()
	{

		$m_style = Model('style')->where([[[['act', '=', 'open']], 'and']])->fetchAll();

		parent::$data['style_info'] = $m_style;

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Templates to be uploaded'),
			array(_('Templates to be uploaded'))
		);

		parent::$data['frame_limit'] = 8 * 1024 * 1024;
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/croppic/css/croppic.css'), 'href');

		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/croppic/js/croppic.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function template_list()
	{
		$user = parent::user_get();
		$where = array();
		$where[] = array(array(array('user_id', '=', $user['user_id'])), 'and');
		$join = array();
		$join[] = array('left join', 'templatestatistics', 'using(template_id)');
		$m_template = Model('template')->column(array('template.template_id', 'template.name', 'template.modifytime', 'template.point', 'template.instruction', 'template.state', 'templatestatistics.count'))->where($where)->join($join)->order(array('template.inserttime' => 'desc'))->fetchAll();

		$a_template = array();
		foreach ($m_template as $k => $v) {
			$a_template[$k]['id'] = $v['template_id'];
			$a_template[$k]['name'] = $v['name'];
			$a_template[$k]['modifytime'] = $v['modifytime'];

			$a_template[$k]['point'] = ($v['point'] == 0 || empty($v['point'])) ? 'Free' : $v['point'] . 'P';
			$a_template[$k]['link'] = 'javascript:void(0)';
			switch ($v['state']) {
				case 'pretreat' :
					$a_template[$k]['class'] = null;
					$a_template[$k]['count'] = 0;
					$a_template[$k]['state'] = _('Under review');
					$a_template[$k]['instruction'] = _('None');
					break;

				case 'success' :
					$a_template[$k]['class'] = 'style="color:#108199;"';
					$a_template[$k]['count'] = $v['count'];
					$a_template[$k]['link'] = parent::url('template', 'content', array('template_id' => $v['template_id']));
					$a_template[$k]['state'] = _('Completed');
					$a_template[$k]['instruction'] = _('None');
					break;

				case 'fail' :
					$a_template[$k]['class'] = 'class="red"';
					$a_template[$k]['count'] = 0;
					$a_template[$k]['state'] = _('Failed');
					$a_template[$k]['instruction'] = _('None');
					$token = encrypt(array('template_id' => $v['template_id']));

					$tmp = array();
					$tmp = json_decode($v['instruction'], true);
					$url = '<a href="' . parent::url('user', 'template_upload', array('act' => 'update', 'template_id' => $v['template_id'], 'token' => $token)) . '">' . '[' . _('re-upload the template') . ']</a>';
					$instruction = '';
					if (is_array($tmp)) {
						foreach ($tmp as $k0 => $v0) {
							$instruction .= $v0['key'] . '：' . $v0['value'] . '&nbsp;&nbsp;&nbsp;<span style="font-size:0.1em;">(' . $v0['remark'] . ')</span>' . '<br>';
						}
						$instruction .= $url;
						$a_template[$k]['instruction'] = $instruction;
					} else {
						$a_template[$k]['instruction'] .= '<br>' . $url;
					}

					break;

				default:
					break;
			}
		}
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('List'),
			array(_('List'))
		);

		parent::$data['template'] = $a_template;
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		//photoswipe
		parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/photoswipe.css'), 'href');
		parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/default-skin/default-skin.css'), 'href');
		parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe.min.js'), 'src');
		parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe-ui-default.min.js'), 'src');

		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.core.css'), 'href');
		parent::$html->set_css(static_file('js/footable/css/footable.standalone.css'), 'href');

		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.paginate.js'), 'src');
		parent::$html->set_js(static_file('js/footable/js/footable.sort.js'), 'src');

		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function template_upload()
	{
		$act = empty($_GET['act']) ? null : $_GET['act'];
		if ($act == null) redirect(parent::url('index', 'index'), _('Abnormal process, please try again.'));

		$page_info = array();
		$page_info['act'] = $act;
		switch ($act) {
			case 'add' :
				//新上傳模板
				$page_info['name'] = empty($_POST['name']) ? null : ($_POST['name']);
				$page_info['style'] = empty($_POST['style']) ? null : $_POST['style'];
				$page_info['style_sign'] = empty($_POST['style_sign']) ? null : $_POST['style_sign'];
				for ($i = 0; $i <= 2; ++$i) {
					if (!empty($_POST['preview_upload' . $i])) $page_info['preview_upload'][$i] = $_POST['preview_upload' . $i];
				}
				$page_info['description'] = empty($_POST['description']) ? null : (str_replace(array("<br>", "<br />", chr(10)), '<br>', str_replace(chr(13) . chr(10), "<br />", nl2br($_POST['description']))));
				if ($page_info['name'] == null || $page_info['description'] == null) redirect(parent::url('index', 'index'), _('Abnormal process, please try again.'));
				$img = array();

				break;

			case 'update' :
				//重新編輯
				$template_id = empty($_GET['template_id']) ? null : $_GET['template_id'];
				$token = empty($_GET['token']) ? null : $_GET['token'];
				if ($token != encrypt(array('template_id' => $template_id))) redirect(parent::url('index', 'index'), _('Abnormal process, please try again.'));

				$where = array();
				$where[] = array(array(array('template_id', '=', $template_id), array('state', '=', 'fail')), 'and');
				$m_template = Model('template')->column(array('template_id', 'name', 'frame_upload', 'description', 'style_id', 'image_promote'))->where($where)->fetch();
				$m_style_name = Model('style')->column(['name'])->where([[[['style_id', '=', $m_template['style_id']]], 'and']])->fetchColumn();
				if (empty($m_template)) redirect(parent::url('index', 'index'), _('Abnormal process, please try again.'));

				$page_info['name'] = $m_template['name'];
				$page_info['description'] = str_replace(array(chr(10)), '', $m_template['description']);
				$page_info['template_id'] = $template_id;
				$page_info['style'] = $m_template['style_id'];
				$page_info['style_sign'] = encrypt(['style_name' => $m_style_name], SITE_SECRET);
				$img = array();
				foreach (json_decode($m_template['frame_upload'], true) as $k => $v) {
					$img[$k + 1]['src'] = URL_UPLOAD . DIRECTORY_SEPARATOR . M_PACKAGE . $v['src'];
					$img[$k + 1]['size'] = filesize(PATH_UPLOAD . DIRECTORY_SEPARATOR . M_PACKAGE . $v['src']);
				}
				break;

			default:
				redirect(parent::url('index', 'index'), _('Abnormal process, please try again.'));
				break;

		}

		$where = array();
		$where[] = array(array(array('act', '=', 'open')), 'and');
		$m_frame_resource = Model('frame_resource')->where($where)->fetchAll();
		$basic = array();
		$basic[] = ['text' => _('Select styles'), 'value' => '0'];
		foreach ($m_frame_resource as $v) {
			$basic[] = ['text' => $v['name'], 'value' => $v['frame_id']];
		}

		/* post_max_size
         * 編碼後的字串約是原本size的1.37倍，參閱:http://zh.wikipedia.org/wiki/Base64
         * 故post_max_size值除1.37
         */
		$post_max_size = ceil((str_replace('M', '', ini_get('post_max_size')) * 1024 * 1024) / 1.37);

		//單張版型大小限制 (MB)
		$frame_limit = 8;

		parent::$data['basic'] = json_encode($basic);
		parent::$data['page_info'] = $page_info;
		parent::$data['post_max_size'] = $post_max_size;
		parent::$data['frame_limit'] = $frame_limit * 1024 * 1024;
		parent::$data['img'] = $img;
		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Upload Template photos'),
			array(_('Upload Template photos'))
		);
		$view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
		$this->member_nav();
		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		//photoswipe
		parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/photoswipe.css'), 'href');
		parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/default-skin/default-skin.css'), 'href');
		parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe.min.js'), 'src');
		parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe-ui-default.min.js'), 'src');

		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
		parent::$html->set_css(static_file('js/croppic/css/croppic.css'), 'href');
		parent::$html->set_css(static_file('js/jquery.ddslick/css/service.ddlist.jquery.css'), 'href');

		parent::$html->set_js(static_file('js/jquery.ddslick/js/jquery.ddslick.min.js'), 'src');
		parent::$html->set_js(static_file('js/croppic/js/croppic.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-ui-1.10.4.custom.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = $view;
	}

	function save_template()
	{
		if (is_ajax()) {
			$user = parent::user_get();
			$frame = (!empty($_POST['frame'])) ? $_POST['frame'] : null;  //取得base64的code
			$act = (!empty($_POST['act'])) ? $_POST['act'] : null;
			$name = (!empty($_POST['name'])) ? $_POST['name'] : null;
			$description = (!empty($_POST['description'])) ? $_POST['description'] : null;
			$style_id = (!empty($_POST['style_id'])) ? $_POST['style_id'] : null;
			$style_sign = (!empty($_POST['style_sign'])) ? $_POST['style_sign'] : null;
			$preview_upload = (!empty($_POST['preview_upload'])) ? $_POST['preview_upload'] : null;

			$pic_code = json_decode($frame, true);
			//[條件1]定義接受的圖片類型，僅接受PNG格式
			foreach ($pic_code as $k => $v) {
				//上傳非png格式
				if (substr_compare($v['src'], 'png', 11, 5) < 0 && (!preg_match("/\.png/", $v['src']))) {
					json_encode_return(0, _('Please upload the image file type is PNG.'));
				}
			}

			//[條件2]版型要求數量
			if (count($pic_code) < 12) {
				json_encode_return(0, _('The number of upload templates are not enough.'));
			}

			//[條件3]檢查宣傳圖
			if ($act == 'add') {
				$preview = $preview_upload;
				foreach ($preview as $k => $v) {
					if (!file_exists(PATH_UPLOAD . $v)) json_encode_return(0, _('File does not exist, please contact us.'));
				}
			}

			//[條件4]檢查類別
			$m_style = Model('style')->where([[[['style_id', '=', $style_id]], 'and']])->fetch();
			if (encrypt(['style_name' => $m_style['name']]) != $style_sign) json_encode_return(0, _('類別錯誤，請重新輸入'), parent::url('user', 'template_info'));

			switch ($act) {
				case 'add':
					//upload路徑 -- 建立今天的dir
					$uniqld = uniqid();
					$str = '/template/' . date('Ymd') . '/' . $user['user_id'] . '/' . $uniqld . '/';

					//上傳圖片在日期dir 下
					$imagePath = PATH_UPLOAD . M_PACKAGE . $str;

					//通過條件，建立folder
					mkdir_p(PATH_UPLOAD, M_PACKAGE . $str);
					$tmp = array();
					foreach ($pic_code as $k => $v) {
						$data = str_replace('data:image/png;base64,', '', $v['src']);
						$data = str_replace(' ', '+', $data);

						//儲存檔案，參數一是圖片名稱+副檔名  參數二是解碼完的資料
						$success = file_put_contents($imagePath . $k . '.png', base64_decode($data));
						$tmp[] = array('src' => $str . $k . '.png', 'resource' => $v['resource']);
					}

					//宣傳圖處理
					foreach ($preview as $k0 => $v0) {
						$promote[$k0] = $str . 'promote_' . uniqid() . '.' . end(explode('.', $v0));
						if (!copy(PATH_UPLOAD . $v0, PATH_UPLOAD . M_PACKAGE . $promote[$k0])) json_encode_return(0, _('失敗'));
					}

					//存入template(待審核)
					$param = array();
					$param['user_id'] = $user['user_id'];
					$param['name'] = $name;
					$param['act'] = 'close';
					$param['frame_upload'] = json_encode($tmp);
					$param['style_id'] = $style_id;
					$param['image'] = $promote[0];
					$param['image_promote'] = json_encode($promote);
					$param['state'] = 'pretreat';
					$param['point'] = 120;
					$param['description'] = $description;
					$param['width'] = 1336;
					$param['height'] = 2004;
					$param['inserttime'] = inserttime();
					$template_id = Model('template')->add($param);

					//templatestatistics(計算下載數)
					$param = array();
					$param['template_id'] = $template_id;
					$param['count'] = 0;
					$param['viewed'] = 0;
					Model('templatestatistics')->add($param);

					json_encode_return(1, _('Your Templates have been submitted. We request for your patience please, thank you.'), parent::url('user', 'template_list'), 'Modal');

					break;

				case 'update':
					$template_id = (!empty($_POST['template_id'])) ? $_POST['template_id'] : null;
					if (empty($template_id)) json_encode_return(0, _('Abnormal process, please try again.'), parent::url('user', 'template_list'), 'Modal');

					$where = array();
					$where[] = array(array(array('template_id', '=', $template_id)), 'and');
					$m_template = Model('template')->column(array('template_id', 'name', 'frame_upload', 'description'))->where($where)->fetch();

					$upload_frame_base = dirname(json_decode($m_template['frame_upload'], true)[0]['src']) . '/';
					$upload_frame_dir = PATH_UPLOAD . M_PACKAGE . $upload_frame_base;

					//先將沒有更改的模板(src=路徑)轉為base64 讓下一個迴圈一次處理
					foreach ($pic_code as $k => $v) {
						if (preg_match("/\.png/", $v['src'])) {
							$path = PATH_UPLOAD . DIRECTORY_SEPARATOR . M_PACKAGE . str_replace(URL_UPLOAD . DIRECTORY_SEPARATOR . M_PACKAGE, '', $v['src']);
							$type = pathinfo($path, PATHINFO_EXTENSION);
							$data = file_get_contents($path);
							$pic_code[$k]['src'] = 'data:image/' . $type . ';base64,' . base64_encode($data);
						}
					}

					//clean dir
					$files = glob($upload_frame_dir . '*');
					foreach ($files as $file) {
						//不刪除檔名為promote 的預覽圖
						if (!strpos($file, 'promote')) {
							if (is_file($file)) {
								if (unlink($file)) {
									\Extension\aws\S3::deleteObject($file);
								}
							}
						}
					}

					$tmp = array();
					//通過條件，建立folder
					foreach ($pic_code as $k => $v) {
						$data = str_replace('data:image/png;base64,', '', $v['src']);
						$data = str_replace(' ', '+', $data);
						//儲存檔案，參數一是圖片名稱+副檔名  參數二是解碼完的資料
						$success = file_put_contents($upload_frame_dir . $k . '.png', base64_decode($data));
						$tmp[] = array('src' => $upload_frame_base . $k . '.png', 'resource' => $v['resource']);
					}

					$param = array();
					$param['state'] = 'pretreat';
					$param['frame_upload'] = json_encode($tmp);
					if (Model('template')->where($where)->edit($param)) json_encode_return(1, _('Your Templates have been submitted. We request for your patience please, thank you.'), parent::url('user', 'template_list'), 'Modal');

					json_encode_return(0, _('Abnormal process, please try again.'), parent::url('user', 'template_list'), 'Modal');
					break;

				default:
					json_encode_return(0, _('Abnormal process, please try again.'), parent::url('user', 'template_list'), 'Modal');
					break;
			}
		}
		json_encode_return(0, _('Abnormal process, please try again.'), parent::url('user', 'template_list'), 'Modal');
	}

	function verify()
	{
		$m_user = (new userModel())->getSession();

		if (is_ajax()) {
			$a_hobby = empty($_POST['hobby']) ? null : $_POST['hobby'];
			if (count($a_hobby) < 1) json_encode_return(0, _('Select at least one interest.'));

			hobby_userModel::setHobbyToUser($m_user['user_id'], $a_hobby);

			(new userModel())->setSession($m_user['user_id']);

			if (empty(query_string_parse()['redirect'])) {
				$redirect = parent::url('creative', 'edit', ['user_id' => $m_user['user_id']]);
				$message = _('Register success.');
			} else {
				$redirect = query_string_parse()['redirect'];
				$message = _('Register success.') . '&nbsp;' . _('將導引至指定頁面...');
			}

			json_encode_return(1, $message, $redirect);
		}

		if (strtotime(date('Y-m-d H:i:s')) > strtotime('+10 minutes', strtotime($m_user['inserttime']))) redirect(parent::url());

		parent::$data['hobby'] = (new hobbyModel())->where([[[['act', '=', 'open']], 'and']])->fetchAll();

		$this->seo(
			Core::settings('SITE_TITLE') . ' | ' . _('Verify your personal information'),
			[_('Verify your personal information')]
		);

		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$html->set_css(static_file('css/dropit.css'), 'href');
		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
		parent::$html->set_css(static_file('js/croppic/css/croppic.css'), 'href');

		parent::$html->set_js(static_file('js/croppic/js/croppic.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
		parent::$html->set_jquery_validation();
		parent::$html->jbox();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
	}

	function album_id_pool($user_id, $time_interval = null)
	{
		/**
		 *  User 要引入的所有作品
		 *  album可能因先販售再關閉造成收益統計錯誤，故搜尋作品時搜出除delete外的全部作品
		 */
		$tmp0 = array();
		$tmp1 = array();

		$unsale = array();

		$where = [[[['act', '!=', 'delete'], ['album.state', '=', 'success'], ['album.zipped', '=', 1]], 'and']];

		$businessuserModel = (new \businessuser\Model())
			->column([
				'businessuser_id',
				'mode',
			])
			->where([[[['user_id', '=', $user_id]], 'and']])
			->fetch();

		switch ($businessuserModel['mode']) {
			case 'company':
			case 'personal':
				$array_user_id = array_column(
					(new \userModel)
						->column(['user_id'])
						->where([[[['businessuser_id', '=', $businessuserModel['businessuser_id']]], 'and']])
						->fetchAll()
					,
					'user_id'
				);

				$where = array_merge($where, [[[['user_id', 'IN', array_merge($array_user_id, [$user_id])]], 'and']]);
				break;

			default:
				$where = array_merge($where, [[[['user_id', '=', $user_id]], 'and']]);
				break;
		}

		$m_album = (new \albumModel)
			->column(['album.album_id', 'album.point'])
			->where($where)
			->fetchAll();

		foreach ($m_album as $k0 => $v0) {
			$tmp0[] = $v0['album_id'];

			//目前point為0的作品
			if ($v0['point'] == 0) {
				$unsale[] = $v0['album_id'];
			}
		}

		//回傳所有album_id
		$return = $tmp0;

		//將目前point=0的作品ID拿到exchange做查詢，檢查是否有販售紀錄
		if ($time_interval != null) {
			$m_exchange = (new exchangeModel)
				->column(['exchange.id'])
				->group(['id'])
				->where([[[['id', 'in', $unsale], ['type', '=', 'album'], ['point', '>', 0], ['inserttime', 'between', $time_interval]], 'and']])
				->fetchAll();

			if (!empty($m_exchange)) {
				foreach ($m_exchange as $k => $v) {
					$tmp1[] = $v['id'];
				}
			}

			//回傳所有期間內有販售紀錄的album_id
			$return = array_diff($tmp0, array_diff($unsale, $tmp1));
		}

		return $return;
	}

	function template_id_pool($user_id)
	{
		/**
		 *  User 要引入的所有版型
		 */
		$all_template_id = [];

		$where = [[[['act', '!=', 'delete'], ['state', '=', 'success']], 'and']];

		$businessuserModel = (new \businessuser\Model())
			->column([
				'businessuser_id',
				'mode',
			])
			->where([[[['user_id', '=', $user_id]], 'and']])
			->fetch();

		switch ($businessuserModel['mode']) {
			case 'company':
			case 'personal':
				$array_user_id = array_column(
					(new \userModel)
						->column(['user_id'])
						->where([[[['businessuser_id', '=', $businessuserModel['businessuser_id']]], 'and']])
						->fetchAll()
					,
					'user_id'
				);

				$where = array_merge($where, [[[['user_id', 'IN', array_merge($array_user_id, [$user_id])]], 'and']]);
				break;

			default:
				$where = array_merge($where, [[[['user_id', '=', $user_id]], 'and']]);
				break;
		}

		$tmp = (new \templateModel)
			->column(['template.template_id'])
			->where($where)
			->fetchAll();

		foreach ($tmp as $k => $v) {
			$all_template_id[] = $v['template_id'];
		}

		return $all_template_id;
	}

	function unsubscribe() {
		$u = (!empty($_GET['u'])) ? $_GET['u'] : null ;
		$v = (!empty($_GET['v'])) ? $_GET['v'] : null ;

		if ($u == null || $v == null) { redirect(parent::url(), '取消訂閱失敗，請重新操作。'); }

		parse_str(base64_decode($u), $parse);
		$user_id = $parse['id'];
		$m_user = (new userModel())->column(['email', 'user_id'])->where([[[['user_id', '=', $user_id]], 'and']])->fetch();
		$verify = md5(urldecode(http_build_query($m_user).M_PACKAGE));

		if(hash_equals($verify, $v)) {
			// 驗證正確 取消訂閱
		}  else {
			redirect(parent::url(), '取消訂閱失敗，請重新操作。');
		}

	}
}