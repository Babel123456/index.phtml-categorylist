<?php

class followfromModel extends Model
{
    protected $database = 'site';
    protected $table = 'followfrom';
    protected $memcache = 'site';
    protected $join_table = ['user'];

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

    static function ableToGetFollowFromList($user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function getFollowFromList($user_id, $limit = '0,4')
    {
        $list = [];

        $Model_user = (new \userModel)
            ->column([
                'user.discuss',
                'user.name',
                'user.user_id',
            ])
            ->join([
                ['INNER JOIN', 'followfrom', 'ON followfrom.from = user.user_id AND followfrom.user_id = ' . (new \userModel)->quote($user_id)]
            ])
            ->where([[[['user.act', '=', 'open']], 'and']])
            ->order([
                'followfrom.inserttime' => 'DESC'
            ])
            ->limit($limit)
            ->fetchAll();

        foreach ($Model_user as $v_0) {
            $picture = PATH_STORAGE . \userModel::getPicture($v_0['user_id']);

            $list[] = [
                'user' => [
                    'discuss' => $v_0['discuss'] === 'open' ? true : false,
                    'is_follow' => \followModel::is_follow($user_id, $v_0['user_id']),
                    'name' => $v_0['name'],
                    'picture' => is_image($picture) ? $picture : null,
                    'user_id' => $v_0['user_id'],
                ]
            ];
        }

        return $list;
    }
}