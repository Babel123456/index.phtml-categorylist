<?php

class tokenModel extends Model
{
    protected $database = 'site';
    protected $table = 'token';
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

    function refreshToken($user_id)
    {
        $token = md5($user_id . time() . SITE_SECRET);

        (new tokenModel)->where([[[['user_id', '=', $user_id]], 'and']])->edit(['token' => $token]);

        return $token;
    }
}