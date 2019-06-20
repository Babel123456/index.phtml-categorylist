<?php

class albumstatisticsModel extends Model
{
    protected $database = 'site';
    protected $table = 'albumstatistics';
    protected $memcache = 'site';
    protected $join_table = ['album', 'categoryarea', 'category', 'categoryarea_category', 'user'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'albumModel'],
            ['class' => 'categoryareaModel'],
            ['class' => 'userModel'],
        ];
    }

    static function getCountOfExchange($album_id)
    {
        return (int)(new \exchangeModel)
            ->column(['COUNT(1)'])
            ->where([
                [[['`type`', '=', 'album'], ['id', '=', $album_id]], 'and'],
                [[['`point`', '>', 0], ['point_free', '>', 0]], 'or']
            ])
            ->fetchColumn();
    }
}