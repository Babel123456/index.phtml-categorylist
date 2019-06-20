<?php

class indexpopularityController extends backstageController
{
    function __construct()
    {
    }

    function user_search()
    {
        if (is_ajax()) {
            $user_id = (!empty($_POST['user_id'])) ? $_POST['user_id'] : null;

            if ($user_id === null) json_encode_return(0, _('未輸入相本id或輸入錯誤,請重新確認'), null, null);

            $user = [
                'user_id' => $user_id,
                'filename' => PATH_STORAGE . Core::get_usercover($user_id),
                'urlFilename' => URL_STORAGE.Core::get_usercover($user_id),
            ];

            if (!empty($user)) {
                json_encode_return(1, null, null, ($user));
            } else {
                json_encode_return(0, _('創作人資料異常'), null, null);
            }
        }
    }

    function index()
    {
        list($html0, $js0) = parent::$html->grid();
        list($html1, $js1) = parent::$html->browseKit(array('selector' => '.grid-img'));
        parent::$data['index'] = $html0 . $html1;
        parent::$html->set_js($js0 . $js1);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function form()
    {
        if (is_ajax()) {

            //form
            $name = $_POST['name'];
            $sequence = $_POST['sequence'];
            $act = $_POST['act'];
            $exhibit = json_decode($_POST['exhibit'], true);

            $indexpopularity_exhibit = [];        //放在 Model::indexpopularity 的 exhibit的值 (array)
            foreach ($exhibit as $k0 => $v0) {
                $indexpopularity_exhibit[] = $v0[0];
            }

            switch ($_GET['act']) {
                //新增
                case 'add':
                    if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    $add = array(
                        'name' => $name,
                        'sequence' => $sequence,
                        'act' => $act,
                        'exhibit' => json_encode($indexpopularity_exhibit),
                        'inserttime' => inserttime(),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    );
                    $M_CLASS_id = Model(M_CLASS)->add($add);
                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    if (Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //form
                    $edit = array(
                        'name' => $name,
                        'sequence' => $sequence,
                        'act' => $act,
                        'exhibit' => json_encode($indexpopularity_exhibit),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    );
                    Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->edit($edit);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始值-from
        $name = null;
        $sequence = null;
        $act = 'close';
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;
        $exhibit = null;

        //form
        $column = array();
        $extra = null;

        $a_user = array();
        $user_id = null;


        $activeCreators = Model('creative')->getActiveCreator();
        $c = ['user.name user_name', 'user.user_id' ,'userstatistics.viewed user_viewed'];
        $join = [['left join', 'userstatistics', 'using(user_id)']];
        $m_user = Model('user')->column($c)->join($join)->where([[[['user.act', '=', 'open'], ['user.creative', '=', 1], ['user.user_id', 'in', $activeCreators]], 'and']])->fetchAll();

        foreach($m_user as $k0 => $v0) {
            $s_album = Model('album')->column(['sum(albumstatistics.count) as count', 'sum(albumstatistics.viewed) as viewed'])->join([['left join', 'albumstatistics', 'using(album_id)']])->where([[[['album.user_id', '=', $v0['user_id']],['album.act', '=', 'open']], 'and']])->fetch();


            $a_user[] = array(
                    'value'=>$v0['user_id'],
                    'text'=>$v0['user_id'].' - '.$v0['user_name']. ' - ('.$s_album['viewed'].')',
                    'attribute' => [
                        'data-img-src' => URL_STORAGE.Core::get_userpicture($v0['user_id']),
                    ],
            );
        }

        //tabs
        $a_tabs = array();

        //layout
        $layout = '<div id="elementlayout">
					<ul id="sort">
						<li data-status="unset" id="item1"><img></li>
						<li data-status="unset" id="item2"><img></li>
						<li data-status="unset" id="item3"><img></li>
						<li data-status="unset" id="item4"><img></li>
						<li data-status="unset" id="item5"><img></li>
						<li data-status="unset" id="item6"><img></li>
					</ul>
				</div>';

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'add'));
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];
                    $m_indexpopularity = Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetch();

                    //form
                    $name = $m_indexpopularity['name'];
                    $act = $m_indexpopularity['act'];
                    $exhibit = json_decode($m_indexpopularity['exhibit']);
                    $sequence = $m_indexpopularity['sequence'];
                    $inserttime = $m_indexpopularity['inserttime'];
                    $modifytime = $m_indexpopularity['modifytime'];
                    $modifyadmin_name = adminModel::getOne($m_indexpopularity['modifyadmin_id'])['name'];
                }

                //fetch user info
                $m_user = Model('user')->column(['user_id', 'name'])->where([[[['act', '=', 'open'], ['user_id', 'in', $exhibit]] ,'and']])->fetchAll();

                foreach ($m_user as $k0 => $user) {
                  $tmp[] = '<li id="item' . ($k0 + 1) . '" data-status="set" data-urlfilename="' . PATH_STORAGE . Core::get_usercover($user['user_id']) . '" data-user_id="' . $user['user_id'] . '"><img src="' . URL_STORAGE . Core::get_usercover($user['user_id']) . '"></li>';
                }


                $layout = '<div id="elementlayout"><ul id="sort">' . implode('', $tmp) . '</ul></div>';

                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
                break;
        }

        list($html, $js) = parent::$html->text('id="name" name="name" value="' . $name . '" size="64" maxlength="64" required');
        $column[] = array('key' => _('Name'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->selectKit(['id'=>'indexcreative', 'name'=>'indexcreative'], $a_user, $user_id);
        $column[] = ['key'=>'選擇職人編號', 'value'=>$html];
        $js = null;
        parent::$html->set_js($js);

        list($html, $js) = array($layout, null);
        $column[] = array('key' => _('Exhibit'), 'value' => $html, 'key_remark' => _('首頁展示內容'));
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="' . $sequence . '" min="0" max="255" required');
        $column[] = array('key' => _('Sequence'), 'value' => $html);
        parent::$html->set_js($js);

        $a_act = array();
        foreach (json_decode(Core::settings('EVENT_ACT'), true) as $k0 => $v0) {
            $a_act[] = array(
                'name' => 'act',
                'value' => $k0,
                'text' => $v0,
            );
        }
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
        $column[] = array('key' => _('Act'), 'value' => $html);
        parent::$html->set_js($js);

        $column[] = array('key' => _('Insert Time'), 'value' => $inserttime);

        $column[] = array('key' => _('Modify Time'), 'value' => $modifytime);

        $column[] = array('key' => _('Modify Admin Name'), 'value' => $modifyadmin_name);

        list($html0, $js0) = parent::$html->submit('value="' . _('Submit') . '"');
        list($html1, $js1) = parent::$html->back('value="' . _('Back') . '"');
        $column[] = array('key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1);
        parent::$html->set_js($js0 . $js1);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[0] = array('href' => '#tabs-0', 'name' => _('Form'), 'value' => $html);
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
        parent::$html->set_css(static_file('js/croppie/css/croppie.css'), 'href');
        parent::$html->set_js(static_file('js/Image-Select/js/ImageSelect.jquery.js'), 'src');
        parent::$html->set_js(static_file('js/croppie/js/croppie.js'), 'src');

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function delete()
    {
        die;
    }

    function json()
    {
        $response = array();

        //column
        $column = array(
            M_CLASS . '_id',
            'name',
            'sequence',
            'act',
            'inserttime',
            'modifytime',
        );

        list($where, $group, $order, $limit) = parent::grid_request_encode();

        //data
        $fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();

        $response['data'] = $fetchAll;

        //total
        $response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();


        die(json_encode($response));
    }

    function grid_edit()
    {
        if (!empty($_REQUEST)) {
            Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', (int)$_REQUEST[M_CLASS . '_id'])), 'and')))->edit(array('sequence' => $_REQUEST['sequence'], 'modifyadmin_id' => adminModel::getSession()['admin_id']));

            json_encode_return(1, 'Edit success.');
        }
        die;
    }
}