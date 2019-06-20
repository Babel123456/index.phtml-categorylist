<?php

class tutorialController extends frontstageController
{
	function __construct() { }

	function index()
	{
		$user = parent::user_get();

		/**
		 * 取得形象影片連結
		 */
		$tutorial_film = Core::settings('TUTORIAL_FILM');
		parent::$data['tutorial_film'] = '<iframe class="emb_movieRWD" width="90%" height="450" src="' . $tutorial_film . '" frameborder="0" allowfullscreen></iframe>';

		/**
		 * 訪問頁面時關閉驚嘆號提示圖
		 */
		if (isset($_GET['tutorial_viewed']) && !empty($user) && $user['tutorial_viewed'] == 0) {
			Model('user')->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['tutorial_viewed' => 1]);
			Model('user')->setSession($user['user_id']);
		}

		$action = [];

		$element = [[
			'btnClass' => 'create',
			'link' => 'javascript:void(0)',
			'onclick' => 'onclick="createAlbum();"',
			'imgSrc' => static_file('images/assets-v6/start.svg'),
		]];

		foreach ($element as $k0 => $v0) {
			$action[] = '<div class="item ' . $v0['btnClass'] . '">
							<p class="link">
								<a ' . $v0['onclick'] . ' href="' . $v0['link'] . '"><img src="' . $v0['imgSrc'] . '"></a>
							</p>
						</div>';
		}
		parent::$data['action'] = $action;

		//創作職人數量
		$m_creative = Model('creative')->column(array('count(1)'))->fetchColumn();
		parent::$data['count'] = (int)(100 + $m_creative);

		parent::head();
		parent::headbar();
		parent::foot();
		parent::footbar();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);

		//owl
		parent::$html->set_css(static_file('js/owl.carousel/css/owl.carousel.css'), 'href');
		parent::$html->set_css(static_file('js/owl.carousel/css/owl.theme.css'), 'href');
		parent::$html->set_css(static_file('js/owl.carousel/css/owl.transitions.css'), 'href');
		parent::$html->set_js(static_file('js/owl.carousel/js/owl.carousel.min.js'), 'src');

		//mediaelement
		parent::$html->set_css(static_file('js/mediaelement-2.22.0/mediaelementplayer.min.css'), 'href');
		parent::$html->set_js(static_file('js/mediaelement-2.22.0/mediaelement-and-player.min.js'), 'src');

		parent::$html->set_css(static_file('js/sweet-alert/css/sweet-alert.css'), 'href');
		parent::$html->set_js(static_file('js/sweet-alert/js/sweet-alert.min.js'), 'src');

		parent::$html->set_css(static_file('css/style.css'), 'href');
		parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
		parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
		parent::$html->jbox();
	}

}