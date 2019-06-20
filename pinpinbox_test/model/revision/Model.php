<?php

namespace Model;

class revision extends \Model
{
    protected
        $database = 'site',
        $join_table = [],
        $memcache = 'site',
        $table = 'revision';

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

    static function setAlbum($user_id, $album_id, $data)
    {
        if ($data) {
            self::insertRevision([
                'data' => json_encode($data),
                'album_id' => $album_id,
                'object' => 'album',
                'user_id' => $user_id,
            ]);
        }
    }

    static function insertRevision(array $param)
    {
        switch ($param['object']) {
            case 'album':
                $version = (new \Model\revision())
                    ->column(['MAX(version)'])
                    ->where([[[['object', '=', 'album'], ['album_id', '=', $param['album_id']]], 'and']])
                    ->fetchColumn();
                break;
        }

        $detect = new \Mobile_Detect;

        if ($detect->isAndroidOS()) {
            $platform = 'google';
        } elseif ($detect->isiOS()) {
            $platform = 'apple';
        } else {
            $platform = 'web';
        }

        (new \Model\revision())
            ->add(
                array_merge(
                    $param,
                    [
                        'platform' => $platform,
                        'version' => ++$version,
                    ]
                )
            );
    }
}