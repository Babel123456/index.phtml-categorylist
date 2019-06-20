<?php

class photousefor_userModel extends Model
{
    protected $database = 'site';
    protected $table = 'photousefor_user';
    protected $memcache = 'site';
    protected $join_table = ['album', 'photo', 'photousefor', 'user'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'photouseforModel'],
        ];
    }

    static function ableToGain($photousefor_user_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return(self::usable($photousefor_user_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $state = (new \photousefor_userModel)
            ->column(['state'])
            ->where([[[['photousefor_user_id', '=', $photousefor_user_id], ['user_id', '=', $user_id]], 'and']])
            ->fetchColumn();

        if (empty($state)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('Data does not exist.');
            goto _return;
        } else {
            switch ($state) {
                case 'pretreat':
                    break;

                case 'success':
                    $result = \Lib\Result::USER_ERROR;
                    $message = _('您已經領取了。');
                    goto _return;
                    break;

                default:
                    \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_ERROR, 'Unknown case. "' . $state . '" of state.');
                    break;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function ableToUpdateUsefor_User($photousefor_user_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return(self::usable($photousefor_user_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function gain($photousefor_user_id, $user_id, array $obj_param)
    {
        (new \photousefor_userModel)
            ->where([[[['photousefor_user_id', '=', $photousefor_user_id], ['user_id', '=', $user_id]], 'and']])
            ->edit([
                'address' => isset($obj_param['address']) ? $obj_param['address'] : \Schema\photousefor_user::address_Default,
                'cellphone' => isset($obj_param['cellphone']) ? \Core\I18N::cellphone($obj_param['cellphone']) : \Schema\photousefor_user::cellphone_Default,
                'name' => isset($obj_param['name']) ? $obj_param['name'] : \Schema\photousefor_user::name_Default,
                'state' => 'success',
            ]);
    }

    static function has_exchanged($photo_id, $user_id)
    {
        $count = (new \photouseforModel)
            ->column(['COUNT(1)'])
            ->join([
                ['INNER JOIN', 'photousefor_user', 'ON photousefor_user.photousefor_id = photousefor.photousefor_id AND photousefor_user.user_id = ' . (new \photouseforModel)->quote($user_id)]
            ])
            ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
            ->fetchColumn();

        return $count ? true : false;
    }

    static function has_gained($photo_id, $user_id)
    {
        $state = (new \photouseforModel)
            ->column(['photousefor_user.state'])
            ->join([
                ['INNER JOIN', 'photousefor_user', 'ON photousefor_user.photousefor_id = photousefor.photousefor_id AND photousefor_user.user_id = ' . (new \photouseforModel)->quote($user_id)]
            ])
            ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
            ->fetchColumn();

        return $state === 'success' ? true : false;
    }

    static function has_slotted($photo_id, $user_id)
    {
        return self::has_exchanged($photo_id, $user_id);
    }

    static function updateUsefor_User($photousefor_user_id, $user_id, array $obj_param)
    {
        (new \photousefor_userModel)
            ->where([[[['photousefor_user_id', '=', $photousefor_user_id], ['user_id', '=', $user_id]], 'and']])
            ->edit([
                'address' => isset($obj_param['address']) ? $obj_param['address'] : \Schema\photousefor_user::address_Default,
                'cellphone' => isset($obj_param['cellphone']) ? \Core\I18N::cellphone($obj_param['cellphone']) : \Schema\photousefor_user::cellphone_Default,
                'name' => isset($obj_param['name']) ? $obj_param['name'] : \Schema\photousefor_user::name_Default,
            ]);
    }

    static function usable($photousefor_user_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        $Model_photousefor_user = (new \photousefor_userModel)
            ->column(['state'])
            ->where([[[['photousefor_user_id', '=', $photousefor_user_id], ['user_id', '=', $user_id]], 'and']])
            ->fetch();

        if (empty($Model_photousefor_user)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('Data does not exist.');
            goto _return;
        } else {
            $album_id = (new \photousefor_userModel)
                ->column(['album.album_id'])
                ->join([
                    ['INNER JOIN', 'photousefor', 'using(photousefor_id)'],
                    ['INNER JOIN', 'photo', 'using(photo_id)'],
                    ['INNER JOIN', 'album', 'using(album_id)'],
                ])
                ->where([
                    [[['photousefor_user.photousefor_user_id', '=', $photousefor_user_id]], 'and']
                ])
                ->fetchColumn();

            list ($result, $message) = array_decode_return((new \albumModel)->usable_v2($album_id, $user_id));
            if ($result != \Lib\Result::SYSTEM_OK) {
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }
}