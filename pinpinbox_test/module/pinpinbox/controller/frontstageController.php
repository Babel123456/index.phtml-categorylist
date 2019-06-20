<?php

class frontstageController extends Core
{
    protected static $html = null;
    protected static $data = [];
    protected static $view = [];
    protected $seo = [];

    function __construct()
    {
		echo "<script>console.log(".json_encode("\controller\\frontstageController.php:start(頁面組成函式)".date ("Y-m-d H:i:s" , mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";

       
		(new \userlogModel)->viewed();

        self::$html = Lib('html');
		
    }

    function alertData() {
		//檢舉
		echo "<script>console.log(".json_encode("\lib\html.php:start(HTML組成)".date ("Y-m-d H:i:s" , mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";
        $m_reportintent = (new reportintentModel())->where([[[['act', '=', 'open']], 'and']])->order(['sequence' => 'desc'])->fetchAll();

        return $m_reportintent;
    }

    function display()
    {
        //css
        self::$data['html_css'] = self::$html->get_css();
		
        //js
        $js = self::$html->get_js();
        if (!empty($js)) $this->js();
        unset($js);

        if (is_array(self::$data)) {
            foreach (self::$data as $k => $v) {
                $$k = $v;
            }
        }
        $view_head = [
            'head',
            'headbar',
            'js_src'
        ];
        $view_foot = [
            'footbar',
            'js',
            'foot'
        ];
        foreach ($view_head as $v1) {
            if (isset(self::$view[$v1]) && file_exists(self::$view[$v1])) include self::$view[$v1];
        }
        ksort(self::$view);
        foreach (self::$view as $k1 => $v1) {
            if (in_array($k1, $view_head, true) || in_array($k1, $view_foot, true)) {
                continue;
            }
            if (file_exists($v1)) include $v1;
        }
        foreach ($view_foot as $v1) {
            if (isset(self::$view[$v1]) && file_exists(self::$view[$v1])) include self::$view[$v1];
        }
    }

    function foot()
    {
        self::$html->set_js(static_file('js/all.min.js'), 'src');
        self::$html->set_js(static_file('js/jquery.nicescroll.min.js'), 'src');

        //jBox
        self::$html->jbox();

        self::$view['foot'] = view(M_PACKAGE, null, null, 'foot');
    }

    function footbar()
    {
        $user = self::user_get();

        //lang
        $m_lang = Model('lang')->order(['sequence' => 'asc'])->fetchAll();
        $a_headbar_lang = [];
        foreach ($m_lang as $v0) {
            $a_headbar_lang[] = [
                'name' => $v0['name'],
                'act' => $v0['act'],
                'url' => self::url(M_CLASS, M_FUNCTION, array_merge(query_string_parse(), ['lang' => $v0['lang_id']])),
            ];
        }

        //tutorial
        $tutorialUrl = (!$user['tutorial_viewed']) ? self::url('tutorial', 'index', ['tutorial_viewed' => 1]) : self::url('tutorial', 'index');
        self::$data['tutorialUrl'] = $tutorialUrl;

        $tutorialIcon = (!$user['tutorial_viewed'] && !empty($user)) ? '<i class="num2"> </i>' : null;
        self::$data['tutorialIcon'] = $tutorialIcon;

        self::$data['headbar_lang'] = $a_headbar_lang;

        self::$view['footbar'] = view(M_PACKAGE, null, null, 'footbar');
    }

    function head()
    {
        $noindex = null;

        if (in_array(M_METHOD, ['creative::recruit', 'user::grade'])) {
            $noindex = '<meta name="robots" content="noindex"><meta name="googlebot" content="noindex">';
        }

        self::$data['noindex'] = $noindex;


        self::$data['lang'] = substr(\Core\Lang::get(), 0, 2);

        //seo
        self::$data['seo'] = $this->seo();

        self::$html->set_js(static_file('js/jquery-1.11.3.min.js'), 'src');
        self::$html->set_js(static_file('js/browser-deeplink-master/browser-deeplink.min.js'), 'src');

        self::$view['head'] = view(M_PACKAGE, null, null, 'head');
    }

    function headbar()
    {
        //style
        $m_style = Model('style')->where([[[['act', '=', 'open']], 'and']])->order(['style_id' => 'asc'])->fetchAll();
        $a_style = [];
        foreach ($m_style as $k0 => $v0) {
            $a_style[] = [
                'name' => \Core\Lang::i18n($v0['name']),
                'url' => self::url('template', 'index', ['style_id' => $v0['style_id']]),
            ];
        }
        self::$data['style'] = $a_style;

        //categoryarea
        $m_categoryarea = Model('categoryarea')->column(['categoryarea_id', 'name'])->where([[[['level', '=', 0], ['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();
        $a_headbar_categoryarea = [];
        $a_headbar_category = [];
        foreach ($m_categoryarea as $k0 => $v0) {
            $a_headbar_categoryarea[] = [
                'name' => \Core\Lang::i18n($v0['name']),
                'url' => self::url('album', 'index', ['categoryarea_id' => $v0['categoryarea_id']]),
            ];

            $categoryarea_category = Model('categoryarea_category')->column(['category_id'])->where([[[['categoryarea_id', '=', $v0['categoryarea_id']], ['act', '=', 'open']], 'and']])->fetchAll();
            foreach ($categoryarea_category as $k1 => $v1) {
                $categoryarea_category_id[$k0][] = $v1['category_id'];
            }
        }
        self::$data['headbar_categoryarea'] = $a_headbar_categoryarea;

        //category
        foreach ($categoryarea_category_id as $k0 => $v0) {
            $a_headbar_category[$k0] = Model('category')->column(['name', 'category_id'])->where([[[['category_id', 'in', $v0], ['act', '=', 'open']], 'and']])->fetchAll();
        }
        self::$data['headbar_category'] = $a_headbar_category;

        //rank
        self::$data['headbar_rank'] = [
            ['name' => _('Hot'), 'url' => self::url('album', 'index', ['rank_id' => 0])],
            ['name' => _('Free'), 'url' => self::url('album', 'index', ['rank_id' => 1])],
            ['name' => _('Sponsored'), 'url' => self::url('album', 'index', ['rank_id' => 2])],
            ['name' => _('Latest'), 'url' => self::url('album', 'index', ['rank_id' => 3])],
        ];

        //search
        $searchtype = isset($_GET['searchtype']) ? urldecode($_GET['searchtype']) : null;
        self::$data['headbar_searchtype'] = $searchtype;
        $searchkey = (isset($_GET['searchkey']) && $_GET['searchkey'] !== '') ? urldecode($_GET['searchkey']) : null;
        self::$data['headbar_searchkey'] = htmlspecialchars($searchkey);

        $user = self::user_get();
        $a_user = [];
        if (!empty($user)) {
            $a_user = [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'creative' => $user['creative'],
                'picture' => URL_STORAGE . Core::get_userpicture($user['user_id']),
                'way' => $user['way'],
            ];
        }
        self::$data['headbar_user'] = $a_user;

        //通知中心未讀提示 icon
        self::$data['pushqueue_viewed_icon'] = ($user && \pushqueueModel::hasUnviewed($user['user_id'])) ?
            '<i class="pushqueue_hasunviewed"></i>'
            :
            null;

        /**
         * headbar上的驚嘆號icon提示
         */
        if (!empty($user) && M_FUNCTION != 'verify') {
            $settings_viewed = (new \userModel)
                ->column(['setting_viewed'])
                ->where([[[['user_id', '=', $user['user_id']]], 'and']])
                ->fetchColumn();

            if (!$settings_viewed) {
                $import_icon_show = '<a href="' . self::url('creative', 'content', ['user_id' => $user['user_id'], 'edit' => true, 'setting_viewed' => true]) . '" class="cicle_btn" title="' . _('到個人專區介紹自己吧!') . '" style="z-index: 100"><i class="num2"> </i></a>';
                self::$data['import_icon_show'] = $import_icon_show;
            }
        }

        /**
         *  特定頁面登入後回到原頁面 login_redirect
         *  album::content
         */
        switch (M_CLASS) {
            case 'album':
                $login_redirect = (M_FUNCTION == 'content') ? ['redirect' => self::url('album', 'content', ['album_id' => $_GET['album_id'], 'categoryarea_id' => \albumModel::getCategoryAreaId($_GET['album_id'])])] : [];
                break;

            default:
                $login_redirect = null;
                break;
        }

        self::$data['login_redirect'] = $login_redirect;

        self::$html->set_css(static_file('js/jquery.bxslider/jquery.bxslider.css'), 'href');
        self::$html->set_js(static_file('js/jquery.bxslider/jquery.bxslider.min.js'), 'src');

        self::$html->set_js(static_file('js/bootstrap.min.js'), 'src');
        self::$html->set_css(static_file('css/bootstrap.min.css'), 'href');
        self::$html->set_css(static_file('css/bootstrap.css'), 'href');
        self::$html->set_css(static_file('css/fontello.css'), 'href');
        self::$html->set_css(static_file('css/font-awesome/css/font-awesome.css'), 'href');

        self::$html->set_js(static_file('js/jquery-timeago-master/js/jquery.timeago.js'), 'src');
        self::$html->set_js(static_file('js/jquery-timeago-master/js/jquery.timeago.zh-TW.js'), 'src');

        self::$html->datalistKit();
        self::$html->jquery_cookie();

        self::$view['headbar'] = view(M_PACKAGE, null, null, 'headbar');
    }

    function js()
    {
        list($js_src, $js) = self::$html->get_js();
        self::$data['html_js_src'] = $js_src;
        self::$data['html_js'] = $js;
        self::$view['js_src'] = view(M_PACKAGE, null, null, 'js_src');
        self::$view['js'] = view(M_PACKAGE, null, null, 'js');
    }

    static function url($class = 'index', $function = 'index', array $param = null)
    {
        $url = URL_ROOT . 'index/';
        if ('index' != $function) {
            $url .= $class . '/';
            $url .= $function . '/';
        } elseif ('index' != $class) {
            $url .= $class . '/';
        }
        if (!empty($param)) {
            $tmp1 = [];
            foreach ($param as $k1 => $v1) {
                $tmp1[urlencode($k1)] = urlencode($v1);
            }
            $url .= '?' . http_build_query($tmp1, '', '&');
        }

        return $url;
    }

    function deeplink($class = 'index', $function = 'index', array $param = null)
    {
        return str_replace(URL_ROOT, 'pinpinbox://', self::url($class, $function, $param));
    }

    //2016-07-05 Lion: 這個準備棄用, 改使用 userModel::getSession
    function user_get()
    {
        return Session::get('user');
    }

    function disqus($case, $id)
    {
        function dsq_hmacsha1($data, $key)
        {
            $blocksize = 64;
            $hashfunc = 'sha1';
            if (strlen($key) > $blocksize)
                $key = pack('H*', $hashfunc($key));
            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hmac = pack(
                'H*', $hashfunc(
                    ($key ^ $opad) . pack(
                        'H*', $hashfunc(
                            ($key ^ $ipad) . $data
                        )
                    )
                )
            );
            return bin2hex($hmac);
        }

        //sso
        $sso = null;
        $user = self::user_get();
        if ($user != null) {
            $data = [
                'id' => $user['user_id'] . '@' . SITE_EVN,
                'username' => $user['name'],
                'email' => $user['account'],
                'avatar' => URL_STORAGE . \Core::get_userpicture($user['user_id']),
                'url' => Core::get_creative_url($user['user_id']),
            ];
            $message = base64_encode(json_encode($data));
            $timestamp = time();
            $hmac = dsq_hmacsha1($message . ' ' . $timestamp, \Core::settings('DISQUS_SECRET_KEY'));

            $sso = 'this.page.remote_auth_s3 = \'' . $message . ' ' . $hmac . ' ' . $timestamp . '\';
					this.page.api_key = \'' . \Core::settings('DISQUS_PUBLIC_KEY') . '\';';
        }
        		
        switch ($case) {
            case 'album':
                $disqus_url = self::disqus_album($id);
                break;

            case 'creative':
                $disqus_url = self::disqus_creative($id);
                break;

            case 'event':
                $disqus_url = self::disqus_event($id);
                break;

            default:
                throw new Exception("[" . __METHOD__ . "] Unknown case");
                break;
        }

        return '<div id="disqus_thread"></div>
				<script>
				var disqus_shortname = \'' . Core::settings('DISQUS_SHORTNAME') . '\';
				var disqus_identifier = \'' . md5($disqus_url) . '\';
				var disqus_url = \'' . $disqus_url . '\';
				var disqus_config = function() {
					' . $sso . '
					this.language = \'' . \Core\Lang::get() . '\';
					this.callbacks.onNewComment = [
						function(comment) { 
							$.post(\'' . url('index', 'disqus') . '\' , {
								comment_id : comment[\'id\'],
								id : ' . $id . ',
								type : \'' . $case . '\',
							},function(r){
								r = $.parseJSON(r);
							});
						}
					];
				};
				(function() {
					var dsq = document.createElement(\'script\'); dsq.type = \'text/javascript\'; dsq.async = true;
					dsq.src = \'//\' + disqus_shortname + \'.disqus.com/embed.js\';
					(document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(dsq);
				})();
				</script>';
    }

    function disqus_event($event_id)
    {
        return self::url('event', 'content', ['event_id' => $event_id]);
    }

    function disqus_album($album_id)
    {
        return self::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]);
    }

    function disqus_creative($user_id)
    {
        return self::url('creative', 'content', ['user_id' => $user_id]);
    }

    function seo($title = null, array $keywords = null, $description = null, $image = null, $url = null)
    {
        //url
        if (empty($url)) $url = $_SERVER['HTTP_HOST'] . '/' . M_CLASS . '/' . M_FUNCTION;
        if (empty($this->seo['url'])) $this->seo['url'] = htmlspecialchars($url);

        //title
        if (empty($title)) $title = Core::settings('SITE_TITLE');
        if (empty($this->seo['title'])) $this->seo['title'] = htmlspecialchars($title);

        //keywords
        $a_keywords = json_decode(Core::settings('SITE_KEYWORDS'), true);
        if (!empty($keywords)) $a_keywords = array_merge($keywords, $a_keywords);
        if (empty($this->seo['keywords'])) $this->seo['keywords'] = htmlspecialchars(strip_tags(implode(',', $a_keywords)));

        //description
        if (empty($description)) $description = Core::settings('SITE_DESCRIPTION');
        if (empty($this->seo['description'])) $this->seo['description'] = preg_replace('/\s+/', ' ', htmlspecialchars(strip_tags($description)));

        //image
        if (empty($image)) $image = static_file('images/kanban.png');
        if (empty($this->seo['image'])) $this->seo['image'] = $image;

        return $this->seo;
    }

    static function type2url($type, $type_id)
    {
        $url = self::url();

        switch ($type) {
            case 'albumcooperation':
                $url = self::url('user', 'albumcontent', ['album_id' => $type_id]);
                break;

            case 'albumqueue':
                $url = self::url('album', 'content', ['album_id' => $type_id, 'autoplay' => true, 'categoryarea_id' => \albumModel::getCategoryAreaId($type_id)]);
                break;

            case 'albumqueue@messageboard':
                $url = self::url('album', 'content', ['album_id' => $type_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($type_id), 'pinpinboard' => 'true']);
                break;

            case 'templatecooperation':
            case 'templatequeue':
                $url = self::url('template', 'content', ['template_id' => $type_id]);
                break;

            case 'follow':
            case 'user':
                $url = Core::get_creative_url($type_id);
                break;
        }

        return $url;
    }

	function type2image_url($type, $type_id, $size = 160)
    {
        $url = static_file('images/logo.png');

        $Image = new \Core\Image();

        switch ($type) {
            case 'albumcooperation':
                $picture = Core::get_userpicture((new albumModel)->column(['user_id'])->where([[[['album_id', '=', $type_id]], 'and']])->fetchColumn());
                $url = is_image(PATH_STORAGE . $picture) ? fileinfo($Image->set(PATH_STORAGE . $picture)->setSize($size, $size)->save())['url'] : null;
                break;

            case 'albumqueue':
            case 'albumqueue@messageboard':
                $cover = (new albumModel)->column(['cover'])->where([[[['album_id', '=', $type_id]], 'and']])->fetchColumn();
                $url = is_image(PATH_UPLOAD . $cover) ? fileinfo($Image->set(PATH_UPLOAD . $cover)->setSize($size, $size)->save())['url'] : null;
                break;

            case 'follow':
            case 'user':
                $picture = Core::get_userpicture($type_id);
                $url = is_image(PATH_STORAGE . $picture) ? fileinfo($Image->set(PATH_STORAGE . $picture)->setSize($size, $size)->save())['url'] : null;
                break;
        }

        return $url;
    }

    static function notifications2data($notifications)
    {
        $return = [];

        foreach ($notifications as $v0) {
            $object_pushqueue = $v0['pushqueue'];

            $target_url = 'javascript:void(0)';
            $trigger_user_id = null;
            $user_pic = static_file('images/m_logo.png');
            $user_url = 'javascript:void(0)';

            if (isset($object_pushqueue['target2type']) && isset($object_pushqueue['target2type_id'])) {
                switch ($object_pushqueue['target2type']) {
                    case 'albumcooperation':
                        $trigger_user_id = (new \albumModel())
                            ->column(['user_id'])
                            ->where([[[['album_id', '=', $object_pushqueue['target2type_id']]], 'and']])
                            ->fetchColumn();

                        $target_url = self::url('album', 'content', ['album_id' => $object_pushqueue['target2type_id'], 'categoryarea_id' => \albumModel::getCategoryAreaId($object_pushqueue['target2type_id'])]);
                        $user_pic = URL_STORAGE . Core::get_userpicture($trigger_user_id);
                        $user_url = Core::get_creative_url($trigger_user_id);
                        break;

                    case 'albumqueue':
                        $m_album_user_id = (new \albumModel())
                            ->column(['user_id'])
                            ->where([[[['album_id', '=', $object_pushqueue['target2type_id']]], 'and']])
                            ->fetchColumn();

                        $trigger_user_id = $m_album_user_id;
                        $user_pic = URL_STORAGE . Core::get_userpicture($trigger_user_id);
                        $user_url = Core::get_creative_url($trigger_user_id);
                        $target_url = self::url('album', 'content', ['album_id' => $object_pushqueue['target2type_id'], 'categoryarea_id' => \albumModel::getCategoryAreaId($object_pushqueue['target2type_id'])]);
                        break;

                    case 'albumqueue@messageboard':
                        $m_album_user_id = (new \albumModel())
                            ->column(['user_id'])
                            ->where([[[['album_id', '=', $object_pushqueue['target2type_id']]], 'and']])
                            ->fetchColumn();

                        $trigger_user_id = $m_album_user_id;
                        $user_pic = URL_STORAGE . Core::get_userpicture($trigger_user_id);
                        $user_url = Core::get_creative_url($trigger_user_id);
                        $target_url = self::url('album', 'content', ['album_id' => $object_pushqueue['target2type_id'], 'categoryarea_id' => \albumModel::getCategoryAreaId($object_pushqueue['target2type_id'])]) . '#pinpinboard';
                        break;

                    case 'user' :
                        $trigger_user_id = $object_pushqueue['target2type_id'];
                        $user_pic = URL_STORAGE . Core::get_userpicture($trigger_user_id);
                        $user_url = Core::get_creative_url($trigger_user_id);
                        $target_url = Core::get_creative_url($trigger_user_id);
                        break;

                    case 'user@messageboard':
                        $trigger_user_id = $object_pushqueue['target2type_id'];
                        $user_pic = URL_STORAGE . Core::get_userpicture($trigger_user_id);
                        $user_url = Core::get_creative_url($trigger_user_id);
                        $target_url = Core::get_creative_url($object_pushqueue['user_id']) . '#pinpinboard';
                        break;
                }
            } elseif (isset($object_pushqueue['url'])) {
                $target_url = $object_pushqueue['url'];
            }

            $return[] = [
                'message' => $object_pushqueue['message'],
                'target_url' => $target_url,
                'time' => $object_pushqueue['inserttime'],
                'trigger_user_id' => $trigger_user_id,
                'trigger_user_pic' => $user_pic,
                'trigger_user_url' => $user_url,
            ];
        }

        return $return;
    }

    /*  index_v2 */

	function head_v2()
	{
		$noindex = null;

		if (in_array(M_METHOD, ['creative::recruit', 'user::grade'])) {
			$noindex = '<meta name="robots" content="noindex"><meta name="googlebot" content="noindex">';
		}

		self::$data['noindex'] = $noindex;

		self::$data['lang'] = substr(\Core\Lang::get(), 0, 2);

		//seo
		self::$data['seo'] = $this->seo();

		self::$html->set_js(static_file('js/jquery-1.11.3.min.js'), 'src');
		self::$html->set_js(static_file('js/browser-deeplink-master/browser-deeplink.min.js'), 'src');
		self::$view['head'] = view(M_PACKAGE, null, null, 'v2/head');
	}

	function headbar_v2()
	{
		//style
		$m_style = Model('style')->where([[[['act', '=', 'open']], 'and']])->order(['style_id' => 'asc'])->fetchAll();
		$a_style = [];
		foreach ($m_style as $k0 => $v0) {
			$a_style[] = [
				'name' => \Core\Lang::i18n($v0['name']),
				'url' => self::url('template', 'index', ['style_id' => $v0['style_id']]),
			];
		}
		self::$data['style'] = $a_style;

		//categoryarea
		$m_categoryarea = Model('categoryarea')->column(['categoryarea_id', 'name'])->where([[[['level', '=', 0], ['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();
		$a_headbar_categoryarea = [];
		$a_headbar_category = [];
		foreach ($m_categoryarea as $k0 => $v0) {
			$a_headbar_categoryarea[] = [
				'name' => \Core\Lang::i18n($v0['name']),
				'url' => self::url('album', 'index', ['categoryarea_id' => $v0['categoryarea_id']]),
			];

			$categoryarea_category = Model('categoryarea_category')->column(['category_id'])->where([[[['categoryarea_id', '=', $v0['categoryarea_id']], ['act', '=', 'open']], 'and']])->fetchAll();
			foreach ($categoryarea_category as $k1 => $v1) {
				$categoryarea_category_id[$k0][] = $v1['category_id'];
			}
		}
		self::$data['headbar_categoryarea'] = $a_headbar_categoryarea;

		//category
		foreach ($categoryarea_category_id as $k0 => $v0) {
			$a_headbar_category[$k0] = Model('category')->column(['name', 'category_id'])->where([[[['category_id', 'in', $v0], ['act', '=', 'open']], 'and']])->fetchAll();
		}
		self::$data['headbar_category'] = $a_headbar_category;

		//rank
		self::$data['headbar_rank'] = [
			['name' => _('Hot'), 'url' => self::url('album', 'index', ['rank_id' => 0])],
			['name' => _('Free'), 'url' => self::url('album', 'index', ['rank_id' => 1])],
			['name' => _('Sponsored'), 'url' => self::url('album', 'index', ['rank_id' => 2])],
			['name' => _('Latest'), 'url' => self::url('album', 'index', ['rank_id' => 3])],
		];

		//search
		$searchtype = isset($_GET['searchtype']) ? urldecode($_GET['searchtype']) : null;
		self::$data['headbar_searchtype'] = $searchtype;
		$searchkey = (isset($_GET['searchkey']) && $_GET['searchkey'] !== '') ? urldecode($_GET['searchkey']) : null;
		self::$data['headbar_searchkey'] = htmlspecialchars($searchkey);

		$user = self::user_get();
		$a_user = [];
		if (!empty($user)) {
			$a_user = [
				'user_id' => $user['user_id'],
				'name' => $user['name'],
				'creative' => $user['creative'],
				'picture' => URL_STORAGE . Core::get_userpicture($user['user_id']),
				'way' => $user['way'],
			];
		}
		self::$data['headbar_user'] = $a_user;

		//通知中心未讀提示 icon
		self::$data['pushqueue_viewed_icon'] = ($user && \pushqueueModel::hasUnviewed($user['user_id']))
			? '<span class="notifier_icon"></span>'
			: null;

		/**
		 * headbar上的驚嘆號icon提示
		 */
		if (!empty($user) && M_FUNCTION != 'verify') {
			$settings_viewed = (new \userModel)
				->column(['setting_viewed'])
				->where([[[['user_id', '=', $user['user_id']]], 'and']])
				->fetchColumn();

			if (!$settings_viewed) {
				$import_icon_show = '<a href="' . self::url('creative', 'content', ['user_id' => $user['user_id'], 'edit' => true, 'setting_viewed' => true]) . '" class="cicle_btn" title="' . _('到個人專區介紹自己吧!') . '" style="z-index: 100"><i class="num2"> </i></a>';
				self::$data['import_icon_show'] = $import_icon_show;
			}
		}

		/**
		 *  特定頁面登入後回到原頁面 login_redirect
		 *  album::content
		 */
		switch (M_CLASS) {
			case 'album':
				$login_redirect = (M_FUNCTION == 'content') ? ['redirect' => self::url('album', 'content', ['album_id' => $_GET['album_id'], 'categoryarea_id' => \albumModel::getCategoryAreaId($_GET['album_id'])])] : [];
				break;

			default:
				$login_redirect = null;
				break;
		}

		self::$data['login_redirect'] = $login_redirect;

		self::$html->set_css(static_file('js/jquery.bxslider/jquery.bxslider.css'), 'href');
		self::$html->set_js(static_file('js/jquery.bxslider/jquery.bxslider.min.js'), 'src');

		self::$html->set_js(static_file('js/jquery-timeago-master/js/jquery.timeago.js'), 'src');
		self::$html->set_js(static_file('js/jquery-timeago-master/js/jquery.timeago.zh-TW.js'), 'src');

		self::$html->set_js(static_file('js/bootstrap.min.js'), 'src');
		self::$html->set_css(static_file('css/bootstrap.min.css'), 'href');
		self::$html->set_css(static_file('css/fontello.css'), 'href');
		self::$html->set_css(static_file('css/font-awesome/css/font-awesome.css'), 'href');

		self::$html->datalistKit();
		self::$html->jquery_cookie();

		self::$view['headbar'] = view(M_PACKAGE, null, null, 'v2/headbar');
	}

	function foot_v2()
	{
//		self::$html->set_js(static_file('js/all.min.js'), 'src');
		self::$html->set_js(static_file('js/jquery.nicescroll.min.js'), 'src');

		//jBox
		self::$html->jbox();

		self::$view['foot'] = view(M_PACKAGE, null, null, 'v2/foot');
	}

	function footbar_v2()
	{
		$user = self::user_get();

		//lang
		$m_lang = Model('lang')->order(['sequence' => 'asc'])->fetchAll();
		$a_headbar_lang = [];
		foreach ($m_lang as $v0) {
			$a_headbar_lang[] = [
				'name' => $v0['name'],
				'act' => $v0['act'],
				'url' => self::url(M_CLASS, M_FUNCTION, array_merge(query_string_parse(), ['lang' => $v0['lang_id']])),
			];
		}

		//tutorial
		$tutorialUrl = (!$user['tutorial_viewed']) ? self::url('tutorial', 'index', ['tutorial_viewed' => 1]) : self::url('tutorial', 'index');
		self::$data['tutorialUrl'] = $tutorialUrl;
		
		$tutorialIcon = (!$user['tutorial_viewed'] && !empty($user)) ? '<i class="num2"> </i>' : null;
		self::$data['tutorialIcon'] = $tutorialIcon;

		self::$data['headbar_lang'] = $a_headbar_lang;

		self::$view['footbar'] = view(M_PACKAGE, null, null, 'v2/footbar');
	}
}