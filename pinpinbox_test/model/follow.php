<?php

class followModel extends Model
{
    protected $database = 'site';
    protected $table = 'follow';
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
            ['class' => 'albumModel'],
            ['class' => 'userModel'],
        ];
    }

    function ableToBuild(array $param_followfrom, array $param_followto)
    {
        $result = 1;
        $message = null;

        if (!isset($param_followfrom['user_id']) || !is_numeric($param_followfrom['user_id'])) {
            $result = 0;
            $message = 'Param error of "followfrom\'s user_id"';
            goto _return;
        }

        if (!isset($param_followto['user_id']) || !is_numeric($param_followto['user_id'])) {
            $result = 0;
            $message = 'Param error of "followto\'s user_id"';
            goto _return;
        }

        $m_user = (new userModel())->column(['COUNT(1)'])->where([[[['user_id', '=', $param_followfrom['user_id']]], 'and']])->fetch();

        if (empty($m_user)) {
            $result = 0;
            $message = 'Data of "followfrom\'s user" does not exist';
            goto _return;
        }

        $m_user = (new userModel())->column(['COUNT(1)'])->where([[[['user_id', '=', $param_followto['user_id']]], 'and']])->fetch();

        if (empty($m_user)) {
            $result = 0;
            $message = 'Data of "followto\'s user" does not exist';
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function build(array $param_followfrom, array $param_followto)
    {
        $user_id_others = $param_followfrom['user_id'];
        $user_id_myself = $param_followto['user_id'];

        (new followfromModel)->replace([
            'user_id' => $user_id_others,
            '`from`' => $user_id_myself
        ]);

        (new followtoModel)->replace([
            'user_id' => $user_id_myself,
            '`to`' => $user_id_others,
        ]);

        (new followModel)->replace([
            'user_id' => $user_id_myself,
            'count_to' => (new followtoModel)->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id_myself]], 'and']])->fetchColumn(),
            'count_from' => (new followfromModel)->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id_myself]], 'and']])->fetchColumn(),
        ]);

        (new followModel())->replace([
            'user_id' => $user_id_others,
            'count_to' => (new followtoModel)->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id_others]], 'and']])->fetchColumn(),
            'count_from' => (new followfromModel)->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id_others]], 'and']])->fetchColumn(),
        ]);

        subscriptionModel::build($user_id_myself, 'follow', $user_id_others);

        $SNSparam = Core::getSNSParams([
            'trigger' => [
                'user_id' => $user_id_myself,
                'type' => 'user',
                'typeId' => $user_id_myself,
                'refer' => 'userFollow',
            ],
            'targetId' => $user_id_others,
            'typeOfSNS' => 'follow',
        ]);
        (new topicModel)
            ->publish($user_id_myself, 'user', $user_id_others, $SNSparam['message'], 'user', $user_id_myself, $SNSparam);
    }

    function destroy()
    {

    }

    static function is_follow($user_id_myself, $user_id_others)
    {
        $m_followto = (new followtoModel())->column(['`to`'])->where([[[['user_id', '=', $user_id_myself]], 'and']])->fetchAll();

        $a_to = array_column($m_followto, 'to');

        return (empty($a_to) || !in_array($user_id_others, $a_to)) ? false : true;
    }
}