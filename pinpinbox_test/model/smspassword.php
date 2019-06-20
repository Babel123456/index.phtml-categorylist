<?php

class smspasswordModel extends Model
{
    protected $database = 'site';
    protected $table = 'smspassword';
    protected $memcache = 'site';
    protected $join_table = [];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
        ];
    }

    static function ableToRequestSMSPasswordForUpdateCellphone($user_id, $cellphone)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $cellphone = (trim($cellphone) === '') ? null : \Core\I18N::cellphone($cellphone);

        if ($cellphone === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "cellphone" is required.';
            goto _return;
        } else {
            $Model_user = (new \userModel)
                ->column(['cellphone'])
                ->where([[[['user_id', '=', $user_id]], 'and']])
                ->fetch();

            if ($cellphone == $Model_user['cellphone']) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('舊的手機號碼與新的手機號碼相同。');
                goto _return;
            }

            $count = (new \userModel)
                ->column(['COUNT(1)'])
                ->where([[[['cellphone', '=', $cellphone]], 'and']])
                ->fetchColumn();

            if ($count) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('The cellphone number already exists, please use another.');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function requestSMSPasswordForUpdateCellphone($user_id, $cellphone)
    {
        $cellphone = \Core\I18N::cellphone($cellphone);

        $Model_smspassword = (new \smspasswordModel)
            ->column(['smspassword'])
            ->where([[[['user_id', '=', $user_id], ['user_cellphone', '=', $cellphone]], 'and']])
            ->fetch();

        if (empty($Model_smspassword)) {
            $smspassword = random_password(4, 's');

            (new \smspasswordModel)
                ->add([
                    'user_id' => $user_id,
                    'user_cellphone' => $cellphone,
                    'smspassword' => $smspassword
                ]);
        } else {
            $smspassword = $Model_smspassword['smspassword'];
        }

        //sms
        list ($sms_result, $sms_message) = array_decode_return(Core::extension('sms', 'every8d')->send($cellphone, 'pinpinbox SMS password : ' . $smspassword));

        if (!$sms_result) {
            email(
                EMAIL_ACCOUNT_INTRANET,
                EMAIL_PASSWORD_INTRANET,
                'pinpinbox',
                'it@vmage.com.tw',
                'SMS Exception',
                implode('<br>', [
                    $cellphone,
                    'pinpinbox SMS password : ' . $smspassword,
                    $sms_message
                ])
            );
        }
    }

    function usefor($usefor, $user_account, $user_cellphone)
    {
        $result = 1;
        $message = null;

        $usefor = (trim($usefor) === '') ? null : trim($usefor);
        $user_account = (trim($user_account) === '') ? null : trim($user_account);
        $user_cellphone = (trim($user_cellphone) === '') ? null : \Core\I18N::cellphone($user_cellphone);

        if ($usefor === null || $user_account === null || $user_cellphone === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        switch ($usefor) {
            case 'editcellphone':
                $m_user = Model('user')->column(['cellphone', 'act'])->where([[[['account', '=', $user_account]], 'and']])->fetch();
                if (empty($m_user)) {
                    $result = 0;
                    $message = _('User does not exist.');
                    goto _return;
                } elseif ($m_user['act'] != 'open') {
                    $result = 0;
                    $message = _('User is not open.');
                    goto _return;
                }

                if ($user_cellphone == $m_user['cellphone']) {
                    $result = 0;
                    $message = _('The old cellphone number and the new cellphone number are the same.');
                    goto _return;
                }

                list($result1, $message1) = array_decode_return(Model('user')->check('cellphone', $user_cellphone));
                if ($result1 != 1) {
                    $result = $result1;
                    $message = $message1;
                    goto _return;
                }
                break;

            case 'register':
                list($result1, $message1) = array_decode_return(Model('user')->check('account', $user_account));
                if ($result1 != 1) {
                    $result = $result1;
                    $message = $message1;
                    goto _return;
                }

                list($result1, $message1) = array_decode_return(Model('user')->check('cellphone', $user_cellphone));
                if ($result1 != 1) {
                    $result = $result1;
                    $message = $message1;
                    goto _return;
                }
                break;

            default:
                throw new Exception('Unknown case');
                break;
        }

        $m_smspassword = Model('smspassword')->column(['smspassword'])->where([[[['user_account', '=', $user_account], ['user_cellphone', '=', $user_cellphone]], 'and']])->fetch();
        if (empty($m_smspassword)) {
            $smspassword = random_password(4, 's');
            Model('smspassword')->add(['user_account' => $user_account, 'user_cellphone' => $user_cellphone, 'smspassword' => $smspassword]);
        } else {
            $smspassword = $m_smspassword['smspassword'];
        }

        //sms
        list ($sms_result, $sms_message) = array_decode_return(Core::extension('sms', 'every8d')->send($user_cellphone, 'pinpinbox SMS password : ' . $smspassword));

        if (!$sms_result) {
            $result = 0;
            $message = _('[SMS] occur exception, please contact us.');
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function verify($user_account, $user_cellphone, $smspassword)
    {
        $result = 1;
        $message = null;

        $user_account = (trim($user_account) === '') ? null : trim($user_account);
        $user_cellphone = (trim($user_cellphone) === '') ? null : \Core\I18N::cellphone($user_cellphone);
        $smspassword = (trim($smspassword) === '') ? null : trim($smspassword);

        if ($user_account === null || $user_cellphone === null || $smspassword === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        $m_smspassword = (new smspasswordModel)->column(['smspassword'])->where([[[['user_account', '=', $user_account], ['user_cellphone', '=', $user_cellphone]], 'and']])->fetch();

        if (empty($m_smspassword)) {
            $result = 0;
            $message = _('SMS-password does not exist.');
            goto _return;
        } elseif ($smspassword != $m_smspassword['smspassword']) {
            $result = 0;
            $message = _('SMS-password is incorrect.');
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }
}