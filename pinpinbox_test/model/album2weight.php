<?php

class album2weightModel extends Model
{
    protected $database = 'site';
    protected $table = 'album2weight';
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