<?php

class exchangeModel extends Model
{
    protected $database = 'cashflow';
    protected $table = 'exchange';
    protected $memcache = 'cashflow';
    protected $join_table = ['album', 'template'];

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

    static function getAlbumSponsorList($album_id, $user_id, $limit = '0,4')
    {
        $list = [];

        $sponsor_user_list = (new \userModel())
            ->column([
                'user.discuss',
                'user.user_id',
                'user.name',
                'SUM(exchange.point + exchange.point_free) `point`'
            ])
            ->join([
                ['INNER JOIN', 'exchange', 'ON exchange.type = \'album\' AND exchange.id = ' . (new \userModel)->quote($album_id) . ' AND exchange.user_id = user.user_id AND user.act = \'open\''],
            ])
            ->group(['user.user_id'])
            ->having([
                ['SUM(exchange.point + exchange.point_free)', '>', 0]
            ])
            ->order([
                '`point`' => 'DESC'
            ])
            ->limit($limit)
            ->fetchAll();

        foreach ($sponsor_user_list as $v_0) {
            $picture = PATH_STORAGE . \userModel::getPicture($v_0['user_id']);

            $list[] = [
                'user' => [
                    'discuss' => $v_0['discuss'] === 'open' ? true : false,
                    'is_follow' => \followModel::is_follow($user_id, $v_0['user_id']),
                    'name' => $v_0['name'],
                    'picture' => is_image($picture) ? $picture : null,
                    'point' => (int)$v_0['point'],
                    'user_id' => (int)$v_0['user_id'],
                ]
            ];
        }

        return $list;
    }

    static function getSponsorList($user_id, $limit = '0,4')
    {
        $list = [];

        $array_0 = [];

        $sponsor_album_list = (new \userModel())
            ->column([
                'user.discuss',
                'user.user_id',
                'user.name',
                'SUM(exchange.point + exchange.point_free) `point`'
            ])
            ->join([
                ['INNER JOIN', 'exchange', 'ON exchange.type = \'album\' AND exchange.user_id = user.user_id AND user.act = \'open\''],
                ['INNER JOIN', 'album', 'ON album.album_id = exchange.id AND album.act IN (\'close\', \'open\') AND album.user_id = ' . (new \userModel())->quote($user_id)]
            ])
            ->group(['user.user_id'])
            ->having([
                ['SUM(exchange.point + exchange.point_free)', '>', 0]
            ])
            ->fetchAll();

        foreach ($sponsor_album_list as $v_0) {
            $array_0[$v_0['user_id']] = [
                'discuss' => $v_0['discuss'],
                'name' => $v_0['name'],
                'point' => $v_0['point'],
                'user_id' => $v_0['user_id'],
            ];
        }

        $sponsor_template_list = (new \userModel())
            ->column([
                'user.discuss',
                'user.user_id',
                'user.name',
                'SUM(exchange.point + exchange.point_free) `point`'
            ])
            ->join([
                ['INNER JOIN', 'exchange', 'ON exchange.type = \'template\' AND exchange.user_id = user.user_id AND user.act = \'open\''],
                ['INNER JOIN', 'template', 'ON template.template_id = exchange.id AND template.act IN (\'close\', \'open\') AND template.user_id = ' . (new \userModel())->quote($user_id)]
            ])
            ->group(['user.user_id'])
            ->having([
                ['SUM(exchange.point + exchange.point_free)', '>', 0]
            ])
            ->fetchAll();

        foreach ($sponsor_template_list as $v_0) {
            if (isset($array_0[$v_0['user_id']])) {
                $array_0[$v_0['user_id']]['point'] += $v_0['point'];
            } else {
                $array_0[$v_0['user_id']] = [
                    'discuss' => $v_0['discuss'],
                    'name' => $v_0['name'],
                    'point' => $v_0['point'],
                    'user_id' => $v_0['user_id'],
                ];
            }
        }

        //2018-01-04 Lion: 依 point desc 排序
        usort($array_0, function ($a, $b) {
            if ($a['point'] == $b['point']) {
                return 0;
            }

            return ($a['point'] < $b['point']) ? 1 : -1;
        });

        list ($offset, $length) = explode(',', $limit);

        $array_0 = array_slice($array_0, $offset, $length);

        if ($array_0) {
            foreach ($array_0 as $v_0) {
                $picture = PATH_STORAGE . \userModel::getPicture($v_0['user_id']);

                $list[] = [
                    'user' => [
                        'discuss' => $v_0['discuss'] === 'open' ? true : false,
                        'is_follow' => \followModel::is_follow($user_id, $v_0['user_id']),
                        'name' => $v_0['name'],
                        'picture' => is_image($picture) ? $picture : null,
                        'point' => $v_0['point'],
                        'user_id' => $v_0['user_id'],
                    ]
                ];
            }
        }

        return $list;
    }
}