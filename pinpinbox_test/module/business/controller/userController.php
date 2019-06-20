<?php

namespace business;

class userController extends \business\basisController
{
    function __construct()
    {
    }

    function editgrade()
    {
        if (is_ajax()) {
            $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
            $origin_grade = isset($_POST['origin_grade']) ? $_POST['origin_grade'] : null;
            $grade = isset($_POST['grade']) ? $_POST['grade'] : null;
            $endtime = isset($_POST['endtime']) ? $_POST['endtime'] : null;
            $editremark = isset($_POST['editremark']) ? $_POST['editremark'] : null;

            if ($user_id == null || $grade == null || $editremark == null) json_encode_return(0, _('資料不完整, 請重新填寫'));

            $starttime = $checktime = date('Y-m-d 11:59:59');
            $user_name = (new userModel)->column(['name'])->where([[[['user_id', '=', $user_id]], 'and']])->fetchColumn();

            $endtime = date('Y-m-d 12:01:00', strtotime($endtime));

            Model()->beginTransaction();

            $param = [
                'grade' => $grade,
                'starttime' => $starttime,
                'endtime' => $endtime,
            ];

            if (!(new usergradeModel)->where([[[['user_id', '=', $user_id]], 'and']])->edit($param)) {
                Model()->rollBack();
                json_encode_return(0, _('更新失敗, 請重新輸入。'));
            }
            $param['user_id'] = $user_id;
            $param['modifyadmin_id'] = $_SESSION['admin']['admin_id'];
            $param['remark'] = $editremark . ' - by ' . $_SESSION['admin']['name'];

            (new usergradequeueModel)->add($param);

            //mail to admin
            $m_admin = (new adminModel)->column(['account', 'email', 'name'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
            foreach ($m_admin as $k0 => $v0) {
                $a_mail_to[] = $v0['email'];
            }
            if (!empty($a_mail_to)) {
                $tmp1 = array(
                    _('User id') . '：' . $user_id,
                    _('User name') . '：' . $user_name,
                    _('Origin Grade') . '：' . $origin_grade,
                    _('To Grade') . '：' . $grade,
                    _('Starttime') . '：' . $starttime,
                    _('Endtime') . '：' . $endtime,
                    _('Remark') . '：' . $editremark,
                    _('Admin id') . '：' . $_SESSION['admin']['admin_id'],
                    _('Admin name') . '：' . $_SESSION['admin']['name'],
                );
                $body = implode('<br>', $tmp1);
                email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $a_mail_to, _('Grade'), $body);
            }

            Model()->commit();

            json_encode_return(1, _('修改完成'));
        }
    }

    function edituserpoint()
    {
        if (is_ajax()) {
            foreach (['platform', 'point', 'user_id'] as $v_0) {
                if (!isset($_POST[$v_0])) {
                    json_encode_return(0, 'Param error. "' . $v_0 . '" is required.');
                    break;
                }
            }

            $platform = $_POST['platform'];
            $point = $_POST['point'];
            $remark = isset($_POST['remark']) ? $_POST['remark'] : null;
            $user_id = $_POST['user_id'];

            Core::set_userpoint([
                'platform' => $platform,
                'point' => $point,
                'trade' => 'system',
                'user_id' => $user_id,
            ]);

            $m_user = (new userModel)
                ->column(['user.name', 'userpoint.platform', 'userpoint.point'])
                ->join([['INNER JOIN', 'userpoint', 'USING(user_id)']])
                ->where([[[['user.user_id', '=', $user_id], ['userpoint.platform', '=', $platform]], 'and']])
                ->fetch();

            //email to admin
            $a_email = array_column((new adminModel)->column(['email'])->where([[[['act', '=', 'open']], 'and']])->fetchAll(), 'email');

            if (!empty($a_email)) {
                $body = implode('<br>', [
                    'User id' . '：' . $user_id,
                    'User name' . '：' . $m_user['name'],
                    'Platform' . '：' . ucfirst($platform),
                    'Increase the number of P point(s)' . '：' . $point,
                    'Remark' . '：' . $remark,
                    'Admin id' . '：' . adminModel::getSession()['admin_id'],
                    'Admin name' . '：' . adminModel::getSession()['name'],
                    'Insert time' . '：' . inserttime(),
                ]);

                email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $a_email, 'User Point', $body);
            }

            json_encode_return(1, '添加成功。', null, ['platform' => $m_user['platform'], 'point' => $m_user['point']]);
        }
        die;
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

    function form()
    {
        if (is_ajax()) {
            switch ($_GET['act']) {
                //新增
                case 'add':
                    $account = $_POST['account'];
                    $act = $_POST['act'];
                    $birthday = $_POST['birthday'];
                    $businessuser_id = \businessuser\Model::getSession()['businessuser_id'];
                    $cellphone = ($_POST['cellphone'] === '') ? '+886' . random_password(9, 's') : $_POST['cellphone'];
                    $gender = $_POST['gender'];
                    $name = $_POST['name'];
                    $password = $_POST['password'];
                    $smspassword = random_password(4, 's');

                    //2017-01-03 Lion: 直接給值, 繞過判斷
                    (new \smspasswordModel)->replace([
                        'user_account' => $account,
                        'user_cellphone' => $cellphone,
                        'smspassword' => $smspassword,
                    ]);

                    $paramUser = [
                        'account' => $account,
                        'act' => $act,
                        'birthday' => $birthday,
                        'businessuser_id' => $businessuser_id,
                        'cellphone' => $cellphone,
                        'gender' => $gender,
                        'name' => $name,
                        'password' => $password,
                        'smspassword' => $smspassword,
                        'way' => 'none',
                    ];

                    list ($result, $message) = array_decode_return((new \userModel)->ableToRegister($paramUser));
                    if ($result != 1) {
                        $result = \Lib\Result::SYSTEM_ERROR;//2017-11-08 Lion: 相容
                        goto _return;
                    }

                    (new \Model)->beginTransaction();

                    (new \userModel)->register_v2($paramUser);

                    (new \Model)->commit();
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];
                    $act = $_POST['act'];

                    $userModel = (new \userModel())
                        ->column(['act'])
                        ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id], ['businessuser_id', '=', \businessuser\Model::getSession()['businessuser_id']]], 'and']])
                        ->fetch();

                    if (empty($userModel)) {
                        $result = \Lib\Result::SYSTEM_ERROR;
                        $message = 'Data does not exist.';
                        goto _return;
                    }

                    if ($userModel['act'] != $act) {
                        if (!\Core::notice_switch(['type' => 'user', 'id' => $M_CLASS_id, 'act' => $act])) {
                            json_encode_return(0, _('Unknown case, please try again.'), parent::url(M_CLASS, 'index'));
                        }
                    }

                    (new \userModel)
                        ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id], ['businessuser_id', '=', \businessuser\Model::getSession()['businessuser_id']]], 'and']])
                        ->edit([
                            'act' => $act,
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

                list ($html, $js) = $Html->phoneKit(['id' => 'cellphone', 'name' => 'cellphone', 'size' => 32, 'maxlength' => 32]);
                $column[] = ['key' => _('Cellphone'), 'value' => $html];
                $Html->set_js($js);

                $genderArray = [];
                foreach (\Schema\user::$gender as $v_0) {
                    $genderArray[] = [
                        'name' => 'gender',
                        'value' => $v_0,
                        'text' => ucfirst($v_0),
                    ];
                }
                list ($html, $js) = $Html->radiotable('150px', '30px', 5, $genderArray, 'none');
                $column[] = ['key' => _('Gender'), 'value' => $html];
                $Html->set_js($js);

                list ($html, $js) = $Html->date('id="birthday" name="birthday" value="' . date('Y-m-d', strtotime('-20 year')) . '"');
                $column[] = ['key' => _('Birthday'), 'value' => $html];
                $Html->set_js($js);

                $actArray = [];
                foreach (array_diff(\Schema\user::$act, ['none' => 'None']) as $k_0 => $v_0) {
                    $actArray[] = [
                        'name' => 'act',
                        'value' => $k_0,
                        'text' => $v_0,
                    ];
                }
                list ($html, $js) = $Html->radiotable('150px', '30px', 5, $actArray, 'open');
                $column[] = ['key' => _('Act'), 'value' => $html];
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

                    $m_user = (new \userModel)
                        ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id], ['businessuser_id', '=', \businessuser\Model::getSession()['businessuser_id']]], 'and']])
                        ->fetch();

                    /**
                     * Form
                     */
                    $column[] = ['key' => _('Account'), 'value' => $m_user['account']];

                    $column[] = ['key' => _('Name'), 'value' => $m_user['name']];

                    $column[] = ['key' => _('Cellphone'), 'value' => $m_user['cellphone']];

                    $column[] = ['key' => _('Email'), 'value' => $m_user['email']];

                    $column[] = ['key' => _('Gender'), 'value' => $m_user['gender']];

                    $column[] = ['key' => _('Birthday'), 'value' => $m_user['birthday']];

                    $column[] = ['key' => _('Picture'), 'value' => '<img src="' . URL_STORAGE . \Core::get_userpicture($M_CLASS_id) . '">'];

                    $column[] = ['key' => _('Relationship'), 'value' => $m_user['relationship']];

                    $column[] = ['key' => _('Description'), 'value' => nl2br(htmlspecialchars($m_user['description']))];

                    $column[] = ['key' => _('Level'), 'value' => $m_user['level']];

                    $column[] = ['key' => _('Social Link'), 'value' => parent::grid_json_decode($m_user['sociallink'])];

                    $column[] = ['key' => _('Discuss'), 'value' => $m_user['discuss']];

                    $column[] = ['key' => _('Creative'), 'value' => $m_user['creative'], 'key_remark' => '是否為創作者，0: 否&emsp;1: 是'];

                    $column[] = ['key' => _('Creative Name'), 'value' => $m_user['creative_name']];

                    $column[] = ['key' => _('Creative Code'), 'value' => $m_user['creative_code']];

                    $column[] = ['key' => _('Creative Viewed'), 'value' => (new \userstatisticsModel)->column(['viewed'])->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->fetchColumn()];

                    $column[] = ['key' => _('Last Login IP'), 'value' => $m_user['lastloginip']];

                    $column[] = ['key' => _('Last Login Time'), 'value' => $m_user['lastlogintime']];

                    $column[] = ['key' => _('Modify Time Self'), 'value' => $m_user['modifytime_self']];

                    $a_act = [];
                    foreach (array_diff(\Schema\user::$act, ['none' => 'None']) as $k_0 => $v_0) {
                        $a_act[] = [
                            'name' => 'act',
                            'value' => $k_0,
                            'text' => $v_0,
                        ];
                    }
                    list($html, $js) = $Html->radiotable('150px', '30px', 5, $a_act, $m_user['act']);
                    $column[] = ['key' => _('Act'), 'value' => $html];
                    $Html->set_js($js);

                    $column[] = ['key' => _('Insert Time'), 'value' => $m_user['inserttime']];

                    $column[] = ['key' => _('Modify Time'), 'value' => $m_user['modifytime']];

                    list($html0, $js0) = $Html->submit('value="' . _('Submit') . '"');
                    list($html1, $js1) = $Html->back('value="' . _('Back') . '"');
                    $column[] = ['key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1];
                    $Html->set_js($js0 . $js1);

                    list($html, $js) = $Html->table('class="table"', $column, $extra);
                    $a_tabs[0] = ['href' => '#tabs-0', 'name' => _('Form'), 'value' => $html];
                    $Html->set_js($js);

                    /**
                     * creative info
                     */
                    if ($m_user['creative']) {
                        $column = [];
                        $extra = null;
                        $m_creative = (new creativeModel())->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->fetch();

                        $column[] = ['key' => 'applyfor', 'value' => $m_creative['applyfor'], 'key_remark' => '個人 / 公司'];
                        $column[] = ['key' => 'remittance', 'value' => $m_creative['remittance'], 'key_remark' => '支付方式(paypal / other)'];

                        $tmp = json_decode($m_creative['remittance_info'], true);
                        if ($m_creative['remittance'] == 'paypal') {
                            $a_remittance_info = '<p><span>paypal帳號 : ' . $tmp['paypal_account'] . '</span></p>';
                            $a_remittance_info .= '<p><span>支付幣別 : ' . $tmp['paypal_currency'] . '</span></p>';
                        } else {
                            $a_remittance_info = '<p><span>帳戶名稱 : ' . $tmp['name'] . '</span></p>';
                            $a_remittance_info .= '<p><span>銀行名稱 : ' . $tmp['bank'] . '</span></p>';
                            $a_remittance_info .= '<p><span>分行名稱 : ' . $tmp['branch'] . '</span></p>';
                            $a_remittance_info .= '<p><span>帳號名稱 : ' . $tmp['account'] . '</span></p>';
                            $a_remittance_info .= '<p><span>備註 : ' . $tmp['remark'] . '</span></p>';
                        }

                        $column[] = ['key' => 'remittance_info', 'value' => $a_remittance_info, 'key_remark' => '支付帳號資訊'];

                        //個人申請
                        $column[] = ['key' => 'personal_email', 'value' => $m_creative['personal_email'], 'key_remark' => '聯絡信箱'];
                        $column[] = ['key' => 'personal_country', 'value' => $m_creative['personal_country'], 'key_remark' => '居住城市'];
                        $column[] = ['key' => 'personal_zipcode', 'value' => $m_creative['personal_zipcode'], 'key_remark' => '郵遞區號'];
                        $column[] = ['key' => 'personal_address', 'value' => $m_creative['personal_address'], 'key_remark' => '地址'];
                        $column[] = ['key' => 'personal_website', 'value' => $m_creative['personal_website']];

                        $m_career_name = (new careerModel())->column(['name'])->where([[[['career_id', '=', $m_creative['personal_career']]], 'and']])->fetchColumn();
                        $column[] = ['key' => 'personal_career', 'value' => $m_creative['personal_career'] . ' (' . $m_career_name . ')', 'key_remark' => '職業'];
                        $column[] = ['key' => 'personal_idcardnumber', 'value' => $m_creative['personal_idcardnumber'], 'key_remark' => '身分證字號'];

                        //公司申請
                        $column[] = ['key' => 'company_email', 'value' => $m_creative['company_email'], 'key_remark' => '聯絡信箱'];
                        $column[] = ['key' => 'company_country', 'value' => $m_creative['company_country'], 'key_remark' => '居住城市'];
                        $column[] = ['key' => 'company_zipcode', 'value' => $m_creative['company_zipcode'], 'key_remark' => '郵遞區號'];
                        $column[] = ['key' => 'company_address', 'value' => $m_creative['company_address'], 'key_remark' => '地址'];
                        $column[] = ['key' => 'company_website', 'value' => $m_creative['company_website']];
                        $column[] = ['key' => 'company_name_zh_TW', 'value' => $m_creative['company_name_zh_TW'], 'key_remark' => '中文名稱'];
                        $column[] = ['key' => 'company_name_en_US', 'value' => $m_creative['company_name_en_US'], 'key_remark' => '英文名稱'];
                        $column[] = ['key' => 'company_telephone', 'value' => $m_creative['company_telephone'], 'key_remark' => '連絡電話'];
                        $column[] = ['key' => 'company_vatnumber', 'value' => $m_creative['company_vatnumber'], 'key_remark' => '統一編號'];
                        $column[] = ['key' => 'inserttime', 'value' => $m_creative['inserttime']];
                        $column[] = ['key' => 'modifytime', 'value' => $m_creative['modifytime']];

                        list($html, $js) = $Html->table('class="table"', $column, $extra);
                        $a_tabs[3] = ['href' => '#tabs-3s', 'name' => _('User Creative Info'), 'value' => $html];
                        $Html->set_js($js);
                    }
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

    function delete()
    {
        die;
    }

    function json()
    {
        $response = [];

        $case = isset($_POST['case']) ? $_POST['case'] : null;

        switch ($case) {
            default:
                //column
                $column = [
                    M_CLASS . '_id',
                    'account',
                    'name',
                    'cellphone',
                    'email',
                    'gender',
                    'creative',
                    'lastlogintime',
                    'act',
                    'modifytime',
                ];

                list ($where, $group, $order, $limit) = parent::grid_request_encode();

                $where = array_merge($where, [[[['businessuser_id', '=', \businessuser\Model::getSession()['businessuser_id']]], 'and']]);//2017-09-07 Lion: 這行很重要, 不然會撈到其他用戶

                //data
                $fetchAll = (new \userModel)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['viewed'] = (new \userstatisticsModel)->column(['viewed'])->where([[[['user_id', '=', $v0['user_id']]], 'and']])->fetchColumn();
                }
                $response['data'] = $fetchAll;


                //total
                $response['total'] = (new \userModel)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;
        }

        die(json_encode($response));
    }
}