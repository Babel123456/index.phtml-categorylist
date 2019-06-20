<?php

class categoryareaController extends backstageController
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
            $name = $_POST['name'];
            $sequence = $_POST['sequence'];
            $act = $_POST['act'];
            $gearing_act = $_POST['gearing_act'];
            $image = $_POST['image'];
            $image_n = $_POST['image_n'];
            $image_204x204 = $_POST['image_204x204'];
            $image_360x360 = $_POST['image_360x360'];

            switch ($_GET['act']) {
                //新增
                case 'add':
                    $level = $_POST['level'];
                    $up = $_POST['up'];

                    if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //form
                    Model(M_CLASS)->add([
                        'name' => $name,
                        'image' => $image,
                        'image_n' => $image_n,
                        'image_204x204' => $image_204x204,
                        'image_360x360' => $image_360x360,
                        'level' => $level,
                        'up' => $up,
                        'sequence' => $sequence,
                        'act' => $act,
                        'inserttime' => inserttime(),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    if (Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //form
                    Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->edit([
                        'name' => $name,
                        'image' => $image,
                        'image_n' => $image_n,
                        'image_204x204' => $image_204x204,
                        'image_360x360' => $image_360x360,
                        'sequence' => $sequence,
                        'act' => $act,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ]);

                    /**
                     *  160713 - act 開關應該與 table::categoryarea_category 連動
                     *             前台在進行 categoryarea<->category比對時才不會相異
                     */
                    if($gearing_act) {
						Model('categoryarea_category')->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])->edit([
							'act' => $act,
						]);
					}

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始值-form
        $name = null;
        $image = null;
        $image_n = null;
        $image_204x204 = null;
        $image_360x360 = null;
        $level = 0;
        $up = null;
        $sequence = 255;
        $act = 'close';
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;

        //table-form
        $column = array();
        $extra = null;

        //tabs
        $a_tabs = array();

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                //不能將資料在修改時更動在自身層級路徑中的層級(不論向上向下), 否則會造成無限迴圈, 因此只能在新增時操作, 修改時則不行; 另一考量是統計方面, area 在建立後就應該不能搬移
                list($html, $js) = parent::$html->selectKit(['id' => 'level', 'name' => 'level', 'onchange' => 'changelevel()'], array(array('value' => 0, 'text' => 0), array('value' => 1, 'text' => 1)));
                $html_level = $html;
                $js .= "
				function changelevel() {
					var level = $('#level'), up = $('#up');
					up.prop('disabled', true).val('').trigger('chosen:updated');
					if (parseInt(level.val(), 10) > 0) {
						$.post('" . parent::url(M_CLASS, 'get_area_up') . "', {
							'level': parseInt(level.val(), 10) - parseInt(1, 10)
						}, function(r){
							r = $.parseJSON(r);
							if (r.result) {
								up.empty().append('<option value=\"\">" . _('Please select') . "</option>');
		   						$.each(r.data, function(k0, v0){
									up.append('<option value=\"'+ k0 +'\">'+ v0 +'</option>');
								});
								up.prop('disabled', false).trigger('chosen:updated');
							} else {
								formerror(r.message);
							}
						});
					}
				}
				$(function(){changelevel();});
				";
                parent::$html->set_js($js);

                $tmp0 = array();
                foreach ($this->get_area_up(array(array(array(array('level', '=', $level - 1)), 'and'))) as $k0 => $v0) {
                    $tmp0[] = array(
                        'value' => $k0,
                        'text' => $v0,
                    );
                }
                list($html, $js) = parent::$html->selectKit(['id' => 'up', 'name' => 'up'], $tmp0);
                $html_up = $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'add'));
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];

                    $m_categoryarea = Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetch();

                    //form
                    $name = $m_categoryarea['name'];
                    $image = $m_categoryarea['image'];
                    $image_n = $m_categoryarea['image_n'];
                    $image_204x204 = $m_categoryarea['image_204x204'];
                    $image_360x360 = $m_categoryarea['image_360x360'];
                    $level = $m_categoryarea['level'];
                    $up = $m_categoryarea['up'];
                    $sequence = $m_categoryarea['sequence'];
                    $act = $m_categoryarea['act'];
                    $inserttime = $m_categoryarea['inserttime'];
                    $modifytime = $m_categoryarea['modifytime'];
                    $modifyadmin_name = adminModel::getOne($m_categoryarea['modifyadmin_id'])['name'];
                }
                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                $html_level = $level;
                $html_up = parent::get_area_level_format_string(M_CLASS, $up);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
                break;
        }

        list($html, $js) = parent::$html->text('id="name" name="name" value="' . $name . '" maxlength="32" size="32" required');
        $column[] = array('key' => _('Name'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image" name="image" value="' . $image . '" required', null);
        $column[] = ['key' => _('Image') . ' 選擇分類時', 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image_n" name="image_n" value="' . $image_n . '" required', null);
        $column[] = ['key' => _('Image') . ' 未選擇分類時', 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image_204x204" name="image_204x204" value="' . $image_204x204 . '" required', null, 204, 204);
        $column[] = ['key' => _('Image') . ' for 新版首頁 (204 x 204)', 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image_360x360" name="image_360x360" value="' . $image_360x360 . '" required', null, 360, 360);
        $column[] = ['key' => _('Image') . ' for App (360 x 360)', 'value' => $html];
        parent::$html->set_js($js);

        $column[] = array('key' => _('Level'), 'value' => $html_level);

        $column[] = array('key' => _('Up'), 'value' => $html_up);

        list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="' . $sequence . '" min="0" max="255" required');
        $column[] = array('key' => _('Sequence'), 'value' => $html);
        parent::$html->set_js($js);

        $a_act = array();
        foreach (json_decode(Core::settings('CATEGORYAREA_ACT'), true) as $k0 => $v0) {
            $a_act[] = array(
                'name' => 'act',
                'value' => $k0,
                'text' => $v0,
            );
        }
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
        $column[] = array('key' => _('Act'), 'value' => $html);
        parent::$html->set_js($js);

		$a_gearing_act = [[
			'name' => 'gearing_act',
			'value' => true,
			'text' => 'Yes',
		],[
			'name' => 'gearing_act',
			'value' => false,
			'text' => 'No',
		]];
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_gearing_act, false);
        $column[] = array('key' => _('Gearing Act'), 'value' => $html, 'key_remark' => '<p>是否連動下層的 Act 狀態</p><p style="color:red;">(請謹慎使用)</p>');
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
            'level',
            'sequence',
            'act',
            'modifytime',
            'modifyadmin_id',
        );

        list($where, $group, $order, $limit) = parent::grid_request_encode();

        //data
        $fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
        foreach ($fetchAll as &$v0) {
            $v0['name'] = parent::get_area_level_format_string(M_CLASS, $v0[M_CLASS . '_id']);
            $v0['modifyadmin_id'] = adminModel::getOne($v0['modifyadmin_id'])['name'];
        }
        $response['data'] = $fetchAll;

        //total
        $response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();

        die(json_encode($response));
    }

    function get_area_up(array $where = null)
    {
        if (is_ajax()) {
            $where = array();
            if (isset($_POST['level'])) $where[] = array(array(array('level', '=', (int)$_POST['level'])), 'and');
        }
        $a_up = array();
        if (!empty($where)) {
            foreach (Model(M_CLASS)->where($where)->fetchAll() as $v0) {
                $a_up[$v0[M_CLASS . '_id']] = parent::get_area_level_format_string(M_CLASS, $v0[M_CLASS . '_id']);
            }
        }
        if (is_ajax()) {
            json_encode_return(1, _('Success'), null, $a_up);
        } else {
            return $a_up;
        }
    }

    function grid_edit()
    {
        if (!empty($_REQUEST['models'])) {
            foreach ($_REQUEST['models'] as $v0) {
                Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', (int)$v0[M_CLASS . '_id'])), 'and')))->edit(array('sequence' => $v0['sequence'], 'modifyadmin_id' => adminModel::getSession()['admin_id']));
            }
            json_encode_return(1, 'Edit success.');
        }
        die;
    }
}