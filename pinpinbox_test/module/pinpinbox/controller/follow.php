<?php

class followController extends frontstageController
{
    function __construct()
    {
    }

    function index()
    {
        redirect_php(parent::url('index'));

        function date2before($val)
        {
            $diff = time() - $val;
            if ($diff < 0) {
                return _('Future');
            } elseif ($diff < 60) {
                return $diff . _('seconds ago');
            } elseif ($diff < 3600) {
                return floor($diff / 60) . _('minutes ago');
            } elseif ($diff < 86400) {
                return floor($diff / 3600) . _('hours ago');
            } elseif ($diff < 604800) {
                return floor($diff / 86400) . _('days ago');
            } else {
                return floor($diff / 604800) . _('weeks ago');
            }
        }

        $user = parent::user_get();
        if ($user == null) redirect(parent::url('user', 'login', array('redirect' => parent::url('follow', 'index'))), _('Please login first.'));

        /**
         * 0617 若沒有關注消息呈現會造成頁面空白，故需要判斷此會員 1.有無關注其他人  2.有沒有關注消息可呈現;
         */
        $where = array();
        $where = array(array(array(array('user_id', '=', $user['user_id'])), 'and'));
        $m_followto = Model('followto')->where($where)->fetchAll();
        if (empty($m_followto)) redirect(parent::url('index', 'index'), _('No other follow-ups or dynamics.'));

        $where = array();
        $where = array(array(array(array('`noticequeue`.`user_id`', '=', $user['user_id']), array('`notice`.`act`', '=', 'open')), 'and'));
        $join = array();
        $join[] = array('left join', 'notice', 'using(notice_id)');
        $m_noticequeue = Model('noticequeue')->where($where)->join($join)->fetchAll();
        if (empty($m_noticequeue)) redirect(parent::url('index', 'index'), _('No other follow-ups or dynamics.'));


        /**
         * 2015-05-05 Lion:
         *     此段邏輯有 model 負載的隱憂, 如果使用者瀏覽越早的 notice, 會耗費[分頁忽略的筆數]以上的 model(但也能 cache 供使用)
         *     如果透過 noticequeue join notice、album(或其它)、user 來達成, 密集的 join 將導致 cache reuse 降低, 而且 notice.type 的設計不便於操作 sql
         *     資訊都往 noticequeue 擺的話 data 重複性又太高, 目前先這麼處理
         */
        $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
        $num_of_per_page = 10;//一頁幾個
        $a_follow = array();
        $continue = 0;
        $where = array(
            array(array(array('user_id', '=', $user['user_id'])), 'and'),
        );
        $m_noticequeue = Model('noticequeue')->where($where)->order(array('inserttime' => 'desc'))->fetchAll();
        $c_noticequeue = Model('noticequeue')->column(array('count(1)'))->where($where)->fetchColumn();
        foreach ($m_noticequeue as $v0) {
            //依 notice_id 取得訊息資料
            $m_notice = Model('notice')->where(array(array(array(array('notice_id', '=', $v0['notice_id']), array('act', '=', 'open'), array('state', '=', 'success')), 'and')))->fetch();

            $a_album = array();
            $user_id = null;
            if (!empty($m_notice)) {
                switch ($m_notice['type']) {
                    case 'album':
                        $column = array(
                            'album.album_id',
                            'album.name album_name',
                            'album.description album_description',
                            'album.cover',
                            'album.point',
                            'user.user_id',
                            'user.name user_name',
                            'albumstatistics.count',
                            'categoryarea_category.categoryarea_id',
                        );
                        $join = array(
                            array('left join', 'user', 'using(user_id)'),
                            array('left join', 'albumstatistics', 'using(album_id)'),
                            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
                        );
                        $where = array(
                            array(array(array('album.album_id', '=', $m_notice['id']), array('album.act', '=', 'open'), array('user.act', '=', 'open')), 'and')
                        );
                        $m_album = (new \albumModel)
                            ->column($column)
                            ->join($join)
                            ->where($where)
                            ->fetch();

                        if (!empty($m_album)) {
                            $a_album = array(
                                'name' => htmlspecialchars($m_album['album_name']),
                                'description' => nl2br(htmlspecialchars($m_album['album_description'])),
                                'cover' => URL_UPLOAD . getimageresize($m_album['cover'], 374, 561),
                                'share_cover' => $m_album['cover'],
                                'point' => $m_album['point'],
                                'url' => parent::url('album', 'content', array('album_id' => $m_album['album_id'], 'categoryarea_id' => $m_album['categoryarea_id'])),
                            );
                            $user_id = $m_album['user_id'];
                        }
                        break;

                    default:
                        throw new Exception("[" . __METHOD__ . "] Unknown case");
                        break;
                }
            }
            if (empty($user_id)) continue;

            //分頁忽略的筆數
            if ($continue < $num_of_per_page * ($page - 1)) {
                ++$continue;
                continue;
            }

            if (!empty($m_notice)) {
                $m_follow = Model('follow')->where(array(array(array(array('user_id', '=', $user_id)), 'and')))->fetch();

                $a_follow[] = array(
                    'album' => $a_album,
                    'albumstatistics' => array(
                        'count' => $m_album['count'],
                    ),
                    'follow' => array(
                        'count_from' => $m_follow['count_from'],
                    ),
                    'notice' => array(
                        'notice_id' => $m_notice['notice_id'],
                        'type' => $m_notice['type'],
                        'id' => $m_notice['id'],
                        'inserttime' => date2before(strtotime($m_notice['inserttime'])),
                    ),
                    'user' => array(
                        'name' => $m_album['user_name'],
                        'picture' => URL_STORAGE . Core::get_userpicture($user_id),
                        'url' => Core::get_creative_url($user_id),
                    ),
                );

                if (count($a_follow) >= $num_of_per_page) break;
            }
        }
        (!empty($a_follow)) ? parent::$data['follow'] = $a_follow : redirect(parent::url('index', 'index'), _('No other follow-ups or dynamics.'));

        //page
        parent::$data['page'] = $page;

        //more
        $num_of_item = $c_noticequeue;
        $num_of_max_page = ceil($num_of_item / $num_of_per_page);
        $num_of_now_page = (1 <= $page && $page <= $num_of_max_page) ? $page : 1;
        if ($page >= $num_of_max_page) {
            $more = null;
        } else {
            $more = parent::url('follow', 'index', array('page' => $num_of_now_page + 1));
        }
        parent::$data['more'] = $more;

        //disqus
        parent::$data['disqus'] = parent::disqus('album', $a_follow[0]['notice']['notice_id']);

        //seo
        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Shortlist'),
            array(_('Shortlist'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.ias/css/jquery.ias.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_js(static_file('js/jquery.ias/js/jquery-ias.min.js'), 'src');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->jbox();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }
}	