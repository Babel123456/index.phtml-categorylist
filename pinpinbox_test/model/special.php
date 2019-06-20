<?php

class specialModel extends Model
{
    protected $database = 'site';
    protected $table = 'special';
    protected $memcache = 'site';
    protected $join_table = ['special_award'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'special_awardModel'],
        ];
    }

    static function getUrl($event_id)
    {
        return \frontstageController::url('event', 'special', ['event_id' => $event_id]);
    }
}