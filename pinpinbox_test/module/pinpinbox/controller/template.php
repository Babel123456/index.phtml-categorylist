<?php

class templateController extends frontstageController
{
    function __construct()
    {
    }

    function _taketemplate()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $template_id = isset($_POST['template_id']) ? $_POST['template_id'] : null;
            $work = isset($_POST['work']) ? $_POST['work'] : null;
            $join_event = isset($_POST['join_event']) ? $_POST['join_event'] : null;
            $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : null;
            $collect_to_use = isset($_POST['collect_to_use']) ? $_POST['collect_to_use'] : null;
            if (empty($template_id)) json_encode_return(0, _('Abnormal process, please try again.'));
            if (empty($user)) json_encode_return(2, null, parent::url('user', 'login', ['redirect' => parent::url('template', 'content', ['template_id' => $template_id])]));

            // fetch template info
            $m_template = Model('template')->where([[[['template_id', '=', $template_id], ['state', '=', 'success'], ['act', '=', 'open']], 'and']])->fetch();
            if (empty($m_template)) json_encode_return(0, _('Template does not exist.'));

            // fetch templatequeue
            $m_templatequeue = Model('templatequeue')->column(['count(1)'])->where([[[['user_id', '=', $user['user_id']], ['template_id', '=', $template_id]], 'and']])->fetchColumn();
            $a_param = array();

            // 已擁有該版型 || 為版型作者時 => 不進行收藏即可使用版型
            if (!empty($m_templatequeue) || $m_template['user_id'] == $user['user_id']) {
                if ($join_event != null) $a_param['join_event'] = $join_event;

                list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->process2($user['user_id']));
                $a_param['album_id'] = $album_id;
                if ($result) json_encode_return(4, null, parent::url('diy', 'index', $a_param), $album_id);

                list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->pretreat($user['user_id'], $template_id));
                $a_param['album_id'] = $album_id;
                $r_data = [];
                $user_id = $user['user_id'];
                $task_for = 'firsttime_download_template';
                $r_data = model('task')->doTask($task_for, $user_id, 'web', ['type' => 'template', 'type_id' => $template_id]);
                json_encode_return(5, null, parent::url('diy', 'index', $a_param), $r_data);
            }

            $a_param['template_id'] = $template_id;
            $a_param['point'] = $m_template['point'];
            $a_param['join_event'] = $join_event;

            if ($work) {
                if ($m_template['point'] == 0) {
                    $confirm = true;
                } elseif ($work == 'collect_to_use') {
                    json_encode_return(7, null, null, $a_param);
                } else {
                    json_encode_return(3, null, null, $a_param);
                }
            }

            if ($confirm) {
                //進行交易(收藏版型)
                (new Model)->beginTransaction();

                list ($result, $message, $redirect, $data) = array_decode_return(Core::exchange($user['user_id'], 'web', 'template', $template_id));
                if (!$result) {
                    (new Model)->rollBack();

                    json_encode_return(0, $message);
                }

                (new Model)->commit();

                $a_param['template_id'] = $template_id;
                if ($join_event != null) $a_param['join_event'] = $join_event;

                //收藏完成並使用
                if ($work == 'collect_to_use' || $collect_to_use) {
                    json_encode_return(6, _('Purchase success!'), null, $a_param);
                } else {
                    /**
                     *  0704 - 執行任務-收藏版型
                     */
                    $r_data = [];
                    $user_id = $user['user_id'];
                    $task_for = 'firsttime_download_template';
                    $r_data = model('task')->doTask($task_for, $user_id, 'web', ['type' => 'template', 'type_id' => $template_id]);

                    json_encode_return(1, _('Purchase success!'), parent::url('template', 'content', $a_param), $r_data);
                }
            }

            json_encode_return(3, null, null, $a_param);
        }
        die;
    }

    function _taketemplate_v2()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            if (empty($user)) json_encode_return(2, null, parent::url('user', 'login', ['redirect' => parent::url('user', 'redirect_to_diy')]));

            $template_id = isset($_POST['template_id']) ? $_POST['template_id'] : null;
            list($result, $message, $redirect, $album_id) = array_decode_return((new \albumModel)->process2($user['user_id']));
            $a_param['album_id'] = $album_id;
            if ($result) json_encode_return(1, null, parent::url('diy', 'index', $a_param), $album_id);

            list($result, $message, $redirect, $album_id) = array_decode_return((new \albumModel)->pretreat($user['user_id'], $template_id));
            $a_param['album_id'] = $album_id;
            json_encode_return(3, null, parent::url('diy', 'index', $a_param));

        }
        die;
    }

    function template_tabs_rank()
    {
    }

    function template_pc_nav()
    {
    }

    function template_mobile_nav()
    {
    }

    function content()
    {

        //20170308-暫時關閉模板相關頁面 - Mars
        redirect_php(parent::url());

        $template_id = empty($_GET['template_id']) ? redirect(parent::url('template', 'index'), _('Abnormal process, please try again.')) : $_GET['template_id'];

        $user = parent::user_get();

        /**
         * 0:熱門  1:免費  2:贊助  3:最新  4:已購買
         */
        $rank = (!empty($_GET['rank']) && in_array($_GET['rank'], array(0, 1, 2, 3, 4))) ? $_GET['rank'] : 0;
        parent::$data['rank'] = $rank;

        $m_style = Model('style')->column(['name', 'style_id'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
        $a_style = array();
        foreach ($m_style as $k => $v) {
            $a_style[] = [
                'url' => parent::url('template', 'index', ['style_id' => $v['style_id'], 'rank' => $rank]),
                'name' => $v['name'],
            ];
        }
        parent::$data['a_style'] = $a_style;

        $rank0 = self::url('template', 'index', ['rank' => 0]);
        $rank1 = self::url('template', 'index', ['rank' => 1]);
        $rank2 = self::url('template', 'index', ['rank' => 2]);
        $rank3 = self::url('template', 'index', ['rank' => 3]);
        $rank4 = self::url('template', 'index', ['rank' => 4]);

        parent::$data['rank0'] = $rank0;
        parent::$data['rank1'] = $rank1;
        parent::$data['rank2'] = $rank2;
        parent::$data['rank3'] = $rank3;
        parent::$data['rank4'] = $rank4;

        //template
        $where = array();
        $where[] = array(array(array('template.template_id', '=', $template_id), array('template.act', '=', 'open'), array('template.state', '=', 'success')), 'and');
        $join = array();
        $join[] = array('left join', 'templatestatistics', 'using(template_id)');
        $m_template = Model('template')->join($join)->column(array('template.*', 'templatestatistics.count', 'templatestatistics.viewed'))->where($where)->fetch();
        if (empty($m_template)) redirect_php(parent::url('template', 'index'));

        //style name
        $style_name = Model('style')->column(['name'])->where([[[['style_id', '=', $m_template['style_id']], ['act', '=', 'open']], 'and']])->fetchColumn();

        $a_template = array();
        $a_template['template_id'] = $m_template['template_id'];
        $a_template['name'] = $m_template['name'];
        $a_template['description'] = $m_template['description'];
        $a_template['point'] = $m_template['point'];
        $a_template['width'] = $m_template['width'];
        $a_template['height'] = $m_template['height'];
        $a_template['point_str'] = (empty($m_template['point']) || $m_template['point'] == 0) ? 'Free' : $m_template['point'] . 'P';
        $a_template['point'] = $m_template['point'];
        $a_template['image'] = URL_UPLOAD . image_reformat(M_PACKAGE . $m_template['image'], 'jpg', 466, 699);
        $a_template['image_promote'] = $m_template['image_promote'];
        $a_template['download'] = $m_template['count'];
        $a_template['viewed'] = $m_template['viewed'];
        $a_template['style_name'] = (!empty($style_name)) ? $style_name : _('所有版型');
        $tmp0 = array();
        $n = array();
        foreach (json_decode($m_template['frame_upload'], true) as $k => $v) {
            $tmp0[$k]['normal'] = URL_UPLOAD . getimageresize(M_PACKAGE . $v['src'], 466, 699);
            $tmp0[$k]['gallery'] = URL_UPLOAD . M_PACKAGE . $v['src'];
            $n[] = $v['resource'];

            if (!in_array($v['resource'], $n)) {
                $n[] = $v['resource'];
            }
        }
        $tmp1 = array();
        if (!empty($a_template['image_promote'])) {
            foreach (json_decode($a_template['image_promote'], true) as $k0 => $v0) {
                $tmp1[$k0]['normal'] = URL_UPLOAD . image_reformat(M_PACKAGE . $v0, 'jpeg', 466, 699);
                $tmp1[$k0]['gallery'] = URL_UPLOAD . image_reformat(M_PACKAGE . $v0, 'jpeg', 1336, 2004);
            }
        }

        $a_template['preview'] = array_merge($tmp1, $tmp0);
        $a_template['count'] = count($n);

        $a_template['record'] = null;
        //templatequeue、template
        if (!empty($user)) {
            //檢查是否已經有購買
            $where = array();
            $where[] = array(array(array('user_id', '=', $user['user_id']), array('template_id', '=', $a_template['template_id'])), 'and');
            $m_templatequeue = Model('templatequeue')->where($where)->fetch();

            //檢查是否為創作者
            $where = array();
            $where = array(array('user_id', '=', $user['user_id']), array('template_id', '=', $template_id));
            $m_tmp_template = Model('template')->where(array(array($where, 'and')))->fetch();
        }
        $a_template['record'] = (!empty($m_templatequeue) || !empty($m_tmp_template)) ? '<span class="red">' . _('Achieved') . '</span>' : false;

        //button show
        $button = '<li>';
        if ((!empty($m_templatequeue) || !empty($m_tmp_template))) {
            //已收藏或自己的模板
            (SDK('Mobile_Detect')->isMobile())
                ? $button = '<a href="#" class="used big" data-uri="' . parent::deeplink('template', 'content', ['template_id' => $template_id]) . '" onclick="clickHandler(this.dataset.uri)">' . _('使用這個版型') . '</a>'
                : $button = '<a href="javascript:void(0);" onclick="taketemplate(\'collect\')" class="used big">' . _('使用這個版型') . '</a>';

        } else {
            //行動裝置的 "建立" 動作需由APP完成,故調用呼叫APP
            (SDK('Mobile_Detect')->isMobile())
                ? $button .= '<a href="#" data-uri="' . parent::deeplink('template', 'content', ['template_id' => $template_id]) . '" onclick="clickHandler(this.dataset.uri)" class="used float">' . _('收藏並建立作品') . '</a>'
                : $button .= '<a href="javascript:void(0)" onclick="taketemplate(\'collect_to_use\')" class="used float">' . _('收藏並建立作品') . '</a>';

            (SDK('Mobile_Detect')->isMobile())
                ? $button .= '<a href="#" data-uri="' . parent::deeplink('template', 'content', ['template_id' => $template_id]) . '" onclick="clickHandler(this.dataset.uri)" class="used float">' . _('收藏') . '</a>'
                : $button .= '<a href="javascript:void(0)" onclick="taketemplate(\'collect\')" class="used float">' . _('收藏') . '</a>';

        }
        $button .= '</li>';
        parent::$data['button'] = $button;

        //creative_template
        $m_user = Model('user')->where(array(array(array(array('act', '=', 'open'), array('user_id', '=', $m_template['user_id'])), 'and')))->fetch();
        $tmp0 = array();
        if (!empty($m_user)) {
            $tmp0['name'] = $m_user['name'];
            $tmp0['user_id'] = $m_user['user_id'];
            $a_template['user'] = $tmp0;
        } else {
            $tmp0['name'] = (!empty($a_template['user_id']) && $a_template['user_id'] == 0) ? 'unknow' : 'pinpinbox';
            $tmp0['user_id'] = 0;
            $a_template['user'] = $tmp0;
        }

        //其他作品
        if ($a_template['user']['user_id'] != 0) {
            $where = array();
            $where[] = array(array(array('template.user_id', '=', $a_template['user']['user_id']), array('template.act', '=', 'open'), array('template.state', '=', 'success'), array('template.template_id', '!=', $template_id)), 'and');
            $m_creative_template = Model('template')->where($where)->limit('0,6')->fetchAll();
        }

        //活動配合
        $m_event_templatejoin = Model('event_templatejoin')->join([['left join', 'event', 'using(event_id)']])->column(['event_templatejoin.template_id', 'event.name', 'event.event_id'])->where([[[['event_templatejoin.template_id', '=', $a_template['template_id']], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])->fetch();
        $a_template['event_templatejoin'] = !empty($m_event_templatejoin) ? '<div class="act_info_name mobilehide"><a href="' . parent::url('event', 'content', ['event_id' => $m_event_templatejoin['event_id']]) . '">' . $m_event_templatejoin['name'] . '</a></div>' : null;

        parent::$data['template'] = $a_template;

        $a_creative_template = array();
        if (!empty($m_creative_template)) {
            foreach ($m_creative_template as $k => $v) {
                $tmp = array();
                $tmp['template_id'] = $v['template_id'];
                $tmp['user_id'] = $v['user_id'];
                $tmp['image'] = URL_UPLOAD . M_PACKAGE . '/' . $v['image'];

                $a_creative_template[] = $tmp;
            }
        }
        parent::$data['creative_template'] = (!empty($a_creative_template)) ? $a_creative_template : null;

        //templatestatistics
        $tmp0 = md5(parent::url('template', 'content', array('template_id' => $template_id)));
        if (!isset($_COOKIE[$tmp0])) {
            setcookie($tmp0, true, time() + 86400);
            $viewed = Model('templatestatistics')->column(array('viewed'))->where(array(array(array(array('template_id', '=', $template_id)), 'and')))->fetchColumn();
            Model('templatestatistics')->where(array(array(array(array('template_id', '=', $template_id)), 'and')))->edit(array('viewed' => $viewed + 1));
        }

        //Quick Create
        $quick = '<a href="' . parent::url('template', 'upload') . '"  class="start_temp">' . _('Quick Create') . '</a>';
        if (isset($_GET['quick']) && $_GET['quick'] == 'false') {
            $quick = null;
        }
        /**
         *  0111 因活動關係，判斷可能會造成使用者誤按"快速建立"造成體驗不佳，故在此版本不做任何判斷便不顯示快速建立按鈕 - Mars
         */
        $quick = null;
        parent::$data['quick'] = $quick;

        //seo
        $this->seo(
            $m_template['name'] . ' | ' . Core::settings('SITE_TITLE'),
            array($m_template['name']),
            $m_template['description'],
            URL_UPLOAD . M_PACKAGE . $m_template['image']
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

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.show-more.js'), 'src');
        parent::$html->set_js(static_file('js/Xslider.js'), 'src');

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }

    function index()
    {
        //20170308-暫時關閉模板相關頁面 - Mars
        redirect_php(parent::url());

        $user = parent::user_get();
        $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
        $num_of_per_page = 10;//一頁幾個
        $a_template = array();
        $tmp = array();

        /**
         * 搜尋
         */
        $searchtype = isset($_GET['searchtype']) ? urldecode($_GET['searchtype']) : null;
        parent::$data['searchtype'] = $searchtype;
        $searchkey = (isset($_GET['searchkey']) && $_GET['searchkey'] !== '') ? urldecode($_GET['searchkey']) : null;
        parent::$data['searchkey'] = htmlspecialchars($searchkey);

        /**
         * 0:最新  1:熱門  2:免費  3:贊助  4:已購買
         */
        $rank_id = (!empty($_GET['rank_id']) && in_array($_GET['rank_id'], [0, 1, 2, 3, 4])) ? $_GET['rank_id'] : 0;
        parent::$data['rank_id'] = $rank_id;
        $rank_name = [_('Latest'), _('Hot'), _('Free'), _('Paid'), _('Purchased')];
        parent::$data['rank_name'] = $rank_name;

        /**
         * 類別
         */
        $style_id = (!empty($_GET['style_id'])) ? $_GET['style_id'] : null;

        parent::$data['style_id'] = $style_id;

        if ($style_id != null) {
            $tmp['style_id'] = $style_id;
        }
        if ($rank_id !== null) $tmp['rank_id'] = $rank_id;
        if ($searchkey !== null) {
            $tmp['searchtype'] = $searchtype;
            $tmp['searchkey'] = $searchkey;
        }

        $style_name = ($style_id != null) ? Model('style')->column(['name'])->where([[[['style_id', '=', $style_id], ['act', '=', 'open']], 'and']])->fetchColumn() : _('風格');
        parent::$data['style_name'] = $style_name;

        $m_style = Model('style')->column(['name', 'style_id'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
        $a_style = array();
        foreach ($m_style as $k => $v) {
            $a_style[] = [
                'url' => parent::url('template', 'index', ['style_id' => $v['style_id'], 'rank_id' => $rank_id]),
                'name' => $v['name'],
            ];
        }
        parent::$data['a_style'] = $a_style;

        $rank0 = self::url('template', 'index', array_merge($tmp, ['rank_id' => 0]));
        $rank1 = self::url('template', 'index', array_merge($tmp, ['rank_id' => 1]));
        $rank2 = self::url('template', 'index', array_merge($tmp, ['rank_id' => 2]));
        $rank3 = self::url('template', 'index', array_merge($tmp, ['rank_id' => 3]));
        $rank4 = self::url('template', 'index', array_merge($tmp, ['rank_id' => 4]));

        parent::$data['rank0'] = $rank0;
        parent::$data['rank1'] = $rank1;
        parent::$data['rank2'] = $rank2;
        parent::$data['rank3'] = $rank3;
        parent::$data['rank4'] = $rank4;
        if ($searchkey !== null) {
            $s_template = Solr('template')->column(['template_id'])->where([[[['_text_', '=', $searchkey]], 'and']])->fetchAll();
            if (empty($s_template)) {
                $m_template = [];
                $c_template = 0;
                goto _relay0;
            }
        }
        switch ($rank_id) {
            case 0 :
                $join = array();
                $join[] = array('left join', 'templatestatistics', 'using(template_id)');
                $where = array();
                $where[] = [[['template.act', '=', 'open'], ['template.state', '=', 'success']], 'and'];
                if (!empty($s_template)) $where[0][0][] = ['template.template_id', 'in', array_column($s_template, 'template_id')];
                if ($style_id != null) $where[0][0][] = ['template.style_id', '=', $style_id];
                $m_template = Model('template')->column(array('template.*', 'template.user_id AS creative', 'templatestatistics.count', 'templatestatistics.viewed'))->join($join)->where($where)->order(array('template.inserttime' => 'desc'))->limit($num_of_per_page * ($page - 1) . ',' . $num_of_per_page)->fetchAll();
                $c_template = Model('template')->column(array('count(1)'))->join($join)->where($where)->fetchColumn();

                break;

            case 1 :
                $join = array();
                $join[] = array('left join', 'template', 'using(template_id)');
                $where = array();
                $where[] = [[['template.act', '=', 'open'], ['template.state', '=', 'success']], 'and'];
                if (!empty($s_template)) $where[0][0][] = ['template.template_id', 'in', array_column($s_template, 'template_id')];
                if ($style_id != null) $where[0][0][] = ['template.style_id', '=', $style_id];
                $m_template = Model('templatestatistics')->column(array('template.*', 'template.user_id AS creative', 'templatestatistics.count', 'templatestatistics.viewed'))->join($join)->where($where)->order(array('templatestatistics.count' => 'desc'))->limit($num_of_per_page * ($page - 1) . ',' . $num_of_per_page)->fetchAll();
                $c_template = Model('templatestatistics')->column(array('count(1)'))->join($join)->where($where)->fetchColumn();
                break;

            case 2 :
                $join = array();
                $join[] = array('left join', 'template', 'using(template_id)');
                $where = array();
                $where[] = [[['template.act', '=', 'open'], ['template.state', '=', 'success'], ['template.point', '=', 0]], 'and'];
                if (!empty($s_template)) $where[0][0][] = ['template.template_id', 'in', array_column($s_template, 'template_id')];
                if ($style_id != null) $where[0][0][] = ['template.style_id', '=', $style_id];
                $m_template = Model('templatestatistics')->column(array('template.*', 'template.user_id AS creative', 'templatestatistics.count', 'templatestatistics.viewed'))->join($join)->where($where)->order(array('templatestatistics.count' => 'desc'))->limit($num_of_per_page * ($page - 1) . ',' . $num_of_per_page)->fetchAll();
                $c_template = Model('templatestatistics')->column(array('count(1)'))->join($join)->where($where)->fetchColumn();
                break;

            case 3 :
                $join = array();
                $join[] = array('left join', 'template', 'using(template_id)');
                $where = array();
                $where[] = [[['template.act', '=', 'open'], ['template.state', '=', 'success'], ['template.point', '>', 1]], 'and'];
                if (!empty($s_template)) $where[0][0][] = ['template.template_id', 'in', array_column($s_template, 'template_id')];
                if ($style_id != null) $where[0][0][] = ['template.style_id', '=', $style_id];
                $m_template = Model('templatestatistics')->column(array('template.*', 'template.user_id AS creative', 'templatestatistics.count', 'templatestatistics.viewed'))->join($join)->where($where)->order(array('templatestatistics.count' => 'desc'))->limit($num_of_per_page * ($page - 1) . ',' . $num_of_per_page)->fetchAll();
                $c_template = Model('templatestatistics')->column(array('count(1)'))->join($join)->where($where)->fetchColumn();
                break;

            case 4 :
                if (empty($user)) redirect(parent::url('template', 'index'), _('Please login first.'));
                $join = array();
                $join[] = array('left join', 'templatequeue', 'using(template_id)');
                $join[] = array('left join', 'templatestatistics', 'using(template_id)');
                $where = [[[['template.act', '=', 'open'], ['template.state', '=', 'success']], 'and'], [[['templatequeue.user_id', '=', $user['user_id']], ['template.user_id', '=', $user['user_id']]], 'or']];
                if (!empty($s_template)) $where[0][0][] = ['template.template_id', 'in', array_column($s_template, 'template_id')];
                if ($style_id != null) $where[0][0][] = ['template.style_id', '=', $style_id];
                $m_template = Model('template')->column(array('template.*', 'template.user_id AS creative', 'templatestatistics.count', 'templatestatistics.viewed'))->join($join)->where($where)->group(array('template_id'))->order(array('template.inserttime' => 'desc'))->limit($num_of_per_page * ($page - 1) . ',' . $num_of_per_page)->fetchAll();
                $c_template = Model('template')->column(array('count(1)'))->join($join)->where($where)->fetchColumn();
                break;
        }

        _relay0:

        $template_id_pool = array();
        $templatequeue_id = array();
        $event_templatejoin_id = array();
        foreach ($m_template as $k => $v0) {
            $template_id_pool[] = $v0['template_id'];
        }  //$template_id_pool = collect this page album_id

        if (!empty($user)) $templatequeue = Model('templatequeue')->column(['template_id'])->where([[[['template_id', 'in', $template_id_pool], ['user_id', '=', $user['user_id']]], 'and']])->fetchAll();
        if (!empty($templatequeue)) foreach ($templatequeue as $v) {
            $templatequeue_id[] = $v['template_id'];
        }

        $m_event_templatejoin = Model('event_templatejoin')->join([['left join', 'event', 'using(event_id)']])->column(['event_templatejoin.template_id'])->where([[[['event_templatejoin.template_id', 'in', $template_id_pool], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])->fetchAll();
        if (!empty($m_event_templatejoin)) foreach ($m_event_templatejoin as $v) {
            $event_templatejoin_id[] = $v['template_id'];
        }

        $s_param = array();
        if (isset($_GET['quick']) && $_GET['quick'] == 'false') $s_param['quick'] = 'false';
        if (isset($_GET['join_event'])) $s_param['join_event'] = $_GET['join_event'];

        foreach ($m_template as $k0 => $v0) {
            $event_vote = null;
            if (in_array($v0['template_id'], $event_templatejoin_id)) {
                $m_eventjoin_id = Model('event_templatejoin')->column(['event_id'])->where([[[['event_templatejoin.template_id', '=', $v0['template_id']]], 'and']])->fetchColumn();
                $event_vote = '<a href="' . parent::url('event', 'content', ['event_id' => $m_eventjoin_id, 'click' => 'template_id_' . $v0['template_id']]) . '"><i title="' . _('參加活動中') . '" class="add_act02"></i></a>';
            }
            $a_param = ['template_id' => $v0['template_id']] + $s_param;
            $a_template[$k0] = array(
                'type' => 'template',
                'template' => array(
                    'template_id' => $v0['template_id'],
                    'cover_url' => parent::url('template', 'content', $a_param + ['click' => 'cover']),
                    'name_url' => parent::url('template', 'content', $a_param + ['click' => 'name']),
                    'user_id' => $v0['user_id'],
                    'name' => htmlspecialchars($v0['name']),
                    'point' => $v0['point'],
                    'description' => $v0['description'],
                    'image' => URL_UPLOAD . M_PACKAGE . $v0['image'],
                    'record' => '<a href="' . parent::url('template', 'content', ['template_id' => $v0['template_id']]) . '"><i title="' . _('未收藏') . '" class="add_no"></i></a>',
                    'event_templatejoin' => $event_vote,
                    'viewed' => $v0['viewed'],
                ),
                'user' => array(
                    'name' => Model('user')->column(array('name'))->where([[[['user_id', '=', $v0['user_id']]], 'and']])->fetchColumn(),
                    'url' => Core::get_creative_url($v0['user_id'], 'template_id_' . $v0['template_id']),
                    'picture' => URL_STORAGE . Core::get_userpicture($v0['user_id']),
                ),
            );
        }

        /**
         * 放入快速建立圖示
         */
        if ($page == 1) {
            if (SDK('Mobile_Detect')->isMobile()) {
                $ad_content = '<a href="#" data-uri="' . parent::deeplink('create') . '" onclick="clickHandler(this.dataset.uri)">
								<img src="' . static_file('images/quick_upload.png') . '">
							</a>';
            } else {
                $ad_content = '<a href="javascript:void(0)" onclick="upload()">
									<img src="' . static_file('images/quick_upload.png') . '">
								</a>';
            }
            $tmp = array(
                'type' => 'ad',
                'template' => null,
                'ad' => array(
                    'content' => $ad_content,
                ),
            );
            array_unshift($a_template, $tmp);
        }

        // 購買or擁有 紀錄比對
        if (!empty($templatequeue) || !empty($user)) {
            foreach ($a_template as $k0 => $v0) {
                if ($v0['template']['user_id'] == $user['user_id'] || in_array($v0['template']['template_id'], $templatequeue_id)) $a_template[$k0]['template']['record'] = '<a href="' . parent::url('template', 'content', ['template_id' => $a_template[$k0]['template']['template_id']]) . '"><i title="' . _('Collected') . '" class="add_love"></i></a>';
            }
        }
        parent::$data['template'] = $a_template;

        //more
        $num_of_item = $c_template;
        $num_of_max_page = ceil($num_of_item / $num_of_per_page);
        $tmp = array();
        if ($style_id != null) $tmp['style_id'] = $style_id;

        $more = ($page >= $num_of_max_page) ? null : parent::url('template', 'index', array_merge($tmp, ['rank_id' => $rank_id], $s_param));
        parent::$data['more'] = $more;

        //trip
        $trip = false;
        if (!empty($user['user_id'])) {
            $m_album = Model('album')->where([[[['state', '=', 'success'], ['user_id', '=', $user['user_id']]], 'and']])->fetchAll();
            $trip = (!empty($m_album)) ? true : false;
        }

        parent::$data['trip'] = $trip;

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('exploration '),
            array(_('exploration'), _('Quick Create'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/trip/css/trip.css'), 'href');
        parent::$html->set_js(static_file('js/trip/js/trip.min.js'), 'src');
        parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }

    function report()
    {
        if (is_ajax()) {
            $value = empty($_POST['value']) ? null : $_POST['value'];
            $text = empty($_POST['text']) ? null : $_POST['text'];
            $template_id = empty($_POST['template_id']) ? null : $_POST['template_id'];

            $user = parent::user_get();
            if ($user == null) json_encode_return(2, _('Please login first.'), parent::url('user', 'login', array('redirect' => parent::url('template', 'content', array('template_id' => $template_id)))));

            /**
             * 同作品重複檢舉且未處理數量超過三筆 , 十分鐘內檢舉過
             */
            $where = array(
                array(array(array('user_id', '=', $user['user_id']), array('id', '=', $template_id), array('state', '=', 'pretreat')), 'and'),
            );
            $m_report = Model('report')->where($where)->order(array('inserttime' => 'desc'))->fetchAll();
            if (!empty($m_report[0]['inserttime'])) {
                if (strtotime('+10 minute', strtotime($m_report[0]['inserttime'])) >= time()) json_encode_return(0, _('This operation cannot redo within 10 minutes.'));
            }

            if (count($m_report) > 3) json_encode_return(0, _('You have been report this template, we will deal with as soon as possible.'));

            $add = array(
                'reportintent_id' => $value,
                'user_id' => $user['user_id'],
                'type' => 'template',
                'id' => $template_id,
                'description' => $text,
                'state' => 'pretreat',
                'inserttime' => inserttime(),
            );

            Model('report')->add($add);

            json_encode_return(1, _('Your report has been sent, we will deal with as soon as possible, thanks.'));
        }
        die;
    }

    function save_album()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            if (empty($user)) json_encode_return(2, null, parent::url('user', 'login', ['redirect' => parent::url('template', 'upload')]));

            $m_album = Model('album')->process($user['user_id'])->fetch();
            if (!empty($m_album)) json_encode_return(3, null, parent::url('diy', 'index', array('album_id' => $m_album['album_id'])));

            $album_data = empty($_POST['album_data']) ? null : json_decode($_POST['album_data'], true);
            if ($album_data == null) json_encode_return(0, _('You are required to upload the photo profile first.'));

            list($all_limit, $amount, $photo_left, $album_limit) = $this->photo_left();

            //檢查上傳數量是否超過使用者身分的單一相簿相片上限
            if (count($album_data) > $album_limit) json_encode_return(0, _('The photo quantity is beyond the limit') . '：' . $album_limit);

            Model('album');
            Model('albumstatistics');
            Model('followfrom');
            Model('notice');
            Model('noticequeue');
            Model('photo');
            Model()->beginTransaction();

            list($result, $message, $redirect, $album_id) = array_decode_return(Model('album')->pretreat($user['user_id'], 0));

            //1.需統一upload路徑，將M_CLASS直接以diy取代  2.目的地路徑
            $subpath_upload = M_PACKAGE . '/diy/' . date('Ymd') . '/';
            mkdir_p(PATH_UPLOAD, $subpath_upload);
            $storage_str = SITE_LANG . '/user/' . $user['user_id'] . '/album/' . $album_id;
            mkdir_p(PATH_STORAGE, $storage_str);

            //製作QRcode
            $tmp_url = parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]);
            $tmp_file = storagefile($storage_str . '/qrcode.jpg');
            if (!$this->make_qrcode($tmp_url, $tmp_file)) {
                Model()->rollBack();
                json_encode_return(0, _('QR code occur exception, please contact us.'));
            }

            $Image = new \Core\Image;

            //0610 不執行合併圖片，因合併背景為全空白的Frame，與放大圖片尺寸會是一樣的View，故執行合併的意義不大且會造成檔案更大一些
            $a_originalsize_img = [];
            $a_resize_img = [];
            foreach ($album_data as $k0 => $v0) {
                //先移至 storage
                $path_storage_file = PATH_STORAGE . storagefile($storage_str . '/' . $k0 . '.jpg');
                if (rename(PATH_UPLOAD . M_PACKAGE . '/template/' . date('Ymd') . '/' . $user['user_id'] . '/fast_upload/' . $v0, $path_storage_file)) {
                    //處理 size
                    list($src_w, $src_h) = getimagesize($path_storage_file);

                    // 0411 不作resize - Mars
                    // if ($src_w != 1336 || $src_h != 2004) $Image->setImage($path_storage_file)->setSize(1336, 2004, true)->setType('jpg')->save($path_storage_file, true);

                    //再由 storage resize copy 至 upload
                    $subpath_upload_file = $subpath_upload . uniqid() . '.jpg';

                    // 0411 不作resize - Mars
                    // $Image->setImage($path_storage_file)->setSize(668, 1002)->save(PATH_UPLOAD.$subpath_upload_file);
                    $Image->set($path_storage_file)->save(PATH_UPLOAD . $subpath_upload_file);

                    $a_originalsize_img[] = $subpath_upload . $v0;
                    $a_resize_img[] = $subpath_upload_file;
                } else {
                    Model()->rollBack();
                    json_encode_return(0, _('Abnormal process, please try again.'));
                }
            }

            //set previe
            $set_preview = (count($a_resize_img) > Core::settings('ALBUM_PREVIEW_LIMIT')) ? array_slice($a_resize_img, 0, Core::settings('ALBUM_PREVIEW_LIMIT')) : $a_resize_img;

            //album - cover + photo
            $edit = [
                'cover' => $a_resize_img[0],
                'photo' => json_encode($a_resize_img),
                'preview' => json_encode($set_preview),
            ];
            Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit($edit);

            //photo
            $add = array();
            foreach ($a_resize_img as $k0 => $v0) {
                $add[] = array(
                    'album_id' => $album_id,
                    'user_id' => $user['user_id'],
                    'image' => $v0,
                    'usefor' => 'image',
                    'state' => 'success',
                    'sequence' => ($k0 + 1),
                    'inserttime' => inserttime(),
                );
            }
            Model('photo')->add($add);

            //album -> zip
            Model('album')->zip($album_id);

            //album - zipped + state
            Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit(['zipped' => 1, 'state' => 'success']);

            //建立notice --> 先取得follow list
            $m_followfrom = Model('followfrom')->column(['`from`'])->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetchAll();
            if ($m_followfrom) {
                //填入notice
                $add = [
                    '`type`' => 'album',
                    'id' => $album_id,
                    'state' => 'success',
                    'act' => 'close',
                    'inserttime' => inserttime(),
                ];
                $notice_id = Model('notice')->add($add);

                //填入noticequeue
                $add = array();
                foreach ($m_followfrom as $v) {
                    $add[] = array(
                        'user_id' => $v['from'],
                        'notice_id' => $notice_id,
                    );
                }
                Model('noticequeue')->add($add);
            }

            Model()->commit();

            json_encode_return(1, _('Success.'), parent::url('user', 'albumcontent', ['user_id' => $user['user_id']]));
        }
        die;
    }

    function make_qrcode($url = null, $file = null)
    {
        if ($url != null && $file != null) {
            if (!class_exists('QRcode')) include PATH_LIB . '/phpqrcode_1.1.4/phpqrcode.php';

            $codeContents = $url;
            $fileName = urldecode($file);
            $outerFrame = 4;
            $pixelPerPoint = 5;
            $jpegQuality = 95;

            $frame = QRcode::text($codeContents, false, QR_ECLEVEL_M);

            $h = count($frame);
            $w = strlen($frame[0]);

            $imgW = $w + 2 * $outerFrame;
            $imgH = $h + 2 * $outerFrame;

            $base_image = imagecreate($imgW, $imgH);

            $col[0] = imagecolorallocate($base_image, 255, 255, 255); // BG, white
            $col[1] = imagecolorallocate($base_image, 0, 0, 0);     // FG, blakc

            imagefill($base_image, 0, 0, $col[0]);

            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    if ($frame[$y][$x] == '1') {
                        imagesetpixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                    }
                }
            }

            $target_image = imagecreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
            imagecopyresized(
                $target_image,
                $base_image,
                0, 0, 0, 0,
                $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH
            );
            imagedestroy($base_image);
            imagejpeg($target_image, PATH_STORAGE . $fileName, $jpegQuality);
            imagedestroy($target_image);

            return true;
        }

        die();
    }

    function photo_left()
    {
        $user = parent::user_get();
        $user_grade = Core::get_usergrade($user['user_id']);
        $amount = 0;

        //相簿相片總和(已用)
        $m_album_count = Model('album')->column(array('photo', 'album_id'))->where(array(array(array(array('user_id', '=', $user['user_id']), array('album.state', '=', 'success'), array('album.zipped', '=', 1), array('act', '!=', 'delete')), 'and')))->fetchAll();
        foreach ($m_album_count as $k => $v) {
            $amount += count(json_decode($v['photo'], true));
        }

        $photos_per_album = json_decode(Core::settings('PHOTOS_PER_ALBUM'), true);
        $photo_grade_limit = json_decode(Core::settings('PHOTO_GRADE_LIMIT'), true);
        switch ($user_grade) {
            default:
            case 'free':
                $all_limit = $photo_grade_limit['free'];  //身分上限
                $photo_left = $all_limit - $amount; //剩下可用的(共)
                $album_limit = ($photo_left > $photos_per_album['free']) ? $photos_per_album['free'] : $photo_left; //正在編輯的作品上限
                break;

            case 'plus':
                $all_limit = $photo_grade_limit['plus']; //身分上限
                $photo_left = $all_limit - $amount; //剩下可用的(共)
                $album_limit = ($photo_left > $photos_per_album['plus']) ? $photos_per_album['plus'] : $photo_left; //正在編輯的作品上限
                break;

            case 'profession':
                $all_limit = $photo_grade_limit['profession'];
                $photo_left = $photo_grade_limit['profession'];
                $album_limit = $photos_per_album['profession'];
                break;
        }

        $return[0] = $all_limit;
        $return[1] = $amount;
        $return[2] = $photo_left;
        $return[3] = $album_limit;
        return $return;
    }

    function upload()
    {
        $user = parent::user_get();
        $judge0 = empty($user) ? true : false;
        $m_album = Model('album')->process($user['user_id'])->fetch();
        $judge1 = !empty($m_album) ? true : false;
        if (is_ajax()) {
            if ($judge0) json_encode_return(2, null, parent::url('user', 'login', ['redirect' => parent::url('template', 'upload')]));
            if ($judge1) json_encode_return(3, null, parent::url('diy', 'index', array('album_id' => $m_album['album_id'])));

            if (isset($_FILES['files'])) {
                switch ($_FILES['files']['error'][0]) {
                    case 0:
                        $upload_folder = '/template/' . date('Ymd') . '/' . $user['user_id'] . '/fast_upload/';
                        mkdir_p(PATH_UPLOAD, M_PACKAGE . $upload_folder);
                        switch ($_FILES['files']['type'][0]) {
                            case 'image/jpeg':
                            case 'image/jpg':
                                $extension = '.jpg';
                                break;

                            case 'image/png':
                                $extension = '.png';
                                break;

                            default:
                                json_encode_return(0, _('Upload file type only can be JPEG / JPG / PNG.'));
                                break;
                        }
                        $filename = uniqid() . $extension;
                        $out_img = PATH_UPLOAD . M_PACKAGE . $upload_folder . $filename;
                        if (move_uploaded_file($_FILES['files']['tmp_name'][0], $out_img)) {
                            \Extension\aws\S3::upload($out_img);

                            /* 0411 快速上傳圖片不再裁切處理 - Mars

                            list($src_w, $src_h) = getimagesize($out_img);
                            if ($src_w > 1336 || $src_h > 2004) {
                                if ($src_w > 1336) {
                                    //原圖寬超過比例定義截圖位置
                                    $src_x = ceil(($src_w/2)-668);
                                    $new_w = 1336;
                                } else {
                                    $src_x = 0;
                                    $new_w = $src_w;
                                }

                                if ($src_h > 2004) {
                                    //原圖高超過比例定義截圖位置
                                    $src_y = ceil(($src_h/2)-1002);
                                    $new_h = 2004;
                                } else {
                                    $src_y = 0;
                                    $new_h = $src_h;
                                }

                                $newImg = imagecreatetruecolor($new_w, $new_h);
                                $srcImg = imagecreatefromjpeg($out_img);

                                imagecopy ($newImg , $srcImg , 0 , 0 , $src_x , $src_y, $new_w , $new_h);
                                imagejpeg($newImg, $out_img);
                                imagedestroy($newImg);
                            }
                            */
                            json_encode_return(1, null, null, $filename);
                        }
                        json_encode_return(0, _('Abnormal process, please try again.'));
                        break;

                    case 1:
                        json_encode_return(0, _('Exceeded upload size limit : ') . ini_get('upload_max_filesize') . 'B');
                        break;

                    default:
                        json_encode_return(0, _('Abnormal process, please try again.'));
                        break;
                }
            }
        }

        if ($judge0) redirect(parent::url('user', 'login', ['redirect' => parent::url('template', 'upload')]), _('Please login first.'));
        if ($judge1) redirect(parent::url('diy', 'index', array('album_id' => $m_album['album_id'])), _('Unfinished! Will go back to the editor.'));

        /**
         * 0:熱門  1:最新  2:已購買
         */
        $rank = (!empty($_GET['rank']) && in_array($_GET['rank'], array(0, 1, 2))) ? $_GET['rank'] : 0;
        parent::$data['rank'] = $rank;

        $sale = (!empty($_GET['sale']) && in_array($_GET['sale'], array('true', 'false', 'purchased', 'upload'))) ? $_GET['sale'] : null;
        parent::$data['sale'] = $sale;

        //photos_per_album
        list($all_limit, $amount, $photo_left, $album_limit) = $this->photo_left();
        parent::$data['photos_per_album'] = $album_limit;

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Quick Create'),
            array(_('exploration'), _('Quick Create'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.fileupload/css/jquery.fileupload.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.fileupload/css/jquery.fileupload-ui.css'), 'href');

        parent::$html->set_js(static_file('js/jquery.ui.widget.js'), 'src');
        parent::$html->set_js(static_file('js/angular.min.js'), 'src');
        parent::$html->set_js(static_file('js/load-image.all.min.js'), 'src');
        parent::$html->set_js(static_file('js/canvas-to-blob.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.iframe-transport.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload.js'), 'src');

        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-process.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-image.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-validate.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-angular.js'), 'src');
        parent::$html->set_js(static_file('js/app.js'), 'src');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }

    function server()
    {
    }

    function show_photo()
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

}