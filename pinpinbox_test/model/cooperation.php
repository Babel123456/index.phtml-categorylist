<?php

class cooperationModel extends Model
{
    protected $database = 'site';
    protected $table = 'cooperation';
    protected $memcache = 'site';
    protected $join_table = ['user'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        $return = [
            ['class' => __CLASS__],
            ['class' => 'albumModel'],
            ['class' => 'photoModel'],
        ];

        return $return;
    }

    function deleteCooperation($type, $type_id, $user_id)
    {
        $result = 1;
        $message = null;

        if (!in_array($type, ['album', 'template'])) {
            $result = 0;
            $message = _('Unknown case of type.');
            goto _return;
        }

        list($result, $message) = array_decode_return(Model('user')->usable($user_id));
        if (!$result) goto _return;

        Model('cooperation')->where([[[['`type`', '=', $type], ['type_id', '=', $type_id], ['user_id', '=', $user_id]], 'and']])->delete();

        Model('subscription')->destroy($user_id, $type . 'cooperation', $type_id);

        _return:
        return array_encode_return($result, $message);
    }

    function insertCooperation($type, $type_id, $user_id)
    {
        $result = 1;
        $message = null;

        if (!in_array($type, ['album', 'template'])) {
            $result = 0;
            $message = _('Unknown case of type.');
            goto _return;
        }

        list ($result, $message) = array_decode_return((new userModel)->usable($user_id));
        if (!$result) goto _return;

        $m = Model($type)
            ->column(['user.user_id', 'user.name'])
            ->join([['inner join', 'user', 'using(user_id)']])
            ->where([[[[$type . '.' . $type . '_id', '=', $type_id]], 'and']])
            ->fetch();

        if ($m['user_id'] == $user_id) {
            $result = 0;
            switch ($type) {
                case 'album':
                    $message = _('此用戶為該相本的管理者。');
                    break;

                case 'template':
                    $message = _('此用戶為該版型的管理者。');
                    break;
            }
            goto _return;
        }

        $m_cooperation = (new cooperationModel)
            ->column(['count(1)'])
            ->where([[[['`type`', '=', $type], ['type_id', '=', $type_id], ['user_id', '=', $user_id]], 'and']])
            ->fetchColumn();

        if ($m_cooperation) {
            $result = 0;
            $message = _('共用關係已建立。');
            goto _return;
        }

        (new cooperationModel)
            ->add([
                'type' => $type,
                'type_id' => $type_id,
                'user_id' => $user_id,
                'identity' => 'viewer',
            ]);

        subscriptionModel::build($user_id, $type . 'cooperation', $type_id);

        (new topicModel)
            ->publish(
                $m['user_id'],
                'user',
                $user_id,
                $m['name'] . _('邀請您共用作品！立即進入？'),
                'albumcooperation',
                $type_id,
                [
                    'title' => M_PACKAGE,
                    'message' => $m['name'] . _('邀請您共用作品！立即進入？'),
                    'icon' => frontstageController::type2image_url('albumcooperation', $type_id),
                ]
            );

        _return:
        return array_encode_return($result, $message);
    }

    function menu($type, $type_id)
    {
        $this->column([
            'cooperation.identity',
            'user.user_id',
            'user.name',
        ]);

        $this->join([
            ['inner join', 'user', 'using(user_id)']
        ]);

        $this->where([
            [[['cooperation.type', '=', $type], ['cooperation.type_id', '=', $type_id], ['user.act', '=', 'open']], 'and']
        ]);

        return $this;
    }

    function updateCooperation($type, $type_id, $user_id, $identity)
    {
        $result = 1;
        $message = null;

        if (!in_array($type, ['album', 'template'])) {
            $result = 0;
            $message = _('Unknown case of type.');
            goto _return;
        }

        if (!in_array($identity, ['admin', 'approver', 'editor', 'viewer'])) {
            $result = 0;
            $message = _('Unknown case of identity.');
            goto _return;
        }

        list($result, $message) = array_decode_return(Model('user')->usable($user_id));
        if (!$result) goto _return;

        $m_cooperation = Model('cooperation')->column(['count(1)'])->where([[[['`type`', '=', $type], ['type_id', '=', $type_id], ['user_id', '=', $user_id]], 'and']])->fetchColumn();
        if (!$m_cooperation) {
            $result = 0;
            $message = _('共用關係不存在。');
            goto _return;
        }

        $edit = [
            'identity' => $identity,
        ];
        Model('cooperation')->where([[[['`type`', '=', $type], ['type_id', '=', $type_id], ['user_id', '=', $user_id]], 'and']])->edit($edit);

        _return:
        return array_encode_return($result, $message);
    }

    function getCooeration($type, $type_id, $user_id)
    {
        $column = [
            'cooperation.identity',
            'user.user_id',
            'user.name',
        ];

        $join = [
            ['inner join', 'user', 'using(user_id)']
        ];

        $where = [
            [[['cooperation.type', '=', $type], ['cooperation.type_id', '=', $type_id], ['user.act', '=', 'open'], ['user.user_id', '=', $user_id]], 'and']
        ];

        $return = Model('cooperation')->column($column)->where($where)->join($join)->fetch();

        return $return;
    }
}