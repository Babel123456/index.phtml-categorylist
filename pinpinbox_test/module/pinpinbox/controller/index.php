<?php

class indexController extends frontstageController
{
    function __construct()
    {
    }

    function adjustapp()
    {
        //不接受桌面訪問
        if (!SDK('Mobile_Detect')->isMobile()) redirect(parent::url('index'));

        $album_id = empty($_GET['album_id']) ? null : $_GET['album_id'];

        $user = parent::user_get();

        if (!is_null($album_id)) {
            $url = parent::deeplink('album', 'content', ['album_id' => $album_id]);
        } else {
            $url = parent::deeplink('index');
        }
        parent::$data['url'] = $url;

        $view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        $this->index_nav();
        parent::$view[] = $view;
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
    }

    function index_nav()
    {
    }

    function qanda()
    {
        $qanda = json_decode(Core::settings('QANDA'));

        //從Json中列出所有type填入變數(不重複)
        $type = array();
        if (!empty($qanda)) {
            foreach ($qanda as $v) {				
                if (!in_array($v->type, $type)) $type[] = $v->type;
			}
        }
        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Q&A'),
            array(_('Q&A'))
        );

        parent::$data['qanda'] = $qanda;
        parent::$data['type'] = $type;
        $view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        $this->index_nav();
        parent::$view[] = $view;
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
        parent::$html->set_css(static_file('js/footable/css/footable.core.css'), 'href');
        parent::$html->set_css(static_file('js/footable/css/footable.standalone.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/footable/js/footable.js'), 'src');
        parent::$html->set_js(static_file('js/footable/js/footable.filter.js'), 'src');
        parent::$html->set_js(static_file('js/footable/js/footable.paginate.js'), 'src');
        parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
    }

    function index()
    {
        /**
         * ad (banner)
         */
        $m_ad = (new adModel())->getByArea(2, \Core\Lang::get());
        $a_ad = [];
        foreach ($m_ad as $v0) {
            $a_ad[] = [
                'html' => URL_UPLOAD . $v0['ad']['image'],
                'name' => $v0['ad']['name'],
                'title' => $v0['ad']['title'],
                'url' => json_decode($v0['ad']['url'], true),
            ];
        }
        parent::$data['banners'] = $a_ad;
		
        /**
         * 贊助次數
         */
        $m_albumqueue = (new albumqueueModel())->column(['COUNT(1) as albumqueue'])->fetchColumn();
        parent::$data['albumqueueCount'] = $m_albumqueue;
        $m_album = (new albumModel())->column(['COUNT(1) as album'])->fetchColumn();
        parent::$data['albumPublishCount'] = $m_album;

		/**
         * 創作人數量
         */
        if (!Session::get('activityUsers')) {
            $activityUsers = (new albumModel())->column(['COUNT(DISTINCT(`user_id`))'])->fetchColumn();
            $value = [
                'activityUsers' => $activityUsers,
                'availabilityDate' => strtotime(date('Y-m-d 00:00:01', strtotime("+1 day"))),
            ];
            Session::set('activityUsers', $value);
        } else {
            $s_activityUsers = Session::get('activityUsers');
            if (time() > $s_activityUsers['availabilityDate']) {
                Session::delete('activityUsers');
                $activityUsers = (new albumModel())->column(['COUNT(DISTINCT(`user_id`))'])->fetchColumn();
                $value = [
                    'activityUsers' => $activityUsers,
                    'availabilityDate' => strtotime(date('Y-m-d 00:00:01', strtotime("+1 day"))),
                ];
                Session::set('activityUsers', $value);
            } else {
                $activityUsers = $s_activityUsers['activityUsers'];
            }
        }
        parent::$data['activityUsers'] = $activityUsers;

        /**
         * 熱門 / 推薦 創作人
         */
        $indexCreative = [];

        $m_indexpopularity = (new indexpopularityModel())
            ->column(['indexpopularity_id', 'name', 'exhibit'])
            ->where([[[['act', '=', 'open']], 'and']])
            ->order(['indexpopularity_id' => 'desc'])
            ->fetchAll();

        foreach ($m_indexpopularity as $k0 => $indexpopularity) {
            $creator_ids = str_replace(['[', ']', '"'], '', explode(',', $indexpopularity['exhibit']));

            $users = (new \userModel)
                ->where([[[['user_id', 'in', $creator_ids]], 'and']])
                ->fetchAll();

            $indexCreative[$k0]['name'] = $indexpopularity['name'];

            foreach ($users as $user) {
                $m_statistics = (new albumModel)
                    ->column([
                        'userstatistics.besponsored + userstatistics.besponsored_manual besponsored',
                        'SUM(albumstatistics.viewed) viewed',
                    ])
                    ->join([
                        ['LEFT JOIN', 'albumstatistics', 'USING(`album_id`)'],
                        ['INNER JOIN', 'userstatistics', 'on userstatistics.user_id = album.user_id'],
                    ])
                    ->where([[[['album.user_id', '=', $user['user_id']]], 'and']])
                    ->fetch();

                $indexCreative[$k0]['user'][] = [
                    'user_id' => $user['user_id'],
                    'name' => $user['name'],
                    'description' => strip_tags($user['description']),
                    'avatar' => path2url(PATH_STORAGE . \userModel::getPicture($user['user_id'])),
                    'cover' => path2url((new \Core\Image)->set(PATH_STORAGE . Core::get_usercover($user['user_id']))->setSize(292, 137)->save()),
                    'url' => Core::get_creative_url($user['user_id']),
                    'creative_name' => $user['creative_name'],
                    'viewed' => $m_statistics['viewed'],
                    'besponsored' => $m_statistics['besponsored'],
                ];
            }
        }

        parent::$data['indexCreative'] = $indexCreative;

        /**
         * 新加入
         */
        $column = ['indexcreative.user_id', 'indexcreative.indexcreative_id', 'user.name', 'user.description', 'user.creative_name'];
        $m_indexcreative = (new indexcreativeModel())->column($column)
            ->join([['LEFT JOIN', 'user', 'USING(user_id)']])
            ->where([[[['indexcreative.act', '=', 'open'], ['user.act', '=', 'open']], 'and']])
            ->order(['indexcreative_id' => 'asc'])->limit(3)->fetchAll();

        $newer_indexCreative = [];
        foreach ($m_indexcreative as $user) {
            $newer_indexCreative[] = [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'description' => strip_tags($user['description']),
                'avatar' => path2url((new \Core\Image)->set(PATH_STORAGE . \userModel::getPicture($user['user_id']))->setSize(300, 300)->save()),
                'url' => Core::get_creative_url($user['user_id']),
                'creative_name' => $user['creative_name'],
            ];
        }

        parent::$data['newer_indexCreative'] = $newer_indexCreative;

        $m_categoryareas = (new categoryareaModel())->where([[[['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->limit(8)->fetchAll();
        $a_categoryareas = [];
        foreach ($m_categoryareas as $categoryarea) {
            $a_categoryareas[] = [
                'categoryarea_id' => $categoryarea['categoryarea_id'],
                'image_204x204' => URL_UPLOAD . $categoryarea['image_204x204'],
                'name' => $categoryarea['name'],
            ];
        }

        parent::$data['categoryareas'] = $a_categoryareas;

        parent::head_v2();
        parent::headbar_v2();
        parent::foot_v2();
        parent::footbar_v2();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);

        //owl
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.carousel.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.theme.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.transitions.css'), 'href');
        parent::$html->set_js(static_file('js/owl.carousel/js/owl.carousel.min.js'), 'src');

        parent::$html->set_js(static_file('js/counterjs/js/jquery.counterup.min.js'), 'src');
        parent::$html->set_js(static_file('js/waypoint/js/waypoint.js'), 'src');

        parent::$html->set_css(static_file('css/style_v2.css'), 'href');

    }

    function index_old()
    {
        /**
         * ad
         */
        $m_ad = Model('ad')->getByArea(2, \Core\Lang::get());
        $a_ad = [];
        foreach ($m_ad as $v0) {
            $a_ad[] = [
                'html' => $v0['ad']['html'],
                'html_mobile' => $v0['ad']['html_mobile'],
            ];
        }
        parent::$data['ad'] = $a_ad;

        /**
         * 分類icon
         */
        $categoryarea_id = (!empty($_GET['categoryarea_id'])) ? $_GET['categoryarea_id'] : 0;

        $categoryareaIcon = [[
            'categoryarea_id' => null,
            'name' => _('首頁'),
            'url' => self::url('index'),
            'icon' => ($categoryarea_id == 0) ? static_file('images/assets-v6/home.svg') : static_file('images/assets-v5/explore_01_n.svg'),
            'iconOpposite' => ($categoryarea_id == 0) ? static_file('images/assets-v6/home.svg') : static_file('images/assets-v5/explore_01_n.svg'),
        ], [
            'categoryarea_id' => null,
            'name' => _('釘達人'),
            'url' => self::url('album', 'explore'),
            'icon' => static_file('images/assets-v5/explore_01_n.svg'),
            'iconOpposite' => static_file('images/assets-v5/explore_01.svg'),
        ]];

        $m_categoryarea = (new categoryareaModel())->column(['categoryarea_id', 'name', 'image', 'image_n'])->where([[[['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();

        foreach ($m_categoryarea as $k0 => $v0) {
            $categoryareaIcon[] = [
                'categoryarea_id' => $v0['categoryarea_id'],
                'name' => $v0['name'],
                'url' => self::url('album', 'explore', ['categoryarea_id' => $v0['categoryarea_id']]),
                'icon' => URL_UPLOAD . $v0['image_n'],
                'iconOpposite' => URL_UPLOAD . $v0['image'],
            ];
        }
        parent::$data['categoryareaIcon'] = $categoryareaIcon;

        /**
         *  釘創作
         */
        $a_indexpopularity = [];

        $m_indexpopularity = (new \indexpopularityModel)
            ->where([[[['indexpopularity.act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        foreach ($m_indexpopularity as $k0 => $v0) {
            $a_tabs[] = $v0['name'];
            $a_exhibit_album_id = json_decode($v0['exhibit'], true);

            $tmp = [];
            $column = [
                'album.album_id',
                'album.user_id',
                'album.cover',
                'album.indexpopularity_cover',
                'album.name album_name',
                'categoryarea_category.categoryarea_id',
                'user.name user_name'
            ];
            $join = [
                ['left join', 'user', 'using(user_id)'],
                ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
            ];
            $where = [[[['album.act', '=', 'open'], ['album.album_id', 'in', $a_exhibit_album_id]], 'and']];

            $m_album = (new \albumModel)
                ->column($column)
                ->join($join)
                ->where($where)
                ->order(['FIELD' => '(album_id, ' . implode(',', $a_exhibit_album_id) . ')'])
                ->fetchAll();

            foreach ($m_album as $v1) {
                $tmp[] = [
                    'album_id' => $v1['album_id'],
                    'album_name' => $v1['album_name'],
                    'album_cover' => path2url((new \Core\Image)->set(PATH_UPLOAD . $v1['indexpopularity_cover'])->setSize(\Config\Image::S4, \Config\Image::S4)->save()),
                    'album_url' => parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id']]),
                    'categoryarea_id' => $v1['categoryarea_id'],
                    'user_id' => $v1['user_id'],
                    'user_name' => $v1['user_name'],
                    'user_pic' => URL_STORAGE . Core::get_userpicture($v1['user_id']),
                    'user_url' => Core::get_creative_url($v1['user_id'], 'album_id_' . $v1['album_id']),
                    'album_tags' => (new albumModel())->hasGiftTags($v1['album_id']),
                ];
            }

            $a_indexpopularity[] = [
                'tab_name' => $v0['name'],
                'album_info' => $tmp,
            ];
        }

        parent::$data['tabs'] = $a_tabs;
        parent::$data['indexpopularity'] = $a_indexpopularity;

        /**
         *  釘關注
         */
        $a_indexcreative = [];

        $array_user_recommended = (new \userModel)->getRecommended(null, null, '0,16');

        foreach ($array_user_recommended as $object) {
            $user = $object['user'];

            $a_indexcreative[] = [
                'user_id' => $user['user_id'],
                'user_pic' => path2url(PATH_STORAGE . \userModel::getPicture($user['user_id'])),
                'user_url' => Core::get_creative_url($user['user_id']),
                'user_name' => $user['name'],
                'user_description' => strip_tags($user['description']),
                'user_belong' => (new \creativeModel)->creative_belong($user['user_id']),
            ];
        }

        parent::$data['indexcreative'] = array_chunk($a_indexcreative, 4);

        /**
         *  最新活動布局
         */
        $column = ['event.event_id', 'event.name', 'event.title', 'event.image', 'event.starttime', 'event.endtime', 'event.contribution', 'SUM(eventjoin.count) AS count', 'eventstatistics.viewed'];
        $join = [['left join', 'eventjoin', 'using(event_id)'], ['left join', 'eventstatistics', 'using(event_id)']];
        $where = [[[['event.act', '=', 'open'], ['event.index_display', '=', 1], ['event.starttime', '<', date('Y-m-d H:i:s')], ['event.endtime', '>', date('Y-m-d H:i:s')]], 'and']];
        $m_event = Model('event')->column($column)->join($join)->where($where)->group(['event.event_id'])->fetchAll();
        $a_event = [];
        if (!empty($m_event)) {
            foreach ($m_event as $k0 => $v0) {
                if (!empty($v0['event_id'])) {
                    //eventjoinAlbumstatistics
                    $c_eventjoinAlbumstatistics = Model('eventjoin')->column(['SUM(albumstatistics.viewed)'])->join([['left join', 'albumstatistics', 'using(`album_id`)']])->where([[[['event_id', '=', $v0['event_id']]], 'and']])->fetchColumn();

                    $a_event[] = [
                        'event_id' => $v0['event_id'],
                        'name' => $v0['name'],
                        'title' => $v0['title'],
                        'url' => parent::url('event', 'content', ['event_id' => $v0['event_id']]),
                        'image' => URL_UPLOAD . getimageresize($v0['image'], 287, 208),
                        'starttime' => $v0['starttime'],
                        'endtime' => $v0['endtime'],
                        'contribution' => $v0['contribution'],
                        'status' => (time() > strtotime($v0['endtime'])) ? 'expired' : 'unexpired',
                        'popularity' => $v0['count'] + $v0['viewed'] + $c_eventjoinAlbumstatistics,
                    ];
                }
            }
        }
        parent::$data['event'] = $a_event;

        /**
         *  模板推薦布局
         */
        $a_template = [];
        $m_indextemplate = Model('indextemplate')->where([[[['act', '=', 'open']], 'and']])->limit(1)->fetch();

        $a_exhibit_template_id = json_decode($m_indextemplate['exhibit'], true);
        $column = ['template.template_id', 'template.user_id', 'template.image', 'template.name template_name', 'user.name user_name'];
        $join = [['left join', 'user', 'using(user_id)']];
        $where = [[[['template.act', '=', 'open'], ['template.template_id', 'in', $a_exhibit_template_id]], 'and']];
        $m_template = Model('template')->column($column)->join($join)->where($where)->order(['FIELD' => '(template_id, ' . implode(',', $a_exhibit_template_id) . ')'])->fetchAll();

        foreach ($m_template as $k0 => $v0) {
            $a_template[] = [
                'template_id' => $v0['template_id'],
                'template_name' => $v0['template_name'],
                'template_cover' => URL_UPLOAD . getimageresize('pinpinbox' . $v0['image'], 668, 1002),
                'template_url' => parent::url('template', 'content', ['template_id' => $v0['template_id'], 'click' => 'template_name']),
                'user_id' => $v0['user_id'],
                'user_name' => $v0['user_name'],
                'user_pic' => URL_STORAGE . Core::get_userpicture($v0['user_id']),
                'user_url' => Core::get_creative_url($v0['user_id'], 'template_id_' . $v0['template_id']),
            ];
        }
        parent::$data['template'] = $a_template;

        /**
         *  方塊磚布局
         */
        $m_indexelement = Model('indexelement')->where([[[['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();
        $a_indexelement = Array();
        foreach ($m_indexelement as $k0 => $v0) {
            $url = URL_ROOT . $v0['url'];
            $a_indexelement[$k0] = [
                'indexelement_id' => $v0['indexelement_id'],
                'name' => $v0['name'],
                'image' => (fileinfo($v0['image'])['extension'] == 'gif') ? URL_UPLOAD . $v0['image'] : URL_UPLOAD . getimageresize($v0['image'], 200, 160),
                'icon' => $v0['icon'],
                'url' => $url,
            ];
            $image_transform = json_decode($v0['image_transform'], true);
            $img_hover = Array();

            switch ($v0['indexelement_for']) {
                case 'category' :
                    break;
                case 'categoryarea' :
                    switch ($image_transform['transform']) {
                        case 'single':
                            $tmp_image = (fileinfo($image_transform['source'])['extension'] == 'gif') ? URL_UPLOAD . $image_transform['source'] : URL_UPLOAD . getimageresize($image_transform['source'], 200, 160);
                            $img_hover[] = '<a href="' . $url . '"><img src="' . $tmp_image . '"></a>';
                            break;

                        case 'multi' :
                            $column = ['album.album_id', 'album.cover', 'categoryarea.categoryarea_id', 'categoryarea_category.category_id'];
                            $where = [[[['categoryarea_category.categoryarea_id', '=', $image_transform['target']], ['album.album_id', '!=', 'NULL'], ['album.act', '=', 'open']], 'and']];
                            $join = [['left join', 'categoryarea_category', 'using(categoryarea_id)'], ['left join', 'album', 'using(category_id)'], ['left join', 'albumstatistics', 'using(album_id)']];
                            $order = ($image_transform['sort'] == 'inserttime') ? ['album.inserttime' => 'desc'] : ['albumstatistics.viewed' => 'desc'];

                            $_m = (new \categoryareaModel)
                                ->column($column)
                                ->join($join)
                                ->where($where)
                                ->order($order)
                                ->limit(6)
                                ->fetchAll();

                            foreach ($_m as $v1) {
                                $img_hover[] = '<a href="' . parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id']]) . '"><img src="' . URL_UPLOAD . getimageresize($v1['cover'], 50, 75) . '"></a>';
                            }

                            break;

                        default:
                            $img_hover[] = '<a href="' . $url . '"><img src="' . URL_UPLOAD . getimageresize($v0['image'], 200, 160) . '"></a>';
                            break;
                    }

                    break;

                case 'creative' :
                    switch ($image_transform['transform']) {
                        case 'single':
                            $tmp_image = (fileinfo($image_transform['source'])['extension'] == 'gif') ? URL_UPLOAD . $image_transform['source'] : URL_UPLOAD . getimageresize($image_transform['source'], 200, 160);
                            $img_hover[] = '<a href="' . $url . '"><img src="' . $tmp_image . '"></a>';
                            break;

                        case 'multi' :
                            $where = [[[['album.user_id', '=', $image_transform['target']], ['album.act', '=', 'open']], 'and']];
                            $order = ($image_transform['sort'] == 'inserttime') ? ['album.inserttime' => 'desc'] : ['albumstatistics.viewed' => 'desc'];

                            $_m = (new \albumModel)
                                ->column([
                                    'album.album_id',
                                    'album.cover',
                                    'albumstatistics.count',
                                    'albumstatistics.viewed',
                                    'categoryarea_category.categoryarea_id',
                                ])
                                ->join([
                                    ['left join', 'albumstatistics', 'using(album_id)'],
                                    ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
                                ])
                                ->where($where)
                                ->order($order)
                                ->limit(6)
                                ->fetchAll();

                            foreach ($_m as $v1) {
                                $img_hover[] = '<a href="' . parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id']]) . '"><img src="' . URL_UPLOAD . getimageresize($v1['cover'], 50, 75) . '"></a>';
                            }

                            break;

                        default:
                            $img_hover[] = '<a href="' . $url . '"><img src="' . URL_UPLOAD . getimageresize($v0['image'], 200, 160) . '"></a>';
                            break;
                    }

                    break;

                case 'custom' :
                    switch ($image_transform['transform']) {
                        case 'single':
                            $tmp_image = (fileinfo($image_transform['source'])['extension'] == 'gif') ? URL_UPLOAD . $image_transform['source'] : URL_UPLOAD . getimageresize($image_transform['source'], 200, 160);
                            $img_hover[] = '<a href="' . $url . '"><img src="' . $tmp_image . '"></a>';
                            break;

                        default:
                            $img_hover[] = '<a href="' . $v0['url'] . '"><img src="' . URL_UPLOAD . getimageresize($v0['image'], 200, 160) . '"></a>';
                            break;
                    }
                    $a_indexelement[$k0]['url'] = $v0['url'];
                    break;

                case 'region' :
                    switch ($image_transform['transform']) {
                        case 'single':
                            $tmp_image = (fileinfo($image_transform['source'])['extension'] == 'gif') ? URL_UPLOAD . $image_transform['source'] : URL_UPLOAD . getimageresize($image_transform['source'], 200, 160);
                            $img_hover[] = '<a href="' . $url . '"><img src="' . $tmp_image . '"></a>';
                            break;

                        case 'multi' :
                            switch ($image_transform['target']) {

                                case 'album_keyword':
                                    $s_album = Solr('album')->column(['album_id'])->where([[[['_text_', '=', $image_transform['keyword']]], 'and']])->fetchAll();

                                    foreach ($s_album as $k1 => $v1) {
                                        $a_album_id[] = $v1['album_id'];
                                    }

                                    $where = [[[['album.album_id', '!=', 'NULL'], ['album.act', '=', 'open'], ['album.album_id', 'in', $a_album_id]], 'and']];
                                    $order = ($image_transform['sort'] == 'inserttime') ? ['album.inserttime' => 'desc'] : ['albumstatistics.viewed' => 'desc'];

                                    $_m = (new \categoryareaModel)
                                        ->column([
                                            'album.album_id',
                                            'album.cover',
                                            'categoryarea.categoryarea_id',
                                            'categoryarea_category.category_id',
                                        ])
                                        ->join([
                                            ['left join', 'categoryarea_category', 'using(categoryarea_id)'],
                                            ['left join', 'album', 'using(category_id)'],
                                            ['left join', 'albumstatistics', 'using(album_id)']
                                        ])
                                        ->where($where)
                                        ->order($order)
                                        ->limit(6)
                                        ->fetchAll();

                                    foreach ($_m as $v1) {
                                        $img_hover[] = '<a href="' . parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id']]) . '"><img src="' . URL_UPLOAD . getimageresize($v1['cover'], 50, 75) . '"></a>';
                                    }
                                    break;

                                case 'template_keyword':
                                    $s_template = Solr('template')->column(['template_id'])->where([[[['_text_', '=', $image_transform['keyword']]], 'and']])->fetchAll();
                                    foreach ($s_template as $k1 => $v1) {
                                        $a_template_id[] = $v1['template_id'];
                                    }
                                    $column = ['template.template_id', 'template.image'];
                                    $where = [[[['template.template_id', '!=', 'NULL'], ['template.act', '=', 'open'], ['template.template_id', 'in', $a_template_id]], 'and']];
                                    $join = [['left join', 'templatestatistics', 'using(template_id)']];
                                    $order = ($image_transform['sort'] == 'inserttime') ? ['template.inserttime' => 'desc'] : ['templatestatistics.viewed' => 'desc'];
                                    $_m = Model('template')->column($column)->join($join)->where($where)->order($order)->limit(6)->fetchAll();
                                    foreach ($_m as $k1 => $v1) {
                                        $img_hover[] = '<a href="' . parent::url('template', 'content', ['template_id' => $v1['template_id']]) . '"><img src="' . URL_UPLOAD . getimageresize('pinpinbox/' . $v1['image'], 50, 75) . '"></a>';
                                    }
                                    break;

                                default:
                                    $creative_group = Model('creative')->creative_group([$image_transform['target']]);
                                    foreach ($creative_group[0]['sort'] as $k1 => $v1) {
                                        $a_user_id[] = $v1['user_id'];
                                    }
                                    if (!empty($a_user_id)) foreach ($a_user_id as $k1 => $v1) {
                                        $img_hover[] = '<a href="' . Core::get_creative_url($v1) . '"><img class="creative" src="' . URL_STORAGE . Core::get_userpicture($v1) . '"></a>';
                                    }
                                    break;
                            }
                            break;

                        default:
                            $img_hover[] = '<a href="' . $url . '"><img src="' . URL_UPLOAD . getimageresize($v0['image'], 200, 160) . '"></a>';
                            break;
                    }

                    break;

                case 'event' :
                    switch ($image_transform['transform']) {
                        case 'single':
                            $tmp_image = (fileinfo($image_transform['source'])['extension'] == 'gif') ? URL_UPLOAD . $image_transform['source'] : URL_UPLOAD . getimageresize($image_transform['source'], 200, 160);
                            $img_hover[] = '<a href="' . $url . '"><img src="' . $tmp_image . '"></a>';

                            break;

                        case 'multi' :
                            $where = [[[['event.event_id', '=', $image_transform['target']], ['event.act', '=', 'open'], ['album.act', '=', 'open']], 'and']];
                            $order = ($image_transform['sort'] == 'inserttime') ? ['eventjoin.inserttime' => 'desc'] : ['eventjoin.count' => 'desc'];

                            $_m = (new \eventModel)
                                ->column([
                                    'event.event_id',
                                    'eventjoin.count',
                                    'eventjoin.inserttime',
                                    'album.album_id', 'album.cover',
                                    'categoryarea_category.categoryarea_id',
                                ])
                                ->join([
                                    ['left join', 'eventjoin', 'using(event_id)'],
                                    ['left join', 'album', 'using(album_id)'],
                                    ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
                                ])
                                ->where($where)
                                ->order($order)
                                ->limit(6)
                                ->fetchAll();

                            foreach ($_m as $v1) {
                                $img_hover[] = '<a href="' . parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id']]) . '"><img src="' . URL_UPLOAD . getimageresize($v1['cover'], 50, 75) . '"></a>';
                            }
                            break;

                        default:
                            $img_hover[] = '<a href="' . $url . '"><img src="' . URL_UPLOAD . getimageresize($v0['image'], 200, 160) . '"></a>';
                            break;
                    }
                    break;

                case 'link' :
                    switch ($image_transform['transform']) {
                        case 'single':
                            $tmp_image = (fileinfo($image_transform['source'])['extension'] == 'gif') ? URL_UPLOAD . $image_transform['source'] : URL_UPLOAD . getimageresize($image_transform['source'], 200, 160);
                            $img_hover[] = '<a href="' . $url . '"><img src="' . $tmp_image . '"></a>';
                            break;

                        case 'multi' :
                            switch ($image_transform['sort']) {
                                case 'hot' :
                                    $join = array();
                                    $join[] = array('left join', 'album', 'using(user_id)');
                                    $join[] = array('left join', 'albumstatistics', 'using(album_id)');
                                    $join[] = array('left join', 'follow', 'using(user_id)');
                                    $column = [
                                        'user.user_id',
                                        'user.name user_name',
                                        'SUM(albumstatistics.viewed) as viewed'
                                    ];
                                    $where = array();
                                    $where[] = [[['user.act', '=', 'open'], ['album.act', '=', 'open']], 'and'];
                                    if (!empty($s_user)) $where[0][0][] = ['user.user_id', 'in', array_column($s_user, 'user_id')];
                                    $m_user = Model('user')->column($column)->join($join)->where($where)->order(array('viewed' => 'desc'))->group(['user.user_id'])->limit(6)->fetchAll();
                                    break;

                                case 'inserttime' :
                                    $join = array();
                                    $join[] = array('left join', 'album', 'using(user_id)');
                                    $join[] = array('left join', 'albumstatistics', 'using(album_id)');
                                    $join[] = array('left join', 'follow', 'using(user_id)');
                                    $join[] = array('left join', 'creative', 'using(user_id)');
                                    $column = [
                                        'user.user_id',
                                        'user.name user_name',
                                        'creative.inserttime creative_inserttime'
                                    ];
                                    $where = array();
                                    $where[] = [[['user.act', '=', 'open'], ['album.act', '=', 'open']], 'and'];
                                    if (!empty($s_user)) $where[0][0][] = ['user.user_id', 'in', array_column($s_user, 'user_id')];
                                    $m_user = Model('user')->column($column)->join($join)->where($where)->order(array('creative_inserttime' => 'desc'))->group(['user.user_id'])->limit(6)->fetchAll();
                                    break;
                            }

                            foreach ($m_user as $k1 => $v1) {
                                $img_hover[] = '<a href="' . Core::get_creative_url($v1['user_id']) . '"><img class="creative" src="' . URL_STORAGE . Core::get_userpicture($v1['user_id']) . '"></a>';
                            }
                            break;

                        default:
                            $img_hover[] = '<a href="' . $url . '"><img src="' . URL_UPLOAD . getimageresize($v0['image'], 200, 160) . '"></a>';
                            break;
                    }
                    break;
            }
            $a_indexelement[$k0]['img_hover'] = $img_hover;
            $a_indexelement[$k0]['transform'] = $image_transform['transform'];
        }

        /**
         * 取得形象影片連結
         */
        $publicity_film = Core::settings('PUBLICITY_FILM');
        parent::$data['publicity_film'] = '<iframe height="450" src="' . $publicity_film . '" frameborder="0" allowfullscreen></iframe>';

        /**
         * 計算使用者總人至 counterJS做動畫效果
         */
        $numberofUsers = 0;
        $numberofUsers = (New userModel())->column(['COUNT(1)'])->fetchColumn();
        parent::$data['numberofUsers'] = $numberofUsers + 10000;

        $get_urlScheme = $this->get_urlScheme_album_id();
        parent::$data['urlScheme'] = $get_urlScheme;

        parent::$data['indexelement'] = $a_indexelement;
        parent::$data['user'] = $m_user = Model('user')->getSession();
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);

        //lightgallery
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
        parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');

        //owl
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.carousel.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.theme.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.transitions.css'), 'href');
        parent::$html->set_js(static_file('js/owl.carousel/js/owl.carousel.min.js'), 'src');

        //mediaelement
        parent::$html->set_css(static_file('js/mediaelement-2.22.0/mediaelementplayer.min.css'), 'href');
        parent::$html->set_js(static_file('js/mediaelement-2.22.0/mediaelement-and-player.min.js'), 'src');

        parent::$html->set_js(static_file('js/counterjs/js/jquery.counterup.min.js'), 'src');
        parent::$html->set_js(static_file('js/waypoint/js/waypoint.js'), 'src');

        //jquery-textcomplete
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.textcomplete.js'), 'src');
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.overlay.js'), 'src');
        parent::$html->set_css(static_file('js/jquery-textcomplete/media/stylesheets/textcomplete.css'), 'href');

        parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.countdown.js'), 'src');
        parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
        parent::$html->jbox();
    }

    function privacy()
    {
        parent::$data['privacy'] = Core::settings('PRIVACY');

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Privacy statement'),
            array(_('Privacy statement'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        $this->index_nav();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
    }

    function copyright()
    {
        parent::$data['copyright'] = Core::settings('COPYRIGHT');

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Copyright statement'),
            array(_('Copyright statement'))

        );
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        $this->index_nav();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
    }

    function terms()
    {
        parent::$data['terms'] = Core::settings('TERMS');

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Platform specifications'),
            array(_('Platform specifications'))

        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        $this->index_nav();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
    }

    function payment_terms()
    {
        parent::$data['payment_terms'] = Core::settings('PAYMENT_TERMS');

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Introduction to the proposal'),
            array(_('Introduction to the proposal'))

        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        $this->index_nav();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
    }

    function show_photo()
    {
        if (is_ajax()) {
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            if ($album_id == null) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_album = Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
            if (empty($m_album)) {
                json_encode_return(0, _('Album does not exist.'));
            } elseif ($m_album['act'] == 'delete') {
                json_encode_return(0, _('Album has been deleted.'));
            }

            $user = parent::user_get();
            $favorited = false;
            $tmp0 = json_decode($m_album['preview'], true);

            if (!empty($user)) {
                $m_albumqueue = Model('albumqueue')->column(['visible'])->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->fetch();
                if ($m_albumqueue && !$m_albumqueue['visible']) {
                    Model('albumqueue')->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->edit(['visible' => 1]);
                }
                if ($m_albumqueue || $m_album['user_id'] == $user['user_id']) {
                    $favorited = true;
                    $tmp0 = Model('photo')->column(['image', 'usefor', 'video_refer', 'video_target'])->where([[[['album_id', '=', $album_id]], 'and']])->order(['sequence' => 'asc'])->fetchAll();
                }
            }

            $Image = new \Core\Image();

            $a_readable = [];
            if ($favorited) {
                foreach ((array)$tmp0 as $v0) {
                    if (!is_image(PATH_UPLOAD . $v0['image'])) continue;

                    $i_image = $Image->set(PATH_UPLOAD . $v0['image']);

                    $a_readable[] = [
                        'image' => URL_UPLOAD . $v0['image'],
                        'image_thumbnail' => fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize(\Config\Image::S2, \Config\Image::S2)->save())['url'],
                        'width' => $i_image->getWidth(),
                        'height' => $i_image->getHeight(),
                        'usefor' => $v0['usefor'],
                        'video_refer' => $v0['video_refer'],
                        'video_target' => ($v0['video_refer'] == 'file') ? URL_UPLOAD . $v0['video_target'] : $v0['video_target'],
                        'page' => count($m_album['photo']),
                    ];
                }
            } else {
                foreach ((array)$tmp0 as $v0) {
                    if (!is_image(PATH_UPLOAD . $v0)) continue;
                    $i_image = $Image->set(PATH_UPLOAD . $v0);
                    $a_readable[] = [
                        'image' => URL_UPLOAD . $v0,
                        'image_thumbnail' => fileinfo($i_image->set(PATH_UPLOAD . $v0)->setSize(\Config\Image::S2, \Config\Image::S2)->save())['url'],
                        'width' => $i_image->getWidth(),
                        'height' => $i_image->getHeight(),
                        'usefor' => null,
                        'video_refer' => null,
                        'video_target' => null,
                        'page' => count(json_decode($m_album['photo'], true)),
                    ];
                }

                //preview 小於 photo數量 => 引入收藏宣傳圖
                if (count($tmp0) < count(json_decode($m_album['photo'], true))) {
                    $a_readable[] = [
                        'image' => static_file('images/preview_end.jpg'),
                        'image_thumbnail' => static_file('images/kanban.png'),
                        'width' => 1336,
                        'height' => 2004,
                        'usefor' => null,
                        'video_refer' => null,
                        'video_target' => null,
                    ];
                }
            }
            if (empty($a_readable)) $a_readable[] = ['image' => static_file('images/origin.jpg'), 'width' => 683, 'height' => 1024];

            //albumstatistics
            (new albumModel())->increaseViewed($album_id);

            json_encode_return(1, null, null, ['favorited' => $favorited, 'readable' => $a_readable]);
        }
        die;
    }

    function show_template()
    {
        if (is_ajax()) {
            $template_id = empty($_POST['template_id']) ? null : $_POST['template_id'];
            if ($template_id == null) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_template = Model('template')->where([[[['template_id', '=', $template_id], ['act', '=', 'open']], 'and']])->fetch();
            if (empty($m_template)) json_encode_return(0, _('Album does not exist.'));

            $user = parent::user_get();
            $favorited = false;
            $Image = new \Core\Image();
            $a_readable = [];

            //宣傳圖
            if (!empty($m_template['image_promote'])) {
                foreach (json_decode($m_template['image_promote'], true) as $k0 => $v0) {
                    list($width, $height) = getimagesize(PATH_UPLOAD . M_PACKAGE . str_replace('.jpeg', '_1336x2004.jpeg', $v0));

                    $a_readable[] = array(
                        'image' => URL_UPLOAD . M_PACKAGE . $v0,
                        'image_thumbnail' => fileinfo($Image->set(PATH_UPLOAD . M_PACKAGE . $v0)->setSize(\Config\Image::S2, \Config\Image::S2)->save())['url'],
                        'width' => $width,
                        'height' => $height,
                    );
                }
            }

            //版型
            $tmp0 = json_decode($m_template['frame_upload'], true);
            foreach ((array)$tmp0 as $v0) {
                if (!is_image(PATH_UPLOAD . M_PACKAGE . $v0['src'])) continue;

                list($width, $height) = getimagesize(PATH_UPLOAD . M_PACKAGE . $v0['src']);
                $a_readable[] = array(
                    'image' => URL_UPLOAD . M_PACKAGE . $v0['src'],
                    'image_thumbnail' => fileinfo($Image->set(PATH_UPLOAD . M_PACKAGE . $v0['src'])->setSize(\Config\Image::S2, \Config\Image::S2)->save())['url'],
                    'width' => $width,
                    'height' => $height,
                );
            }
            if (empty($a_readable)) $a_readable[] = ['image' => static_file('images/origin.jpg'), 'width' => 683, 'height' => 1024];
            json_encode_return(1, null, null, ['favorited' => $favorited, 'readable' => $a_readable]);
        }
        die;
    }

    function get_urlScheme_album_id()
    {
        $data_uri = null;
        $user = parent::user_get();
        $data = parent::deeplink('index', 'index');
        if (!empty($user)) {
            $template_id = 0;

            list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->process2($user['user_id']));

            if (!$result) {
                list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->pretreat($user['user_id'], $template_id));
            }
            $data = parent::deeplink('diy', 'content', ['album_id' => $album_id, 'template_id' => 0, 'identity' => 'admin']);
        }

        return $data;
    }

    function getNotifications()
    {
        if (is_ajax()) {
            $user = parent::user_get();

            /**
             * 通知中心內容
             */
            $a_notifications[] = [
                'message' => _('目前沒有通知!'),
                'trigger_user_id' => null,
                'trigger_user_pic' => static_file('images/m_logo.png'),
                'trigger_user_url' => 'javascript:void(0)',
                'target_url' => 'javascript:void(0)',
                'time' => null,
            ];

            if (!empty($user)) {
                $Model_pushqueue = \pushqueueModel::getByUserId($user['user_id'], 8);

                if ($Model_pushqueue) {
                    $a_notifications = parent::notifications2data($Model_pushqueue);
                }
            }

            json_encode_return(1, null, null, $a_notifications);
        }
        die;
    }

}