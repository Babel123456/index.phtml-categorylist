<?php

class backstageController extends Core
{
    protected static $html = null;
    protected static $data = [];
    protected static $view = [];
    protected static $view_disable = [];

    function __construct()
    {
        if (empty(adminModel::getSession())) {
            if (M_METHOD != 'index::login') {
                $redirect = self::url('index', 'login', ['redirect' => $_SERVER['REQUEST_URI']]);
                $message = _('Please login first.');

                if (is_ajax()) {
                    json_encode_return(2, $message, $redirect);
                } else {
                    redirect($redirect, $message);
                }
            }
        } else {
            /**
             * 2013-07-08 Lion
             * 權限判斷, 目前作法會使預設可訪問的 class 以下的所有 function 皆可訪問,
             * 而當 class 不屬於預設且判斷通過, 則再檢查 function;
             * 這裡不適合僅用 M_METHOD 處理, 例如 M_CLASS::index, 這樣就所有人皆可存取任何 class 的 index 了
             */
            //預設可訪問的 class
            //^ upload 應該要控管
            if (!in_array(M_CLASS, ['index', 'upload', 'excel', 'pdf', 'generate_qrcode'])) {
                $denied = false;

                $join = [
                    ['left join', 'admin_admingroup', 'using(admin_id)'],
                    ['left join', 'admingroup', 'using(admingroup_id)'],
                    ['left join', 'admingroup_adminmenu', 'using(admingroup_id)'],
                    ['left join', 'adminmenu', 'using(adminmenu_id)'],
                ];
                $where = [
                    [[['admin.admin_id', '=', adminModel::getSession()['admin_id']], ['admingroup_adminmenu.act', '!=', ''], ['adminmenu.class', '!=', '']], 'and'],
                ];
                $group = [
                    'admingroup_adminmenu.act',
                    'adminmenu.class',
                ];
                $m_admin = Model('admin')->column(['admingroup_adminmenu.act', 'adminmenu.class'])->join($join)->where($where)->group($group)->fetchAll();

                $tmp1 = [];
                $tmp1 = array_multiple_search($m_admin, 'class', M_CLASS);
                if (empty($tmp1)) {
                    $denied = true;
                } else {
                    //預設可訪問的 function (act)
                    $a_default_access_act = [
                        'analysis',
                        'download',
                        'extra0',
                        'get_area_up',
                        'grid_edit',
                        'index',
                        'json',
                        'form',
                        'excel',
                        'pdf',
                        'file',
                        'file2',
                        'copy',
                        'elementlayout',
                        'get_user_url',
                        'album_search',
                        'template_search',
                        'editgrade',
                        'edituserpoint',
                        'edituserpointsplit',
                        'edituserstatistics',
                        'get_data',
                        'remove_album',
                        'croppie',
                        'transferdata',
                        'refreshlayout',
                        'style_type',
                        'user_search',
                    ];

                    $tmp3 = [];
                    $tmp3 = array_multiple_search($tmp1, 'act', M_FUNCTION);
                    if (!in_array(M_FUNCTION, $a_default_access_act) && empty($tmp3)) {
                        $denied = true;
                    }

                    /**
                     * 2014-04-01 Lion
                     * 由於大部分 add 與 edit 的功能、畫面欄位大同小異, 所以我將它們的 url 以及 code 都集中到 form,
                     * 用 $_GET 來區別它們, 而不是以 function, 但這讓 M_FUNCTION 無法發揮權限控管的設計, 因此特別處理 add 跟 edit 的權限管理
                     */
                    if (M_FUNCTION == 'form') {
                        $tmp3 = [];
                        $tmp3 = array_multiple_search($tmp1, 'act', $_GET['act']);
                        if (!isset($_GET['act']) || ('add' != $_GET['act'] && 'edit' != $_GET['act'])) {
                            $denied = true;
                        } elseif (!in_array($_GET['act'], $a_default_access_act) && empty($tmp3)) {
                            $denied = true;
                        }
                    }
                }

                if ($denied) {
                    if (is_ajax()) {
                        json_encode_return(0, _('Access Denied'));
                    } else {
                        redirect(self::url('index', 'index'), _('Access Denied'));
                    }
                }
            }
        }

        self::$html = Lib('html');
    }

    function __destruct()
    {
    }

    function display()
    {
        $js = self::$html->get_js();
        if (!empty($js)) $this->js();
        unset($js);
        $this->head();
        $this->foot();
        if (is_array(self::$data)) {
            foreach (self::$data as $k => $v) {
                $$k = $v;
            }
        }
        $tmp1 = ['head', 'headbar', 'js_src'];
        $tmp2 = ['footbar', 'js', 'foot'];
        foreach (self::$view_disable as $v0) {
            if (array_key_exists($v0, self::$view)) unset(self::$view[$v0]);
        }
        foreach ($tmp1 as $v1) {
            if (isset(self::$view[$v1]) && file_exists(self::$view[$v1])) include self::$view[$v1];
        }
        ksort(self::$view);
        foreach (self::$view as $k1 => $v1) {
            if (in_array($k1, $tmp1, true) || in_array($k1, $tmp2, true)) {
                continue;
            }
            if (file_exists($v1)) include $v1;
        }
        foreach ($tmp2 as $v1) {
            if (isset(self::$view[$v1]) && file_exists(self::$view[$v1])) include self::$view[$v1];
        }
    }

    //^可能棄用
    function get_admin_admingroup_class()
    {
        return array_multiple_search(Model('admin_admingroup')->get(sql_select_encode(null, null, [['admin_id', '=', adminModel::getSession()['admin_id']]]), 'fetchAll'), 'admingroup_id', adminModel::getSession()['lastloginadmingroup_id'])[0]['class'];
    }

    function get_adminmenu_name_by_class($class)
    {
        return Model('adminmenu')->where([[[['class', '=', $class]], 'and']])->fetch()['name'];
    }

    /**
     * 由自身 level 以迴圈取得到最上層 level 的所有 area name
     * <p>v1.0 2014-07-07</p>
     * @param unknown $model
     * @param unknown $id
     * @return string
     */
    function get_area_from_level_desc($model, $id)
    {
        $return = [];
        switch ($model) {
            default:
                $m = Model($model)->where([[[[$model . '_id', '=', (int)$id]], 'and']])->fetch();
                break;
        }
        if (!empty($m)) $return[] = $m;
        if ($m['up'] > 0) $return = array_merge($return, $this->get_area_from_level_desc($model, $m['up']));

        return $return;
    }

    /**
     * 取得 area level 格式化字串
     * <p>v1.0 2014-07-07</p>
     * @param unknown $model
     * @param unknown $id
     * @return string
     */
    function get_area_level_format_string($model, $id)
    {
        $tmp0 = array();
        foreach ($this->get_area_from_level_desc($model, $id) as $v0) {
            $tmp0[] = $v0['name'];
        }

        return implode(' > ', array_reverse($tmp0));
    }

    function get_form_select($model)
    {
        $return = array();
        switch ($model) {

            case 'banner_type' :
                $m = (new categoryarea_styleModel())->fetchEnum('banner_type');
                foreach ($m as $v0) {
                    $return[] = array(
                        'value' => $v0,
                        'text' => $v0
                    );
                }
                break;
            case 'category' :
                $m = Model($model)->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                foreach ($m as $v0) {
                    $return[] = array(
                        'value' => $v0[$model . '_id'],
                        'text' => $v0[$model . '_id'] . ' - ' . $v0['name'],
                    );
                }
                break;
            case 'categoryarea' :
                $m = Model($model)->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                foreach ($m as $v0) {
                    $return[] = array(
                        'value' => $v0[$model . '_id'],
                        'text' => $v0[$model . '_id'] . ' - ' . $v0['name'],
                    );
                }
                break;
            case 'custom' :
                /**
                 *  0321 : 預設為自訂URL
                 */
                $return[] = ['value' => 'url', 'text' => '自訂外部URL'];
                break;
            case 'event' :
                /**
                 *  僅檢索未過期的活動
                 */
                $m = Model($model)->where([[[['act', '=', 'open'], ['endtime', '>', date('Y-m-d h:m:s', time())]], 'and']])->fetchAll();
                foreach ($m as $v0) {
                    $return[] = array(
                        'value' => $v0[$model . '_id'],
                        'text' => $v0[$model . '_id'] . ' - ' . $v0['name'],
                    );
                }
                break;
            case 'pinpinmenu' :
                $return[] = ['value' => 'template', 'text' => '選擇模板 - template'];
                $return[] = ['value' => 'creative/recruit', 'text' => '職人招募 - creative/recruit',];
                $return[] = ['value' => 'creative', 'text' => '職人專區 - creative',];
                $return[] = ['value' => 'recruit', 'text' => '合作計畫 - recruit',];
                break;
            case 'region' :
                $m = Model('categoryarea')->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                foreach ($m as $v0) {
                    $return[] = array(
                        'value' => $v0['categoryarea_id'],
                        'text' => '職人 - ' . $v0['name'],
                    );
                }
                $return[] = ['value' => 'album_keyword', 'text' => '相本關鍵字'];
                $return[] = ['value' => 'template_keyword', 'text' => '版型關鍵字'];
                break;
                break;
            case 'lang':
            case 'style':
                $m = Model($model)->where(array(array(array(array('act', '=', 'open')), 'and')))->order(array($model . '_id' => 'asc'))->fetchAll();
                foreach ($m as $v0) {
                    $return[] = array(
                        'value' => $v0[$model . '_id'],
                        'text' => $v0[$model . '_id'] . ' - ' . $v0['name']
                    );
                }
                break;
            case 'special':
                $m = Model($model)->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                foreach ($m as $v0) {
                    $return[] = array(
                        'value' => $v0[$model . '_id'],
                        'text' => $v0[$model . '_id'] . ' - ' . $v0['name'],
                    );
                }
                break;
        }

        return $return;
    }

    function get_gird_img(array $attr)
    {
        $alt = isset($attr['alt']) ? $attr['alt'] : null;
        $src = isset($attr['src']) ? $attr['src'] : null;
        if ($src === null) throw new Exception("[" . __METHOD__ . "] Parameters error");

        $return = null;
        if (is_image(PATH_UPLOAD . $src)) {
            static $Image;

            if (!$Image) $Image = new \Core\Image;

            $o_image = $Image->set(PATH_UPLOAD . $src);

            /**
             * grid-img: jquery selector 用
             * data-size: browseKit 用
             */
            $return = '
			<a class="grid-img" title="' . $alt . '" href="' . URL_UPLOAD . $src . '" data-size="' . $o_image->getWidth() . 'x' . $o_image->getHeight() . '">
				<img alt="' . $alt . '" src="' . fileinfo($o_image->setSize()->save())['url'] . '">
			</a>';
        }

        return $return;
    }

    function get_grid_display($model, $id)
    {
        $return = null;
        switch ($model) {
            case 'album':
            case 'audio':
            case 'cashflow':
            case 'category':
            case 'categoryarea':
            case 'event':
            case 'lang':
            case 'question':
            case 'recruitintent':
            case 'reportintent':
            case 'style':
            case 'template':
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) $return = implode('<br>', ['name: ' . $m['name'], 'act: ' . $m['act']]);
                break;

            case 'ad':
                static $Image;
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) {
                    if (!$Image) $Image = new \Core\Image;
                    $return = implode('<br>', ['name: ' . $m['name'], 'image: <img src="' . fileinfo($Image->set(fileinfo($m['image'])['path'])->setSize()->save())['url'] . '" border="0" width="50" height="50">', 'act: ' . $m['act']]);
                }
                break;

            case 'adarea':
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) $return = implode('<br>', ['name: ' . $this->get_area_level_format_string($model, $id), 'act: ' . $m['act']]);
                break;

            case 'admingroup':
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) $return = implode('<br>', ['name: ' . $m['name']]);
                break;

            case 'hobby':
                $m = Model($model)->column(['hobby_id', 'name'])->join([['inner join', 'hobby_user', 'using(hobby_id)']])->where([[[['user_id', '=', $id]], 'and']])->fetchAll();
                if ($m) {
                    $array = [];
                    foreach ($m as $v0) {
                        $array[] = $v0['hobby_id'] . ' - ' . $v0['name'];
                    }
                    $return = implode('<br>', $array);
                }
                break;

            case 'income':
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) $return = implode('<br>', ['total: ' . $m['total'], 'currency: ' . $m['currency'], 'state: ' . $m['state']]);
                break;

            case 'payment':
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) $return = implode('<br>', ['state: ' . $m['state']]);
                break;

            case 'settlement':
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) $return = implode('<br>', ['point_album: ' . $m['point_album'], 'point_template: ' . $m['point_template'], 'state: ' . $m['state']]);
                break;

            case 'user':
                $m = Model($model)->where([[[[$model . '_id', '=', $id]], 'and']])->fetch();
                if ($m) $return = implode('<br>', ['account: ' . $m['account'], 'name: ' . $m['name'], 'act: ' . $m['act']]);
                break;

            default:
                throw new Exception("[" . __FUNCTION__ . "] Unknown case");
                break;
        }

        return $return;
    }

    function grid_json_decode($json, $level = 0)
    {
        $return = null;
        if (is_array($tmp0 = json_decode($json, true))) {
            $tmp1 = [];
            foreach ($tmp0 as $v0 => $k0) {
                if (is_array($k0)) {
                    $tmp1[] = $v0 . ' : <br>' . $this->grid_json_decode(json_encode($k0), $level + 1);
                } else {
                    $prefix = null;
                    for ($i = 0; $i < $level; ++$i) {
                        $prefix .= '&nbsp;-&nbsp;';
                    }
                    $tmp1[] = $prefix . $v0 . ' : ' . $k0;
                }
            }
            $return = implode('<br>', $tmp1);
        }

        return $return;
    }

    /**
     * 處理 grid 的 request
     * <p>v1.0 2014-12-23</p>
     */
    function grid_request_encode()
    {
        //where//^貌似還有預設帶入的 GET 未處理
        $where = [];
        if (!empty($_REQUEST['filter'])) {
            $tmp1 = [];
            foreach ($_REQUEST['filter']['filters'] as $v1) {
                //僅篩選一個欄位
                if (empty($v1['filters'])) {
                    //2016-11-22 Lion: 特殊欄位
                    switch ($v1['field']) {
                        case 'enabled':
                            $v1['value'] = filter_var($v1['value'], FILTER_VALIDATE_BOOLEAN);
                            break;
                    }

                    switch ($v1['operator']) {
                        case 'startswith':
                            $v1['operator'] = 'like';
                            $v1['value'] = $v1['value'] . '%';
                            break;
                        case 'contains':
                            $v1['operator'] = 'like';
                            $v1['value'] = '%' . $v1['value'] . '%';
                            break;
                        case 'doesnotcontain':
                            $v1['operator'] = 'not like';
                            $v1['value'] = '%' . $v1['value'] . '%';
                            break;
                        case 'endswith':
                            $v1['operator'] = 'like';
                            $v1['value'] = '%' . $v1['value'];
                            break;
                        case 'eq':
                            $v1['operator'] = '=';
                            break;
                        case 'neq':
                            $v1['operator'] = '!=';
                            break;
                        case 'gte':
                            $v1['operator'] = '>=';
                            break;
                        case 'gt':
                            $v1['operator'] = '>';
                            break;
                        case 'lte':
                            $v1['operator'] = '<=';
                            break;
                        case 'lt':
                            $v1['operator'] = '<';
                            break;
                    }
                    $tmp1[] = [$v1['field'], $v1['operator'], $v1['value']];
                } //篩選兩個以上欄位
                else {
                    $tmp2 = [];
                    foreach ($v1['filters'] as $v2) {
                        //2016-11-22 Lion: 特殊欄位
                        switch ($v2['field']) {
                            case 'enabled':
                                $v2['value'] = filter_var($v2['value'], FILTER_VALIDATE_BOOLEAN);
                                break;
                        }

                        switch ($v2['operator']) {
                            case 'startswith':
                                $v2['operator'] = 'like';
                                $v2['value'] = $v2['value'] . '%';
                                break;
                            case 'contains':
                                $v2['operator'] = 'like';
                                $v2['value'] = '%' . $v2['value'] . '%';
                                break;
                            case 'doesnotcontain':
                                $v2['operator'] = 'not like';
                                $v2['value'] = '%' . $v2['value'] . '%';
                                break;
                            case 'endswith':
                                $v2['operator'] = 'like';
                                $v2['value'] = '%' . $v2['value'];
                                break;
                            case 'eq':
                                $v2['operator'] = '=';
                                break;
                            case 'neq':
                                $v2['operator'] = '!=';
                                break;
                            case 'gte':
                                $v2['operator'] = '>=';
                                break;
                            case 'gt':
                                $v2['operator'] = '>';
                                break;
                            case 'lte':
                                $v2['operator'] = '<=';
                                break;
                            case 'lt':
                                $v2['operator'] = '<';
                                break;
                        }
                        $tmp2[] = [$v2['field'], $v2['operator'], $v2['value']];
                    }
                    if (!empty($tmp2)) $where[] = [$tmp2, $v1['logic']];
                }
            }
            if (!empty($tmp1)) $where[] = [$tmp1, $_REQUEST['filter']['logic']];
        }

        //group
        $group = [];
        if (!empty($_REQUEST['group'])) {
            foreach ($_REQUEST['group'] as $v1) {
                //^ group 有 asc desc ? $v1['dir']
                $group[] = $v1['field'];
            }
        }

        //order
        $order = [];
        if (!empty($_REQUEST['sort'])) {
            foreach ($_REQUEST['sort'] as $v1) {
                $order[$v1['field']] = $v1['dir'];
            }
        }

        //limit
        $limit = $_REQUEST['skip'] . ',' . $_REQUEST['take'];

        return [$where, $group, $order, $limit];
    }

    function head()
    {
        self::$html->set_css(static_file('css/head.css'), 'href');

        self::$data['html_css'] = self::$html->get_css();

        self::$view['head'] = PATH_ROOT . 'module/admin/view/' . SITE_LANG . '/head.phtml';
    }

    function headbar()
    {
        self::$html->set_css(static_file('css/style.css'), 'href');

        //表單驗證
        self::$html->set_jquery_validation();

        //lazyload
        self::$html->lazyload();

        //pace
        self::$html->pace();

        /**
         * Lion 2015-01-15: 由底層選單往上層找, 這樣在處理 admingroup_adminmenu 時就不用特別建立上層的資料
         */
        $join = array(
            array('left join', 'admingroup_adminmenu', 'using(adminmenu_id)'),
            array('left join', 'admingroup', 'using(admingroup_id)'),
            array('left join', 'admin_admingroup', 'using(admingroup_id)'),
            array('left join', 'admin', 'using(admin_id)')
        );
        $where = array(
            array(array(array('adminmenu.level', '=', 1), array('admin.admin_id', '=', adminModel::getSession()['admin_id'])), 'and'),
        );
        $group = array('adminmenu.name');
        $m_adminmenu1 = Model('adminmenu')->column(array('adminmenu.name', 'adminmenu.up', 'adminmenu.class'))->join($join)->where($where)->group($group)->order(array('adminmenu.sequence' => 'asc'))->fetchAll();

        $m_adminmenu0 = array();
        if ($m_adminmenu1) {
            $a_up = array();
            foreach ($m_adminmenu1 as $v0) {
                $a_up[] = $v0['up'];
            }
            $where = array();
            $where[] = array(array(array('adminmenu_id', 'in', $a_up)), 'and');
            $m_adminmenu0 = Model('adminmenu')->column(array('adminmenu_id', 'name'))->where($where)->order(array('sequence' => 'asc'))->fetchAll();
        }

        $a_menu = array();
        foreach ($m_adminmenu0 as $v1) {
            $tmp1 = array();
            $tmp1['name'] = $v1['name'];
            foreach (array_multiple_search($m_adminmenu1, 'up', $v1['adminmenu_id']) as $v2) {
                $tmp1['down'][] = array('name' => $v2['name'], 'url' => self::url($v2['class'], 'index'));
            }
            $a_menu[] = $tmp1;
        }
        self::$data['a_menu'] = $a_menu;

        //nav
        $nav = array();
        $nav[0] = _('You are here') . '：<a href="' . self::url('index') . '">' . _('Home') . '</a>';
        $m_adminmenu = Model('adminmenu')->where(array(array(array(array('class', '=', M_CLASS)), 'and')))->fetch();
        if (!empty($m_adminmenu)) {
            $nav[2] = '<img src="' . static_file('images/nav_arrow_right.png') . '" /><a href="' . self::url(M_CLASS) . '">' . $m_adminmenu['name'] . '</a>';
            Session::set('tmp', array('adminmenu_name_lv1' => $m_adminmenu['name']));

            $m_adminmenu = Model('adminmenu')->where(array(array(array(array('adminmenu_id', '=', $m_adminmenu['up'])), 'and')))->fetch();
            $nav[1] = '<img src="' . static_file('images/nav_arrow_right.png') . '" /><a href="' . self::url(M_CLASS) . '">' . $m_adminmenu['name'] . '</a>';
            Session::set('tmp', array('adminmenu_name_lv0' => $m_adminmenu['name']));
        }
        ksort($nav);
        self::$data['nav'] = implode('&emsp;', $nav);

        self::$data['admin_name'] = adminModel::getSession()['name'];

        //admingroup switch
        $column = array(
            'admingroup.admingroup_id',
            'admingroup.name',
            'admin_admingroup.class',
        );
        $join = array(
            array('left join', 'admin_admingroup', 'using(admin_id)'),
            array('left join', 'admingroup', 'using(admingroup_id)'),
        );
        $where = array(
            array(array(array('admin.admin_id', '=', adminModel::getSession()['admin_id'])), 'and'),
        );
        $order = array(
            'admingroup.name' => 'asc',
            'admin_admingroup.class' => 'asc',
        );
        $m_admin = Model('admin')->column($column)->join($join)->where($where)->order($order)->fetchAll();
        $a_admingroup_switch = array();
        foreach ($m_admin as $v0) {
            $a_admingroup_switch[] = array(
                'value' => $v0['admingroup_id'],
                'text' => $v0['name'] . ' - ' . $v0['class'],
            );
        }
        list($html, $js) = self::$html->selectKit(['id' => 'admingroup_switch', 'name' => 'admingroup_switch'], $a_admingroup_switch, adminModel::getSession()['lastloginadmingroup_id']);
        $js .= '
		$(function(){
			$("#admingroup_switch").on("change", function() {
				var admingroup_id = $(this).val();
				if (admingroup_id) {
					$.post("' . self::url('index', 'admingroup_switch') . '", {
						admingroup_id: admingroup_id
					}, function(r){
						r = $.parseJSON(r);
						if (r.result) {
							window.location.reload();
						} else {
							alert(r.message);
						}
					});
				}
			});
		});
		';
        self::$data['admingroup_switch'] = $html;
        self::$html->set_js($js);

        //lang switch
        $a_lang_switch = [
            ['value' => 'en_US', 'text' => 'en_US'],
            ['value' => 'zh_TW', 'text' => 'zh_TW'],
        ];
        list($html, $js) = self::$html->selectKit(['id' => 'lang_switch', 'name' => 'lang_switch'], $a_lang_switch, \Core\Lang::get());
        $js .= '$(function(){$("#lang_switch").on("change", function() {if ($(this).val()) location.href = "' . self::url(M_CLASS, M_FUNCTION) . '?lang=" + $(this).val();});});';
        self::$data['lang_switch'] = $html;
        self::$html->set_js($js);

        self::$view['headbar'] = PATH_ROOT . 'module/admin/view/' . SITE_LANG . '/headbar.phtml';
    }

    function foot()
    {
        self::$view['foot'] = PATH_ROOT . 'module/admin/view/' . SITE_LANG . '/foot.phtml';
    }

    function footbar()
    {
        self::$view['footbar'] = PATH_ROOT . 'module/admin/view/' . SITE_LANG . '/footbar.phtml';
    }

    function jquery_set()
    {
        self::$html->set_jquery();
        self::$html->set_jquery_ui();
    }

    function js()
    {
        self::$html->set_js(URL_ROOT . 'js/php.js', 'src');
        list($js_src, $js) = self::$html->get_js();
        self::$data['html_js_src'] = $js_src;
        self::$data['html_js'] = $js;
        self::$view['js_src'] = PATH_ROOT . 'module/admin/view/' . SITE_LANG . '/js_src.phtml';
        self::$view['js'] = PATH_ROOT . 'module/admin/view/' . SITE_LANG . '/js.phtml';
    }

    function logout()
    {
        Session::delete('admin');
    }

    function set_admin($admin_id)
    {
        $m_admin = Model('admin')->where([[[['admin_id', '=', $admin_id]], 'and']])->fetch();

        $a_admin = [
            'admin_id' => $m_admin['admin_id'],
            'name' => $m_admin['name'],
            'lastloginadmingroup_id' => $m_admin['lastloginadmingroup_id'],
        ];

        Session::set('admin', $a_admin);
    }

    static function url($class = 'index', $function = 'index', array $param = null, $package = M_PACKAGE)
    {
        $url = ($package == 'admin') ? URL_ROOT . 'index/admin/' : URL_ROOT . 'index/';
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
}