<?php

namespace Model;

class bookmark extends \Model
{
    protected
        $database = 'site',
        $join_table = [],
        $memcache = 'site',
        $table = 'bookmark';

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

    static function ableToInsertBookmark($user_id, $photo_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return(\photoModel::usable($photo_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function getBookmarkList($user_id)
    {
        $list = [];

        $Model_bookmark = (new \Model\bookmark)
            ->column([
                'photo.photo_id',
                'photo.usefor',
            ])
            ->join([
                ['INNER JOIN', 'photo', 'ON photo.photo_id = bookmark.photo_id AND photo.act = \'open\''],
                ['INNER JOIN', 'album', 'ON album.album_id = photo.album_id AND album.act = \'open\' AND album.state = \'success\' AND album.zipped = true']
            ])
            ->where([[[['bookmark.user_id', '=', $user_id]], 'and']])
            ->order([
                'bookmark.inserttime' => 'DESC'
            ])
            ->fetchAll();

        if ($Model_bookmark) {
            $Image = new \Core\Image();

            foreach ($Model_bookmark as $v_0) {
                switch ($v_0['usefor']) {
                    case 'exchange':
                        $Model_photousefor = (new \photouseforModel)
                            ->column([
                                'photousefor.description',
                                'photousefor.endtime',
                                'photousefor.image',
                                'photousefor.name',
                                'photousefor.photousefor_id',
                                'photousefor.starttime',
                                'photousefor_user.photousefor_user_id',
                            ])
                            ->join([
                                ['LEFT JOIN', 'photousefor_user', 'ON photousefor_user.photousefor_id = photousefor.photousefor_id AND photousefor_user.user_id = ' . (new \photouseforModel)->quote($user_id)]
                            ])
                            ->where([[[['photousefor.photo_id', '=', $v_0['photo_id']]], 'and']])
                            ->fetch();
                        break;

                    case 'slot':
                        $Model_photousefor = (new \photouseforModel)
                            ->column([
                                'photousefor.description',
                                'photousefor.endtime',
                                'photousefor.image',
                                'photousefor.name',
                                'photousefor.photousefor_id',
                                'photousefor.starttime',
                                'photousefor_user.photousefor_user_id',
                            ])
                            ->join([
                                ['INNER JOIN', 'photousefor_user', 'ON photousefor_user.photousefor_id = photousefor.photousefor_id AND photousefor_user.user_id = ' . (new \photouseforModel)->quote($user_id)]
                            ])
                            ->where([[[['photousefor.photo_id', '=', $v_0['photo_id']]], 'and']])
                            ->fetch();
                        break;
                }

                $list[] = [
                    'photo' => [
                        'has_gained' => \photousefor_userModel::has_gained($v_0['photo_id'], $user_id),
                        'photo_id' => $v_0['photo_id'],
                    ],
                    'photousefor' => [
                        'description' => $Model_photousefor['description'],
                        'endtime' => $Model_photousefor['endtime'] === '0000-00-00 00:00:00' ? null : $Model_photousefor['endtime'],
                        'image' => is_image(PATH_UPLOAD . $Model_photousefor['image']) ? path2url($Image->set(PATH_UPLOAD . $Model_photousefor['image'])->setSize(\Config\Image::S5, \Config\Image::S5)->save()) : null,
                        'name' => $Model_photousefor['name'],
                        'photousefor_id' => $Model_photousefor['photousefor_id'],
                        'starttime' => $Model_photousefor['starttime'] === '0000-00-00 00:00:00' ? null : $Model_photousefor['starttime'],
                    ],
                    'photousefor_user' => $Model_photousefor['photousefor_user_id'] === null ?
                        null
                        :
                        [
                            'photousefor_user_id' => $Model_photousefor['photousefor_user_id']
                        ]
                ];
            }
        }

        return $list;
    }

    static function insertBookmark($user_id, $photo_id)
    {
        (new \Model\bookmark)
            ->replace([
                'photo_id' => $photo_id,
                'user_id' => $user_id,
            ]);
    }

    static function is_existing($user_id, $photo_id)
    {
        $count = (new \Model\bookmark)
            ->column(['COUNT(1)'])
            ->where([[[['user_id', '=', $user_id], ['photo_id', '=', $photo_id]], 'and']])
            ->fetchColumn();

        return $count ? true : false;
    }
}