<?php

class albumController extends frontstageController
{

    function __construct()
    {
    }

    function album_pc_nav()
    {
    }

    function album_mobile_nav()
    {
    }

    function album_tabs_rank()
    {
    }

    function buyalbum()
    {
        if (is_ajax()) {
            $result = 1;
            $message = _('感謝您的支持！'); // Mars : 181114 - 前面接作者名稱 xxx 感謝您的支持！ #2223
            $redirect = null;
            $data = null;

            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            if ($album_id === null) {
                $result = 0;
                $message = _('Abnormal process, please try again.');
                goto _return;
            }

            $user = parent::user_get();
            if (empty($user)) {
                $result = 2;
                $message = _('Please login first.');

                $redirect_param = [
                    'album_id' => $album_id,
                    'autobuy' => true,
                    'categoryarea_id' => \albumModel::getCategoryAreaId($album_id),
                ];
                if (isset($_GET['agent'])) $redirect_param['agent'] = $_GET['agent'];

                $redirect = parent::url('user', 'login', ['redirect' => parent::url('album', 'content', $redirect_param)]);
                goto _return;
            }

            $buy = empty($_POST['buy']) ? null : $_POST['buy'];
            if ($buy) {
                $m_album = (new albumModel())->column(['point', 'reward_after_collect', 'user.name user_name'])->join([['LEFT JOIN', 'user', 'USING(`user_id`)']])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
				$message = $m_album['user_name'].'&nbsp;'.$message ;
                $albumPoint = $m_album['point'];
                $point_to_use = empty($_POST['point_to_use']) ? null : $_POST['point_to_use'];
				$recipient = empty($_POST['recipient']) ? null : $_POST['recipient'];

                if ($point_to_use < $albumPoint) {
                    $result = 0;
                    $message = _('最低贊助額度: ' . $albumPoint . 'P');
                    goto _return;
                }

                list($result1, $message1) = array_decode_return(Model('album')->buyable($album_id, $user['user_id']));
                switch ($result1) {
                    case 0:
                        $result = $result1;
                        $message = $message1;
                        goto _return;
                        break;

                    case 2:
                        $result = $result1;
                        $message = $message1;
                        $redirect = parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]);
                        goto _return;
                        break;
                }

                //頻繁操作驗證
                $m_download = Model('download')->where([[[['user_id', '=', $user['user_id']]], 'and']])->order(['inserttime' => 'desc'])->limit('9,1')->fetch();

                if (strtotime('+1 minute', strtotime($m_download['inserttime'])) >= time()) {
                    $result = 0;
                    $message = _('This operation cannot redo within 1 minutes.');
                    goto _return;
                } else {
                    //進行交易(收藏作品)
                    (new Model)->beginTransaction();
                    list($result1, $message1, $redirect1, $data1) = array_decode_return(Core::exchange($user['user_id'], 'web', 'album', $album_id, $point_to_use));
                    if (!$result1) {
                        (new Model)->rollBack();

                        $result = 0;
                        $message = $message1;
                        $data = $data1;
                        //P點不足時內容 $result 與 $message 再造
                        if (!$data1['balance']) {
                            $result = 3;
                            $message = '<div class="content"><p>P點不足</p><p class="red">(是否離開作品前往儲值P點?)</p></div>';
                        }
                        goto _return;
                    }

					/**
					 *  贊助回饋紀錄
					 */
					if($m_album['reward_after_collect']) {
						$add = [
							'user_id' => $user['user_id'],
							'exchange_id' => $data1['exchange_id'],
							'type' => 'album',
							'type_id' => $album_id,
							'recipient' => $recipient['recipient'],
							'recipient_tel' => $recipient['recipient_tel'],
							'recipient_address' => $recipient['recipient_address'],
							'recipient_text' => (empty($recipient['recipient_text'])) ? '' : $recipient['recipient_text'],
							'inserttime' => inserttime(),
						];
						(new rewardModel())->add($add);
					}

                    /**
                     *  0704 - 執行任務-收藏相本
                     */
                    $m_album_point = (new albumModel())->column(['point'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();
                    $data = [];
                    $task_for = ($m_album_point == 0) ? 'collect_free_album' : 'collect_pay_album';
                    $user_id = $user['user_id'];
                    $data = model('task')->doTask($task_for, $user_id, 'web', ['type' => 'album', 'type_id' => $album_id]);
                    $data['album_count'] = Model('albumstatistics')->column(['`count`'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();;

                    (new Model)->commit();
                }
            } else {
                $result = 4;//詢問是否收藏
                $message = null;
                $data = $this->content_property($album_id);

                // 170503 issue#1252 - 當P點為0 以及未設定預覽頁面時不跳出詢問視窗故直接收藏 - Mars
                if ($data['album']['data']['album']['point'] == 0 && (count(json_decode($data['album']['data']['album']['preview'], true)) == count(json_decode($data['album']['data']['album']['photo'], true)))) {

                    list($result1, $message1) = array_decode_return(Model('album')->buyable($album_id, $user['user_id']));
                    switch ($result1) {
                        case 0:
                            $result = $result1;
                            $message = $message1;
                            goto _return;
                            break;

                        case 2:
                            $result = $result1;
                            $message = $message1;
                            $redirect = parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]);
                            goto _return;
                            break;
                    }

                    //頻繁操作驗證
                    $m_download = Model('download')->where([[[['user_id', '=', $user['user_id']]], 'and']])->order(['inserttime' => 'desc'])->limit('9,1')->fetch();

                    if (strtotime('+1 minute', strtotime($m_download['inserttime'])) >= time()) {
                        $result = 0;
                        $message = _('This operation cannot redo within 1 minutes.');
                        goto _return;
                    } else {
                        //進行交易(收藏作品)
                        (new Model)->beginTransaction();

                        list($result1, $message1, $redirect1, $data1) = array_decode_return(Core::exchange($user['user_id'], 'web', 'album', $album_id));
                        if (!$result1) {
                            (new Model)->rollBack();

                            $result = 0;
                            $message = $message1;
                            $data = $data1;

                            //P點不足時內容 $result 與 $message 再造
                            if (!$data1['balance']) {
                                $result = 3;
                                $message = '<div class="content"><p>P點不足</p><p class="red">(是否離開作品前往儲值P點?)</p></div>';
                            }
                            goto _return;
                        }

                        /**
                         *  0704 - 執行任務-收藏相本
                         */
                        $m_album_point = Model('album')->column(['point'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();
                        $data = [];
                        $task_for = ($m_album_point == 0) ? 'collect_free_album' : 'collect_pay_album';
                        $user_id = $user['user_id'];
                        $data = model('task')->doTask($task_for, $user_id, 'web', ['type' => 'album', 'type_id' => $album_id]);
                        $data['album_count'] = Model('albumstatistics')->column(['`count`'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();;

                        (new Model)->commit();
                    }

                    $result = 5;
                    $message = null;
                }
            }

            _return:
            json_encode_return($result, $message, $redirect, $data);
        }
        die;
    }

    function categoryareaList()
    {

        $categoryarea_id = (!empty($_GET['categoryarea_id'])) ? $_GET['categoryarea_id'] : 0;

        $categoryareaIcon = [[
            'categoryarea_id' => null,
            'name' => _('首頁'),
            'url' => self::url('index'),
            'icon' => static_file('images/assets-v6/home_n.svg'),
            'iconOpposite' => static_file('images/assets-v6/home.svg'),
        ]];

        $m_categoryarea = (new categoryareaModel())->column(['categoryarea_id', 'name', 'image', 'image_n'])->where([[[['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();

        foreach ($m_categoryarea as $k0 => $v0) {
            $categoryareaIcon[] = [
                'categoryarea_id' => $v0['categoryarea_id'],
                'name' => $v0['name'],
                'url' => self::url('album', 'explore', ['categoryarea_id' => $v0['categoryarea_id']]),
                'icon' => ($categoryarea_id == $v0['categoryarea_id']) ? URL_UPLOAD . $v0['image'] : URL_UPLOAD . $v0['image_n'],
                'iconOpposite' => ($categoryarea_id == $v0['categoryarea_id']) ? URL_UPLOAD . $v0['image_n'] : URL_UPLOAD . $v0['image'],
            ];
        }

        return $categoryareaIcon;

    }

	/**
	 * 準備棄用改用新版 Mars - 181222
	 */
    function content()
    {
        //取得相簿ID
        $album_id = empty($_GET['album_id']) ? null : $_GET['album_id'];
        if ($album_id == null) redirect_php(parent::url('album', 'index'));
        $property = $this->content_property($album_id);
        list($result, $message, , $data0) = array_decode_return((new \albumModel)->content($album_id));
        if ($result != 1) redirect(parent::url('album', 'index'), $message);

        //search
        $searchtype = isset($_GET['searchtype']) ? urldecode($_GET['searchtype']) : null;
        parent::$data['searchtype'] = $searchtype;
        $searchkey = (isset($_GET['searchkey']) && $_GET['searchkey'] !== '') ? urldecode($_GET['searchkey']) : null;
        parent::$data['searchkey'] = htmlspecialchars($searchkey);

        //rank
        $rank_id = (!empty($_GET['rank_id']) && in_array($_GET['rank_id'], [0, 1, 2, 3])) ? $_GET['rank_id'] : 0;
        $rank_name = [_('Hot'), _('Free'), _('Sponsored'), _('Latest')];
        parent::$data['rank_id'] = $rank_id;
        parent::$data['rank_name'] = $rank_name;

        //categoryarea
        $column = ['categoryarea_id', 'name'];
        $where = [
            [[['level', '=', 0], ['act', '=', 'open']], 'and'],
        ];
        $order = ['sequence' => 'asc'];
        $m_categoryarea = Model('categoryarea')->column($column)->where($where)->order($order)->fetchAll();
        $a_categoryarea = [];
        $a_categoryarea_id = [];
        $a_category_id = [];
        $tmp2 = [];
        if ($rank_id !== null) $tmp2['rank_id'] = $rank_id;
        if ($searchkey !== null) {
            $tmp2['searchtype'] = $searchtype;
            $tmp2['searchkey'] = $searchkey;
        }
        foreach ($m_categoryarea as $v0) {
            $tmp0 = [];
            $tmp0['categoryarea_id'] = $v0['categoryarea_id'];
            $tmp0['name'] = \Core\Lang::i18n($v0['name']);
            $tmp0['url'] = parent::url('album', 'index', array_merge($tmp2, ['categoryarea_id' => $v0['categoryarea_id']]));

            //category
            $column = ['category_id', 'name'];
            $join = [
                ['left join', 'categoryarea_category', 'using(category_id)'],
            ];
            $where = [
                [[['categoryarea_category.categoryarea_id', '=', $v0['categoryarea_id']], ['categoryarea_category.act', '=', 'open']], 'and'],
            ];
            $order = ['categoryarea_category.sequence' => 'asc'];
            $m_category = Model('category')->column($column)->join($join)->where($where)->order($order)->fetchAll();
            $a_category = [];
            foreach ($m_category as $v1) {
                $a_category[] = [
                    'category_id' => $v1['category_id'],
                    'name' => \Core\Lang::i18n($v1['name']),
                    'url' => parent::url('album', 'index', array_merge($tmp2, ['categoryarea_id' => $v0['categoryarea_id'], 'category_id' => $v1['category_id']])),
                ];
                $a_category_id[] = $v1['category_id'];

                //從album_id取得此相簿的分類名稱(name)
                if ($data0['album']['category_id'] == $v1['category_id']) {
                    $album_categoryarea_id = $v0['categoryarea_id'];
                    $categoryarea_name = \Core\Lang::i18n($v0['name']);
                    $category_name = \Core\Lang::i18n($v1['name']);
                }
            }
            $tmp0['category'] = $a_category;

            $a_categoryarea[] = $tmp0;
            $a_categoryarea_id[] = $v0['categoryarea_id'];
        }
        parent::$data['categoryarea'] = $a_categoryarea;

        $categoryarea_id = !empty($album_categoryarea_id) ? $album_categoryarea_id : null;
        parent::$data['categoryarea_id'] = $categoryarea_id;

        $category_id = (!empty($_GET['category_id']) && in_array($_GET['category_id'], $a_category_id)) ? $_GET['category_id'] : null;
        parent::$data['category_id'] = $category_id;

        //rank
        $tmp0 = [];
        if ($categoryarea_id !== null) $tmp0['categoryarea_id'] = $categoryarea_id;
        if ($category_id !== null) $tmp0['category_id'] = $category_id;
        if ($searchkey !== null) {
            $tmp0['searchtype'] = $searchtype;
            $tmp0['searchkey'] = $searchkey;
        }
        parent::$data['rank0'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 0]));
        parent::$data['rank1'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 1]));
        parent::$data['rank2'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 2]));
        parent::$data['rank3'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 3]));

        //template
        $m_template = Model('template')->column(['width', 'height'])->where([[[['template_id', '=', $data0['album']['template_id']]], 'and']])->fetch();
        if (empty($m_template)) $m_template = null;

        //album.preview
        $a_preview = [];
        $tmp0 = json_decode($data0['album']['preview'], true);
        if (empty($tmp0)) {
            $a_preview[] = [
                'normal' => static_file('images/origin.jpg'),
                'gallery' => static_file('images/origin.jpg'),
            ];
        } else {
            foreach ($tmp0 as $v0) {
                $a_preview[] = [
                    'normal' => URL_UPLOAD . getimageresize($v0, 466, 699),
                    'gallery' => URL_UPLOAD . $v0,
                ];
            }
        }

        parent::$data['user'] = $m_user = Model('user')->getSession();

        /**
         * 下拉選單
         */
        $_edit = null;        //修改相本
        $_report = null;    //檢舉相本
        $_delete = null;    //刪除相本
        if (!empty($m_user)) {
            if ($data0['user']['user_id'] != $m_user['user_id']) {
                $_report = '<li><a class="alert_btn" href="javascript:void(0)" data-album_id="' . $album_id . '">' . _('Report') . '</a></li>';
            } else {
                $_edit = '<li><a href="' . parent::url('user', 'albumcontent_setting', ['album_id' => $album_id]) . '">' . _('編輯') . '</a></li>';
                $_delete = '<li><a href="javascript:void(0)" onclick="delete_album();">' . _('Delete') . '</a></li>';
            }
        } else {
            $_report = '<li><a class="alert_btn" href="' . parent::url('user', 'login', ['redirect' => parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id), 'report' => true])]) . '">' . _('Report') . '</a></li>';
        }

        //album
        $a_album = [
            'album' => [
                'album_id' => $album_id,
                'name' => htmlspecialchars($data0['album']['name']),
                'description' => nl2br(htmlspecialchars($data0['album']['description'])),
                'location' => $data0['album']['location'],
                'rating' => $data0['album']['rating'] == 'general' ? _('Suitable for all ages') : _('Restricted album'), // issue774 移除前台分類標籤顯示 此處先不動. Mars
                'preview' => $a_preview,
                'point' => $data0['album']['point'],
                'inserttime' => date('Y/m/d', strtotime($data0['album']['inserttime'])),
                'publishtime' => date('Y/m/d', strtotime($data0['album']['publishtime'])),
                'page' => empty($data0['album']['photo']) ? 0 : count(json_decode($data0['album']['photo'], true)),
                'cover' => URL_UPLOAD . $data0['album']['cover'],
                'c_description' => mb_strlen(strip_tags(nl2br($data0['album']['description'])), 'UTF-8'),
            ],
            'albumstatistics' => [
                'count' => $data0['albumstatistics']['count'],
                'viewed' => (new albumModel())->getAlbumViewed($album_id),
            ],
            'album2likes' => [
                'count' => (new album2likesModel())->countlikes($album_id),
            ],
            'categoryarea' => [
                'categoryarea_id' => $data0['categoryarea']['categoryarea_id'],
                'name' => empty($categoryarea_name) ? null : \Core\Lang::i18n($categoryarea_name),
                'url' => (!empty($album_categoryarea_id)) ? parent::url('album', 'index', ['categoryarea_id' => $album_categoryarea_id]) : null,
            ],
            'category' => [
                'name' => empty($category_name) ? null : \Core\Lang::i18n($category_name),
                'url' => (!empty($album_categoryarea_id)) ? parent::url('album', 'index', ['categoryarea_id' => $album_categoryarea_id, 'category_id' => $data0['album']['category_id']]) : null,
            ],
            'template' => [
                'width' => $m_template['width'],
                'height' => $m_template['height'],
            ],
            'user' => [
                'user_id' => $data0['user']['user_id'],
                'name' => $data0['user']['name'],
                'url' => Core::get_creative_url($data0['user']['user_id']),
            ],
            'pinpinboard' => [
                'count' => (new pinpinboardModel())->countComment('album', $album_id),
            ],
            'dropdownMenu' => [$_edit, $_report, $_delete],
        ];

        //參加活動提示
        $m_eventjoin = Model('eventjoin')->join([['left join', 'event', 'using(event_id)']])->column(['event.event_id', 'event.name'])->where([[[['eventjoin.album_id', '=', $album_id], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])->fetch();
        $a_album['album']['eventjoin'] = !empty($m_eventjoin) ? $m_eventjoin['event_id'] : false;

        parent::$data['album'] = $a_album;

        $trip_tip = true;
        if (!empty($m_user)) {
            $trip_tip = $data0['user']['user_id'] == $m_user['user_id'] ? false : true;

            $m_exchange = (new exchangeModel)->where([[[['user_id', '=', $m_user['user_id']], ['type', '=', 'album'], ['id', '=', $album_id]], 'and']])->fetch();
            if (!empty($m_exchange)) $trip_tip = false;
        }
        parent::$data['trip_tip'] = $trip_tip;

        //albumstatistics
        (new albumModel())->increaseViewed($album_id);

        //other album
        $a_other_album = [];
        $m_other_album = (new \albumModel)
            ->column([
                'album.*',
                'albumstatistics.*',
                'categoryarea_category.categoryarea_id',
            ])
            ->join([
                ['left join', 'albumstatistics', 'using(album_id)'],
                ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
            ])
            ->where([[[['album.user_id', '=', $a_album['user']['user_id']], ['album.album_id', '!=', $a_album['album']['album_id']], ['album.act', '=', 'open'], ['album.state', '=', 'success']], 'and']])
            ->order(['album.publishtime' => 'desc'])
            ->limit(4)
            ->fetchAll();

        foreach ($m_other_album as $k0 => $v0) {
            if (!empty($m_user)) $collect = Model('albumqueue')->column(['COUNT(1)'])->where([[[['user_id', '=', $m_user['user_id']], ['album_id', '=', $v0['album_id']]], 'and']])->fetchColumn();

            $tmp = [
                'album' => [
                    'album_id' => $v0['album_id'],
                    'name' => $v0['name'],
                    'url' => parent::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id']]),
                    'cover_url' => parent::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id'], 'click' => 'cover']),
                    'name_url' => parent::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id'], 'click' => 'name']),
                    'cover' => URL_UPLOAD . $v0['cover'],
                    'description' => $v0['description'],
                    'point' => $v0['point'],
                    'viewed' => $v0['viewed'],
                    'album_tags' => (new albumModel())->hasGiftTags($v0['album_id']),
                ],
                'user' => [
                    'name' => $a_album['user']['name'],
                    'url' => Core::get_creative_url($a_album['user']['user_id']),
                    'collect' => (!empty($collect)) ? '<i class="add_love"></i>' : '<i class="add_no"></i>',
                    'picture' => URL_STORAGE . Core::get_userpicture($a_album['user']['user_id']),
                ]
            ];
            $a_other_album[] = $tmp;
        }
        parent::$data['other_album'] = $a_other_album;

        //disqus
        parent::$data['disqus'] = parent::disqus('album', $album_id);

        //tags
        $a_tags = [
            'exchange' => false,
            'slot' => false,
            'video' => false,
            'audio' => ($data0['album']['audio_mode'] != 'none') ? true : false,
        ];
        $m_album_tags = Model('photo')->column(['DISTINCT(`usefor`)'])->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])->fetchAll();

        foreach ($m_album_tags as $k0 => $v0) {
            if ($v0['usefor'] == 'image') continue;
            $a_tags[$v0['usefor']] = true;
        }

        parent::$data['album_tags'] = $a_tags;

        //行動版下拉選單
        $m_select_option = [];
        $tmp = [];
        foreach ($m_categoryarea as $k0 => $v0) {
            if (!empty($rank_id)) $tmp = ['rank_id' => $rank_id];
            $m_select_option[$k0]['categoryarea_id'] = $v0['categoryarea_id'];
            $m_select_option[$k0]['url'] = parent::url('album', 'index', array_merge($tmp, ['categoryarea_id' => $v0['categoryarea_id']]));
            $m_select_option[$k0]['name'] = $v0['name'];
        }
        parent::$data['m_select_option'] = $m_select_option;

        //seo
        $this->seo(
            empty($data0['album']['name']) ? null : $data0['album']['name'] . ' | ' . Core::settings('SITE_TITLE'),
            empty($data0['album']['name']) ? null : [$data0['album']['name']],
            $data0['album']['description'],
            URL_UPLOAD . $data0['album']['cover'],
            parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)])
        );

        //nav
        $nav_level0 = (!empty($album_categoryarea_id)) ? '<a href="' . parent::url('album', 'index', ['categoryarea_id' => $album_categoryarea_id]) . '">' . $a_album['categoryarea']['name'] . '</a>' : null;
        $nav_level1 = (!empty($album_categoryarea_id)) ? '<a href="' . parent::url('album', 'index', ['categoryarea_id' => $album_categoryarea_id, 'category_id' => $data0['album']['category_id']]) . '"><span>' . $a_album['category']['name'] . '</span></a>' : null;

        $user = parent::user_get();
        if (!empty($user)) parent::$data['user'] = $user;

        //pinpinboard
        $a_pinpinboard = (new pinpinboardModel())->getComment('album', $album_id, $user['user_id']);
        parent::$data['pinpinboard'] = $a_pinpinboard;

        $pinpinboardParam = [
            'type' => 'album',
            'type_id' => $album_id,
            'redirectParam' => 'album_id',
        ];
        parent::$data['pinpinboardParam'] = $pinpinboardParam;

        //navigation
        parent::$data['navigation'] = $nav_level0 . $nav_level1 . $a_album['album']['name'];
        parent::$data['agent'] = isset($_GET['agent']) ? $_GET['agent'] : null;
        parent::$data['autoplay'] = isset($_GET['autoplay']) ? true : false;
        parent::$data['autobuy'] = isset($_GET['autobuy']) ? true : false;
        parent::$data['property'] = $property;

        $this->album_pc_nav();
        $this->album_mobile_nav();
        $this->album_tabs_rank();

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->jbox();

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

        //jquery-textcomplete
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.textcomplete.js'), 'src');
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.overlay.js'), 'src');
        parent::$html->set_css(static_file('js/jquery-textcomplete/media/stylesheets/textcomplete.css'), 'href');

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');

        parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.show-more.js'), 'src');

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function content_v2() {
		//取得相簿ID
		$album_id = empty($_GET['album_id']) ? null : $_GET['album_id'];
		if ($album_id == null) redirect_php(parent::url('album', 'index'));

		$property = $this->content_property($album_id);

		list($result, $message, , $data0) = array_decode_return((new \albumModel)->content($album_id));
		if ($result != 1) redirect(parent::url('album', 'index'), $message);

		parent::$data['user'] = $m_user = (new userModel())->getSession();

		// 暫時的作品連結 function 統一放這裡
		$tmp_function = 'content_v2';

		// 右側按鈕組成
		$browseKitKeyPress = (isset($data0['album']['reward_after_collect']) && $data0['album']['reward_after_collect']) ? $data0['album']['reward_after_collect'] : 0 ;
        // 20190213 Mars 先修改觀看按鈕為無法開啟預覽的一般文字提示按鈕
		// $mainButton = '<span class="btn_new btn_main" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\', keyPress : '.$browseKitKeyPress.'})">'._('觀看').'</span>';
        $mainButtonText = (!$data0['album']['point']) ? _('已收藏') : _('已贊助') ;
        $mainButton = '<span class="btn_new btn_attention">'.$mainButtonText.'</span>';
		$afterCollectAlbum = $mainButton;

		// 右側按鈕
		if (!$property['user']['collected']) {
			if (!$data0['album']['point']) {
				$mainButton = '<span class="btn_new btn_main" onclick="buyalbum();">'._('收藏').'</span>';
			} else {
				$mainButton = '<span class="btn_new btn_main" onclick="buyalbum();">'._('贊助').'</span>';
			}
		}

		//tags
		$a_tags = [
			'exchange' => false,
			'slot' => false,
			'video' => false,
			'audio' => ($data0['album']['audio_mode'] != 'none') ? true : false,
		];
		$m_album_tags = (new photoModel())->column(['DISTINCT(`usefor`)'])->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])->fetchAll();

		foreach ($m_album_tags as $k0 => $v0) {
			if ($v0['usefor'] == 'image') continue;
			$a_tags[$v0['usefor']] = true;
		}

		$a_sociallink = [
			'blog' => '',
			'facebook' => '',
			'google' => '',
			'instagram' => '',
			'line' => '',
			'linkedin' => '',
			'pinterest' => '',
			'twitter' => '',
			'youtube' => '',
			'web' => '',
		];

		/**
		 * 下拉選單
		 */
		$_edit = null;      //修改相本
		$_report = null;    //檢舉相本
		$_delete = null;    //刪除相本
		if (!empty($m_user)) {
			if ($data0['user']['user_id'] != $m_user['user_id']) {
				$_report = '<li><a class="alert_btn" href="javascript:void(0)" data-type_id="' . $album_id . '">' . _('Report') . '</a></li>';
			} else {
				$_edit = '<li><a href="' . parent::url('user', 'albumcontent_setting', ['album_id' => $album_id]) . '">' . _('編輯') . '</a></li>';
				$_delete = '<li><a href="javascript:void(0)" onclick="delete_album();">' . _('Delete') . '</a></li>';
			}
		} else {
			$_report = '<li><a class="alert_btn" href="' . parent::url('user', 'login', ['redirect' => parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id), 'report' => true])]) . '">' . _('Report') . '</a></li>';
		}

		// publishTime
		$publishTime = ($data0['album']['publishtime'] != '0000-00-00 00:00:00') ? date('Y/m/d', strtotime($data0['album']['publishtime'])) : null ;

		// follow
        $is_follow = (new followModel())->is_follow($m_user['user_id'], $data0['user']['user_id']);


		// album
		$a_album = [
			'album' => [
				'album_id' => $album_id,
				'adjustAppQrcodeUrl' =>  $property['album']['adjustAppQrcodeUrl'],
				'autoPlayUrl' => parent::url('album', $tmp_function, ['album_id' => $album_id, 'autoplay' => true]),
				'browseKitKeyPress' => $property['property']['browseKitKeyPress'],
				'c_description' => mb_strlen(strip_tags(nl2br($data0['album']['description'])), 'UTF-8'),
				'cover' => URL_UPLOAD . $data0['album']['cover'],
				'description' => nl2br(htmlspecialchars($data0['album']['description'])),
                'location' => $data0['album']['location'],
				'inserttime' => date('Y/m/d', strtotime($data0['album']['inserttime'])),
				'name' => htmlspecialchars($data0['album']['name']),
				'page' => empty($data0['album']['photo']) ? 0 : count(json_decode($data0['album']['photo'], true)),
				'point' => $data0['album']['point'],
				'publishtime' => $publishTime,
				'tags' => $a_tags,
				'url' => parent::url('album', $tmp_function, ['album_id' => $album_id]),
				'qrcodeUrl' => $property['album']['qrcodeUrl'],
			],
			'album2likes' => [
				'count' => (new album2likesModel())->countlikes($album_id),
                'userHasLikes' => (new album2likesModel())->hasLikes($m_user['user_id'] ,$album_id),
			],
			'albumstatistics' => [
				'count' => $data0['albumstatistics']['count'],
				'viewed' => (new albumModel())->getAlbumViewed($album_id),
			],
			'button' => [
				'afterCollectAlbum' => $afterCollectAlbum,
				'mainButton' => $mainButton,
			],
			'dropdownMenu' => [$_edit, $_report, $_delete],
			'eventjoin' => $property['album']['eventjoin'],
            'follow' => [
                'is_follow' => $is_follow,
            ],
			'user' => [
				'cover' => URL_STORAGE . \userModel::getPicture($data0['user']['user_id']),
				'name' => $data0['user']['name'],
				'url' => Core::get_creative_url($data0['user']['user_id']),
				'user_id' => $data0['user']['user_id'],
			],
			'pinpinboard' => [
				'count' => (new pinpinboardModel())->countComment('album', $album_id),
			],
			'sociallink' => (count(json_decode($property['user']['sociallink'], true)) > 0) ? array_merge($a_sociallink, json_decode($property['user']['sociallink'], true)) : $a_sociallink,
		];

		parent::$data['album'] = $a_album;
		parent::$data['property'] = $property;

		// 更多作品
		$a_other_album = [];
		$m_other_album = (new albumModel())
			->column([
				'album.*',
				'albumstatistics.*',
				'categoryarea_category.categoryarea_id',
			])
			->join([
				['left join', 'albumstatistics', 'using(album_id)'],
				['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
			])
			->where([[[['album.user_id', '=', $data0['user']['user_id']], ['album.album_id', '!=', $album_id], ['album.act', '=', 'open'], ['album.state', '=', 'success']], 'and']])
			->order(['album.publishtime' => 'desc'])
			->limit(8)
			->fetchAll();

		foreach ($m_other_album as $k0 => $v0) {
			if (!empty($m_user)) $collect = (new albumqueueModel())->column(['COUNT(1)'])->where([[[['user_id', '=', $m_user['user_id']], ['album_id', '=', $v0['album_id']]], 'and']])->fetchColumn();

			$tmp = [
				'album' => [
					'album_id' => $v0['album_id'],
					'name' => $v0['name'],
					'url' => parent::url('album', $tmp_function, ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id']]),
					'cover_url' => parent::url('album', $tmp_function, ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id'], 'click' => 'cover']),
					'name_url' => parent::url('album', $tmp_function, ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id'], 'click' => 'name']),
					'cover' => URL_UPLOAD . $v0['cover'],
					'description' => $v0['description'],
					'point' => $v0['point'],
					'viewed' => $v0['viewed'],
					'album_tags' => (new albumModel())->hasGiftTags($v0['album_id']),
				],
				'user' => [
					'name' => $data0['user']['name'],
					'url' => Core::get_creative_url($data0['user']['user_id']),
					'collect' => (!empty($collect)) ? '<i class="add_love"></i>' : '<i class="add_no"></i>',
					'picture' => URL_STORAGE . \userModel::getPicture($data0['user']['user_id']),
				]
			];
			$a_other_album[] = $tmp;
		}
		parent::$data['moreAlbums'] = $a_other_album;

		//pinpinboard
		$a_pinpinboard = (new pinpinboardModel())->getComment('album', $album_id, $m_user['user_id']);
		parent::$data['pinpinboard'] = $a_pinpinboard;

		$pinpinboardParam = [
			'type' => 'album',
			'type_id' => $album_id,
			'redirectParam' => 'album_id',
		];
		parent::$data['pinpinboardParam'] = $pinpinboardParam;

		parent::$data['autoplay'] = isset($_GET['autoplay']) ? true : false;
		parent::$data['autobuy'] = isset($_GET['autobuy']) ? true : false;

        // reportintent
        $reportintent = parent::alertData();
        parent::$data['reportintent'] = $reportintent;

		//seo
		$this->seo(
			empty($data0['album']['name']) ? null : $data0['album']['name'] . ' | ' . Core::settings('SITE_TITLE'),
			empty($data0['album']['name']) ? null : [$data0['album']['name']],
			$data0['album']['description'],
			URL_UPLOAD . $data0['album']['cover'],
			parent::url('album', $tmp_function, ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)])
		);

		parent::head_v2();
		parent::headbar_v2();
		parent::foot_v2();
		parent::footbar_v2();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);

		parent::$html->set_css(static_file('css/style_v2.css'), 'href');

		//lightgallery
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
		parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');

		//jquery-textcomplete
		parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.textcomplete.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.overlay.js'), 'src');
		parent::$html->set_css(static_file('js/jquery-textcomplete/media/stylesheets/textcomplete.css'), 'href');

		parent::$html->set_js(static_file('js/autolink-min.js'), 'src');

		parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
		parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');

		parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
		parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');

        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/pinpinbox/report.js', 'src');

    }

    function content_property($album_id)
    {
        $user = parent::user_get();
        list($result, $message, , $data0) = array_decode_return((new \albumModel)->content($album_id));
        $userpoint = (isset($user)) ? userModel::newly()->getPoint($user['user_id'], 'web') : '0';
        $balance = $userpoint - $data0['album']['point'];

        /**
         *  device 裝置  1 =行動裝置  0=傳統電腦裝置
         */
        $mobile = SDK('Mobile_Detect')->isMobile();

        //登入
        $login = empty($user) ? 0 : 1;

        //favorited 收藏
        $favorited = false;
        if (!empty($user)) {
            $m_albumqueue = (new albumqueueModel)->column(['visible'])->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->fetch();
            if ($m_albumqueue && !$m_albumqueue['visible']) {
                Model('albumqueue')->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->edit(['visible' => 1]);
            }
            if ($m_albumqueue || $data0['user']['user_id'] == $user['user_id']) {
                $favorited = true;
            }
        }

        //photo 讀取相片
        $where = $favorited ? [[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']] : [[[['album_id', '=', $album_id], ['act', '=', 'open'], ['image', 'in', json_decode($data0['album']['preview'], true)]], 'and']];
        $column = ['`name`', 'description', 'image', 'usefor', 'hyperlink', 'audio_loop', 'audio_refer', 'audio_target', 'video_refer', 'video_target'];
        $photo = (new photoModel)->column($column)->where($where)->order(['sequence' => 'asc'])->fetchAll();

        //albumPhotos 總頁數
        $albumPhotos = count(json_decode($data0['album']['photo'], true));

        //previewPhotos 預覽頁數
        $previewPhotos = count($photo);

        /**
         *  releaseMode 閱覽模式  1 =全部內容  0 =部分內容
         */
        $releaseMode = ($albumPhotos === $previewPhotos) ? 1 : 0;

        /**
         *  buyAlbumBox 以下處理購買相本時彈出視窗內容
         */
        //Btn 彈出視窗的按鈕型態
		if($login) {
			$buyAlbumBoxBtn = (!$data0['album']['point']) ? _('收藏') : _('贊助') ;
		} else {
			$buyAlbumBoxBtn = _('請先登入') ;
		}

        //Content 文字內容
        $buyAlbumBoxContent = '<div class="content">';
        //content0
        if (!$releaseMode) {
            if (!$data0['album']['point']) {
                $content0 = '<p class="keypoint" style="font-size:2em;">' . _('馬上收藏 看全部內容') . '</p><br>';
            } else {
                $content0 = '<p class="keypoint" style="font-size:2em;">' . _('贊助P點 看全部內容') . '</p><br>';
            }
        } else {
            $content0 = ($data0['album']['point']) ? '<p class="keypoint" style="font-size:2em;">' . _('喜歡作品就給個鼓勵吧!') . '</p><br>' : '<p class="keypoint" style="font-size:2em;">' . _('馬上收藏 接收更新通知') . '</p><br>';
        }

        if ($login) {
				// 是否顯示贊助次數
				$display_num_of_collect_text = ($data0['album']['display_num_of_collect'] && $data0['albumstatistics']['exchange'] > 0) ? '已被贊助 ' . $data0['albumstatistics']['exchange'] . ' 次' : null;
				$display_balance_tips = ($balance < 0) ? '您的P點不足, 是否前往<a href="javascript:void(0)" onclick="deposit();">儲值</a>?' : null;

        	$content1 = (!$data0['album']['point']) ? null : '<p>' . _('現有P點') . '&nbsp;：<span class="red">' . $userpoint . 'P</span></p>
				  <p>' . _('贊助P點') . '&nbsp;：<input name="customerpoint" onkeypress="return event.charCode >= 48 && event.charCode <= 57;" onkeyup="checkpoint(this, this.value);" onchange="checkpoint(this, this.value);" type="number" maxlength="5" max="50000" value="' . $data0['album']['point'] . '" style="width:30%;"></p>
				  <p class="red">NT: <span id="albumPoint2TWD">'.($data0['album']['point']/2).'</span></p>
				  <p class="red" name="display_num_of_collect">'.$display_num_of_collect_text.'</p><p class="red" name="pointTips">'.$display_balance_tips.'</p>';
        } else {
            $content1 = (!$data0['album']['point']) ? null : '<p>' . _('贊助P點') . '&nbsp;：<span class="red">' . $data0['album']['point'] . 'P</span></p>';
            $content1 .= (!$data0['album']['point']) ? null : '<p class="red">NT: <span id="albumPoint2TWD">'.($data0['album']['point']/2).'</span></p>';
        }

        $contentEnd = '</div><br>';

        // 贊助回饋填寫區塊
		$rewardInfoContent = null;
		if($data0['album']['reward_after_collect']) {

			// 標題強制更改
			$content0 = '<p class="keypoint" style="font-size:2em;">' . _('贊助購買') . '</p><br>';

			if($login) {
				$getLastRecord = (new rewardModel())->getLastRecord($user['user_id']);
				$lastRecipient = ($getLastRecord['recipient']) ? $getLastRecord['recipient'] : null;
				$lastRecipientTel = $getLastRecord['recipient_tel'] ? $getLastRecord['recipient_tel'] : null;
				$lastRecipientAddress = $getLastRecord['recipient_address'] ? $getLastRecord['recipient_address'] : null;
				$lastRecipientText = $getLastRecord['recipient_text'] ? $getLastRecord['recipient_text'] : null;

				$rewardInfoContent = '<div class="reward_content">';
				$rewardInfoContent .= '<p class="title">' . _('回饋寄送填寫') . '</p>';
				$rewardInfoContent .= '<p class="sub_title">' . _('收件人') . '：</p>';
				$rewardInfoContent .= '<p><input type="text" class="sub_text" name="recipient" maxlength="16" value="' . $lastRecipient . '"></p>';
				$rewardInfoContent .= '<p class="sub_title">' . _('連絡電話') . '：</p>';
				$rewardInfoContent .= '<p><input type="text" class="sub_text" name="recipient_tel" maxlength="16" value="' . $lastRecipientTel . '"></p>';
				$rewardInfoContent .= '<p class="sub_title">' . _('寄送住址') . '：</p>';
				$rewardInfoContent .= '<p><input type="text" class="sub_text" name="recipient_address" maxlength="32" value="' . $lastRecipientAddress . '"></p>';
				$rewardInfoContent .= '<p class="sub_title"> 給' . $data0['user']['name'] . '留言：</p>';
				$rewardInfoContent .= '<p><textarea class="sub_text" style="margin: 0 auto;" name="recipient_text" cols="3">'.$lastRecipientText.'</textarea></p>';
			}

			$rewardInfoContent .= '<p class="sub_description_title">' . _('說明：') . '</p>';
			$rewardInfoContent .= '<p class="description_text">' . $data0['album']['reward_description'] . '</p>';
			$rewardInfoContent .= '</div>';
		}

        $buyAlbumBoxContent .= $content0 . $content1 . $contentEnd . $rewardInfoContent;
        // --buyAlbumBox文字區塊結尾

        //collected 是否收藏
        if ($login) {
            //檢查是否已經有購買
            $m_albumqueue = (new \albumqueueModel)->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->fetch();
            //檢查是否為職人
            $album_owner = ($user['user_id'] == $data0['user']['user_id']) ? true : false;
        }
        $collected = (!empty($m_albumqueue) || !empty($album_owner)) ? true : false;

        //是否已點讚
        $hasLikes = (new album2likesModel)->hasLikes($user['user_id'], $album_id);

        //eventjoin 相本是否參加活動
        $_time = date('Y-m-d H:i:s', time());
        $where = [[[['eventjoin.album_id', '=', $album_id], ['event.act', '=', 'open'], ['event.endtime', '>', $_time], ['event.vote_starttime', '<', $_time], ['event.vote_endtime', '>', $_time]], 'and']];
        $m_eventjoin = (new \eventjoinModel)->join([['left join', 'event', 'using(event_id)']])->column(['event.event_id', 'event.name', 'eventjoin.count'])->where($where)->fetch();

        $hasVoted = null;
        if (!empty($m_eventjoin)) {
            $hasVoted = (new eventModel)->hasVoted($m_eventjoin['event_id'], $album_id, $user['user_id']);
        }

        //pc_button mobile_button 電腦版及手機板投票樣式
		// * 準備棄用改用新版 Mars - 181226
        $vote_btn = $btn_class = $m_vote_btn = null;
		$browseKitKeyPress = (isset($data0['album']['reward_after_collect']) && $data0['album']['reward_after_collect']) ? $data0['album']['reward_after_collect'] : 0 ;
        if ($collected) {
            if (!empty($m_eventjoin) && (!$hasVoted)) {
                $btn_class = 'twobtns';

                $vote_btn = '<a id="vote" href="javascript:void(0)" class="used big white ' . $btn_class . '">
						<img class="icon_vote" src="' . static_file('images/assets-v6/icon-ticket-green-25.svg') . '" >' . _('投票') . '
					</a>';

                $m_vote_btn = '<a id="vote_mobile" href="javascript:void(0);" class="used big white ' . $btn_class . '">
                                <img class="icon_vote" src="' . static_file('images/assets-v6/icon-ticket-green-25.svg') . '">' . _('投票') . '
                            </a>';
            }

            $pc_button = '<li class="mobilehide02" id="collected">
				' . $vote_btn . '
				<a id="read" href="javascript:void(0);" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\', keyPress : '.$browseKitKeyPress.'})" class="used big ' . $btn_class . '">' . _('觀看') . '
				</a>
			</li>';

            $mobile_button = $m_vote_btn . '<a id="read" href="javascript:void(0);" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\', keyPress : '.$browseKitKeyPress.'})" class="used big ' . $btn_class . '">' . _('觀看') . '</a>';
        } else {
            $btn_class = 'twobtns ';
            if (!empty($m_eventjoin) && (!$hasVoted)) {
                $m_vote_btn_style = 'background-color:#fff;color:#00acc1;border:1px #00acc1 solid;';

                $btn_class = 'threebtns ';

                $vote_btn = '<a id="vote" href="javascript:void(0)" class="used big white ' . $btn_class . '">
						<img class="icon_vote" src="' . static_file('images/assets-v6/icon-ticket-green-25.svg') . '" >' . _('投票') . '
					</a>';

                $m_vote_btn = '<a id="vote_mobile" href="javascript:void(0);" class="used big white ' . $btn_class . '">
                    <img class="icon_vote" src="' . static_file('images/assets-v6/icon-ticket-green-25.svg') . '">' . _('投票') . '
                </a>';
            }

            $heart_text = ($data0['album']['point'] > 0) ? _('Sponsored') : _('Collection');
            $pc_button = '<li class="mobilehide02" id="preview">
					' . $vote_btn . '
					<a href="javascript:void(0);" id="read" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\', keyPress : '.$browseKitKeyPress.'})" class="used big white ' . $btn_class . '" style="margin-bottom:5px;">' . _('觀看') . '
					</a>
					<a href="javascript:void(0);" onclick="buyalbum()" id="trip_collect" class="used big ' . $btn_class . '">
						' . $heart_text . '
					</a>
				</li>';

            $mobile_button = $m_vote_btn . '<a href="javascript:void(0);" id="read" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\', keyPress : '.$browseKitKeyPress.'})" class="used big white ' . $btn_class . '">' . _('觀看') . '
					</a>
					<a href="javascript:void(0);" onclick="buyalbum()" id="trip_collect" class="used big ' . $btn_class . '">
						' . $heart_text . '
					</a>';
        }

        $storage_user_album_path = SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $data0['user']['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $album_id . DIRECTORY_SEPARATOR;

        $app_qrcode_path = PATH_STORAGE . storagefile($storage_user_album_path . 'adjust_app_qrcode.jpg');
        $qrcodeUrl = str_replace('\\', '/', URL_STORAGE . storagefile($storage_user_album_path . 'qrcode.jpg'));

        //2018-04-17 舊抽獎作品若沒有產生QRcode圖片從此處處理 Mars
        (new \Core\QRcode())
            ->setTextUrl(frontstageController::url('index', 'adjustapp', ['album_id' => $album_id]))
            ->setLevel(1)
            ->setSize(5)
            ->save($app_qrcode_path);

        if (is_file($app_qrcode_path)) {
            \Extension\aws\S3::upload($app_qrcode_path);
        }

        // sociallink
		$sociallink = (new userModel())->column(['sociallink'])->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetchColumn();

        $return = [
            'album' => [
                'data' => $data0,
                'photo' => $photo,
                'releaseMode' => $releaseMode,
                'albumPhotos' => $albumPhotos,
                'previewPhotos' => $previewPhotos,
                'eventjoin' => $m_eventjoin,
                'qrcodeUrl' => $qrcodeUrl,
                'adjustAppQrcodeUrl' => path2url($app_qrcode_path),
            ],

            'user' => [
                'mobileDevice' => $mobile,
                'userpoint' => $userpoint,
                'collected' => $collected,
                'haslikes' => $hasLikes,
                'hasvoted' => $hasVoted,
				'sociallink' => $sociallink,
            ],

            'property' => [
                'login' => $login,
                'balance' => $balance,
                'favorited' => $favorited,
                'buyAlbumBoxContent' => $buyAlbumBoxContent,
                'buyAlbumBoxBtn' => $buyAlbumBoxBtn,
                'pc_button' => $pc_button,
                'mobile_button' => $mobile_button,
				'browseKitKeyPress' => $browseKitKeyPress,
            ],
        ];

        return $return;
    }

    function clear_task()
    {
        /**
         *  0704 - 執行任務-分享至FB
         */
        $data = [];
        $result = false;
        $task_for = 'share_to_fb';
        $user = parent::user_get();
        $album_id = $_POST['album_id'];
        $user_id = $user['user_id'];
        $data = model('task')->doTask($task_for, $user_id, 'web', ['type' => 'album', 'type_id' => $album_id]);
        $result = ($data['task']['result']) ? 1 : 0;
        json_encode_return($result, null, null, $data);
    }

    function explore()
    {
        $user = parent::user_get();
        $categoryarea_id = (!empty($_GET['categoryarea_id'])) ? $_GET['categoryarea_id'] : 0;
        parent::$data['categoryarea_id'] = $categoryarea_id;

        $banner = [];
        $m_categoryarea_style = (new categoryarea_styleModel())->getCategoryarea_style($categoryarea_id);
        foreach ($m_categoryarea_style as $k0 => $v0) {
            $banner_type_data = json_decode($v0['banner_type_data'], true);
            switch ($v0['banner_type']) {

                case 'image' :
                    $tmp = [
                        'banner_type' => $v0['banner_type'],
                        'image' => URL_UPLOAD . $v0['image'],
                        'url' => $banner_type_data['url'],
                        'btntext' => $banner_type_data['btntext'],
                    ];
                    break;

                case 'video' :
                    $url = 'https://www.youtube.com/embed/' . $banner_type_data['url'] . '?rel=0&showinfo=0';
                    $url .= ($banner_type_data['auto'] == true) ? '&autoplay=1' : '&autoplay=0';
                    $url .= ($banner_type_data['mute'] == true) ? '&mute=1' : '&mute=0';
                    $url .= ($banner_type_data['repeat'] == true) ? '&loop=1' : '&loop=0';
                    $link = ($banner_type_data['link']) ? $banner_type_data['link'] : null;
                    $btntext = (isset($banner_type_data['btntext'])) ? $banner_type_data['btntext'] : null;
                    $videotext = (isset($banner_type_data['videotext'])) ? $banner_type_data['videotext'] : null;

                    $tmp = [
                        'banner_type' => $v0['banner_type'],
                        'image' => URL_UPLOAD . $v0['image'],
                        'url' => $url,
                        'link' => $link,
                        'btntext' => $btntext,
                        'videotext' => $videotext,
                    ];

                    break;

                case 'creative' :
                    /**
                     * banner 區 取得分類內創作人發布排行
                     */
                    $banner_id = (empty($categoryarea_id)) ? 1 : $categoryarea_id;
                    $creative_group = Model('creative')->creative_group_by_friday([$banner_id], (6 - count($banner_type_data)));
                    $creative_group[0]['sort'] = array_merge(Model('creative')->assign_creative($banner_type_data), $creative_group[0]['sort']);

                    $tmp = [
                        'banner_type' => $v0['banner_type'],
                        'image' => URL_UPLOAD . $v0['image'],
                        'creative' => $banner_type_data,
                        'creative_group' => $creative_group,
                    ];
                    break;
            }
            $banner[] = $tmp;
        }

        parent::$data['banner'] = $banner;

        $categoryareaIcon = $this->categoryareaList();
        parent::$data['categoryareaIcon'] = $categoryareaIcon;

        $albumExplore = [];
        $Image = new \Core\Image;
        $m_albumexplore = (new albumexploreModel())->where([[[['act', '=', 'open'], ['categoryarea2explore_id', '=', $categoryarea_id]], 'and']])->order(['sequence' => 'asc'])->fetchAll();

        if (!empty($m_albumexplore)) {
            foreach ($m_albumexplore as $k0 => $v0) {
                $itemTmp = [];
                switch ($v0['basis']) {
                    case 'category' :
                        $m_album = (new albumexploreModel())->getByCategory($v0['basis_id']);
                        break;

                    case 'categoryarea' :
                        $m_album = (new albumexploreModel())->getByCategoryarea($v0['basis_id']);
                        break;

                    case 'creative' :
                        $m_album = (new albumexploreModel())->getByCreative($v0['categoryarea2explore_id'], $v0['basis_id']);
                        break;

                    case 'manual' :
                        $albumItemColumn = [
                            'album.album_id',
                            'album.user_id',
                            'album.name',
                            'album.cover',
                            'user.name user_name',
                            'albumstatistics.viewed viewed',
                            'categoryarea_category.categoryarea_id',
                        ];

                        $a_album_id = json_decode($v0['exhibit'], true);
                        $m_album = [];

                        foreach ($a_album_id as $k1 => $v1) {
                            $tmp_album = (new albumModel())
                                ->column($albumItemColumn)
                                ->join([
                                    ['left join', 'albumstatistics', 'using(album_id)'],
                                    ['left join', 'user', 'using(user_id)'],
                                    ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
                                ])
                                ->where([[[['album.act', '=', 'open'], ['album.album_id', '=', $v1]], 'and']])
                                ->fetch();

                            $m_album[] = $tmp_album;
                        }

                        break;

                    default :
                        break;
                }

                if (!empty($m_album)) {
                    foreach ($m_album as $k1 => $v1) {
                        //有參加投稿活動則不顯示autoplay功能
                        $m_eventjoin = (new eventjoinModel())->join([['left join', 'event', 'using(event_id)']])->column(['event.event_id', 'event.name'])->where([[[['eventjoin.album_id', '=', $v1['album_id']], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])->fetch();
                        $shareUrl = !empty($m_eventjoin) ? parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id']]) : parent::url('album', 'content', ['autoplay' => 1, 'album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id']]);

                        $cover = is_image(PATH_UPLOAD . $v1['cover']) ? fileinfo($Image->set(PATH_UPLOAD . $v1['cover'])->setSize(\Config\Image::S4, \Config\Image::S4)->save())['url'] : null;
                        $qrcodeUrl = str_replace('\\', '/', URL_STORAGE . storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $v1['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $v1['album_id'] . DIRECTORY_SEPARATOR . 'qrcode.jpg'));
                        /**
                         * 下拉選單
                         */
                        $_collect = null;    //收藏相本
                        $_collect_icon = null;    //收藏 icon
                        $_edit = null;        //修改相本
                        $_report = null;    //檢舉相本
                        if (!empty($user)) {
                            if ($v1['user_id'] != $user['user_id']) {
                                if (!(new albumModel)->is_own($v1['album_id'], $user['user_id'])) {
                                    $_collect = '<li data-albumId="' . $v1['album_id'] . '"><a onclick="buyalbum(' . $v1['album_id'] . ');" href="javascript:void(0)">' . _('Collection') . '</a></li>';
                                } else {
                                    $_collect_icon = '<img src="' . static_file('images/assets-v5/icon-collection-h.svg') . '">';
                                }
                                $_report = '<li><a class="alert_btn" href="javascript:void(0)" data-album_id="' . $v1['album_id'] . '">' . _('Report') . '</a></li>';
                            } else {
                                $_edit = '<li><a href="' . parent::url('user', 'albumcontent_setting', ['album_id' => $v1['album_id']]) . '">' . _('編輯') . '</a></li>';
                            }

                        } else {
                            $_collect = '<li data-albumId="' . $v1['album_id'] . '"><a href="javascript:void(0)" onclick="buyalbum(' . $v1['album_id'] . ');">' . _('Collection') . '</a></li>';
                            $_report = '<li><a class="alert_btn" href="' . parent::url('user', 'login', ['redirect' => parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id'], 'report' => true])]) . '">' . _('Report') . '</a></li>';
                        }

                        $splitNum = (is_null($_collect_icon)) ? 10 : 9;
                        $_name = (mb_strlen(strip_tags(nl2br($v1['name']), 'UTF-8')) > 30) ? mb_substr(strip_tags(nl2br($v1['name'])), 0, $splitNum, 'UTF-8') . '...' : strip_tags(nl2br($v1['name']));

                        $itemTmp[] = [
                            'album' => [
                                'album_id' => $v1['album_id'],
                                'name' => $_name,
                                'name_all' => $v1['name'],
                                'cover' => $cover,
                                'cover_url' => parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id'], 'click' => 'cover']),
                                'name_url' => parent::url('album', 'content', ['album_id' => $v1['album_id'], 'categoryarea_id' => $v1['categoryarea_id'], 'click' => 'name']),
                                'viewed' => $v1['viewed'],
                                'collect_icon' => $_collect_icon,
                                'album_tags' => (new albumModel())->hasGiftTags($v1['album_id']),
                            ],
                            'categoryarea' => [
                                'categoryarea_id' => $v1['categoryarea_id'],
                            ],
                            'user' => [
                                'url' => Core::get_creative_url($v1['user_id'], 'album_id_' . $v1['album_id']),
                                'picture' => URL_STORAGE . Core::get_userpicture($v1['user_id']),
                                'name' => $v1['user_name'],
                            ],
                            'dropdownMenu' => [
                                '<li><a href="javascript:void(0)" onclick="share_album(' . $v1['album_id'] . ', \'' . $qrcodeUrl . '\' , \'' . $cover . '\', \'' . $shareUrl . '\')">' . _('分享') . '</a></li>',
                                $_collect,
                                $_edit,
                                $_report
                            ],
                        ];
                    }
                }

                $albumExplore[] = [
                    'bookcase' => [
                        'albumexplore_id' => $v0['albumexplore_id'],
                        'name' => $v0['name'],
                        'description' => $v0['description'],
                        'sequence' => $v0['sequence'],
                        'url' => $v0['url'],
                    ],
                    'item' => $itemTmp,
                ];
            }
        }

        //若有取得autobuyalbumid則視為接回原來操作動作, 開啟收藏詢問視窗
        $autoBuyAlbumId = null;
        if (!empty($_GET['buyalbumid'])) {
            $autoBuyAlbumId = 'buyalbum(' . $_GET['buyalbumid'] . ')';
        }
        parent::$data['autoBuyAlbumId'] = $autoBuyAlbumId;

        //若有取得autoplay則自動撥放
        $autoplay = null;
        if (!empty($_GET['autoplay'])) {
            $autoplay = 'browseKit_album(\'' . self::url('album', 'show_photo') . '\', {album_id: \'' . $_GET['autoplay'] . '\'});';
        }
        parent::$data['autoplay'] = $autoplay;

        parent::$data['explore'] = $albumExplore;

        parent::$data['user'] = $m_user = Model('user')->getSession();
        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Explore') . ' | ',
            [_('Explore')]
        );

        //lightgallery
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
        parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');

        //jquery-textcomplete
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.textcomplete.js'), 'src');
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.overlay.js'), 'src');
        parent::$html->set_css(static_file('js/jquery-textcomplete/media/stylesheets/textcomplete.css'), 'href');

        //owl
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.carousel.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.theme.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.transitions.css'), 'href');
        parent::$html->set_js(static_file('js/owl.carousel/js/owl.carousel.min.js'), 'src');

        $this->album_tabs_rank();
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
        parent::$html->set_js(URL_ROOT . 'js/jquery-ias.min.js', 'src');
        parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
        parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }

    function getContentProperty()
    {
        if (is_ajax()) {
            $result = 1;
            $message = null;
            $redirect = null;
            $data = null;

            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            if (is_null($album_id)) {
                $result = 0;
                goto _return;
            }

            $data = $this->content_property($album_id);
            if (empty($data) || !is_array($data)) {
                $result = 0;
                goto _return;
            }


            _return:
            json_encode_return($result, $message, $redirect, $data);
        }
    }

    function index()
    {
        $user = parent::user_get();
        parent::$data['user'] = $user;

        $categoryareaIcon = $this->categoryareaList();
        parent::$data['categoryareaIcon'] = $categoryareaIcon;

        //search
        $searchtype = isset($_GET['searchtype']) ? urldecode($_GET['searchtype']) : null;
        parent::$data['searchtype'] = $searchtype;
        $searchkey = (isset($_GET['searchkey']) && $_GET['searchkey'] !== '') ? urldecode($_GET['searchkey']) : null;
        parent::$data['searchkey'] = htmlspecialchars($searchkey);

        //rank
        $rank_id = (!empty($_GET['rank_id']) && in_array($_GET['rank_id'], [0, 1, 2, 3])) ? $_GET['rank_id'] : 0;
        $rank_name = [_('Latest'), _('Hot'), _('Free'), _('Sponsored')];
        parent::$data['rank_id'] = $rank_id;
        parent::$data['rank_name'] = $rank_name;
        //categoryarea
        $column = ['categoryarea_id', 'name'];
        $where = [[[['level', '=', 0], ['act', '=', 'open']], 'and']];
        $order = ['sequence' => 'asc'];
        $m_categoryarea = Model('categoryarea')->column($column)->where($where)->order($order)->fetchAll();
        $a_categoryarea = array();
        $a_categoryarea_id = array();
        $a_category_id = array();
        $tmp2 = array();
        if ($rank_id !== null) $tmp2['rank_id'] = $rank_id;
        if ($searchkey !== null) {
            $tmp2['searchtype'] = $searchtype;
            $tmp2['searchkey'] = $searchkey;
        }

        foreach ($m_categoryarea as $v0) {
            $tmp0 = array();
            $tmp0['categoryarea_id'] = $v0['categoryarea_id'];
            $tmp0['name'] = \Core\Lang::i18n($v0['name']);
            $tmp0['url'] = parent::url('album', 'index', array_merge($tmp2, array('categoryarea_id' => $v0['categoryarea_id'])));

            //category
            $column = ['category_id', 'name'];
            $join = [['left join', 'categoryarea_category', 'using(category_id)']];
            $where = [[[['categoryarea_category.categoryarea_id', '=', $v0['categoryarea_id']], ['categoryarea_category.act', '=', 'open']], 'and']];
            $order = ['categoryarea_category.sequence' => 'asc'];
            $m_category = Model('category')->column($column)->join($join)->where($where)->order($order)->fetchAll();
            $a_category = array();
            foreach ($m_category as $v1) {
                $a_category[] = [
                    'category_id' => $v1['category_id'],
                    'name' => \Core\Lang::i18n($v1['name']),
                    'url' => parent::url('album', 'index', array_merge($tmp2, ['categoryarea_id' => $v0['categoryarea_id'], 'category_id' => $v1['category_id']])),
                ];
                $a_category_id[] = $v1['category_id'];
            }
            $tmp0['category'] = $a_category;

            $a_categoryarea[] = $tmp0;
            $a_categoryarea_id[] = $v0['categoryarea_id'];
        }
        parent::$data['categoryarea'] = $a_categoryarea;

        $categoryarea_id = (!empty($_GET['categoryarea_id']) && in_array($_GET['categoryarea_id'], $a_categoryarea_id)) ? $_GET['categoryarea_id'] : null;
        parent::$data['categoryarea_id'] = $categoryarea_id;

        $category_id = (!empty($_GET['category_id']) && in_array($_GET['category_id'], $a_category_id)) ? $_GET['category_id'] : null;
        parent::$data['category_id'] = $category_id;

        //rank
        $tmp0 = array();
        if ($categoryarea_id !== null) $tmp0['categoryarea_id'] = $categoryarea_id;
        if ($category_id !== null) $tmp0['category_id'] = $category_id;
        if ($searchkey !== null) {
            $tmp0['searchtype'] = $searchtype;
            $tmp0['searchkey'] = $searchkey;
        }
        parent::$data['rank0'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 0]));
        parent::$data['rank1'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 1]));
        parent::$data['rank2'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 2]));
        parent::$data['rank3'] = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => 3]));

        //album
        $page = empty($_GET['page']) ? 1 : (int)$_GET['page'];
        $num_of_per_page = 10;//一頁幾個

        $array_album_id = [];
        $array_user_id = [];

        if ($searchkey !== null) {
            switch ($searchtype) {
                case 'album':
                    $s_album = Solr('album')
                        ->column(['album_id'])
                        ->where([[[['_text_', '=', $searchkey]], 'and']])
                        ->fetchAll();

                    if (empty($s_album)) {
                        $m_album = [];
                        $num_of_item = 0;
                        goto _relay0;
                    }

                    $array_album_id = array_column($s_album, 'album_id');
                    break;

                case 'user':
                    $s_user = Solr('user')
                        ->column(['user_id'])
                        ->where([[[['_text_', '=', $searchkey]], 'and']])
                        ->fetchAll();

                    if (empty($s_user)) {
                        $m_album = [];
                        $num_of_item = 0;
                        goto _relay0;
                    }

                    $array_user_id = array_column($s_user, 'user_id');
                    break;
            }
        }

        switch ($rank_id) {
            default:
            case 0://最新作品
                $m_album = \albumModel::getLatest($categoryarea_id, $category_id, $array_album_id, $array_user_id, $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
                $num_of_item = \albumModel::getLatestCount($categoryarea_id, $category_id, $array_album_id, $array_user_id);
                break;

            case 1://熱門作品
                $m_album = \albumModel::getHot($categoryarea_id, $category_id, $array_album_id, $array_user_id, $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
                $num_of_item = \albumModel::getHotCount($categoryarea_id, $category_id, $array_album_id, $array_user_id);
                break;

            case 2://免費作品
                $m_album = \albumModel::getFree($categoryarea_id, $category_id, $array_album_id, $array_user_id, $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
                $num_of_item = \albumModel::getFreeCount($categoryarea_id, $category_id, $array_album_id, $array_user_id);
                break;

            case 3://贊助作品
                $m_album = \albumModel::getSponsored($categoryarea_id, $category_id, $array_album_id, $array_user_id, $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
                $num_of_item = \albumModel::getSponsoredCount($categoryarea_id, $category_id, $array_album_id, $array_user_id);
                break;
        }

        _relay0:

        $num_of_max_page = ceil($num_of_item / $num_of_per_page);
        $num_of_now_page = (1 <= $page && $page <= $num_of_max_page) ? $page : 1;
        $a_album = [];

        $collected = [];
        if (!empty($user)) {
            $m_albumqueue = Model('albumqueue')
                ->column(['album_id'])
                ->where([[[['user_id', '=', $user['user_id']]], 'and']])
                ->fetchAll();

            $collected = array_column($m_albumqueue, 'album_id');
        }

        /**
         *  Join event album highlight icon
         */
        $album_id_pool = [];
        $eventjoin_album_id = [];
        foreach (array_column($m_album, 'album') as $v0) {
            $album_id_pool[] = $v0['album_id'];
        } // $album_id_pool = collect this page album_id

        $m_eventjoin = Model('eventjoin')
            ->join([['left join', 'event', 'using(event_id)']])
            ->column(['DISTINCT eventjoin.album_id'])
            ->where([[[['eventjoin.album_id', 'in', $album_id_pool], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])
            ->fetchAll();

        foreach ($m_eventjoin as $v0) {
            $eventjoin_album_id[] = $v0['album_id'];
        }

        /**
         *  Top 10 album count
         **/
        $m_albumstatistics = Model('albumstatistics')
            ->join([['left join', 'album', 'using(album_id)']])
            ->where([[[['album.act', '=', 'open'], ['album.state', '=', 'success']], 'and']])
            ->column(['albumstatistics.album_id'])
            ->order(['albumstatistics.count' => 'desc'])
            ->limit(10)
            ->fetchAll();

        $hot_album_id = [];
        foreach ($m_albumstatistics as $v0) {
            $hot_album_id[] = $v0['album_id'];
        }

        $Image = new \Core\Image;

        foreach ($m_album as $v0) {
            //有參加投稿活動則不顯示autoplay功能
            $m_eventjoin = (new eventjoinModel())->join([['left join', 'event', 'using(event_id)']])->column(['event.event_id', 'event.name'])->where([[[['eventjoin.album_id', '=', $v0['album']['album_id']], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])->fetch();
            $shareUrl = !empty($m_eventjoin) ? parent::url('album', 'content', ['album_id' => $v0['album']['album_id'], 'categoryarea_id' => $categoryarea_id]) : parent::url('album', 'content', ['autoplay' => 1, 'album_id' => $v0['album']['album_id'], 'categoryarea_id' => $categoryarea_id]);

            //產生QRcode
            $_qrcodeUrl = str_replace('\\', '/', URL_STORAGE . storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $v0['user']['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $v0['album']['album_id'] . DIRECTORY_SEPARATOR . 'qrcode.jpg'));

            //封面圖
            if (is_image(PATH_UPLOAD . $v0['album']['cover'])) {
                $Image->set(PATH_UPLOAD . $v0['album']['cover']);

                switch ($Image->getType()) {
                    case 1:
                        //2017-08-21 Lion: 不做 resize 處理
                        break;

                    default:
                        $Image->setSize(\Config\Image::S4, \Config\Image::S4);
                        break;
                }

                $_cover = fileinfo($Image->save())['url'];
            } else {
                $_cover = null;
            }

            $_event_vote = null;
            $_description = (mb_strlen(strip_tags(nl2br($v0['album']['description']), 'UTF-8')) > 108) ? mb_substr(strip_tags(nl2br($v0['album']['description'])), 0, 35, 'UTF-8') . '...' : strip_tags(nl2br($v0['album']['description']));

            if (in_array($v0['album']['album_id'], $eventjoin_album_id)) {
                $m_eventjoin_id = Model('eventjoin')->column(['event_id'])->where([[[['eventjoin.album_id', '=', $v0['album']['album_id']]], 'and']])->order(['inserttime' => 'desc'])->limit(1)->fetchColumn();
                $_event_vote = '<a href="' . parent::url('event', 'content', ['event_id' => $m_eventjoin_id, 'click' => 'album_id_' . $v0['album']['album_id']]) . '"><i title="' . _('參加活動中') . '" class="add_act01"></i></a>';
            }

            $_share = '<li><a href="javascript:void(0)" onclick="share_album(' . $v0['album']['album_id'] . ', \'' . $_qrcodeUrl . '\' , \'' . $_cover . '\', \'' . $shareUrl . '\')">' . _('分享') . '</a></li>';
            $_collect = '';            //收藏相本
            $_collect_icon = '';    //收藏相本icon
            $_edit = '';            //修改相本
            $_report = '';            //檢舉相本

            if (!empty($user)) {
                if ($v0['user']['user_id'] != $user['user_id']) {
                    if (!Model('album')->is_own($v0['album']['album_id'], $user['user_id'])) {
                        $_collect = '<li data-albumid="' . $v0['album']['album_id'] . '"><a onclick="buyalbum(' . $v0['album']['album_id'] . ');" href="javascript:void(0)">' . _('Collection') . '</a></li>';
                    } else {
                        $_collect_icon = '<img src="' . static_file('images/assets-v5/icon-collection-h.svg') . '">';
                    }
                    $_report = '<li><a class="alert_btn" href="javascript:void(0)" data-type="album" data-type_id="' . $v0['album']['album_id'] . '">' . _('Report') . '</a></li>';
                } else {
                    $_edit = '<li><a href="' . parent::url('user', 'albumcontent_setting', ['album_id' => $v0['album']['album_id']]) . '">' . _('編輯') . '</a></li>';
                }
            } else {
                $_collect = '<li data-albumid="' . $v0['album']['album_id'] . '"><a href="javascript:void(0)" onclick="buyalbum(' . $v0['album']['album_id'] . ');">' . _('Collection') . '</a></li>';
                $_report = '<li><a class="alert_btn" href="' . parent::url('user', 'login', ['redirect' => parent::url('album', 'content', ['album_id' => $v0['album']['album_id'], 'categoryarea_id' => $categoryarea_id, 'report' => true])]) . '">' . _('Report') . '</a></li>';
            }
            $splitNum = ($_collect_icon == '') ? 17 : 16;
            $_name = (mb_strlen(strip_tags(nl2br($v0['album']['name']), 'UTF-8')) > 45) ? mb_substr(strip_tags(nl2br($v0['album']['name'])), 0, $splitNum, 'UTF-8') . '...' : strip_tags(nl2br($v0['album']['name']));

            $a_album[] = [
                'album' => [
                    'album_id' => $v0['album']['album_id'],
                    'name' => $_name,
                    'name_all' => $v0['album']['name'],
                    'description' => $_description,
                    'cover' => $_cover,
                    'cover_url' => parent::url('album', 'content', ['album_id' => $v0['album']['album_id'], 'categoryarea_id' => $categoryarea_id, 'click' => 'cover']),
                    'name_url' => parent::url('album', 'content', ['album_id' => $v0['album']['album_id'], 'categoryarea_id' => $categoryarea_id, 'click' => 'name']),
                    'hot' => (in_array($v0['album']['album_id'], $hot_album_id)) ? '<i title="' . _('Hot') . _('Album') . '" class="new_icon"></i>' : null,    //前台暫不顯示
                    'event_vote' => $_event_vote,                                                                                                            //前台暫不顯示
                    'menulist' => array_values(array_filter([$_share, $_collect, $_edit, $_report], function ($value) {
                        return $value !== '';
                    })),
                    'album_tags' => (new albumModel())->hasGiftTags($v0['album']['album_id']),
                ],
                'albumstatistics' => [
                    'viewed' => (new albumModel())->getAlbumViewed($v0['album']['album_id']),
                ],
                'categoryarea' => [
                    'categoryarea_id' => $categoryarea_id,
                ],
                'user' => [
                    'url' => Core::get_creative_url($v0['user']['user_id'], 'album_id_' . $v0['album']['album_id']),
                    'picture' => URL_STORAGE . Core::get_userpicture($v0['user']['user_id']),
                    'name' => $v0['user']['name'],
                    'collect' => $_collect_icon,
                ]
            ];
        }
        parent::$data['album'] = $a_album;

        //行動版下拉選單
        $m_select_option = [];
        $tmp = [];
        foreach ($m_categoryarea as $k0 => $v0) {
            if (!empty($rank_id)) $tmp = ['rank_id' => $rank_id];
            $m_select_option[$k0]['categoryarea_id'] = $v0['categoryarea_id'];
            $m_select_option[$k0]['url'] = parent::url('album', 'index', array_merge($tmp, ['categoryarea_id' => $v0['categoryarea_id']]));
            $m_select_option[$k0]['name'] = $v0['name'];
        }
        parent::$data['m_select_option'] = $m_select_option;

        //若有取得autobuyalbumid則視為接回原來操作動作, 開啟收藏詢問視窗
        $autoBuyAlbumId = null;
        if (!empty($_GET['buyalbumid'])) {
            $autoBuyAlbumId = 'buyalbum(' . $_GET['buyalbumid'] . ')';
        }
        parent::$data['autoBuyAlbumId'] = $autoBuyAlbumId;

        //若有取得autoplay則自動撥放
        $autoplay = null;
        if (!empty($_GET['autoplay'])) {
            $autoplay = 'browseKit_album(\'' . self::url('album', 'show_photo') . '\', {album_id: \'' . $_GET['autoplay'] . '\'});';
        }
        parent::$data['autoplay'] = $autoplay;

        /**
         * banner 區 取得分類內創作人發布排行
         */
        $banner_id = (empty($categoryarea_id)) ? 1 : $categoryarea_id;
        $creative_group = Model('creative')->creative_group_by_friday([$banner_id]);
        parent::$data['creative_group'] = $creative_group;

        //more
        if ($page >= $num_of_max_page) {
            $more = null;
        } else {
            $tmp0 = [];
            if ($categoryarea_id !== null) $tmp0['categoryarea_id'] = $categoryarea_id;
            if ($category_id !== null) $tmp0['category_id'] = $category_id;
            if ($searchkey !== null) {
                $tmp0['searchtype'] = $searchtype;
                $tmp0['searchkey'] = $searchkey;
            }
            $more = parent::url('album', 'index', array_merge($tmp0, ['rank_id' => $rank_id]));
        }
        parent::$data['more'] = $more;

        //seo
        if (!empty($categoryarea_id)) {
            $m_categoryarea = Model('categoryarea')->column(['name'])->where([[[['categoryarea_id', '=', $categoryarea_id]], 'and']])->fetchColumn();
        }
        if (!empty($category_id)) {
            $m_category = Model('category')->column(['name'])->where([[[['category_id', '=', $category_id]], 'and']])->fetchColumn();
        }

        parent::$data['current_categoryarea_name'] = $m_categoryarea;
        parent::$data['current_category_name'] = $m_category;

        if (empty($categoryarea_id)) {
            //所有作品
            switch ($rank_id) {
                case 0://最新
                    $suffix = _('Latest');
                    break;

                case 1://熱門
                    $suffix = _('Hot');
                    break;

                case 2://免費作品
                    $suffix = _('Free');
                    break;

                case 3://贊助作品
                    $suffix = _('Sponsored');
                    break;
            }
        } elseif (empty($category_id)) {
            //主分類
            $suffix = \Core\Lang::i18n($m_categoryarea);
        } else {
            //子分類
            $suffix = \Core\Lang::i18n($m_category);
        }

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Explore') . ' | ' . $suffix,
            [_('Explore'), $suffix]
        );

        //lightgallery
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
        parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');

        //jquery-textcomplete
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.textcomplete.js'), 'src');
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.overlay.js'), 'src');
        parent::$html->set_css(static_file('js/jquery-textcomplete/media/stylesheets/textcomplete.css'), 'href');

        //owl
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.carousel.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.theme.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.transitions.css'), 'href');
        parent::$html->set_js(static_file('js/owl.carousel/js/owl.carousel.min.js'), 'src');

        $this->album_tabs_rank();
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
        parent::$html->set_js(URL_ROOT . 'js/jquery-ias.min.js', 'src');
        parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
        parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }

    function likes()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;
            if (empty($user)) json_encode_return(2, _('Please login first.'), parent::url('user', 'login', ['redirect' => parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)])]));

            $hasLikes = (new album2likesModel)->hasLikes($user['user_id'], $album_id);

            if ($hasLikes) {
                //已按讚=>動作為取消
                (new album2likesModel)->cancelLikes($user['user_id'], $album_id);
            } else {
                //未按讚=>動作為按讚
                (new album2likesModel)->addLikes($user['user_id'], $album_id);
            }

            json_encode_return(1, null, null, !$hasLikes);
            exit;
        }
    }

    function report()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $url = !empty($_POST['url']) ? $_POST['url'] : null;

            if (empty($user)) {
                json_encode_return(2, _('Please login first.'), parent::url('user', 'login', ['redirect' => $url]));
            }

            $value = !empty($_POST['value']) ? $_POST['value'] : null;
            $text = '';
            $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;

            if ($album_id == null) {
                json_encode_return(0, _('Abnormal process, please try again.'));
            }

            $m_album = (new albumModel())->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
            if (empty($m_album) || $m_album['act'] != 'open') {
                json_encode_return(0, _('Album does not exist.'));
            }

            /**
             * 同作品重複檢舉且未處理數量超過三筆 , 十分鐘內檢舉過
             */
            $where = [[[['user_id', '=', $user['user_id']], ['id', '=', $album_id], ['state', '=', 'pretreat']], 'and']];
            $m_report = (new reportModel())->where($where)->order(['inserttime' => 'desc'])->fetchAll();
            if (!empty($m_report[0]['inserttime'])) {
                if (strtotime('+10 minute', strtotime($m_report[0]['inserttime'])) >= time()) json_encode_return(0, _('This operation cannot redo within 10 minutes.'));
            }

            if (count($m_report) > 3) json_encode_return(0, _('You have been report this album, we will deal with as soon as possible.'));

            $add = array(
                'reportintent_id' => $value,
                'user_id' => $user['user_id'],
                'type' => 'album',
                'id' => $album_id,
                'description' => $text,
                'state' => 'pretreat',
                'inserttime' => inserttime(),
            );

            (new reportModel())->add($add);

            json_encode_return(1, _('Your report has been sent, we will deal with as soon as possible, thanks.'));
        }
        die;
    }

    function save_album_ajax()
    {
        if (is_ajax()) {
            /**
             *  若因同時編輯造成兩本相本為process狀態時會觸發此流程
             *  取得"當下"編輯的相本ID，並強迫儲存其他處於process狀態的相本跳脫復數以上process相本異常
             */
            $user = parent::user_get();
            $album_id = isset($_POST['album_id']) ? $_POST['album_id'] : null;
            if ($album_id == null) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_album = Model('album')->column(['album_id'])->where([[[['state', '=', 'process'], ['user_id', '=', $user['user_id']]], 'and']])->fetchAll();
            if (!empty($m_album) && count($m_album) > 0) {
                foreach ($m_album as $k0 => $v0) {
                    if (!Model('album')->save($v0['album_id'])) {
                        json_encode_return(0, _('Abnormal process, please try again.') . '[Wrong album id]');
                    }
                }
            }

            list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->pretreat($user['user_id'], 0));
            json_encode_return(1, null, parent::url('diy', 'index', ['album_id' => $album_id]));
        }
    }

    function show_photo()
    {
        if (is_ajax()) {

            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $pageCalledByXHR = empty($_POST['pageCalledByXHR']) ? null : $_POST['pageCalledByXHR'];
            if ($album_id == null) json_encode_return(0, _('Abnormal process, please try again.'));

            $property = $this->content_property($album_id);

            $m_album = Model('album')->column(['user_id', 'preview', 'photo', 'audio_mode', 'audio_loop', 'audio_refer', 'audio_target', 'act', 'point'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();

            if (empty($m_album)) {
                json_encode_return(0, _('Album does not exist.'));
            } elseif ($m_album['act'] == 'delete') {
                json_encode_return(0, _('Album has been deleted.'));
            }

            //audio
            $a_audio = [];
            if ($m_album['audio_mode'] !== 'none') {
                $m_audio = Model('audio')->column(['audio_id', '`file`'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                foreach ($m_audio as $v0) {
                    if (is_audio(PATH_STATIC_FILE . $v0['file'])) $a_audio[$v0['audio_id']] = URL_STATIC_FILE . $v0['file'];
                }
            }

            $favorited = false;
            $user = parent::user_get();

            if (!empty($user)) {
                $m_albumqueue = Model('albumqueue')->column(['visible'])->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->fetch();
                if ($m_albumqueue && !$m_albumqueue['visible']) {
                    Model('albumqueue')->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->edit(['visible' => 1]);
                }
                if ($m_albumqueue || $m_album['user_id'] == $user['user_id']) {
                    $favorited = true;
                }
            }

            $where = $favorited ? [[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']] : [[[['album_id', '=', $album_id], ['act', '=', 'open'], ['image', 'in', json_decode($m_album['preview'], true)]], 'and']];
            $m_photo = Model('photo')->column(['`name`', 'description', 'image', 'usefor', 'hyperlink', 'audio_loop', 'audio_refer', 'audio_target', 'video_refer', 'video_target'])->where($where)->order(['sequence' => 'asc'])->fetchAll();

            $a_readable = [];
            $Image = new \Core\Image();

            foreach ($m_photo as $v0) {
                if (!is_image(PATH_UPLOAD . $v0['image'])) continue;

                $Image->set(PATH_UPLOAD . $v0['image']);

                $a_hyperlink = [];
                if (!empty($v0['hyperlink'])) {
                    foreach (json_decode($v0['hyperlink'], true) as $v1) {
                        if (trim($v1['url']) === '') continue;
                        if ($v1['text'] == '') $v1['text'] = $v1['url'];
                        $a_hyperlink[] = $v1;
                    }
                }

                //audio
                $audio_loop = null;
                $audio_target = null;
                switch ($m_album['audio_mode']) {
                    case 'singular':
                        $audio_loop = $m_album['audio_loop'];
                        switch ($m_album['audio_refer']) {
                            case 'file':
                                $audio_target = URL_UPLOAD . $m_album['audio_target'];
                                break;

                            case 'system':
                                if (isset($a_audio[$m_album['audio_target']])) $audio_target = $a_audio[$m_album['audio_target']];
                                break;
                        }
                        break;

                    case 'plural':
                        $audio_loop = $v0['audio_loop'];
                        switch ($v0['audio_refer']) {
                            case 'file':
                                $audio_target = URL_UPLOAD . $v0['audio_target'];
                                break;

                            case 'system':
                                if (isset($a_audio[$v0['audio_target']])) $audio_target = $a_audio[$v0['audio_target']];
                                break;
                        }
                        break;
                }

                $Image->set(PATH_UPLOAD . $v0['image']);

                switch ($Image->getType()) {
                    case 1:
                        //2017-08-18 Lion: 不做 resize 處理
                        break;

                    default:
                        $Image->setSize(\Config\Image::S2, \Config\Image::S2);
                        break;
                }

                $a_readable[] = [
                    'name' => htmlspecialchars($v0['name']),
                    'description' => nl2br(htmlspecialchars($v0['description'])),
                    'image' => URL_UPLOAD . $v0['image'],
                    'image_thumbnail' => fileinfo($Image->save())['url'],
                    'width' => $Image->getWidth(),
                    'height' => $Image->getHeight(),
                    'usefor' => $v0['usefor'],
                    'hyperlink' => $a_hyperlink,
                    'audio_mode' => $m_album['audio_mode'],
                    'audio_loop' => $audio_loop,
                    'audio_target' => $audio_target,
                    'video_refer' => $v0['video_refer'],
                    'video_target' => ($v0['video_refer'] == 'file') ? URL_UPLOAD . $v0['video_target'] : $v0['video_target'],
                    'recommendedBuyAlbum' => false,
                ];
            }

            if (empty($a_readable)) {
                $a_readable[] = ['image' => static_file('images/origin.jpg'), 'width' => 668, 'height' => 1002];
            } else {
                $addPreviewEndImg = false;
                if (!$favorited) {
                    if ($property['album']['releaseMode'] && $m_album['point'] > 0) {
                        //全部內容
                        $addPreviewEndImg = true;
                        $PreviewEndImg = static_file('images/preview_end_all.jpg');
                        $PreviewEndImgThumbnail = fileinfo($Image->set(PATH_STATIC_FILE . M_PACKAGE . DIRECTORY_SEPARATOR . SITE_LANG . DIRECTORY_SEPARATOR . 'images/preview_end_all.jpg')->setSize(\Config\Image::S2, \Config\Image::S2)->save())['url'];
                    }

                    if (!$property['album']['releaseMode']) {
                        //部分內容
                        $addPreviewEndImg = true;
                        $PreviewEndImg = static_file('images/preview_end_all.jpg');
                        $PreviewEndImgThumbnail = fileinfo($Image->set(PATH_STATIC_FILE . M_PACKAGE . DIRECTORY_SEPARATOR . SITE_LANG . DIRECTORY_SEPARATOR . 'images/preview_end_all.jpg')->setSize(\Config\Image::S2, \Config\Image::S2)->save())['url'];
                    }

                    $addPreviewEndImg_audioMode = ($m_album['audio_mode'] == 'singular') ? $m_album['audio_mode'] : null;
                    $addPreviewEndImg_audioTarget = ($m_album['audio_mode'] == 'singular') ? $audio_target : null;

                    if ($addPreviewEndImg) {
                        $a_readable[] = [
                            'image' => $PreviewEndImg,
                            'image_thumbnail' => $PreviewEndImgThumbnail,
                            'width' => 668,
                            'height' => 1002,
                            'recommendedBuyAlbum' => true,
                            'audio_mode' => $addPreviewEndImg_audioMode,
                            'audio_loop' => null,
                            'audio_target' => $addPreviewEndImg_audioTarget,
                        ];
                    }
                }
            }

            /*
             * albumModel->albumstatistics
             * pageCalledByXHR : 在popview彈出視窗內開啟預覽套件會造成瀏覽次數重複計算, 故若透過popview內的預覽套件則不再次計算瀏覽次數
             */
            if (!$pageCalledByXHR) (new albumModel())->increaseViewed($album_id);

            json_encode_return(1, null, null, ['favorited' => $favorited, 'readable' => $a_readable, 'property' => $property]);
        }
        die;
    }

    function vote()
    {
        if (is_ajax()) {
            $user = parent::user_get();

            $act = !empty($_POST['act']) ? $_POST['act'] : null;
            $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;
            $event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
            $result = 1;
            $message = null;
            $redirect = null;
            $data = null;

            if ($act == null || $album_id == null || $event_id == null) {
                $result = 0;
                $message = _('Abnormal process, please try again.');
                goto _return;
            }

            if (empty($user)) {
                $result = 2;
                $message = _('Please login first.');
                $redirect = parent::url('user', 'login', ['redirect' => parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)])]);
                $data = 'Modal';
                goto _return;
            }

            //取得此活動可投票數
            $m_event = (new eventModel())->where([[[['event_id', '=', $event_id], ['act', '=', 'open']], 'and']])->fetch();

            if (time() < strtotime($m_event['vote_starttime'])) {
                $result = 0;
                $message = _('投票時間尚未開始');
                goto _return;
            }

            if (time() > strtotime($m_event['vote_endtime'])) {
                $result = 0;
                $message = _('投票時間已經結束');
                goto _return;
            }

            //取得user已投票數
            $eventvoteModel = (new eventvoteModel());
            $m_eventvote = $eventvoteModel->column(['count(1)'])->where([[[['event_id', '=', $event_id], ['user_id', '=', $user['user_id']], ['inserttime', '>', date('Y-m-d 00:00:00')]], 'and']])->fetchColumn();

            if ($m_eventvote >= $m_event['vote']) {
                $result = 0;
                $message = _('Number of votes exceeds the limit.');
                goto _return;
            }

            Model('eventjoin');
            Model('eventvote');
            Model()->beginTransaction();

            $where = [[[['event_id', '=', $event_id], ['album_id', '=', $album_id], ['user_id', '=', $user['user_id']], ['inserttime', '>', date('Y-m-d 00:00:00')]], 'and']];
            $m_eventvoted = $eventvoteModel->column(['count(1)'])->where($where)->lock('for update')->fetchColumn();

            if (!empty($m_eventvoted)) {
                Model()->rollBack();
                $result = 0;
                $message = _('今天已經投過票囉, 請明天再來。');
                goto _return;
            }

            $add = [
                'event_id' => $event_id,
                'user_id' => $user['user_id'],
                'album_id' => $album_id
            ];
            $eventvoteModel->add($add);

            $m_eventvote = $eventvoteModel->column(['count(1)'])->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->fetchColumn();

            Model('eventjoin')->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->edit(['count' => $m_eventvote]);

            Model()->commit();

            $result = 1;
            $message = _('Vote success.');
            $redirect = parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]);
            $data = $album_id;
            goto _return;

            _return:
            json_encode_return($result, $message, $redirect, $data);
        }
        die;
    }
}