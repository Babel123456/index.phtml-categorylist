<?php

class albumqueueModel extends Model
{
    protected $database = 'site';
    protected $table = 'albumqueue';
    protected $memcache = 'site';
    protected $join_table = ['album,', 'albumstatistics', 'user'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'albumModel'],
            ['class' => 'albumstatisticsModel'],
            ['class' => 'userModel'],
        ];
    }

    function myCollect($user_id, array $where = null, array $order = null, $limit = null)
    {
        $column = [
            'albumqueue.album_id',
            'albumqueue.user_id creative_user_id',
            'album.template_id',
            'album.name album_name',
            'album.description',
            'album.cover',
            'album.location',
            'album.point',
            'album.zipped',
            'album.act',
            'album.inserttime album_inserttime',
            'album.user_id user_id',
            'user.name user_name',
            'albumstatistics.viewed',
            'categoryarea_category.categoryarea_id',
        ];
        $join = [
            ['inner join', 'album', 'using(album_id)'],
            ['inner join', 'user', 'ON album.user_id = user.user_id'],
            ['inner join', 'albumstatistics', 'using(album_id)'],
            ['inner join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];
        $where = array_merge([[[['album.state', 'in', ['success']], ['album.act', '=', 'open']], 'and']], (array)$where);

        return (new \albumqueueModel)->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
    }
}