<?php

class userController extends backstageController
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

    function edituserpointsplit()
    {
        if (is_ajax()) {
            $ratio = isset($_POST['ratio']) ? $_POST['ratio'] : null;
            $remark = isset($_POST['remark']) ? $_POST['remark'] : null;
            $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

            if ($ratio === null) {
                json_encode_return(0, _('請填寫 Ratio。'));
            } else {
                if ($ratio > 1) {
                    json_encode_return(0, _('Ratio 最大值為 1。'));
                }

                if ($ratio < 0) {
                    json_encode_return(0, _('Ratio 最小值為 0。'));
                }
            }

            if ($remark === null) {
                json_encode_return(0, _('請填寫 Remark。'));
            }

            if ($user_id === null) {
                json_encode_return(0, _('user id 不能為空。'));
            }

            (new \Model\userpointsplit)
                ->replace([
                    'user_id' => $user_id,
                    'ratio' => $ratio,
                ]);

            (new \Model\userpointsplitqueue)
                ->add([
                    'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    'user_id' => $user_id,
                    'ratio' => $ratio,
                    'remark' => $remark,
                ]);

            $m_user = (new \userModel)
                ->column(['user.name'])
                ->where([[[['user.user_id', '=', $user_id]], 'and']])
                ->fetch();

            //email to admin
            $a_email = array_column((new \adminModel)->column(['email'])->where([[[['act', '=', 'open']], 'and']])->fetchAll(), 'email');

            if (!empty($a_email)) {
                $body = implode('<br>', [
                    'User id：' . $user_id,
                    'User name：' . $m_user['name'],
                    'Update user point split to：' . ($ratio * 100) . '%',
                    'Admin id：' . adminModel::getSession()['admin_id'],
                    'Admin name：' . adminModel::getSession()['name'],
                    'Insert time：' . inserttime(),
                ]);

                email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $a_email, 'User Point Split', $body);
            }

            json_encode_return(1, '修改成功。');
        }

        die;
    }

    function edituserstatistics()
    {
        if (is_ajax()) {
            $besponsored_manual = isset($_POST['besponsored_manual']) ? $_POST['besponsored_manual'] : null;
            $followfrom_manual = isset($_POST['followfrom_manual']) ? $_POST['followfrom_manual'] : null;
            $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
            $viewed_manual = isset($_POST['viewed_manual']) ? $_POST['viewed_manual'] : null;

            if ($besponsored_manual === null) {
                json_encode_return(0, _('請填寫被贊助次數。'));
            }

            if ($followfrom_manual === null) {
                json_encode_return(0, _('請填寫被關注次數。'));
            }

            if ($user_id === null) {
                json_encode_return(0, _('user id 不能為空。'));
            }

            if ($viewed_manual === null) {
                json_encode_return(0, _('請填寫被瀏覽次數。'));
            }

            (new \userstatisticsModel)
                ->replace([
                    'besponsored_manual' => $besponsored_manual,
                    'followfrom_manual' => $followfrom_manual,
                    'user_id' => $user_id,
                    'viewed_manual' => $viewed_manual,
                ]);

            $m_user = (new \userModel)
                ->column(['user.name'])
                ->where([[[['user.user_id', '=', $user_id]], 'and']])
                ->fetch();

            //email to admin
            $a_email = array_column((new \adminModel)->column(['email'])->where([[[['act', '=', 'open']], 'and']])->fetchAll(), 'email');

            if (!empty($a_email)) {
                $body = implode('<br>', [
                    'User id：' . $user_id,
                    'User name：' . $m_user['name'],
                    '被瀏覽次數：+' . $viewed_manual,
                    '被贊助次數：+' . $besponsored_manual,
                    '被關注次數：+' . $followfrom_manual,
                    'Admin id：' . adminModel::getSession()['admin_id'],
                    'Admin name：' . adminModel::getSession()['name'],
                    'Insert time：' . inserttime(),
                ]);

                email(
                    EMAIL_ACCOUNT_INTRANET,
                    EMAIL_PASSWORD_INTRANET,
                    'pinpinbox',
                    [
                        'cailum@vmage.com.tw',
                        'lion@vmage.com.tw',
                        'sung@vmage.com.tw',
                    ],
                    'User Statistics',
                    $body
                );
            }

            json_encode_return(1, '修改成功。');
        }

        die;
    }

    function index()
    {
        $Html = new Lib\html();

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
            $result = 1;
            $message = _('Success, back to previous page?');
            $redirect = parent::url(M_CLASS, 'index');

            switch ($_GET['act']) {
                //新增
                case 'add':
                    $account = $_POST['account'];
                    $password = $_POST['password'];
                    $name = $_POST['name'];
                    $cellphone = ($_POST['cellphone'] === '') ? '+886' . random_password(9, 's') : $_POST['cellphone'];
                    $gender = $_POST['gender'];
                    $birthday = $_POST['birthday'];
                    $act = $_POST['act'];

                    $smspassword = random_password(4, 's');

                    Model()->beginTransaction();

                    //2017-01-03 Lion: 直接給值, 繞過判斷
                    (new smspasswordModel)->add(['user_account' => $account, 'user_cellphone' => $cellphone, 'smspassword' => $smspassword]);

                    list ($result_0, $message_0) = array_decode_return((new userModel)->register([
                        'account' => $account,
                        'password' => $password,
                        'name' => $name,
                        'cellphone' => $cellphone,
                        'gender' => $gender,
                        'birthday' => $birthday,
                        'way' => 'none',
                        'smspassword' => $smspassword,
                    ]));

                    if ($result_0 != 1) {
                        $result = $result_0;
                        $message = $message_0;

                        Model()->rollBack();

                        goto _return;
                    }

                    Model()->commit();
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];
                    $act = $_POST['act'];

                    $m_user_act = (new userModel)->column(['act'])->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])->fetchColumn();

                    if ($m_user_act != $act) {
                        if (!Core::notice_switch(['type' => 'user', 'id' => $M_CLASS_id, 'act' => $act])) json_encode_return(0, _('Unknown case, please try again.'), parent::url(M_CLASS, 'index'));
                    }

                    (new userModel)->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])->edit([
                        'act' => $act,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ]);
                    break;
            }

            _return:
            json_encode_return($result, $message, $redirect);
        }

        $Html = new Lib\html();

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

                $a_act = [];
                foreach (array_diff((new userModel)->fetchEnum('act'), ['none']) as $v0) {
                    $a_act[] = [
                        'name' => 'act',
                        'value' => $v0,
                        'text' => ucfirst($v0),
                    ];
                }
                list ($html, $js) = $Html->radiotable('150px', '30px', 5, $a_act, 'open');
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

                    $m_user = (new userModel)->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])->fetch();

                    /**
                     * Form
                     */
                    $column[] = ['key' => _('Account'), 'value' => $m_user['account']];

                    $column[] = ['key' => _('Password'), 'value' => $m_user['password']];

                    $column[] = ['key' => _('Name'), 'value' => $m_user['name']];

                    $column[] = ['key' => _('Cellphone'), 'value' => $m_user['cellphone']];

                    $column[] = ['key' => _('Email'), 'value' => $m_user['email']];

                    $column[] = ['key' => _('Gender'), 'value' => $m_user['gender']];

                    $column[] = ['key' => _('Birthday'), 'value' => $m_user['birthday']];

                    $column[] = ['key' => _('Picture'), 'value' => '<img src="' . URL_STORAGE . Core::get_userpicture($M_CLASS_id) . '">'];

                    $column[] = ['key' => _('Relationship'), 'value' => $m_user['relationship']];

                    $column[] = ['key' => _('Description'), 'value' => nl2br(htmlspecialchars($m_user['description']))];

                    $column[] = ['key' => _('Level'), 'value' => $m_user['level']];

                    $column[] = ['key' => _('Social Link'), 'value' => parent::grid_json_decode($m_user['sociallink'])];

                    $column[] = ['key' => _('Discuss'), 'value' => $m_user['discuss']];

                    $column[] = ['key' => _('Creative'), 'value' => $m_user['creative'], 'key_remark' => '是否為創作者，0: 否&emsp;1: 是'];

                    $column[] = ['key' => _('Creative Name'), 'value' => $m_user['creative_name']];

                    $column[] = ['key' => _('Creative Code'), 'value' => $m_user['creative_code']];

                    $column[] = ['key' => _('Creative Viewed'), 'value' => (new userstatisticsModel)->column(['viewed'])->where([[[['user_id', '=', $M_CLASS_id]], 'and']])->fetchColumn()];

                    $column[] = ['key' => _('Last Login IP'), 'value' => $m_user['lastloginip']];

                    $column[] = ['key' => _('Last Login Time'), 'value' => $m_user['lastlogintime']];

                    $column[] = ['key' => _('Modify Time Self'), 'value' => $m_user['modifytime_self']];

                    $column[] = ['key' => _('Way'), 'value' => $m_user['way']];

                    $a_act = [];
                    foreach (json_decode(Core::settings('USER_ACT'), true) as $k0 => $v0) {
                        $a_act[] = [
                            'name' => 'act',
                            'value' => $k0,
                            'text' => $v0,
                        ];
                    }
                    list($html, $js) = $Html->radiotable('150px', '30px', 5, $a_act, $m_user['act']);
                    $column[] = ['key' => _('Act'), 'value' => $html];
                    $Html->set_js($js);

                    $column[] = ['key' => _('Insert Time'), 'value' => $m_user['inserttime']];

                    $column[] = ['key' => _('Modify Time'), 'value' => $m_user['modifytime']];

                    $column[] = ['key' => _('Modify Admin Name'), 'value' => adminModel::getOne($m_user['modifyadmin_id'])['name']];

                    list($html0, $js0) = $Html->submit('value="' . _('Submit') . '"');
                    list($html1, $js1) = $Html->back('value="' . _('Back') . '"');
                    $column[] = ['key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1];
                    $Html->set_js($js0 . $js1);

                    list($html, $js) = $Html->table('class="table"', $column, $extra);
                    $a_tabs[0] = ['href' => '#tabs-0', 'name' => _('Form'), 'value' => $html];
                    $Html->set_js($js);

                    /**
                     * userpoint + userpointqueue
                     */
                    $column = [];
                    $extra = null;

                    $column1 = [];
                    $extra1 = null;

                    $column1[] = ['key' => 'Apple', 'value' => '<span id="userpoint_platform_apple">' . number_format(Core::get_userpoint($m_user['user_id'], 'apple')) . '</span>'];

                    $column1[] = ['key' => 'Google', 'value' => '<span id="userpoint_platform_google">' . number_format(Core::get_userpoint($m_user['user_id'], 'google')) . '</span>'];

                    $column1[] = ['key' => 'Web', 'value' => '<span id="userpoint_platform_web">' . number_format(Core::get_userpoint($m_user['user_id'], 'web')) . '</span>'];

                    list($html, $js) = $Html->table('class="table"', $column1, $extra1);
                    $column[] = ['key' => _('User Point'), 'value' => $html];
                    $Html->set_js($js);

                    $column1 = [];
                    $extra1 = null;

                    $a_platform = [];
                    foreach (array_diff(\Schema\userpoint::$platform, ['none']) as $v_0) {
                        $a_platform[] = [
                            'value' => $v_0,
                            'text' => ucfirst($v_0),
                        ];
                    }
                    list($html, $js) = $Html->selectKit(['id' => 'userpoint_platform', 'name' => 'userpoint_platform', 'style' => 'width:10%;'], $a_platform);
                    $column1[] = ['key' => 'Platform', 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->number('id="userpoint_point" name="userpoint_point" placeholder="請填寫正整數" min="1" max="65535" style="width:10%;"');
                    $column1[] = ['key' => 'Point', 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->text('id="userpoint_remark" name="userpoint_remark" placeholder="Remark" style="width:20%;"');
                    $column1[] = ['key' => 'Remark', 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->button('id="userpoint_submit" name="userpoint_submit" value="提交"');
                    $column1[] = ['key' => null, 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->table('class="table"', $column1, $extra1);
                    $column[] = ['key' => 'Edit User Point', 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->grid('userpointqueue-grid');
                    $column[] = ['key' => _('User Point Queue'), 'value' => $html];
                    $Html->set_js($js);
                    parent::$data[M_CLASS . '_id'] = empty($M_CLASS_id) ? '[]' : $M_CLASS_id;

                    list($html, $js) = $Html->table('class="table"', $column, $extra);
                    $a_tabs[1] = ['href' => '#tabs-1', 'name' => _('User Point'), 'value' => $html];
                    $Html->set_js($js);

                    /**
                     * userpointsplit
                     */
                    $ratio = (new \Model\userpointsplit)
                        ->column(['ratio'])
                        ->where([[[['user_id', '=', $m_user['user_id']]], 'and']])
                        ->fetchColumn();

                    if ($ratio === false) $ratio = 0.5;

                    $column = [];
                    $extra1 = null;

                    list($html, $js) = $Html->number('id="userpointsplit_ratio" name="userpointsplit_ratio" min="0" max="1" step="0.01" style="width:10%;" value="' . $ratio . '" required');
                    $column[] = ['key' => 'Ratio', 'value' => $html, 'key_remark' => '0.01 為 1%'];
                    $Html->set_js($js);

                    list ($html, $js) = $Html->text('id="userpointsplit_remark" name="userpointsplit_remark" size="64" maxlength="64" required');
                    $column[] = ['key' => _('Remark'), 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->button('id="userpointsplit_submit" name="userpointsplit_submit" value="提交"');
                    $column[] = ['key' => null, 'value' => $html];
                    $Html->set_js($js);

                    list ($html, $js) = $Html->grid('userpointsplit-grid');
                    $column[] = ['key' => _('User Point Split Queue'), 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->table('class="table"', $column, $extra);
                    $a_tabs[2] = ['href' => '#tabs-2', 'name' => _('User Point Split'), 'value' => $html];
                    $Html->set_js($js);

                    /**
                     * usergrade + usergradequeue
                     */
                    $column = [];
                    $extra = null;
                    parent::$data['user_grade'] = $user_grade = Core::get_usergrade($m_user['user_id']);
                    $column[] = ['key' => 'User Grade', 'value' => $user_grade];

                    $grade_info = (new usergradeModel)->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->fetch();
                    $e_grade = (new usergradeModel)->fetchEnum('grade');
                    $a_grade = [];
                    foreach ($e_grade as $v0) {
                        $a_grade[] = [
                            'value' => $v0,
                            'text' => $v0,
                        ];
                    }

                    list($htmlsK, $jssK) = $Html->selectKit(['id' => 'new_grade', 'name' => 'new_grade', 'style' => 'width:10%;'], $a_grade, [$user_grade]);
                    list($htmlt2, $jst2) = $Html->datetime('id="endtime" name="endtime" value="' . $grade_info['endtime'] . '" ');
                    list($htmlremark, $js) = $Html->text('id="editremark" name="editremark" placeholder="Remark" style="width:20%;"');
                    list($htmlbtn, $jsbtn) = $Html->button('id="btn" name="btn" value="Edit" ');
                    $starttime = date('Y-m-d');
                    $edit_column = '<p>Update Grade : ' . $htmlsK . '</p><br><p>Start time : <span id="starttime">' . $starttime . '</span></p><br><p> End Time  : ' . $htmlt2 . '</p><br><p> Remark : ' . $htmlremark . ' - by ' . $_SESSION['admin']['name'] . '</p><br><p>' . $htmlbtn . '</p>';
                    $column[] = ['key' => _('Edit User Grade'), 'value' => $edit_column];
                    $Html->set_js($jssK);
                    $Html->set_js($jst2);

                    list($html, $js) = $Html->grid('usergradequeue-grid');

                    $column[] = ['key' => _('User Grade Queue'), 'value' => $html];
                    $Html->set_js($js);
                    parent::$data[M_CLASS . '_id'] = empty($M_CLASS_id) ? '[]' : $M_CLASS_id;

                    list($html, $js) = $Html->table('class="table"', $column, $extra);
                    $a_tabs[3] = ['href' => '#tabs-3', 'name' => _('User Grade'), 'value' => $html];
                    $Html->set_js($js);

                    //
                    $object_userstatistics = (new \userstatisticsModel)
                        ->column([
                            'besponsored_manual',
                            'followfrom_manual',
                            'viewed_manual',
                        ])
                        ->where([[[['user_id', '=', $m_user['user_id']]], 'and']])
                        ->fetch();

                    $column = [];
                    $extra1 = null;

                    list($html, $js) = $Html->number('id="userstatistics_viewed_manual" name="userstatistics_viewed_manual" min="0" max="65535" style="width:10%;" value="' . $object_userstatistics['viewed_manual'] . '" required');
                    $column[] = ['key' => '被瀏覽次數(調整數值)', 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->number('id="userstatistics_besponsored_manual" name="userstatistics_besponsored_manual" min="0" max="65535" style="width:10%;" value="' . $object_userstatistics['besponsored_manual'] . '" required');
                    $column[] = ['key' => '被贊助次數(調整數值)', 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->number('id="userstatistics_followfrom_manual" name="userstatistics_followfrom_manual" min="0" max="65535" style="width:10%;" value="' . $object_userstatistics['followfrom_manual'] . '" required');
                    $column[] = ['key' => '被關注次數(調整數值)', 'value' => $html];
                    $Html->set_js($js);

                    list($html, $js) = $Html->button('id="userstatistics_submit" name="userstatistics_submit" value="提交"');
                    $column[] = ['key' => null, 'value' => $html, 'key_remark' => '實際數值 + 調整數值 = 顯示數值'];
                    $Html->set_js($js);

                    list($html, $js) = $Html->table('class="table"', $column, $extra);
                    $a_tabs[4] = ['href' => '#tabs-4', 'name' => _('User Statistics'), 'value' => $html];
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
                        $a_tabs[5] = ['href' => '#tabs-5', 'name' => _('User Creative Info'), 'value' => $html];
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
                    'way',
                    'businessuser_id',
                    'act',
                    'modifytime',
                ];

                list ($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = (new userModel)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['viewed'] = (new userstatisticsModel)->column(['viewed'])->where([[[['user_id', '=', $v0['user_id']]], 'and']])->fetchColumn();
                }
                $response['data'] = $fetchAll;


                //total
                $response['total'] = (new userModel)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;

            case 'userpointqueue':
                //column
                $column = [
                    'user_id',
                    'trade',
                    'trade_id',
                    'platform',
                    'point_before',
                    'point',
                    'inserttime',
                ];

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = (new userpointqueueModel)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = (new userpointqueueModel)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;

            case 'userpointsplitqueue':
                list ($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = (new \Model\userpointsplitqueue)
                    ->column([
                        'user_id',
                        'ratio',
                        'remark',
                        'inserttime',
                        'modifyadmin_id',
                    ])
                    ->where($where)
                    ->group($group)
                    ->order($order)
                    ->limit($limit)
                    ->fetchAll();

                foreach ($fetchAll as &$v0) {
                    $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
                }

                $response = [
                    'data' => $fetchAll,
                    'total' => (new \Model\userpointsplitqueue)->column(['count(1)'])->where($where)->group($group)->fetchColumn(),
                ];
                break;

            case 'usergradequeue':
                //column
                $column = [
                    'user_id',
                    'order_id',
                    'grade',
                    'starttime',
                    'endtime',
                    'inserttime',
                ];

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = (new usergradequeueModel)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = (new usergradequeueModel)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;
        }

        die(json_encode($response));
    }

    function extra0()
    {
        if (is_ajax()) {
            $M_CLASS_id = isset($_POST[M_CLASS . '_id']) ? $_POST[M_CLASS . '_id'] : null;
            $execute = isset($_POST['execute']) ? $_POST['execute'] : null;

            $result = 1;
            $message = null;
            $data = null;

            if ($M_CLASS_id == null) {
                $result = 0;
                $message = _('Param error.');
                goto _return;
            }

            $m_user = (new userModel)->column(['user_id', 'account', '`name`'])->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])->fetch();

            if ($m_user == null) {
                $result = 0;
                $message = _('Data does not exist.');
                goto _return;
            }

            if ($execute) {
                (new userModel)->logout();

                (new userModel)->setSession($m_user['user_id']);

                //2016-02-03 Lion: 避免干擾 userlog 紀錄
                \Session::set('userlog', false);

                $message = _('Login success.');
                $data = URL_ROOT;
            } else {
                $message = '您確定要以此用戶身分登入？<br><br>User ID: ' . $m_user['user_id'] . '<br>' . _('Account') . ': ' . $m_user['account'] . '<br>' . _('Name') . ': ' . $m_user['name'];
            }

            _return:
            json_encode_return($result, $message, null, $data);
        }
        die;
    }
}