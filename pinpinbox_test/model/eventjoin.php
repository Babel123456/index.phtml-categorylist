<?php

class eventjoinModel extends Model
{
    protected $database = 'site';
    protected $table = 'eventjoin';
    protected $memcache = 'site';
    protected $join_table = ['album', 'event'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'albumModel'],
            ['class' => 'eventModel'],
        ];
    }

    static function getEventJoinList($event_id, $user_id, array $order = null, $limit = null, $searchkey = null)
    {
        $eventjoinArray = [];

        $where = [[[['eventjoin.event_id', '=', $event_id]], 'and']];

        if ($searchkey !== null) {
            $array_album_id = array_column(
                Solr('album')->column(['album_id'])->where([[[['name', '=', $searchkey]], 'and']])->fetchAll(),
                'album_id'
            );

            $array_album_id = array_unique(array_merge($array_album_id, [$searchkey]));//作為搜尋 album_id

            $array_user_id = array_column(
                Solr('user')->column(['user_id'])->where([[[['name', '=', $searchkey]], 'and']])->fetchAll(),
                'user_id'
            );

            $where = array_merge(
                $where,
                [[[['album.album_id', 'IN', $array_album_id], ['user.user_id', 'IN', $array_user_id]], 'or']]
            );
        }

        $eventjoinModel = (new \eventjoinModel())
            ->column([
                'album.album_id',
                'album.cover',
                'album.cover_hex',
                'album.name album_name',
                'eventjoin.count',
                'user.user_id',
                'user.name user_name',
            ])
            ->join([
                [
                    'INNER JOIN',
                    'album',
                    'on album.album_id = eventjoin.album_id AND album.act = \'open\''
                ],
                [
                    'INNER JOIN',
                    'user',
                    'on user.user_id = album.user_id AND user.act = \'open\''
                ]
            ])
            ->where($where)
            ->order(
                ['eventjoin.count' => 'DESC']
            )
            ->limit($limit)
            ->fetchAll();

        foreach ($eventjoinModel as $v_0) {
            $eventjoinArray[] = [
                'album' => [
                    'album_id' => $v_0['album_id'],
                    'cover' => $v_0['cover'],
                    'cover_hex' => $v_0['cover_hex'],
                    'name' => $v_0['album_name'],
                ],
                'eventjoin' => [
                    'count' => $v_0['count'],
                ],
                'user' => [
                    'name' => $v_0['user_name'],
                    'user_id' => $v_0['user_id'],
                ],
            ];
        }

        return $eventjoinArray;
    }
}