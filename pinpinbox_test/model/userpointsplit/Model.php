<?php

namespace Model;

class userpointsplit extends \Model
{
    protected
        $database = 'site',
        $join_table = [],
        $memcache = 'site',
        $table = 'userpointsplit';

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
}