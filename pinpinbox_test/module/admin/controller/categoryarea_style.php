<?php

class categoryarea_styleController extends backstageController
{
    function __construct()
    {
    }

    function getUsers()
    {
        //條件: user至少需要有相本, 相本數量先不設限
        $column = ['COUNT(album.album_id) as album_count', 'user.user_id', 'user.name as user_name'];
        $where = [[[['user.act', '=', 'open'], ['album.act', '=', 'open']], 'and']];
        $join = [['left join', 'user', 'using(user_id)']];
        $group = ['user_id'];
        $m_user = Model('album')->column($column)->where($where)->join($join)->group($group)->fetchAll();

        return $m_user;
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
            $categoryarea_id = $_POST['categoryarea_id'];
            $banner_type = $_POST['banner_type'];
            $banner_type_data = $_POST['banner_type_data'];
            $image = $_POST['image'];
            $sequence = $_POST['sequence'];
            $act = $_POST['act'];

            switch ($_GET['act']) {
                //新增
                case 'add':
                    $M_CLASS_id = Model(M_CLASS)->add([
                        'categoryarea_id' => $categoryarea_id,
                        'banner_type' => $banner_type,
                        'banner_type_data' => $banner_type_data,
                        'image' => $image,
                        'sequence' => $sequence,
                        'act' => $act,
                        'inserttime' => inserttime(),
                    ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    //form
                    Model(M_CLASS)
                        ->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))
                        ->edit([
                            'categoryarea_id' => $categoryarea_id,
                            'banner_type' => $banner_type,
                            'banner_type_data' => $banner_type_data,
                            'image' => $image,
                            'sequence' => $sequence,
                            'act' => $act,
                        ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始值-from
        $categoryarea_style_id = null;
        $categoryarea_id = null;
        $banner_type = 'creatvie';
        $image = null;
        $banner_type_data = null;
        $sequence = 1;
        $act = 'close';
        $inserttime = null;
        $modifytime = null;
        $layout = null;

        //form
        $column = array();
        $extra = null;

        //tabs
        $a_tabs = array();

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'add'));
                parent::$data['banner_type'] = $banner_type;
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];
                    $m_categoryarea_style = Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetch();

                    //form
                    $categoryarea_style_id = $m_categoryarea_style['categoryarea_style_id'];
                    $categoryarea_id = $m_categoryarea_style['categoryarea_id'];
                    $banner_type = $m_categoryarea_style['banner_type'];
                    $image = $m_categoryarea_style['image'];
                    $banner_type_data = json_decode($m_categoryarea_style['banner_type_data'], true);
                    $act = $m_categoryarea_style['act'];
                    $sequence = $m_categoryarea_style['sequence'];
                    $inserttime = $m_categoryarea_style['inserttime'];
                    $modifytime = $m_categoryarea_style['modifytime'];
                }

                parent::$data['banner_type'] = $banner_type;

                switch ($banner_type) {
                    case 'creative' :
                        //條件: user至少需要有相本, 相本數量先不設限
                        $m_user = $this->getUsers();

                        $layout .= '<select id="creative_id" multiple><option value="">' . _('Please select') . '</option> ';
                        if ($banner_type_data) {
                            foreach ($m_user as $k => $v0) {
                                $selected = (in_array($v0['user_id'], $banner_type_data)) ? 'selected="selected"' : null;
                                $layout .= '<option data-img-src="' . URL_STORAGE . Core::get_userpicture($v0['user_id']) . '" value="' . $v0['user_id'] . '" ' . $selected . '>' . $v0['user_id'] . ' - ' . $v0['user_name'] . '</option> ';
                            }
                        }
                        $layout .= '</select>';
                        break;

                    case 'image' :
                        list($html, $js) = parent::$html->text('id="url" name="url" value="' . $banner_type_data['url'] . '" size="64" maxlength="64" required placeholder="url"');
                        list($html2, $js) = parent::$html->text('id="btntext" name="btntext" value="' . $banner_type_data['btntext'] . '" size="20" placeholder="按鈕文字 (不超過四個字)"');
                        $layout = $html . '<br /><br />' . $html2;
                        break;

                    case 'video' :
                        $autoCheckBox = ($banner_type_data['auto'] == true) ? 'checked="checked"' : null;
                        $muteCheckBox = ($banner_type_data['mute'] == true) ? 'checked="checked"' : null;
                        $repeatCheckBox = ($banner_type_data['repeat'] == true) ? 'checked="checked"' : null;

                        $attribute = '<input id="checkBox" id="auto" name="auto" ' . $autoCheckBox . ' type="checkbox"><label for="auto">Auto</label>&nbsp;&nbsp;
								  <input id="checkBox" id="mute" name="mute" ' . $muteCheckBox . ' type="checkbox"><label for="mute">Mute</label>&nbsp;&nbsp;
								  <input id="checkBox" id="repeat" name="repeat" ' . $repeatCheckBox . ' type="checkbox"><label for="repeat">Repeat</label>';
                        list($html, $js) = parent::$html->text('id="url" name="url" value="' . $banner_type_data['url'] . '" size="16" maxlength="64" required placeholder="video-id"');
                        list($html2, $js) = parent::$html->text('id="link" name="link" value="' . urldecode($banner_type_data['link']) . '" size="50" placeholder="link"');
                        list($html3, $js) = parent::$html->text('id="btntext" name="btntext" value="' . $banner_type_data['btntext'] . '" size="50" placeholder="按鈕文字 (不超過四個字)"');
                        list($html4, $js) = parent::$html->text('id="videotext" name="videotext" value="' . $banner_type_data['videotext'] . '" size="50" placeholder="影片標題 (不超過15個字)"');
                        $layout = 'https://www.youtube.com/embed/' . $html . '<br /><br />' . $html2 . '<br /><br />' . $html3 . '<br /><br />' . $html4 . '<br /><br />' . $attribute;
                        break;

                }

                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
                break;
        }

        $a_categoryarea_id = array_merge([['value' => 0, 'text' => '熱門主題']], parent::get_form_select('categoryarea'));
        list($html, $js) = parent::$html->selectKit(['id' => 'categoryarea_id', 'name' => 'categoryarea_id'], $a_categoryarea_id, $categoryarea_id);
        $column[] = ['key' => _('類別區域'), 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->selectKit(['id' => 'banner_type', 'name' => 'banner_type'], parent::get_form_select('banner_type'), $banner_type);
        $column[] = ['key' => _('樣式型態'), 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image" name="image" value="' . $image . '" required');
        $column[] = array('key' => _('背景圖') . ' (960 x 540)', 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = array('<div id="layout">' . $layout . '</div>', null);
        $column[] = ['key' => '關聯資料', 'value' => $html];
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
        parent::$html->set_js(static_file('js/Image-Select/js/ImageSelect.jquery.js'), 'src');

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
            'categoryarea_style_id',
            'categoryarea_id',
            'banner_type',
            'image',
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

    function style_type()
    {
        if (is_ajax()) {
            $value = (!empty($_POST['value'])) ? $_POST['value'] : null;
            $data = '';

            switch ($value) {
                case 'creative':

                    $m_user = $this->getUsers();
                    $data .= '<select id="creative_id" multiple><option value="">' . _('Please select') . '</option> ';
                    foreach ($m_user as $k => $v0) {
                        $selected = null;
                        $data .= '<option data-img-src="' . URL_STORAGE . Core::get_userpicture($v0['user_id']) . '" value="' . $v0['user_id'] . '" ' . $selected . '>' . $v0['user_id'] . ' - ' . $v0['user_name'] . '</option> ';
                    }
                    $data .= '</select>';
                    break;

                case 'image' :
                    list($html, $js) = parent::$html->text('id="url" name="url" value="" size="64" maxlength="64" required placeholder="video-id"');
                    list($html2, $js) = parent::$html->text('id="btntext" name="btntext" value="" size="20" placeholder="按鈕文字 (不超過四個字)"');
                    $data = $html . '<br /><br />' . $html2;
                    break;

                case 'video' :
                    list($html, $js) = parent::$html->text('id="url" name="url" value="" size="16" required placeholder="video-id"');
                    list($html2, $js) = parent::$html->text('id="link" name="link" value="" size="50" placeholder="link"');
                    list($html3, $js) = parent::$html->text('id="btntext" name="btntext" value="" size="50" placeholder="按鈕文字 (不超過4個字)"');
                    list($html4, $js) = parent::$html->text('id="videotext" name="videotext" value="" size="50" placeholder="影片標題 (不超過15個字)"');
                    $attribute = '<input id="checkBox" id="auto" name="auto" type="checkbox"><label for="auto">Auto</label>&nbsp;
								  <input id="checkBox" id="mute" name="mute" type="checkbox"><label for="mute">Mute</label>&nbsp;&nbsp;
								  <input id="checkBox" id="repeat" name="repeat" type="checkbox"><label for="repeat">Repeat</label>';
                    $data = 'https://www.youtube.com/embed/' . $html . '<br /><br />' . $html2 . '<br /><br />' . $html3 . '<br /><br />' . $html4 . '<br /><br />' . $attribute;
                    break;
            }

            json_encode_return(1, null, null, $data);
        }
    }
}