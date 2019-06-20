<?php

class usergradeModel extends Model
{
    protected $database = 'site';
    protected $table = 'usergrade';
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

    static function getPhotoLimitOfAlbum($user_id)
    {
        $int = 0;

        if (!empty($user_id)) {
            $a_photo_limit_of_album = json_decode(Core::settings('PHOTOS_PER_ALBUM'), true);
            $usergrade = Core::get_usergrade($user_id);

            if (!empty($a_photo_limit_of_album[$usergrade])) {
                $int = $a_photo_limit_of_album[$usergrade];
            }
        }

        return $int;
    }
}