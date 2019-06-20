<?php

class eventController extends frontstageController
{
    function __construct()
    {
    }

    function _taketemplate_v2()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
            if (empty($user)) json_encode_return(2, null, parent::url('user', 'login', ['redirect' => parent::url('template', 'index')]));
            $a_param['event_id'] = $event_id;
            $template_id = isset($_POST['template_id']) ? $_POST['template_id'] : null;
            list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->process2($user['user_id']));
            $a_param['album_id'] = $album_id;
            $a_param['join_event'] = encrypt(['user_id' => $user['user_id'], 'event_id' => $event_id]);
            if ($result) json_encode_return(1, null, parent::url('diy', 'index', $a_param), $album_id);

            list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->pretreat($user['user_id'], $template_id));
            $a_param['album_id'] = $album_id;
            $a_param['join_event'] = encrypt(['user_id' => $user['user_id'], 'event_id' => $event_id]);
            json_encode_return(3, null, parent::url('diy', 'index', $a_param));

        }
        die;
    }

    function content()
    {
        $event_id = !empty($_GET['event_id']) ? $_GET['event_id'] : redirect(parent::url('event', 'index'), _('Event does not exist.'));
        $m_event = (new eventModel())->where([[[['event_id', '=', $event_id], ['act', '=', 'open']], 'and']])->fetch();

        if (empty($m_event)) redirect(parent::url('event', 'index'), _('Event does not exist.'));

        $user = parent::user_get();
        parent::$data['user'] = $user;

        //search
        $searchtype = null;
        if (isset($_GET['searchtype'])) {
            $searchtype = urldecode($_GET['searchtype']);
        }
        parent::$data['searchtype'] = $searchtype;

        $searchkey = null;
        if (isset($_GET['searchkey'])) {
            $searchkey = $_GET['searchkey'] == null ? null : urldecode($_GET['searchkey']);
        }
        parent::$data['searchkey'] = $searchkey;

        //eventjoin
        $m_eventjoin = (new eventjoinModel())
            ->where([[[['event_id', '=', $m_event['event_id']]], 'and']])
            ->order(['`count`' => 'desc'])
            ->fetchAll();

        $a_album = array();
        $a_user_album = array();
        foreach ($m_eventjoin as $k0 => $v0) {
            $column = array(
                'album.album_id',
                'album.user_id',
                'album.name',
                'album.cover',
                'albumstatistics.viewed',
                'user.name user_name',
                'categoryarea_category.categoryarea_id',
            );
            $join = array(
                array('left join', 'user', 'using(user_id)'),
                array('left join', 'albumstatistics', 'using(album_id)'),
                ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
            );
            $where = array(
                array(array(array('album.album_id', '=', $v0['album_id']), array('album.act', '=', 'open'), array('user.act', '=', 'open')), 'and')
            );

            switch ($searchtype) {
                case 'album_id' :
                    if (!is_null($searchkey)) $where[] = array(array(array('album.album_id', '=', str_replace(array('%', '_'), array('\%', '\_'), strtolower($searchkey)) . '%')), 'and');//特別跳脫 % 和 _
                    break;

                case 'album' :
                    $where[] = array(array(array('album.name', 'like', str_replace(array('%', '_'), array('\%', '\_'), strtolower($searchkey)) . '%')), 'and');//特別跳脫 % 和 _
                    break;

                case 'user' :
                    $where[] = array(array(array('user.name', 'like', str_replace(array('%', '_'), array('\%', '\_'), strtolower($searchkey)) . '%')), 'and');//特別跳脫 % 和 _
                    break;

                default:
                    break;
            }

            $m_album = (new \albumModel)
                ->column($column)
                ->join($join)
                ->where($where)
                ->fetch();

            if (!empty($m_album)) {
                $tmpData = [
                    'album_id' => $v0['album_id'],
                    'user_id' => $m_album['user_id'],
                    'name' => $m_album['name'],
                    'cover' => URL_UPLOAD . getimageresize($m_album['cover'], 150, 225),
                    'user_name' => $m_album['user_name'],
                    'vote' => $v0['count'],
                    'viewed' => $m_album['viewed'],
                    'picture' => URL_STORAGE . Core::get_userpicture($m_album['user_id']),
                    'url' => parent::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $m_album['categoryarea_id']]),
                    'user_url' => Core::get_creative_url($m_album['user_id']),
                    'qrcodeUrl' => str_replace('\\', '/', URL_STORAGE . storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $m_album['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $v0['album_id'] . DIRECTORY_SEPARATOR . 'qrcode.jpg')),
                ];

                //所有參賽相本
                $a_album[] = $tmpData;

                //登入者參賽相本
                if ($user['user_id'] == $m_album['user_id']) $a_user_album[] = $tmpData;
            }
        }
        parent::$data['album'] = $a_album;
        parent::$data['user_album'] = $a_user_album;

        //votetotal
        $c_eventjoin = (new eventjoinModel())->column(['SUM(`count`)'])->where([[[['event_id', '=', $m_event['event_id']]], 'and']])->fetchColumn();

        //eventstatistics
        $m_eventstatistics = (new eventstatisticsModel())->column(['viewed'])->where([[[['event_id', '=', $event_id]], 'and']])->fetchColumn();

        //eventjoinAlbumstatistics
        $c_eventjoinAlbumstatistics = (new eventjoinModel())->column(['SUM(albumstatistics.viewed)'])->join([['left join', 'albumstatistics', 'using(`album_id`)']])->where([[[['event_id', '=', $event_id]], 'and']])->fetchColumn();

        if (time() < strtotime($m_event['starttime']) && time() < strtotime($m_event['endtime'])) {
            $status = 'prepare';
        } else if (time() > strtotime($m_event['starttime']) && time() < strtotime($m_event['endtime'])) {
            $status = 'unexpired';
        } else {
            $status = 'expired';
        }

        if (time() < strtotime($m_event['contribute_starttime']) && time() < strtotime($m_event['contribute_endtime'])) {
            $contribute_status = 'prepare';
        } else if (time() > strtotime($m_event['contribute_starttime']) && time() < strtotime($m_event['contribute_endtime'])) {
            $contribute_status = 'unexpired';
        } else {
            $contribute_status = 'expired';
        }

        if (time() < strtotime($m_event['vote_starttime']) && time() < strtotime($m_event['vote_endtime'])) {
            $vote_status = 'prepare';
        } else if (time() > strtotime($m_event['vote_starttime']) && time() < strtotime($m_event['vote_endtime'])) {
            $vote_status = 'unexpired';
        } else {
            $vote_status = 'expired';
        }

        //event
        $a_event = [
            'event_id' => $m_event['event_id'],
            'name' => $m_event['name'],
            'title' => $m_event['title'],
            'image' => URL_UPLOAD . $m_event['image'],
            'description' => $m_event['description'],
            'award' => json_decode($m_event['award'], true),
            'show_rank_num' => $m_event['show_rank_num'],
            'starttime' => $m_event['starttime'],
            'endtime' => $m_event['endtime'],
            'contribute_starttime' => $m_event['contribute_starttime'],
            'contribute_endtime' => $m_event['contribute_endtime'],
            'vote_starttime' => $m_event['vote_starttime'],
            'vote_endtime' => $m_event['vote_endtime'],
            'contribution' => $m_event['contribution'],
            'status' => $status,
            'contribute_status' => $contribute_status,
            'vote_status' => $vote_status,
            'popularity' => $c_eventjoin + $m_eventstatistics + $c_eventjoinAlbumstatistics,
        ];
        parent::$data['event'] = $a_event;

        //auto vote
        $auto_vote = null;
        $album_id = !empty($_GET['album_id']) ? $_GET['album_id'] : null;
        if ($album_id != null && !empty($_GET['key']) && encrypt(['album_id' => $album_id, 'event_id' => $event_id]) == $_GET['key']) {
            $auto_vote = '$(\'.vote_btn[data-album_id="' . $album_id . '"]\').trigger(\'click\'); ';
        }
        parent::$data['auto_vote'] = $auto_vote;

        //auto browseKit_album
        $auto_play = null;
        $auto_browseKitAlbumId = !empty($_GET['auto_play']) ? $_GET['auto_play'] : null;
        if ($auto_browseKitAlbumId != null) {
            $auto_play = 'browseKit_album("' . parent::url('album', 'show_photo') . '", {album_id:' . $auto_browseKitAlbumId . '});';
        }
        parent::$data['auto_play'] = $auto_play;

        /**
         *  取得登入者已投票的相簿
         *  160902 - 改為跨日可在投票, 故此處改為取得"當天"有投票的相簿紀錄
         */
        $user_voted = array();
        if (!empty($user)) {
            $where = [[[['event_id', '=', $event_id], ['user_id', '=', $user['user_id']], ['inserttime', '>', date('Y-m-d 00:00:00')]], 'and']];
            $m_eventvote = (new eventvoteModel())->column(['album_id'])->where($where)->fetchAll();
            if (!empty($m_eventvote)) {
                foreach ($m_eventvote as $v0) {
                    $user_voted[] = $v0['album_id'];
                }
            }
        }
        parent::$data['user_voted'] = $user_voted;

        //auto tab
        $auto_tab = (isset($_GET['exchange']) && $_GET['exchange'] == 'true') ? '$(\'#my_albums\').trigger(\'click\'); ' : null;
        parent::$data['auto_tab'] = $auto_tab;

        //eventstatistics
        $tmp0 = md5(parent::disqus_event($event_id));
        if (!isset($_COOKIE[$tmp0])) {
            setcookie($tmp0, true, time() + 86400);
            $viewed = Model('eventstatistics')->column(array('viewed'))->where(array(array(array(array('event_id', '=', $event_id)), 'and')))->fetch();
            (!is_numeric($viewed['viewed'])) ? Model('eventstatistics')->add(['event_id' => $event_id, 'viewed' => 1]) : Model('eventstatistics')->where(array(array(array(array('event_id', '=', $event_id)), 'and')))->edit(['viewed' => $viewed['viewed'] + 1]);
        }

        //pinpinboard
        $a_pinpinboard = (new pinpinboardModel())->getComment('event', $event_id, $user['user_id']);
        parent::$data['pinpinboard'] = $a_pinpinboard;

        $pinpinboardParam = [
            'type' => 'event',
            'type_id' => $event_id,
            'redirectParam' => 'event_id',
        ];
        parent::$data['pinpinboardParam'] = $pinpinboardParam;

        //exchange_page
        $exchange_page['web'] = ($m_event['exchange_page'] && !empty($user) && !empty($a_user_album)) ? '<a href="' . self::url('event', 'special', ['event_id' => $m_event['event_id']]) . '" class="join ora"><img src="' . static_file('images/gift.png') . '" height="20" width="18">' . _('我要兌換') . '</a>' : null;
        $exchange_page['mobile'] = ($m_event['exchange_page'] && !empty($user) && !empty($a_user_album)) ? '<a href="' . self::url('event', 'special', ['event_id' => $m_event['event_id']]) . '"><img src="' . static_file('images/icon_gift.svg') . '" onerror="this.onerror=null; this.src=\'' . static_file('images/icon_gift.png') . '\'"></a>' : null;
        parent::$data['exchange_page'] = $exchange_page;

        //disqus
        parent::$data['disqus'] = parent::disqus('event', $m_event['event_id']);

        //seo
        $this->seo(
            $m_event['name'] . ' | ' . Core::settings('SITE_TITLE'),
            array($m_event['name']),
            $m_event['title'],
            URL_UPLOAD . $m_event['image']
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();

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

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.ias/css/jquery.ias.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.ias/js/jquery-ias.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.countdown.js'), 'src');
        // 需使用 horizontalOrder 參數故此頁使用 4.2版本
        parent::$html->set_js(static_file('js/masonry-4.2.pkgd.min.js'), 'src');
        parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
        parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
        parent::$html->jbox();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function content_property()
    {
        if (is_ajax()) {
            $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;
            $user = parent::user_get();
            list($result, $message, , $data0) = array_decode_return(Model('album')->content($album_id));
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
            if ($login) {
                $buyAlbumBoxBtn = ($balance < 0) ? _('前往儲值') : _('Yes');
            } else {
                $buyAlbumBoxBtn = _('Yes');
            }

            //Content 文字內容
            $buyAlbumBoxContent = '<div class="content">';
            //content0
            if (!$releaseMode) {
                if (!$data0['album']['point']) {
                    $Content0 = '<p class="keypoint" style="font-size:2em;">' . _('馬上收藏 看全部內容') . '</p><br>';
                } else {
                    $Content0 = '<p class="keypoint" style="font-size:2em;">' . _('贊助P點 看全部內容') . '</p><br>';
                }
            } else {
                $Content0 = ($data0['album']['point']) ? '<p class="keypoint" style="font-size:2em;">' . _('喜歡作品就給個鼓勵吧!') . '</p><br>' : '<p class="keypoint" style="font-size:2em;">' . _('馬上收藏 接收更新通知') . '</p><br>';
            }

            //content1
            if ($mobile) {
                $content1 = '<p>';
                $content1 .= '<a data-uri="' . Core::settings('ANDROID_DATA_URI') . '" onclick="clickHandler(this.dataset.uri)"><img class="app" src="' . static_file('images/jbox_and.png') . '"></a>';
                $content1 .= '<a data-uri="' . Core::settings('IOS_DATA_URI') . '" onclick="clickHandler(this.dataset.uri); ios_notice();" href="javascript:void(0)"><img class="app" src="' . static_file('images/jbox_ios.png') . '"></a>';
                $content1 .= '</p>';
            } else {
                $content1 = '<p>';
                $content1 .= '<a href ="' . Core::settings('GOOGLEPLAY_APK_URL') . '"><img class="app" src="' . static_file('images/jbox_and.png') . '"></a>';
                $content1 .= '<a href="javascript:void(0)" onclick="ios_notice()"><img class="app" src="' . static_file('images/jbox_ios.png') . '"></a>';
                $content1 .= '</p>';
            }

            //content2
            $content2 = null;
            if ($login) {
                $content3 = (!$data0['album']['point']) ? null : '<p>' . _('現有P點') . '&nbsp;：<span class="red">' . $userpoint . 'P</span></p>
					  <p>' . _('贊助P點') . '&nbsp;：<input name="customerpoint" onkeypress="return event.charCode >= 48 && event.charCode <= 57;" onkeyup="checkpoint(this, this.value);" onchange="checkpoint(this, this.value);" type="number" maxlength="4" value="' . $data0['album']['point'] . '" style="width:30%;"></p>
					  <p class="red" name="pointTips"></p>';
            } else {
                $content3 = (!$data0['album']['point']) ? null : '<p>' . _('贊助P點') . '&nbsp;：<span class="red">' . $data0['album']['point'] . 'P</span></p>';
            }

            $ContentEnd = '</div>';
            $buyAlbumBoxContent .= $Content0 . $content1 . $content2 . $content3 . $ContentEnd;
            // --buyAlbumBox文字區塊結尾

            //collected 是否收藏
            if ($login) {
                //檢查是否已經有購買
                $m_albumqueue = albumqueueModel::newly()->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->fetch();
                //檢查是否為職人
                $album_owner = ($user['user_id'] == $data0['user']['user_id']) ? true : false;
            }
            $collected = (!empty($m_albumqueue) || !empty($album_owner)) ? true : false;

            //是否已點讚
            $hasLikes = (new album2likesModel)->hasLikes($user['user_id'], $album_id);

            //eventjoin 相本是否參加活動
            $m_eventjoin = eventjoinModel::newly()->join([['left join', 'event', 'using(event_id)']])->column(['event.event_id', 'event.name', 'eventjoin.count'])->where([[[['eventjoin.album_id', '=', $album_id], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])->fetch();

            $hasVoted = null;
            if (!empty($m_eventjoin)) {
                $hasVoted = (new eventModel)->hasVoted($m_eventjoin['event_id'], $album_id, $user['user_id']);
            }

            //pc_button mobile_button 電腦版及手機板投票樣式
            $vote_btn = $btn_class = $m_vote_btn = null;
            if ($collected) {
                if (!empty($m_eventjoin) && (!$hasVoted)) {
                    $btn_class = 'twobtns';

                    $vote_btn = '<a id="vote" href="javascript:void(0)" class="used big white ' . $btn_class . '">
							<img class="icon_vote" src="' . static_file('images/icon_vote.svg') . '" >' . _('投票') . '
						</a>';

                    $m_vote_btn = '<a id="vote_mobile" href="javascript:void(0);" class="used big white ' . $btn_class . '">
	                                <img class="icon_vote" src="' . static_file('images/icon_vote.svg') . '">' . _('投票') . '
	                            </a>';
                }

                $pc_button = '<li class="mobilehide02" id="collected">
					' . $vote_btn . '
					<a id="read" href="javascript:void(0);" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})" class="used big ' . $btn_class . '">' . _('觀看') . '
					</a>
				</li>';

                $mobile_button = $m_vote_btn . '<a id="read" href="javascript:void(0);" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})" class="used big ' . $btn_class . '">' . _('觀看') . '</a>';
            } else {
                $btn_class = 'twobtns ';
                if (!empty($m_eventjoin) && (!$hasVoted)) {
                    $m_vote_btn_style = 'background-color:#fff;color:#00acc1;border:1px #00acc1 solid;';

                    $btn_class = 'threebtns ';

                    $vote_btn = '<a id="vote" href="javascript:void(0)" class="used big white ' . $btn_class . '">
							<img class="icon_vote" src="' . static_file('images/icon_vote.svg') . '" >' . _('投票') . '
						</a>';

                    $m_vote_btn = '<a id="vote_mobile" href="javascript:void(0);" class="used big white ' . $btn_class . '">
	                    <img class="icon_vote" src="' . static_file('images/icon_vote.svg') . '">' . _('投票') . '
	                </a>';
                }

                $heart_text = ($data0['album']['point'] > 0) ? _('Sponsored') : _('Collection');
                $pc_button = '<li class="mobilehide02" id="preview">
						' . $vote_btn . '
						<a href="javascript:void(0);" id="read" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})" class="used big white ' . $btn_class . '" style="margin-bottom:5px;">' . _('觀看') . '
						</a>
						<a href="javascript:void(0);" onclick="buyalbum()" id="trip_collect" class="used big ' . $btn_class . '">
							' . $heart_text . '
						</a>
					</li>';

                $mobile_button = $m_vote_btn . '<a href="javascript:void(0);" id="read" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})" class="used big white ' . $btn_class . '">' . _('觀看') . '
						</a>
						<a href="javascript:void(0);" onclick="buyalbum()" id="trip_collect" class="used big ' . $btn_class . '">
							' . $heart_text . '
						</a>';
            }

            $qrcodeUrl = str_replace('\\', '/', URL_STORAGE . storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $data0['user']['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $album_id . DIRECTORY_SEPARATOR . 'qrcode.jpg'));

            $return = [
                'album' => [
                    'data' => $data0,
                    'photo' => $photo,
                    'releaseMode' => $releaseMode,
                    'albumPhotos' => $albumPhotos,
                    'previewPhotos' => $previewPhotos,
                    'eventjoin' => $m_eventjoin,
                    'qrcodeUrl' => $qrcodeUrl,
                ],

                'user' => [
                    'mobileDevice' => $mobile,
                    'userpoint' => $userpoint,
                    'collected' => $collected,
                    'haslikes' => $hasLikes,
                    'hasvoted' => $hasVoted,
                ],

                'property' => [
                    'login' => $login,
                    'balance' => $balance,
                    'favorited' => $favorited,
                    'buyAlbumBoxContent' => $buyAlbumBoxContent,
                    'buyAlbumBoxBtn' => $buyAlbumBoxBtn,
                    'pc_button' => $pc_button,
                    'mobile_button' => $mobile_button,
                ],
            ];

            json_encode_return(1, null, null, $return);
        }
    }

    function edit_event_template_join()
    {
        if (is_ajax()) {
            $template_id = !empty($_POST['template_id']) ? $_POST['template_id'] : null;
            $event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
            $user = parent::user_get();

            if ($template_id == null || $event_id == null) json_encode_return(0, _('Abnormal process, please try again.'));

            //驗證版型配合活動
            $m_event_templatejoin = Model('event_templatejoin')->where([[[['event_id', '=', $event_id], ['template_id', '=', $template_id]], 'and']])->fetchAll();
            if (empty($m_event_templatejoin)) json_encode_return(0, _('Abnormal process, please try again.') . '[event_templatejoin]');

            //驗證使用者以投稿數量
            $m_eventContribution = Model('event')->column(['contribution'])->where([[[['event_id', '=', $event_id]], 'and']])->fetch();
            if (!$m_eventContribution['contribution']) json_encode_return(0, _('Abnormal process, please try again.') . '[event_contribution]');
            $m_eventjoin = Model('eventjoin')->column(['COUNT(1) as num'])->join([['left join', 'album', 'using(album_id)']])->where([[[['event_id', '=', $event_id], ['album.user_id', '=', $user['user_id']]], 'and']])->fetch();
            if (($m_eventContribution['contribution'] - $m_eventjoin['num']) < 1) json_encode_return(0, _('投稿數量已達上限, 請先撤下已投稿作品。'));

            //檢查使用者是否購買過此版型 或 為版型作者
            $m_template = Model('template')->where([[[['template_id', '=', $template_id], ['state', '=', 'success'], ['act', '=', 'open']], 'and']])->fetch();
            if (empty($m_template)) json_encode_return(0, _('Template does not exist.'));
            $m_templatequeue = Model('templatequeue')->column(['count(1)'])->where([[[['user_id', '=', $user['user_id']], ['template_id', '=', $template_id]], 'and']])->fetchColumn();
            $a_param = array();
            $a_param['join_event'] = encrypt(['user_id' => $user['user_id'], 'event_id' => $event_id]);
            //擁有該版型 or 為版型作者時
            if (!empty($m_templatequeue) || $m_template['user_id'] == $user['user_id']) {
                list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->process2($user['user_id']));
                $a_param['album_id'] = $album_id;
                if ($result) json_encode_return(4, null, parent::url('diy', 'index', $a_param), $album_id);

                list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->pretreat($user['user_id'], $template_id));
                $a_param['album_id'] = $album_id;
                json_encode_return(5, null, parent::url('diy', 'index', $a_param));
            }

            //進行交易(收藏版型)
            (new Model)->beginTransaction();

            list ($result, $message, $redirect, $data) = array_decode_return(Core::exchange($user['user_id'], 'web', 'template', $template_id));
            if (!$result) {
                (new Model)->rollBack();

                json_encode_return(0, $message);
            }
            (new Model)->commit();

            list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->pretreat($user['user_id'], $template_id));
            $a_param['album_id'] = $album_id;
            json_encode_return(1, _('Purchase success!'), parent::url('diy', 'index', $a_param));
        }
    }

    function event_join_album($user_id, $event_id)
    {
        $user_eventjoin = $album = null;
        $user_other_eventjoin = [];
        $where = [['album.user_id', '=', $user_id], ['album.act', '=', 'open'], ['album.state', '=', 'success']];

        //檢查此活動是否有限定版型製作的作品參加
        $m_event_templatejoin = Model('event_templatejoin')->where([[[['event_id', '=', $event_id]], 'and']])->fetchAll();
        if (!empty($m_event_templatejoin)) {
            foreach ($m_event_templatejoin as $k0 => $v0) {
                $tmp[] = $v0['template_id'];
            }
            $where[] = ['album.template_id', 'in', $tmp];
        }

        $m_album = (new albumModel())->column(['album.*', 'albumstatistics.*'])->join([['left join', 'albumstatistics', 'using(album_id)']])->order(['album.inserttime' => 'desc'])->where([[$where, 'and']])->fetchAll();

        //使用者參加的活動作品統計
        $m_eventjoin = (new eventjoinModel())->column(['eventjoin.album_id', 'eventjoin.event_id'])->join([['left join', 'album', 'using(album_id)']])->where([[[['album.user_id', '=', $user_id], ['album.act', '=', 'open'], ['album.state', '=', 'success']], 'and']])->fetchAll();

        foreach ($m_eventjoin as $k0 => $v0) {
            if ($v0['event_id'] == $event_id) {
                $user_eventjoin[] = $v0['album_id'];
            } else {
                $user_other_eventjoin[] = $v0['album_id'];
            }
        }

        if (!empty($m_album)) {
            $album = [];
            foreach ($m_album as $k0 => $v0) {
                if (!in_array($v0['album_id'], $user_other_eventjoin)) {
                    $album[$k0]['album_id'] = $v0['album_id'];
                    $album[$k0]['cover'] = $v0['cover'];
                    $album[$k0]['name'] = $v0['name'];
                    $album[$k0]['viewed'] = $v0['viewed'];
                }
            }
        }

        return array($album, $user_eventjoin);
    }

    function index()
    {
        $m_event = (new eventModel())->where([[[['act', '=', 'open']], 'and']])->order(['inserttime' => 'desc'])->fetchAll();

        $a_event = [];
        foreach ($m_event as $k0 => $v0) {
            if (time() < strtotime($v0['starttime']) && time() < strtotime($v0['endtime'])) {
                $status = 'prepare';
            } else if (time() > strtotime($v0['starttime']) && time() < strtotime($v0['endtime'])) {
                $status = 'unexpired';
            } else {
                $status = 'expired';
            }

            $a_event[] = [
                'event_id' => $v0['event_id'],
                'name' => $v0['name'],
                'title' => $v0['title'],
                'image' => URL_UPLOAD . $v0['image'],
                'starttime' => $v0['starttime'],
                'endtime' => $v0['endtime'],
                'contribute_starttime' => $v0['contribute_starttime'],
                'contribute_endtime' => $v0['contribute_endtime'],
                'vote_starttime' => $v0['vote_starttime'],
                'vote_endtime' => $v0['vote_endtime'],
                'contribution' => $v0['contribution'],
                'status' => $status,
                'popularity' => \eventModel::getPopularity($v0['event_id']),
            ];
        }

        parent::$data['event'] = $a_event;

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Activities'),
            array(_('Activities'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.ias/css/jquery.ias.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/jquery.ias/js/jquery-ias.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.countdown.js'), 'src');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }

    function info()
    {
        $event_id = empty($_GET['event_id']) ? redirect(parent::url('event', 'index'), _('Event does not exist.')) : $_GET['event_id'];

        $m_event = (new \eventModel)
            ->where([[[['event_id', '=', $event_id], ['starttime', '<', date('Y-m-d H:i:s', time())], ['act', '=', 'open']], 'and']])
            ->fetch();

        if (empty($m_event)) redirect(parent::url('event', 'index'), _('Event does not exist.'));

        //event
        parent::$data['event'] = [
            'description' => $m_event['description'],
        ];

        parent::head();
        parent::foot();

        parent::$html->set_css(static_file('css/bootstrap.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function join()
    {
        $user = parent::user_get();

        if (is_ajax()) {
            $a_album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;
            $event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
            if ($event_id == null) {
                json_encode_return(0, _('Abnormal process, please try again.'));
            }

            $m_event = Model('event')->where(array(array(array(array('event_id', '=', $event_id), array('act', '=', 'open')), 'and')))->fetch();

            if (empty($m_event)) json_encode_return(0, _('Event does not exist.'), null, 'Modal');
            if (empty($user)) json_encode_return(2, _('Please login first.'), parent::url('user', 'login', array('redirect' => parent::url('event', 'join', array('event_id' => $event_id)))), 'Modal');
            if (strtotime('NOW') > strtotime($m_event['endtime'])) json_encode_return(0, _('Event is expired.'), parent::url('event', 'content', array('event_id' => $event_id)), 'Modal');//活動截止轉址
            if (count($a_album_id) > $m_event['contribution']) json_encode_return(0, _('The quantity you submitted is beyond the limit.'));

            //取得user相簿資料 [登入者所有作品],[參加此活動的作品]
            list($album, $user_eventjoin) = $this::event_join_album($user['user_id'], $m_event['event_id']);

            //沒有已投稿的作品
            if ($user_eventjoin != null) {
                $tmp_delete = array_diff($user_eventjoin, $a_album_id);    //要刪除的ID
                $tmp_add = array_diff($a_album_id, $user_eventjoin);    //要新增的ID
            } else {
                $tmp_add = $a_album_id;
            }

            /**
             *    由於不限制投稿數，所以delete不一定等於add，故無法從count驗證
             */

            //先做delete->eventjoin、eventvote
            if (!empty($tmp_delete)) {
                $where = array();
                $where[] = array(array(array('album_id', 'in', $tmp_delete), array('event_id', '=', $event_id)), 'and');
                Model('eventjoin')->where($where)->delete();
                Model('eventvote')->where($where)->delete();
            }

            //做add
            if (!in_array('', $a_album_id)) {
                $tmp0 = array();
                foreach ($tmp_add as $v0) {
                    if (empty(Model('album')->where(array(array(array(array('album_id', '=', $v0), array('user_id', '=', $user['user_id']), array('act', '=', 'open')), 'and')))->fetch())) json_encode_return(0, _('Album does not exist.'), null, 'Modal');
                    $tmp0[] = array(
                        'event_id' => $event_id,
                        'album_id' => $v0
                    );
                }
                if (!empty($tmp0)) {
                    $a_album_id = array();
                    foreach ($tmp0 as $v0) {
                        $a_album_id[] = $v0['album_id'];
                    }
                    if (!empty($a_album_id)) {
                        Model('eventjoin')->where(array(array(array(array('event_id', '=', $event_id), array('album_id', 'in', $a_album_id)), 'and')))->delete();
                    }
                    Model('eventjoin')->add($tmp0);
                }

                $return_message = _('Thanks for your participation.');
            } else {
                $return_message = _('You have signed out of the event, thank you.');
            }

            json_encode_return(1, $return_message, parent::url('event', 'content', array('event_id' => $event_id)));
        }

        $event_id = !empty($_GET['event_id']) ? $_GET['event_id'] : redirect(parent::url('event', 'index'), _('Event does not exist.'));

        $m_event = Model('event')->where(array(array(array(array('event_id', '=', $event_id), array('act', '=', 'open')), 'and')))->fetch();

        if (empty($m_event)) redirect(parent::url('event', 'index'), _('Event does not exist.'));
        if (empty($user)) redirect(parent::url('user', 'login', array('redirect' => parent::url('event', 'join', array('event_id' => $event_id)))), _('Please login first.'));
        if (strtotime('NOW') > strtotime($m_event['endtime'])) redirect(parent::url('event', 'content', array('event_id' => $event_id)), _('Event is expired.'));//活動截止轉址

        //event
        parent::$data['event'] = array(
            'event_id' => $m_event['event_id'],
            'name' => $m_event['name'],
            'contribution' => $m_event['contribution'],
        );

        //取得user相簿資料 [登入者所有作品],[參加此活動的作品]
        list($album, $user_eventjoin) = $this::event_join_album($user['user_id'], $m_event['event_id']);

        //event_templatejoin
        $column = [
            'template.template_id',
            'template.style_id',
            'template.image',
            'template.name',
            'templatestatistics.viewed',
        ];
        $m_event_templatejoin = Model('event_templatejoin')->column($column)->join([['left join', 'template', 'USING(`template_id`)'], ['left join', 'templatestatistics', 'USING(`template_id`)']])->where([[[['event_templatejoin.event_id', '=', $m_event['event_id']]], 'and']])->fetchAll();

        $a_template_join = array();
        foreach ($m_event_templatejoin as $k0 => $v0) {
            $tmp = array();
            $tmp['template_id'] = $v0['template_id'];
            $tmp['style_id'] = $v0['style_id'];
            $tmp['image'] = URL_UPLOAD . image_reformat(M_PACKAGE . $v0['image'], 'jpg', 220, 330);
            $tmp['name'] = $v0['name'];
            $tmp['viewed'] = $v0['viewed'];

            $a_template_join[] = $tmp;
        }

        $titleText = _('請選擇欲投稿作品') . ' <span class="numSelect">' . count($user_eventjoin) . '</span>/<span class="numLimit">' . $m_event['contribution'] . '</span>';

        //提交按鈕樣式
        if (count($a_template_join) == 0) {
            if (count($album) == 0) {
                $button[] = [
                    'href' => 'javascript:void(0)',
                    'style' => '',
                    'onclick' => 'create();',
                    'text' => _('新建投稿作品'),
                ];
                $titleText = _('您沒有可以投稿的作品，快去建立一個吧。');
            } else {
                $button = [
                    [
                        'href' => 'javascript:void(0)',
                        'style' => null,
                        'onclick' => 'confirm_select()',
                        'text' => _('Submit'),
                    ]];

                if (count($user_eventjoin) < $m_event['contribution']) {
                    $button[] = [
                        'href' => 'javascript:void(0)',
                        'style' => 'style="right:31%"',
                        'onclick' => 'create();',
                        'text' => _('新建投稿作品'),
                    ];
                }
            }
        } else {
            $button = [[
                'href' => 'javascript:void(0)',
                'style' => null,
                'onclick' => 'confirm_select()',
                'text' => _('Submit'),
            ]];
        };

        parent::$data['titleText'] = $titleText;
        parent::$data['event_templatejoin'] = $a_template_join;
        parent::$data['button'] = $button;
        parent::$data['album'] = $album;
        parent::$data['user_eventjoin'] = (!empty($user_eventjoin)) ? $user_eventjoin : array();
        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . $m_event['name'],
            array(_('My Album'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_jquery_validation();
        parent::$html->jbox();

        //photoswipe
        parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/photoswipe.css'), 'href');
        parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/default-skin/default-skin.css'), 'href');
        parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe.min.js'), 'src');
        parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe-ui-default.min.js'), 'src');

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.ias/css/jquery.ias.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/jquery.ias/js/jquery-ias.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.countdown.js'), 'src');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function pingirl()
    {

        $user_id = explode(',', Core::settings('PINGIRL_USER_ID'));

        $starttime = Core::settings('PINGIRL_STARTTIME');
        $endtime = Core::settings('PINGIRL_ENDTIME');

        parent::$data['starttime'] = date('Y/m/d', strtotime($starttime));
        parent::$data['endtime'] = date('Y/m/d', strtotime($endtime));

        $eventPointTotal = 0;
        $pingirl = [];

        $m_pingirl = (new eventModel())->getPinGirl($user_id, $starttime, $endtime);

        if ($m_pingirl) {
            foreach ($m_pingirl as $k0 => $v0) {
                $pingirl[] = [
                    'userPingirlId' => $v0['create_user_id'],
                    'userPingirlPic' => URL_STORAGE . Core::get_userpicture($v0['create_user_id']),
                    'userPingirlUrl' => Core::get_creative_url($v0['create_user_id']),
                    'userPingirlUrlScheme' => parent::deeplink('creative', 'content', ['user_id' => $v0['create_user_id']]),
                    'userPingirlName' => $v0['user_name'],
                    'userPingirlPointTotal' => $v0['total']
                ];
                $eventPointTotal += $v0['total'];
            }
        }

        parent::$data['pingirl'] = $pingirl;
        parent::$data['eventPointTotal'] = $eventPointTotal;
        parent::$data['event_url'] = parent::url('event', 'content', ['event_id' => 14, 'p' => 'info']);

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.ias/css/jquery.ias.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.ias/js/jquery-ias.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.countdown.js'), 'src');
        parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
        parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
        parent::$html->jbox();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function show_album_preview()
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
                    $where = array(
                        array(array(array('album_id', '=', $album_id)), 'and'),
                    );
                    $tmp0 = Model('photo')->where($where)->fetchAll();
                }
            }

            $a_readable = array();
            if ($favorited) {
                foreach ((array)$tmp0 as $v0) {
                    if (!is_image(PATH_UPLOAD . $v0['image'])) continue;

                    list($width, $height) = getimagesize(PATH_UPLOAD . $v0['image']);
                    $a_readable[] = array(
                        'image' => URL_UPLOAD . $v0['image'],
                        'width' => $width,
                        'height' => $height,
                        'usefor' => $v0['usefor'],
                        'video_refer' => $v0['video_refer'],
                        'video_target' => ($v0['video_refer'] == 'file') ? URL_UPLOAD . $v0['video_target'] : $v0['video_target'],
                    );
                }
            } else {
                foreach ((array)$tmp0 as $v0) {
                    if (!is_image(PATH_UPLOAD . $v0)) continue;

                    list($width, $height) = getimagesize(PATH_UPLOAD . $v0);
                    $a_readable[] = array(
                        'image' => URL_UPLOAD . $v0,
                        'width' => $width,
                        'height' => $height,
                        'usefor' => null,
                        'video_refer' => null,
                        'video_target' => null,
                    );
                }
            }
            if (empty($a_readable)) $a_readable[] = ['image' => static_file('images/origin.jpg'), 'width' => 683, 'height' => 1024];

            json_encode_return(1, null, null, ['favorited' => $favorited, 'readable' => $a_readable]);
        }
        die;
    }

    function special()
    {
        $event_id = !empty($_GET['event_id']) ? $_GET['event_id'] : redirect(parent::url('event', 'index'), _('Event does not exist.'));
        $m_event = (new eventModel())->where([[[['event_id', '=', $event_id], ['act', '=', 'open']], 'and']])->fetch();
        $time = date('Y-m-d H:i:s', time());

        if (empty($m_event) || !$m_event['exchange_page']) redirect(parent::url('event', 'index'), _('活動獎品兌換頁不存在.'));

        //取得活動資訊
        $m_special = (new specialModel())->where([[[['event_id', '=', $event_id]], 'and']])->fetchAll();

        /**
         * 防呆 , 若活動期限到期或未到舉辦期間 皆視為expire => 將贊助活動調整 act = close
         */
        $expire = ($m_event['endtime'] < $time || $m_event['starttime'] > $time) ? true : false;
        if ($expire && $m_special[0]['act'] == 'open') {
            if (!Model('special')->where([[[['event_id', '=', $event_id]], 'and']])->edit(['act' => 'close'])) redirect(parent::url('event', 'content', ['event_id' => $event_id]), _('[Special] occurs exception, please contact us.'));
        }

        if (empty($m_special)) redirect(parent::url('event', 'content', ['event_id' => $event_id]), _('[Special] occurs exception, please contact us.'));
        $a_special = array();
        foreach ($m_special as $k0 => $v0) {
            $a_special = [
                'special_id' => $v0['special_id'],
                'event_id' => $v0['event_id'],
                'name' => $v0['name'],
                'description' => $v0['description'],
                'info_required' => $v0['info_required'],
                'act' => $v0['act'],
                'remark' => $v0['remark'],
                'event_image' => URL_UPLOAD . $m_event['image'],
            ];
        };

        //驗證是否已兌換
        $user = parent::user_get();
        if (!empty($user)) {
            $m_special_exchange = Model('special_exchange')->where([[[['user_id', '=', $user['user_id']], ['event_id', '=', $event_id], ['special_id', '=', $a_special['special_id']], ['state', '!=', 'none']], 'and']])->fetchAll();
        }
        //取得活動獎項資訊
        $m_special_award = Model('special_award')->where([[[['special_id', '=', $a_special['special_id']], ['act', '=', 'open']], 'and']])->fetchAll();
        $a_special_award = array();
        foreach ($m_special_award as $k0 => $v0) {
            //1.已無可兌換獎品  2.登入者已經完成此活動  3.活動過期
            $status = ($v0['current'] < 1 || !empty($m_special_exchange) || $expire) ? 'disabled' : null;
            $a_special_award[] = [
                'special_award_id' => $v0['special_award_id'],
                'special_id' => $v0['special_id'],
                'name' => $v0['name'],
                'current' => $v0['current'],
                'status' => $status,
                'unit' => $v0['unit'],
                'description' => $v0['description'],
                'image' => URL_UPLOAD . $v0['image'],
                'key' => encrypt(['event_id' => $event_id, 'special_id' => $a_special['special_id'], 'award_id' => $v0['special_award_id']], SITE_SECRET),
            ];
        };
        parent::$data['exchanged_award_id'] = (!empty($m_special_exchange)) ? $m_special_exchange[0]['special_award_id'] : null;

        //提交按鈕樣式
        $btn_text = _('Submit');
        $btn_id = 'id="exchge_submit"';
        $btn_onclick = null;
        if ($expire || $a_special['act'] == 'close') {
            //活動過期
            $btn_text = _('This activity has ended!');
            $btn_id = null;
        } elseif (empty($user)) {
            //未登入
            $btn_text = _('I want to join');
            $btn_id = null;
            $btn_onclick = 'onclick="var r = {result: 0, message: \'' . _('Please login first.') . '\', redirect: \'' . parent::url('user', 'login', array('redirect' => parent::url('event', 'join', array('event_id' => $event_id)))) . '\'};site_jBox(r);"';
        } elseif (!empty($m_special_exchange)) {
            //兌換過
            $btn_text = _('您已經兌換過此活動，謝謝。');
            $btn_id = null;
        }
        $button = '<a ' . $btn_onclick . ' href="javascript:void(0)" ' . $btn_id . ' class="member_enter">' . $btn_text . '</a>';

        if (!$a_special['info_required']) $button = null;

        $back_btn = '<a href="' . parent::url('event', 'content', ['event_id' => $event_id]) . '" class="member_enter">' . _('回活動頁') . '</a>';
        //seo
        $this->seo(
            empty($a_special['name']) ? null : $a_special['name'] . ' | ' . Core::settings('SITE_TITLE'),
            empty($a_special['name']) ? null : array($a_special['name']),
            _('我正在參加pinpinbox的') . '[' . $a_special['name'] . ']，' . _('快來幫我投票吧!!'),
            URL_UPLOAD . $m_event['image']
        );

        parent::$data['button'] = $button;
        parent::$data['back_btn'] = $back_btn;
        parent::$data['special'] = $a_special;
        parent::$data['special_award'] = $a_special_award;

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_css(static_file('css/bootstrap-social.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');

        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/zeroclipboard/js/ZeroClipboard.min.js'), 'src');
        parent::$html->jbox();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function special_exchange()
    {
        if (is_ajax()) {
            $name = (empty($_POST['name'])) ? null : $_POST['name'];
            $phone = (empty($_POST['phone'])) ? null : $_POST['phone'];
            $address = (empty($_POST['address'])) ? null : $_POST['address'];
            $email = (empty($_POST['email'])) ? null : $_POST['email'];
            $award_id = (empty($_POST['award_id'])) ? null : $_POST['award_id'];
            $key = (empty($_POST['key'])) ? null : $_POST['key'];
            $event_id = (empty($_POST['event_id'])) ? null : $_POST['event_id'];
            $special_id = (empty($_POST['special_id'])) ? null : $_POST['special_id'];
            $user = parent::user_get();
            if (empty($user) || $name == null || $phone == null || $address == null || $email == null || $award_id == null || $event_id == null || $special_id == null || $key == null) json_encode_return(0, _('資訊不完整，請重新填寫。'));
            $info = array();
            //驗證Key
            if (encrypt(['event_id' => $event_id, 'special_id' => $special_id, 'award_id' => $award_id]) != $key) json_encode_return(0, _('[Special exchange] occur exception, please contact us.'), parent::url('event', 'content', ['event_id' => $event_id]));

            /**
             * 驗證是否完成任務
             * 0105 若該活動沒有"限定模板"，則不加入 ['event_templatejoin.event_id', '=', $event_id] 條件=> 意即只要投稿則算完成任務
             */

            //先取得是否限定模板
            $m_event_templatejoin = Model('event_templatejoin')->join([['left join', 'event', 'using(event_id)']])->where([[[['event_templatejoin.event_id', '=', $event_id], ['event.act', '=', 'open']], 'and']])->fetchAll();
            $where = [['album.act', '=', 'open'], ['album.state', '=', 'success'], ['user_id', '=', $user['user_id']]];
            //有限定模板則加入條件
            if (!empty($m_event_templatejoin)) $where[] = ['event_templatejoin.event_id', '=', $event_id];
            $m_album_id = Model('album')->column(['album_id'])->join([['left join', 'event_templatejoin', 'using(template_id)']])->where([[$where, 'and']])->fetchAll();
            $a_album_id = array();
            if (!empty($m_album_id)) {
                foreach ($m_album_id as $k => $v0) {
                    $a_album_id[] = $v0['album_id'];
                }
                $m_eventjoin = Model('eventjoin')->where([[[['event_id', '=', $event_id], ['album_id', 'in', $a_album_id]], 'and']])->fetchAll();
            }
            if (empty($m_album_id) || empty($m_eventjoin)) json_encode_return(0, _('任務還沒有完成哦，快去參加任務吧!!'), parent::url('event', 'join', ['event_id' => $event_id]));
            $info['album_id'] = $a_album_id;

            /**
             * 驗證活動狀態
             */
            $m_special = Model('special')->where([[[['event_id', '=', $event_id], ['special_id', '=', $special_id], ['act', '=', 'open']], 'and']])->fetchAll();
            if (empty($m_special)) json_encode_return(0, _('活動已截止，謝謝。'), parent::url('event', 'special', ['event_id' => $event_id]));

            /**
             * 獎品
             */
            $m_special_award = Model('special_award')->where([[[['special_id', '=', $special_id], ['special_award_id', '=', $award_id], ['current', '>', 0]], 'and']])->fetch();
            if (empty($m_special_award)) json_encode_return(0, _('此獎品已兌換完畢，請選擇其他獎品。'), parent::url('event', 'special', ['event_id' => $event_id]));
            if ($m_special_award['act'] != 'open') json_encode_return(0, _('此獎品停止兌換完畢，請選擇其他獎品。'), parent::url('event', 'special', ['event_id' => $event_id]));

            /**
             * 驗證是否兌換
             */
            $m_special_exchange = Model('special_exchange')->where([[[['user_id', '=', $user['user_id']], ['event_id', '=', $event_id], ['special_id', '=', $special_id], ['state', '!=', 'none']], 'and']])->fetchAll();
            if (!empty($m_special_exchange)) json_encode_return(0, _('您已經兌換過此活動，謝謝。'), parent::url('event', 'special', ['event_id' => $event_id]));

            Model('special_award');
            Model('special_exchange');
            Model()->beginTransaction();

            /**
             *  獎品數量減少一
             */
            $after_exchange = $m_special_award['current'] - 1;
            if (!Model('special_award')->where([[[['special_id', '=', $special_id], ['special_award_id', '=', $award_id], ['current', '>', 0]], 'and']])->edit(['current' => $after_exchange])) {
                Model()->rollBack();
                json_encode_return(0, _('[Order] occurs exception, please contact us.'));
            }

            /**
             * 填寫兌換資訊
             */
            $tmp0 = [
                'user_id' => $user['user_id'],
                'event_id' => $event_id,
                'special_id' => $special_id,
                'special_award_id' => $award_id,
                'award_exchange_before' => $m_special_award['current'],
                'receipt' => json_encode(['name' => $name, 'phone' => $phone, 'address' => $address, 'email' => $email]),
                'state' => 'process',
                'inserttime' => inserttime(),
            ];

            $result = Model('special_exchange')->add($tmp0);
            $info['event_id'] = $event_id;
            $info['award_id'] = $award_id;
            $info['special_id'] = $special_id;
            $info['user'] = $user;

            if ($result) {
                Model()->commit();
                $fb_url_album_id = (is_array($a_album_id)) ? $a_album_id[0] : $a_album_id;
                json_encode_return(1, $this->special_exchange_message($info), parent::url('event', 'content', ['event_id' => $event_id, 'exchange' => 'true']), $fb_url_album_id);
            } else {
                Model()->rollBack();
                json_encode_return(0, _('[Order] occurs exception, please contact us.'));
            }
        }
    }

    function special_exchange_message($info)
    {
        $m_event = Model('event')->where([[[['event_id', '=', $info['event_id']], ['act', '=', 'open']], 'and']])->fetch();
        /**
         * 0107 改由取得 special_award.exchange_message 的訊息內容回傳
         * (棄用)因不同獎項型態回傳訊息  entity: 實體獎品   virtual:虛擬獎品(P點、會員資格體驗)   exchange: Email/SMS 方式發送讓user自行做實體化處裡(兌換券、憑訊息兌換、廠商或商家活動)
         */
        $return = '<p style="text-align: center;" class="red">';
        $m_special_award = Model('special_award')->where([[[['special_id', '=', $info['special_id']], ['special_award_id', '=', $info['award_id']]], 'and']])->fetch();
        $return .= (!empty($m_special_award['exchange_message'])) ? $m_special_award['exchange_message'] : _('獎品已兌換完成，感謝您參加活動');
        $return .= '</p>';

        $m_eventjoin_album_id = (new eventjoinModel())->column(['album_id'])->where([[[['eventjoin.event_id', '=', $info['event_id']], ['album.user_id', '=', $info['user']['user_id']]], 'and']])->join([['left join', 'album', 'USING(album_id)']])->fetchAll();
        $a_eventjoin_album_id = array_column($m_eventjoin_album_id, 'album_id');

        $text = _('我正在參加pinpinbox的') . '[' . $m_event['name'] . ']活動,' . _('我的參賽作品編號') . ':#' . implode(',', $a_eventjoin_album_id) . ',' . _('快來幫我投票吧!!');
        $return .= '<input style="display:none;" type="text" id="fb-share-text" value="' . $text . '">';
        $return .= '<br><p  class="keypoint">' . _('完成以下步驟讓您的朋友幫您投票拿大獎!') . '</p>';
        $return .= '<p class="red">STEP 1</p>';
        $return .= '<p>' . _('複製以下文字:') . '<a style="font-size:12px" href="javascript:void(0)" data-clipboard-target="fb-share-text" id="copy">(' . _('按我複製') . ')</a></p>';
        $return .= '<p>"<span class="keypoint" style="background-color:#fefbd9;">' . $text . '</span>"</p><br>';
        $return .= '<p class="red">SETP 2</p>';
        $return .= '<p>' . _('將上述文字分享至FB') . '</p>';
        $return .= '<p style="width:25%"><a style="border-radius: 2px;cursor:pointer;" id="fb_share" class="btn-block btn-social btn-facebook"> <span class="fa fa-facebook"></span> Share to Facebook&nbsp;&nbsp;&nbsp;</a></p>';
        return $return;
    }

    function tutorial_img()
    {
        if (is_ajax()) {
            $event_id = (empty($_POST['event_id'])) ? null : $_POST['event_id'];
            $a_img = [];

            /**
             * 0106 暫時使用此邏輯載入教學圖片，規劃改成從後台活動上傳 -> 制式化路徑載入
             * 0107 因活動教學圖片內容相近，故不個別處理，皆以此套教學步驟為主，將檔案置放於static_file下
             */
            $dir = PATH_STATIC_FILE . M_PACKAGE . DIRECTORY_SEPARATOR . 'zh_TW' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'tutorial' . DIRECTORY_SEPARATOR . 'event';
            $filenum = count(glob("$dir/*.jpg*"));

            for ($i = 1; $i <= $filenum; $i++) {
                $a_img[] = static_file('images/tutorial/event/step' . $i . '.jpg');
            }
            json_encode_return(1, null, null, $a_img);
        }
    }

    function template_img()
    {
        if (is_ajax()) {
            $event_id = (empty($_POST['event_id'])) ? null : $_POST['event_id'];
            $template_id = (empty($_POST['template_id'])) ? null : $_POST['template_id'];
            $a_img = [];

            $m_template = Model('template')->column(['frame_upload'])->where([[[['template_id', '=', $template_id]], 'and']])->fetch();
            $a_template = json_decode($m_template['frame_upload'], true);
            foreach ($a_template as $k0 => $v0) {
                $a_img[] = URL_UPLOAD . M_PACKAGE . $v0['src'];
            }
            json_encode_return(1, null, null, $a_img);
        }
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
                $result = 3;
                $message = _('Please login first.');
                $redirect = parent::url('user', 'login', array('redirect' => parent::url('event', 'content', ['event_id' => $event_id, 'album_id' => $album_id, 'key' => encrypt(['album_id' => $album_id, 'event_id' => $event_id])])));
                $data = 'Modal';
                goto _return;
            }

            switch ($act) {
                /**
                 * 160902 - 移除"取消投票"的功能,故不會再進入[cancelvote]邏輯, 先暫時保留 -Mars
                 */
                case 'cancelvote'://取消投票
                    //取得user投票此相簿的時間
                    $where = [];
                    $where = array(array('event_id', '=', $event_id), array('user_id', '=', $user['user_id']), array('album_id', '=', $album_id));
                    $m_eventvote = Model('eventvote')->where(array(array($where, 'and')))->fetch();

                    //投票超過十分鐘後才可取消
                    if (strtotime('+10 minute', strtotime($m_eventvote['inserttime'])) >= time()) {
                        json_encode_return(0, _('This operation cannot redo within 10 minutes.'), null, 'Modal');
                    } else {
                        Model('eventjoin');
                        Model('eventvote');
                        Model()->beginTransaction();

                        Model('eventvote')->where(array(array(array(array('event_id', '=', $event_id), array('user_id', '=', $user['user_id']), array('album_id', '=', $album_id)), 'and')))->delete();

                        $m_eventvote = Model('eventvote')->column(array('count(1)'))->where(array(array(array(array('event_id', '=', $event_id), array('album_id', '=', $album_id)), 'and')))->fetchColumn();

                        Model('eventjoin')->where(array(array(array(array('event_id', '=', $event_id), array('album_id', '=', $album_id)), 'and')))->edit(array('count' => $m_eventvote));

                        Model()->commit();

                        json_encode_return(1, _('Vote has been canceled.'), null, $album_id);
                    }
                    break;

                case 'addvote'://進行投票
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

                    (new eventjoinModel())->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->edit(['count' => $m_eventvote]);

                    Model()->commit();

                    $result = 2;
                    $message = _('Vote success.');
                    $redirect = empty(query_string_parse()['event_id']) ? null : parent::url('event', 'content', ['event_id' => query_string_parse()['event_id']]);
                    $data = $album_id;
                    goto _return;

                    break;

                default:
                    $result = 0;
                    $message = _('Abnormal process, please try again.');
                    goto _return;

                    break;
            }

            _return:
            json_encode_return($result, $message, $redirect, $data);
        }
        die;
    }

}