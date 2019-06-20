<?php

class adarea_adController extends backstageController
{
    function __construct()
    {
    }

    function index()
    {
        list($html, $js) = parent::$html->grid();
        parent::$data['index'] = $html;
        parent::$html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function form()
    {
        if (is_ajax()) {
            //form
            $adarea_id = $_POST['adarea_id'];
            $ad_id = $_POST['ad_id'];
            $lang_id = $_POST['lang_id'];
            $sequence = $_POST['sequence'];
            $act = $_POST['act'];

            switch ($_GET['act']) {
                //新增
                case 'add':
                    if ((new \adarea_adModel)->column(['count(1)'])->where([[[['adarea_id', '=', $adarea_id], ['ad_id', '=', $ad_id], ['lang_id', '=', $lang_id]], 'and']])->fetchColumn()) {
                        json_encode_return(0, _('Data already exists by : ') . parent::get_adminmenu_name_by_class('adarea') . ' & ' . parent::get_adminmenu_name_by_class('ad') . ' & ' . parent::get_adminmenu_name_by_class('lang'));
                    }

                    //adarea_ad
                    (new \adarea_adModel)->add([
                        'adarea_id' => $adarea_id,
                        'ad_id' => $ad_id,
                        'lang_id' => $lang_id,
                        'sequence' => $sequence,
                        'act' => $act,
                        'inserttime' => inserttime(),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    //adarea_ad
                    (new \adarea_adModel)->replace([
                        'adarea_id' => $adarea_id,
                        'ad_id' => $ad_id,
                        'lang_id' => $lang_id,
                        'sequence' => $sequence,
                        'act' => $act,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        $Image = new \Core\Image;

        //初始值-form
        $adarea_id = null;
        $ad_id = null;
        $lang_id = \Core\Lang::$default;
        $sequence = 255;
        $act = 'close';
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;

        //table-form
        $column = [];
        $extra = null;

        //tabs
        $a_tabs = array();

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                //adarea
                $m_adarea = (new \adareaModel)->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                $tmp0 = [];
                foreach ($m_adarea as $v0) {
                    $tmp0[] = [
                        'value' => $v0['adarea_id'],
                        'text' => $v0['adarea_id'] . ' - ' . parent::get_area_level_format_string('adarea', $v0['adarea_id']),
                    ];
                }
                list($html, $js) = parent::$html->selectKit(['id' => 'adarea_id', 'name' => 'adarea_id'], $tmp0);
                $html_adarea = $html;
                parent::$html->set_js($js);

                //ad
                $m_ad = (new \adModel)->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                $tmp0 = [];
                foreach ($m_ad as $v0) {
                    if (is_file(PATH_UPLOAD . $v0['image'])) {
                        $tmp0[] = [
                            'value' => $v0['ad_id'],
                            'text' => $v0['ad_id'] . ' - ' . $v0['name'],
                            'attribute' => [
                                'data-img-src' => fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize()->save())['url'],
                            ],
                        ];
                    }
                }
                list($html, $js) = parent::$html->selectKit(['id' => 'ad_id', 'name' => 'ad_id'], $tmp0);
                $html_ad = $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'add']);
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $adarea_id = $_GET['adarea_id'];
                    $ad_id = $_GET['ad_id'];
                    $lang_id = $_GET['lang_id'];

                    $m_adarea_ad = (new \adarea_adModel)->where([[[['adarea_id', '=', $adarea_id], ['ad_id', '=', $ad_id], ['lang_id', '=', $lang_id]], 'and']])->fetch();

                    //adarea_ad
                    $sequence = $m_adarea_ad['sequence'];
                    $act = $m_adarea_ad['act'];
                    $inserttime = $m_adarea_ad['inserttime'];
                    $modifytime = $m_adarea_ad['modifytime'];
                    $modifyadmin_name = adminModel::getOne($m_adarea_ad['modifyadmin_id'])['name'];

                    //adarea
                    list($html, $js) = parent::$html->hidden('id="adarea_id" name="adarea_id" value="' . $adarea_id . '"');
                    $html_adarea = parent::get_grid_display('adarea', $adarea_id) . $html;
                    parent::$html->set_js($js);

                    //ad
                    list($html, $js) = parent::$html->hidden('id="ad_id" name="ad_id" value="' . $ad_id . '"');
                    $html_ad = parent::get_grid_display('ad', $ad_id) . $html;
                    parent::$html->set_js($js);
                }

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
                break;
        }

        $column[] = array('key' => parent::get_adminmenu_name_by_class('adarea'), 'value' => $html_adarea);

        $column[] = array('key' => parent::get_adminmenu_name_by_class('ad'), 'value' => $html_ad);

        list($html, $js) = parent::$html->selectKit(['id' => 'lang_id', 'name' => 'lang_id'], parent::get_form_select('lang'), $lang_id);
        $column[] = array('key' => parent::get_adminmenu_name_by_class('lang'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="' . $sequence . '" min="0" max="255" required');
        $column[] = array('key' => _('Sequence'), 'value' => $html);
        parent::$html->set_js($js);

        $a_act = array();
        foreach (json_decode(Core::settings('ADAREA_AD_ACT'), true) as $k0 => $v0) {
            $a_act[] = array(
                'name' => 'act',
                'value' => $k0,
                'text' => $v0,
            );
        }
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
        $column[] = array('key' => _('Act'), 'value' => $html);
        parent::$html->set_js($js);

        $column[] = array('key' => _('Insert Time'), 'value' => '<span id="inserttime">' . $inserttime . '</span>');

        $column[] = array('key' => _('Modify Time'), 'value' => '<span id="modifytime">' . $modifytime . '</span>');

        $column[] = array('key' => _('Modify Admin Name'), 'value' => '<span id="modifyadmin_id">' . $modifyadmin_name . '</span>');

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
            'adarea_id',
            'ad_id',
            'lang_id',
            'sequence',
            'act',
            'modifytime',
        );

        list($where, $group, $order, $limit) = parent::grid_request_encode();

        //data
        $fetchAll = adarea_adModel::newly()->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
        foreach ($fetchAll as &$v0) {
            $v0['adareaX'] = parent::get_grid_display('adarea', $v0['adarea_id']);
            $v0['adX'] = parent::get_grid_display('ad', $v0['ad_id']);
        }
        $response['data'] = $fetchAll;

        //total
        $response['total'] = adarea_adModel::newly()->column(array('count(1)'))->where($where)->group($group)->fetchColumn();

        die(json_encode($response));
    }

    function grid_edit()
    {
        if (!empty($_REQUEST['models'])) {
            foreach ($_REQUEST['models'] as $v0) {
                adarea_adModel::newly()->where(array(array(array(array('adarea_id', '=', (int)$v0['adarea_id']), array('ad_id', '=', (int)$v0['ad_id']), array('lang_id', '=', $v0['lang_id'])), 'and')))->edit(array('sequence' => $v0['sequence'], 'modifyadmin_id' => adminModel::getSession()['admin_id']));
            }
            json_encode_return(1, 'Edit success.');
        }
        die;
    }

    //取得指定 lang_id 的值
    function extra0()
    {
        if (is_ajax()) {
            $m_adarea_ad = adarea_adModel::newly()->where(array(array(array(array('adarea_id', '=', $_POST['adarea_id']), array('ad_id', '=', $_POST['ad_id']), array('lang_id', '=', $_POST['lang_id'])), 'and')))->fetch();
            $data = array(
                'sequence' => $m_adarea_ad['sequence'],
                'act' => $m_adarea_ad['act'],
                'inserttime' => $m_adarea_ad['inserttime'],
                'modifytime' => $m_adarea_ad['modifytime'],
                'modifyadmin_id' => adminModel::getOne($m_adarea_ad['modifyadmin_id'])['name'],
            );
            json_encode_return(1, null, null, $data);
        }
        die();
    }
}