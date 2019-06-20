<?php

class pushlogModel extends Model
{
    protected $database = 'site';
    protected $table = 'pushlog';
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
}