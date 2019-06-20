<?php

class adController extends backstageController
{
    function __construct()
    {
    }

    function index()
    {
        list($html0, $js0) = parent::$html->grid();
        list($html1, $js1) = parent::$html->browseKit(['selector' => '.grid-img']);
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
            //ad
            $album_id = $_POST['album_id'];
            $event_id = $_POST['event_id'];
            $template_id = $_POST['template_id'];
            $user_id = $_POST['user_id'];
            $name = $_POST['name'];
            $title = $_POST['title'];
            $image = $_POST['image'];
            $image_960x540 = $_POST['image_960x540'];
            $a_url = $_POST['url'];
            $_html = $_POST['html'];
            $_html_mobile = $_POST['html_mobile'];
            $act = $_POST['act'];

            switch ($_GET['act']) {
                //新增
                case 'add':
                    if (adModel::newly()->column(['count(1)'])->where([[[['name', '=', $name]], 'and']])->fetchColumn()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //ad
                    (new \adModel)->add([
                        'album_id' => $album_id,
                        'event_id' => $event_id,
                        'template_id' => $template_id,
                        'user_id' => $user_id,
                        'name' => $name,
                        'title' => $title,
                        'image' => $image,
                        'image_960x540' => $image_960x540,
                        'url' => json_encode($a_url),
                        'html' => $_html,
                        'html_mobile' => $_html_mobile,
                        'act' => $act,
                        'inserttime' => inserttime(),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    if ((new \adModel)->column(['count(1)'])->where([[[[M_CLASS . '_id', '!=', $M_CLASS_id], ['name', '=', $name]], 'and']])->fetchColumn()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //ad
                    (new \adModel)
                        ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])
                        ->edit([
                            'album_id' => $album_id,
                            'event_id' => $event_id,
                            'template_id' => $template_id,
                            'user_id' => $user_id,
                            'name' => $name,
                            'title' => $title,
                            'image' => $image,
                            'image_960x540' => $image_960x540,
                            'url' => json_encode($a_url),
                            'html' => $_html,
                            'html_mobile' => $_html_mobile,
                            'act' => $act,
                            'modifyadmin_id' => adminModel::getSession()['admin_id'],
                        ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始值-form
        $album_id = null;
        $event_id = null;
        $template_id = null;
        $user_id = null;
        $name = null;
        $title = null;
        $image = null;
        $image_960x540 = null;
        $a_url = ['target' => '_self'];
        $_html = null;
        $_html_mobile = null;
        $act = 'close';
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;

        //form
        $column = [];
        $extra = null;

        //tabs
        $a_tabs = [];

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'add']);
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];

                    $m_ad = (new \adModel)
                        ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])
                        ->fetch();

                    $album_id = $m_ad['album_id'];
                    $event_id = $m_ad['event_id'];
                    $template_id = $m_ad['template_id'];
                    $user_id = $m_ad['user_id'];
                    $name = $m_ad['name'];
                    $title = $m_ad['title'];
                    $image = $m_ad['image'];
                    $image_960x540 = $m_ad['image_960x540'];
                    $a_url = json_decode($m_ad['url'], true);
                    $_html = $m_ad['html'];
                    $_html_mobile = $m_ad['html_mobile'];
                    $act = $m_ad['act'];
                    $inserttime = $m_ad['inserttime'];
                    $modifytime = $m_ad['modifytime'];
                    $modifyadmin_name = adminModel::getOne($m_ad['modifyadmin_id'])['name'];
                }

                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'edit']);
                break;
        }

        list($html, $js) = parent::$html->text('id="name" name="name" value="' . $name . '" size="64" maxlength="64" required');
        $column[] = array('key' => _('Name'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->text('id="title" name="title" value="' . $title . '" size="128" maxlength="128"');
        $column[] = array('key' => _('Title'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image" name="image" value="' . $image . '" required');
        $column[] = array('key' => _('Image'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image_960x540" name="image_960x540" value="' . $image_960x540 . '" required', null, 960, 540);
        $column[] = ['key' => _('Image') . ' for App (960 x 540)', 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->urltable('id="url" name="url"', $a_url);
        $column[] = array('key' => _('Url'), 'value' => $html . '<br><span style="color: #222;">WEB/APP共用導引連結設定，如無需導引則留空
APP專屬頁面對應如下，若設定其他網址，APP將開啟WEB VIEW<br><br>
創作人專區&emsp;' . frontstageController::url('creative', 'content', ['user_id' => null]) . '<br><br>
作品資訊頁&emsp;' . frontstageController::url('album', 'content', ['album_id' => null]) . '<br><br>
分類書櫃&emsp;' . frontstageController::url('album', 'explore', ['categoryarea_id' => null]) . '<br><br>
活動資訊頁&emsp;' . frontstageController::url('event', 'content', ['event_id' => null]) . '<br><br>
購買P點畫面&emsp;' . frontstageController::url('user', 'point') . '</span>');
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->htmlEditorKit(['id' => 'html'], $_html);
        $column[] = ['key' => 'Html', 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->htmlEditorKit(['id' => 'html_mobile'], $_html_mobile);
        $column[] = ['key' => 'Html for mobile', 'value' => $html];
        parent::$html->set_js($js);

        $a_act = [];
        foreach (json_decode(Core::settings('AD_ACT'), true) as $k0 => $v0) {
            $a_act[] = [
                'name' => 'act',
                'value' => $k0,
                'text' => $v0,
            ];
        }
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
        $column[] = ['key' => _('Act'), 'value' => $html];
        parent::$html->set_js($js);

        $column[] = ['key' => _('Insert Time'), 'value' => $inserttime];

        $column[] = ['key' => _('Modify Time'), 'value' => $modifytime];

        $column[] = ['key' => _('Modify Admin Name'), 'value' => $modifyadmin_name];

        list($html0, $js0) = parent::$html->submit('value="' . _('Submit') . '"');
        list($html1, $js1) = parent::$html->back('value="' . _('Back') . '"');
        $column[] = array('key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1);
        parent::$html->set_js($js0 . $js1);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[0] = array('href' => '#tabs-0', 'name' => _('Form'), 'value' => $html);
        parent::$html->set_js($js);

        //album
        $column = [];
        $extra = null;

        $where = [
            [[['act', '=', 'open']], 'and'],
        ];
        $m_album = Model('album')->column(['album_id', '`name`'])->where($where)->fetchAll();
        $array0 = [];
        foreach ($m_album as $v0) {
            $array0[] = [
                'value' => $v0['album_id'],
                'text' => $v0['album_id'] . ' - ' . $v0['name'],
            ];
        }
        list($html, $js) = parent::$html->selectKit(['id' => 'album_id', 'name' => 'album_id'], $array0, $album_id);
        $column[] = ['key' => parent::get_adminmenu_name_by_class('album'), 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[1] = ['href' => '#tabs-1', 'name' => parent::get_adminmenu_name_by_class('album'), 'value' => $html];
        parent::$html->set_js($js);

        //event
        $column = [];
        $extra = null;

        $where = [
            [[['act', '=', 'open']], 'and'],
            [[['endtime', '=', '0000-00-00 00:00:00'], ['endtime', '>=', date('Y-m-d H:i:s', time())]], 'or'],
        ];
        $m_event = Model('event')->column(['event_id', '`name`'])->where($where)->fetchAll();
        $array0 = [];
        foreach ($m_event as $v0) {
            $array0[] = [
                'value' => $v0['event_id'],
                'text' => $v0['event_id'] . ' - ' . $v0['name'],
            ];
        }
        list($html, $js) = parent::$html->selectKit(['id' => 'event_id', 'name' => 'event_id'], $array0, $event_id);
        $column[] = ['key' => parent::get_adminmenu_name_by_class('event'), 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[2] = ['href' => '#tabs-2', 'name' => parent::get_adminmenu_name_by_class('event'), 'value' => $html];
        parent::$html->set_js($js);

        //template
        $column = [];
        $extra = null;

        $where = [
            [[['act', '=', 'open']], 'and'],
        ];
        $m_template = Model('template')->column(['template_id', '`name`'])->where($where)->fetchAll();
        $array0 = [];
        foreach ($m_template as $v0) {
            $array0[] = [
                'value' => $v0['template_id'],
                'text' => $v0['template_id'] . ' - ' . $v0['name'],
            ];
        }
        list($html, $js) = parent::$html->selectKit(['id' => 'template_id', 'name' => 'template_id'], $array0, $template_id);
        $column[] = ['key' => parent::get_adminmenu_name_by_class('template'), 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[3] = ['href' => '#tabs-3', 'name' => parent::get_adminmenu_name_by_class('template'), 'value' => $html];
        parent::$html->set_js($js);

        //user
        $column = [];
        $extra = null;

        $where = [
            [[['act', '=', 'open']], 'and'],
        ];
        $m_user = Model('user')->column(['user_id', '`name`'])->where($where)->fetchAll();
        $array0 = [];
        foreach ($m_user as $v0) {
            $array0[] = [
                'value' => $v0['user_id'],
                'text' => $v0['user_id'] . ' - ' . $v0['name'],
            ];
        }
        list($html, $js) = parent::$html->selectKit(['id' => 'user_id', 'name' => 'user_id'], $array0, $user_id);
        $column[] = ['key' => parent::get_adminmenu_name_by_class('user'), 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[4] = ['href' => '#tabs-4', 'name' => parent::get_adminmenu_name_by_class('user'), 'value' => $html];
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
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function delete()
    {
        if (!empty($_POST)) {
            adModel::newly()->where([[[[M_CLASS . '_id', '=', $_POST[M_CLASS . '_id']]], 'and']])->delete();
            json_encode_return(1, _('Success'));
        }
        die;
    }

    function json()
    {
        $response = [];

        //column
        $column = [
            M_CLASS . '_id',
            'album_id',
            'event_id',
            'template_id',
            'user_id',
            'name',
            'title',
            'image',
            'url',
            'act',
            'modifytime',
        ];

        list($where, $group, $order, $limit) = parent::grid_request_encode();

        //data
        $fetchAll = adModel::newly()->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
        foreach ($fetchAll as &$v0) {
            $v0['albumX'] = parent::get_grid_display('album', $v0['album_id']);

            $v0['eventX'] = parent::get_grid_display('event', $v0['event_id']);

            $v0['templateX'] = parent::get_grid_display('template', $v0['template_id']);

            $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);

            if (!empty($v0['image'])) {
                $v0['image'] = parent::get_gird_img(['alt' => $v0['name'], 'src' => $v0['image']]);
            }

            $tmp0 = [];
            foreach (json_decode($v0['url'], true) as $k1 => $v1) {
                $tmp0[] = $k1 . ': ' . $v1;
            }
            $v0['url'] = implode("<br>", $tmp0);
        }
        $response['data'] = $fetchAll;

        //total
        $response['total'] = adModel::newly()->column(['count(1)'])->where($where)->group($group)->fetchColumn();
        die(json_encode($response));
    }
}