<?php

namespace Model;

class userpointsplitqueue extends \Model
{
    protected
        $database = 'site',
        $join_table = [],
        $memcache = 'site',
        $table = 'userpointsplitqueue';

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