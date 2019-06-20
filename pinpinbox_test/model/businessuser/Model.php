<?php

namespace businessuser;

class Model extends \Model
{
    protected $database = 'site';
    protected $table = 'businessuser';
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

    function ableToInsert(array $param)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        $account = isset($param['account']) ? $param['account'] : null;
        $enabled = isset($param['enabled']) ? $param['enabled'] : null;
        $mode = isset($param['mode']) ? $param['mode'] : null;
        $name = isset($param['name']) ? $param['name'] : null;
        $password = isset($param['password']) ? $param['password'] : null;

        if ($account === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "account" is required.';
            goto _return;
        } else {
            if (charlen($account) > \Schema\businessuser::$account_Length) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. The string of "account" is longer than ' . \Schema\businessuser::$account_Length . '.';
                goto _return;
            }

            $businessuserCount = (new \businessuser\Model())
                ->column(['COUNT(1)'])
                ->where([[[['account', '=', $account]], 'and']])
                ->fetchColumn();

            if ($businessuserCount > 0) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Data already exists by "account".';
                goto _return;
            }
        }

        if ($enabled === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "enabled" is required.';
            goto _return;
        }

        if ($mode === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "mode" is required.';
            goto _return;
        } else {
            if (!array_key_exists($mode, \Schema\businessuser::$mode)) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. "mode" is an invalid value.';
                goto _return;
            }
        }

        if ($name === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "name" is required.';
            goto _return;
        } else {
            if (charlen($name) > \Schema\businessuser::$name_Length) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. The string of "name" is longer than ' . \Schema\businessuser::$name_Length . '.';
                goto _return;
            }

            $businessuserCount = (new \businessuser\Model())
                ->column(['COUNT(1)'])
                ->where([[[['name', '=', $name]], 'and']])
                ->fetchColumn();

            if ($businessuserCount > 0) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Data already exists by "name".';
                goto _return;
            }
        }

        if ($password === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "password" is required.';
            goto _return;
        }

        list ($result, $message) = array_decode_return((new \userModel)->ableToRegister($param));
        if ($result != 1) {
            $result = \Lib\Result::SYSTEM_ERROR;
            goto _return;
        } else {
            //2017-09-12 Lion: 相容
            if ($result == 1) {
                $result = \Lib\Result::SYSTEM_OK;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function ableToLogin(array $param)
    {
        $account = isset($param['account']) ? $param['account'] : null;
        $password = isset($param['password']) ? $param['password'] : null;

        if ($account === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "account" is required.';
            goto _return;
        }

        if ($password === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "password" is required.';
            goto _return;
        }

        $businessuserModel = (new \businessuser\Model())
            ->column([
                'businessuser_id',
                '`password`',
                'enabled',
            ])
            ->where([[[['account', '=', $account]], 'and']])
            ->fetch();

        if (empty($businessuserModel)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('Account does not exist.');
            goto _return;
        } else {
            if (!password_verify($password, $businessuserModel['password'])) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('Password is incorrect.');
                goto _return;
            } elseif ($businessuserModel['enabled'] == false) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('Account is closed.');
                goto _return;
            }
        }

        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        _return:
        return array_encode_return($result, $message);
    }

    function getQrcodePath($businessuser_id)
    {
        return $this->getStoragePath($businessuser_id) . 'qrcode.jpg';
    }

    function getQrcodeUrl($businessuser_id)
    {
        return path2url($this->getQrcodePath($businessuser_id));
    }

    static function getSession()
    {
        return \Session::get('businessuser');
    }

    function getStoragePath($businessuser_id)
    {
        return mkdir_p_v2(PATH_STORAGE . SITE_LANG . DIRECTORY_SEPARATOR . $this->table . DIRECTORY_SEPARATOR . $businessuser_id . DIRECTORY_SEPARATOR);
    }

    function insertBusinessUser(array $param)
    {
        $userModel = (new \userModel)->register_v2($param);

        $businessuser_id = (new \businessuser\Model())
            ->add([
                'account' => $param['account'],
                'enabled' => $param['enabled'],
                'mode' => $param['mode'],
                'name' => $param['name'],
                'password' => password_hash($param['password'], PASSWORD_DEFAULT),
                'user_id' => $userModel['id'],
                'modifyadmin_id' => \adminModel::getSession()['admin_id'],
            ]);

        self::setUserGrade($businessuser_id, $userModel['id'], 'By Admin of ' . \adminModel::getSession()['name'] . '.');

        $this->setQrcode($businessuser_id);
    }

    static function isCompany($user_id)
    {
        return (new \businessuser\Model())->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id], ['mode', '=', 'company']], 'and']])->fetchColumn() ? true : false;
    }

    static function isPersonal($user_id)
    {
        return (new \businessuser\Model())->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id], ['mode', '=', 'personal']], 'and']])->fetchColumn() ? true : false;
    }

    static function isUpline($user_id)
    {
        return (new \businessuser\Model())->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id]], 'and']])->fetchColumn() ? true : false;
    }

    static function login(array $param)
    {
        $businessuserModel = (new \businessuser\Model())
            ->column([
                'businessuser_id',
            ])
            ->where([[[['account', '=', $param['account']]], 'and']])
            ->fetch();

        \businessuser\Model::setSession($businessuserModel['businessuser_id']);

        (new \businessuser\Model())
            ->where([[[['businessuser_id', '=', $businessuserModel['businessuser_id']]], 'and']])
            ->edit([
                'lastloginip' => remote_ip(),
                'lastlogintime' => inserttime(),
            ]);
    }

    static function logout()
    {
        \Session::delete('businessuser');
    }

    static function setSession($businessuser_id)
    {
        $businessuserModel = (new \businessuser\Model())
            ->column([
                'businessuser_id',
                'mode',
                'name',
            ])
            ->where([[[['businessuser_id', '=', $businessuser_id]], 'and']])
            ->fetch();

        \Session::set(
            'businessuser',
            [
                'businessuser_id' => $businessuserModel['businessuser_id'],
                'mode' => $businessuserModel['mode'],
                'name' => $businessuserModel['name'],
            ]
        );
    }

    function setQrcode($businessuser_id)
    {
        (new \Core\QRcode())
            ->setTextUrl(\frontstageController::url('businessuser', 'businesssubuserfastregister', ['businessuser_id' => $businessuser_id]))
            ->setLevel(1)
            ->setSize(5)
            ->save($this->getQrcodePath($businessuser_id));

        if (is_file($this->getQrcodePath($businessuser_id))) {
            \Extension\aws\S3::upload($this->getQrcodePath($businessuser_id));
        }
    }

    static function setUserGrade($businessuser_id, $user_id, $remark = null)
    {
        $businessuserModel = (new \businessuser\Model())
            ->column([
                'mode',
                'name',
            ])
            ->where([[[['businessuser_id', '=', $businessuser_id]], 'and']])
            ->fetch();

        if ($businessuserModel) {
            switch ($businessuserModel['mode']) {
                case 'company':
                    $param = [
                        'user_id' => $user_id,
                        'grade' => 'profession',
                        'starttime' => date('Y-m-d 00:00:00'),
                        'endtime' => date('Y-m-d 23:59:59', strtotime('+1 year')),
                        'remark' => isset($remark) ? $remark : null,
                    ];
                    break;

                case 'personal':
                    $param = [
                        'user_id' => $user_id,
                        'grade' => 'plus',
                        'starttime' => date('Y-m-d 00:00:00'),
                        'endtime' => date('Y-m-d 23:59:59', strtotime('+1 year')),
                        'remark' => isset($remark) ? $remark : null,
                    ];
                    break;

                default:
                    throw new \Exception('Unknown case of "mode".');
                    break;
            }

            \Core::set_usergrade($param);
        }
    }
}