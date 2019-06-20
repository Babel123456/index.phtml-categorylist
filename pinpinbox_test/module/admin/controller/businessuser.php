<?php

class businessuserController extends backstageController
{
    function __construct()
    {
    }

    function delete()
    {
        die;
    }

    function form()
    {
        if (is_ajax()) {
            switch ($_GET['act']) {
                //新增
                case 'add':
                    $account = $_POST['account'];
                    $birthday = $_POST['birthday'];
                    $cellphone = ($_POST['cellphone'] === '') ? '+886' . random_password(9, 's') : $_POST['cellphone'];
                    $enabled = $_POST['enabled'];
                    $gender = $_POST['gender'];
                    $mode = $_POST['mode'];
                    $name = $_POST['name'];
                    $password = $_POST['password'];
                    $smspassword = random_password(4, 's');

                    //2017-01-03 Lion: 直接給值, 繞過判斷
                    (new smspasswordModel)->replace([
                        'user_account' => $account,
                        'user_cellphone' => $cellphone,
                        'smspassword' => $smspassword,
                    ]);

                    $param = [
                        'account' => $account,
                        'act' => $enabled ? 'open' : 'close',
                        'birthday' => $birthday,
                        'cellphone' => $cellphone,
                        'enabled' => $enabled,
                        'gender' => $gender,
                        'mode' => $mode,
                        'name' => $name,
                        'password' => $password,
                        'smspassword' => $smspassword,
                        'way' => 'none',
                    ];

                    list ($result, $message) = array_decode_return((new \businessuser\Model())->ableToInsert($param));
                    if ($result != \Lib\Result::SYSTEM_OK) {
                        goto _return;
                    }

                    (new \Model)->beginTransaction();

                    (new \businessuser\Model())->insertBusinessUser($param);

                    (new \Model)->commit();
                    break;

                //修改
                case 'edit':
                    $businessuser_id = $_POST['businessuser_id'];
                    $enabled = $_POST['enabled'];

                    (new \businessuser\Model())
                        ->where([[[['businessuser_id', '=', $businessuser_id]], 'and']])
                        ->edit([
                            'enabled' => $enabled,
                            'modifyadmin_id' => adminModel::getSession()['admin_id'],
                        ]);
                    break;
            }

            $result = \Lib\Result::SYSTEM_OK;
            $message = _('Success, back to previous page?');
            $redirect = parent::url(M_CLASS, 'index');

            _return:
            json_encode_return($result, $message, isset($redirect) ? $redirect : null);
        }

        $Html = new \Lib\html();

        //form
        $column = [];
        $extra = null;

        //tabs
        $a_tabs = [];

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                list ($html, $js) = $Html->email(['id' => 'account', 'name' => 'account', 'size' => 128, 'maxlength' => 128, 'required' => true]);
                $column[] = ['key' => _('Account'), 'value' => $html];
                $Html->set_js($js);

                list ($html, $js) = $Html->password(['id' => 'password', 'name' => 'password', 'required' => true]);
                $column[] = ['key' => _('Password'), 'value' => $html];
                $Html->set_js($js);

                list ($html, $js) = $Html->password(['id' => 'repassword', 'name' => 'repassword']);
                $column[] = ['key' => _('Re Password'), 'value' => $html];
                $Html->set_js($js);

                list ($html, $js) = $Html->text('id="name" name="name" size="64" maxlength="64" required');
                $column[] = ['key' => _('Name'), 'value' => $html];
                $Html->set_js($js);

                $modeArray = [];
                foreach (\Schema\businessuser::$mode as $k_0 => $v_0) {
                    $modeArray[] = [
                        'name' => 'mode',
                        'value' => $k_0,
                        'text' => $v_0,
                    ];
                }
                list ($html, $js) = $Html->radiotable('150px', '30px', 5, $modeArray, null, true);
                $column[] = ['key' => _('Mode'), 'value' => $html];
                $Html->set_js($js);

                list ($html, $js) = $Html->phoneKit(['id' => 'cellphone', 'name' => 'cellphone', 'size' => 32, 'maxlength' => 32]);
                $column[] = ['key' => _('Cellphone'), 'value' => $html];
                $Html->set_js($js);

                $a_gender = [];
                foreach ((new userModel)->fetchEnum('gender') as $v0) {
                    $a_gender[] = [
                        'name' => 'gender',
                        'value' => $v0,
                        'text' => ucfirst($v0),
                    ];
                }
                list ($html, $js) = $Html->radiotable('150px', '30px', 5, $a_gender, 'none');
                $column[] = ['key' => _('Gender'), 'value' => $html];
                $Html->set_js($js);

                list ($html, $js) = $Html->date('id="birthday" name="birthday" value="' . date('Y-m-d', strtotime('-20 year')) . '"');
                $column[] = ['key' => _('Birthday'), 'value' => $html];
                $Html->set_js($js);

                $enabledArray = [];
                foreach (\Schema\businessuser::$enabled as $k_0 => $v_0) {
                    $enabledArray[] = [
                        'name' => 'enabled',
                        'value' => $k_0,
                        'text' => $v_0,
                    ];
                }
                list ($html, $js) = $Html->radiotable('150px', '30px', 5, $enabledArray, true);
                $column[] = ['key' => _('Enabled'), 'value' => $html];
                $Html->set_js($js);

                list ($html0, $js0) = $Html->submit('value="' . _('Submit') . '"');
                list ($html1, $js1) = $Html->back('value="' . _('Back') . '"');
                $column[] = ['key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1];
                $Html->set_js($js0 . $js1);

                list ($html, $js) = $Html->table('class="table"', $column, $extra);
                $a_tabs[0] = ['href' => '#tabs-0', 'name' => _('Form'), 'value' => $html];
                $Html->set_js($js);
                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'add']);
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];

                    list ($html, $js) = $Html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                    $extra .= $html;
                    $Html->set_js($js);

                    $businessuserModel = (new \businessuser\Model())
                        ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])
                        ->fetch();

                    /**
                     * Form
                     */
                    $column[] = ['key' => _('Account'), 'value' => $businessuserModel['account']];

                    $column[] = ['key' => _('Name'), 'value' => $businessuserModel['name']];

                    $column[] = ['key' => _('Mode'), 'value' => \Schema\businessuser::$mode[$businessuserModel['mode']]];

                    $column[] = ['key' => _('Last Login IP'), 'value' => $businessuserModel['lastloginip']];

                    $column[] = ['key' => _('Last Login Time'), 'value' => $businessuserModel['lastlogintime']];

                    $column[] = ['key' => _('Modify Time Self'), 'value' => $businessuserModel['modifytime_self']];

                    $enabledArray = [];
                    foreach (\Schema\businessuser::$enabled as $k_0 => $v_0) {
                        $enabledArray[] = [
                            'name' => 'enabled',
                            'value' => $k_0,
                            'text' => $v_0,
                        ];
                    }
                    list($html, $js) = $Html->radiotable('150px', '30px', 5, $enabledArray, $businessuserModel['enabled']);
                    $column[] = ['key' => _('Enabled'), 'value' => $html];
                    $Html->set_js($js);

                    $column[] = ['key' => _('Insert Time'), 'value' => $businessuserModel['inserttime']];

                    $column[] = ['key' => _('Modify Time'), 'value' => $businessuserModel['modifytime']];

                    $column[] = ['key' => _('Modify Admin Name'), 'value' => adminModel::getOne($businessuserModel['modifyadmin_id'])['name']];

                    list($html0, $js0) = $Html->submit('value="' . _('Submit') . '"');
                    list($html1, $js1) = $Html->back('value="' . _('Back') . '"');
                    $column[] = ['key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1];
                    $Html->set_js($js0 . $js1);

                    list($html, $js) = $Html->table('class="table"', $column, $extra);
                    $a_tabs[0] = ['href' => '#tabs-0', 'name' => _('Form'), 'value' => $html];
                    $Html->set_js($js);
                }

                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'edit']);
                break;
        }

        list($html, $js) = $Html->tabs($a_tabs);
        $formcontent = $html;
        $Html->set_js($js);

        list($html, $js) = $Html->form('id="form"', $formcontent);
        parent::$data['form'] = $html;
        $Html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function index()
    {
        $Html = new \Lib\html();

        list ($html, $js) = $Html->grid();
        parent::$data['index'] = $html;
        $Html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function json()
    {
        $response = [];

        $case = isset($_POST['case']) ? $_POST['case'] : null;

        switch ($case) {
            default:
                list ($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $response['data'] = (new \businessuser\Model)
                    ->column([
                        'businessuser_id',
                        'account',
                        'name',
                        'mode',
                        'enabled',
                        'modifytime',
                    ])
                    ->where($where)
                    ->group($group)
                    ->order($order)
                    ->limit($limit)
                    ->fetchAll();


                //total
                $response['total'] = (new \businessuser\Model)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;
        }

        die(json_encode($response));
    }

    function extra0()
    {
        if (is_ajax()) {
            $businessuser_id = isset($_POST['businessuser_id']) ? $_POST['businessuser_id'] : null;

            if ($businessuser_id === null) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. "businessuser_id" is required.';
                $data = null;
                goto _return;
            }

            $result = \Lib\Result::SYSTEM_OK;
            $message = null;
            $data = (new \businessuser\Model)->getQrcodeUrl($businessuser_id);

            _return:
            json_encode_return($result, $message, null, $data);
        }
        die;
    }
}