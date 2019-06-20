<?php

class userwhitelistModel extends Model
{
    protected $database = 'site';
    protected $memcache = 'site';
    protected $table = 'userwhitelist';
    protected $join_table = array();

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        $return = array(
            array('class' => __CLASS__),
        );

        return $return;
    }
}