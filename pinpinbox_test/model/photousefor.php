<?php

class photouseforModel extends Model
{
    protected $database = 'site';
    protected $table = 'photousefor';
    protected $memcache = 'site';
    protected $join_table = ['album', 'photo', 'photousefor_user', 'user'];

    function __construct()
    {
        parent::__construct_child();
    }

    static function ableToExchangePhotoUsefor($photo_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return(\photoModel::usable($photo_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $Model_photousefor = (new \photouseforModel)
            ->column([
                'photousefor.`count`',
                'photousefor.starttime',
            ])
            ->join([
                ['INNER JOIN', 'photo', 'ON photo.photo_id = photousefor.photo_id AND photo.usefor = \'exchange\'']
            ])
            ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
            ->fetch();

        if (empty($Model_photousefor)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('資料不存在。');
            goto _return;
        } else {
            if (\photousefor_userModel::has_gained($photo_id, $user_id)) {
                $result = \Lib\Result::PHOTOUSEFOR_USER_HAS_GAINED;
                $message = _('您已經領取了。');
                goto _return;
            }

            if (\photousefor_userModel::has_exchanged($photo_id, $user_id)) {
                $result = \Lib\Result::PHOTOUSEFOR_USER_HAS_EXCHANGED;
                $message = _('您已經兌換了。');
                goto _return;
            }

            $time = time();

            if ($Model_photousefor['starttime'] && $Model_photousefor['starttime'] !== '0000-00-00 00:00:00' && $time < strtotime($Model_photousefor['starttime'])) {
                $result = \Lib\Result::PHOTOUSEFOR_NOT_YET_STARTED;
                $message = _('尚未開始。');
                goto _return;
            }

            if (\photouseforModel::has_expired($photo_id)) {
                $result = \Lib\Result::PHOTOUSEFOR_HAS_EXPIRED;
                $message = _('已過期。');
                goto _return;
            }

            if (\photouseforModel::has_sent_finished($photo_id)) {
                $result = \Lib\Result::PHOTOUSEFOR_HAS_SENT_FINISHED;
                $message = _('已發送完畢。');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function ableToSlotPhotoUsefor($photo_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return(\photoModel::usable($photo_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $Model_photousefor = (new \photouseforModel)
            ->column([
                'photousefor.`count`',
                'photousefor.endtime',
                'photousefor.starttime',
            ])
            ->join([
                ['INNER JOIN', 'photo', 'ON photo.photo_id = photousefor.photo_id AND photo.usefor = \'slot\'']
            ])
            ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
            ->fetchAll();

        if (empty($Model_photousefor)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('資料不存在。');
            goto _return;
        } else {
            if (\photousefor_userModel::has_gained($photo_id, $user_id)) {
                $result = \Lib\Result::PHOTOUSEFOR_USER_HAS_GAINED;
                $message = _('您已經領取了。');
                goto _return;
            }

            if (\photousefor_userModel::has_slotted($photo_id, $user_id)) {
                $result = \Lib\Result::PHOTOUSEFOR_USER_HAS_SLOTTED;
                $message = _('您已經抽獎了。');
                goto _return;
            }

            $time = time();

            //
            $before_start = true;
            $timeliness = false;

            foreach ($Model_photousefor as $v_0) {
                if ($v_0['starttime'] && $v_0['starttime'] !== '0000-00-00 00:00:00') {
                    $timeliness = true;

                    if (strtotime($v_0['starttime']) <= $time) {
                        $before_start = false;
                        break;
                    }
                }
            }

            if ($before_start && $timeliness) {
                $result = \Lib\Result::PHOTOUSEFOR_NOT_YET_STARTED;
                $message = _('尚未開始。');
                goto _return;
            }

            //
            $after_end = true;
            $timeliness = false;

            foreach ($Model_photousefor as $v_0) {
                if ($v_0['endtime'] && $v_0['endtime'] !== '0000-00-00 00:00:00') {
                    $timeliness = true;

                    if ($time <= strtotime($v_0['endtime'])) {
                        $after_end = false;
                        break;
                    }
                }
            }

            if ($after_end && $timeliness) {
                $result = \Lib\Result::PHOTOUSEFOR_HAS_EXPIRED;
                $message = _('已過期。');
                goto _return;
            }

            //
            $count = (new \photouseforModel)
                ->column(['COUNT(1)'])
                ->where([[[['photo_id', '=', $photo_id], ['`count`', '>', 0]], 'and']])
                ->fetchColumn();

            if ($count == 0) {
                $result = \Lib\Result::PHOTOUSEFOR_HAS_SENT_FINISHED;
                $message = _('已發送完畢。');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'photousefor_userModel'],
        ];
    }

    static function exchangePhotoUsefor($photo_id, $user_id, $device_id)
    {
        $photousefor_user_id = null;

        $Model_photousefor = (new \photouseforModel)
            ->column([
                '`count`',
                'photousefor_id',
            ])
            ->where([[[['photo_id', '=', $photo_id], ['`count`', '>', 0]], 'and']])
            ->lock('for update')
            ->fetch();

        if ($Model_photousefor) {
            (new \photouseforModel)
                ->where([[[['photousefor_id', '=', $Model_photousefor['photousefor_id']]], 'and']])
                ->edit([
                    '`count`' => $Model_photousefor['count'] - 1
                ]);

            $photousefor_user_id = (new \photousefor_userModel)
                ->add([
                    'calculate' => -1,
                    '`count`' => $Model_photousefor['count'],
                    'device_id' => $device_id,
                    'photousefor_id' => $Model_photousefor['photousefor_id'],
                    'state' => 'pretreat',
                    'user_id' => $user_id,
                ]);
        }

        return $photousefor_user_id;
    }

    static function getPhotoUsefor($photo_id)
    {
        $data = [
            'photo' => [],
            'photousefor' => []
        ];

        $Model_photousefor = (new \photouseforModel)
            ->column([
                'photo.usefor',
                'photousefor.description',
                'photousefor.endtime',
                'photousefor.image',
                'photousefor.name',
                'photousefor.photousefor_id',
                'photousefor.starttime',
            ])
            ->join([
                ['INNER JOIN', 'photo', 'ON photo.photo_id = photousefor.photo_id']
            ])
            ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
            ->fetch();

        if ($Model_photousefor) {
            $Image = new \Core\Image;

            $data = [
                'photo' => [
                    'usefor' => $Model_photousefor['usefor'],
                ],
                'photousefor' => [
                    'description' => strip_tags($Model_photousefor['description']),
                    'endtime' => $Model_photousefor['endtime'] === '0000-00-00 00:00:00' ? null : $Model_photousefor['endtime'],
                    'image' => is_image(PATH_UPLOAD . $Model_photousefor['image']) ? fileinfo($Image->set(PATH_UPLOAD . $Model_photousefor['image'])->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
                    'name' => $Model_photousefor['name'],
                    'photousefor_id' => $Model_photousefor['photousefor_id'],
                    'starttime' => $Model_photousefor['starttime'] === '0000-00-00 00:00:00' ? null : $Model_photousefor['starttime'],
                ]
            ];
        }

        return $data;
    }

    static function getPhotoUseforByUserId($photo_id, $user_id)
    {
        $data = [
            'photousefor' => []
        ];

        $Model_photousefor = (new \photouseforModel)
            ->column([
                'photousefor.description',
                'photousefor.endtime',
                'photousefor.image',
                'photousefor.name',
                'photousefor.photousefor_id',
                'photousefor.starttime',
                'photousefor.useless_award',
            ])
            ->join([['INNER JOIN', 'photousefor_user', 'ON photousefor_user.photousefor_id = photousefor.photousefor_id AND photousefor_user.user_id = ' . (new \photouseforModel)->quote($user_id)]])
            ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
            ->fetch();

        if ($Model_photousefor) {
            $Image = new \Core\Image;

            $data['photousefor'] = [
                'description' => strip_tags($Model_photousefor['description']),
                'endtime' => $Model_photousefor['endtime'] === '0000-00-00 00:00:00' ? null : $Model_photousefor['endtime'],
                'image' => is_file(PATH_UPLOAD . $Model_photousefor['image']) ? path2url($Image->set(PATH_UPLOAD . $Model_photousefor['image'])->setSize(\Config\Image::S5, \Config\Image::S5)->save()) : null,
                'name' => $Model_photousefor['name'],
                'photousefor_id' => $Model_photousefor['photousefor_id'],
                'starttime' => $Model_photousefor['starttime'] === '0000-00-00 00:00:00' ? null : $Model_photousefor['starttime'],
                'useless_award' => $Model_photousefor['useless_award'] ? true : false,
            ];
        }

        return $data;
    }

    static function has_expired($photo_id)
    {
        $boolean = false;

        $usefor = (new \photoModel)
            ->column(['usefor'])
            ->where([[[['photo_id', '=', $photo_id]], 'and']])
            ->fetchColumn();

        switch ($usefor) {
            case 'exchange':
                $Model_photousefor = (new \photouseforModel)
                    ->column([
                        'photousefor.endtime',
                    ])
                    ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
                    ->fetch();

                if ($Model_photousefor) {
                    if ($Model_photousefor['endtime'] && $Model_photousefor['endtime'] !== '0000-00-00 00:00:00' && time() > strtotime($Model_photousefor['endtime'])) {
                        $boolean = true;
                    }
                }
                break;

            case 'slot':
                $Model_photousefor = (new \photouseforModel)
                    ->column([
                        'photousefor.endtime',
                    ])
                    ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
                    ->fetchAll();

                if ($Model_photousefor) {
                    $after_end = true;
                    $timeliness = false;

                    foreach ($Model_photousefor as $v_0) {
                        if ($v_0['endtime'] && $v_0['endtime'] !== '0000-00-00 00:00:00') {
                            $timeliness = true;

                            if (time() <= strtotime($v_0['endtime'])) {
                                $after_end = false;
                                break;
                            }
                        }
                    }

                    if ($after_end && $timeliness) {
                        $boolean = true;
                    }
                }
                break;
        }

        return $boolean;
    }

    static function has_sent_finished($photo_id)
    {
        $boolean = false;

        $usefor = (new \photoModel)
            ->column(['usefor'])
            ->where([[[['photo_id', '=', $photo_id]], 'and']])
            ->fetchColumn();

        switch ($usefor) {
            case 'exchange':
                $Model_photousefor = (new \photouseforModel)
                    ->column(['`count`'])
                    ->where([[[['photo_id', '=', $photo_id]], 'and']])
                    ->fetch();

                if ($Model_photousefor && $Model_photousefor['count'] <= 0) {
                    $boolean = true;
                }
                break;

            case 'slot':
                $Model_photousefor = (new \photouseforModel)
                    ->column(['`count`'])
                    ->where([[[['photo_id', '=', $photo_id], ['`count`', '>', 0]], 'and']])
                    ->fetch();

                if (empty($Model_photousefor)) {
                    $boolean = true;
                }
                break;
        }

        return $boolean;
    }

    static function notYetStarted($photo_id)
    {
        $boolean = false;

        $usefor = (new \photoModel)
            ->column(['usefor'])
            ->where([[[['photo_id', '=', $photo_id]], 'and']])
            ->fetchColumn();

        switch ($usefor) {
            case 'exchange':
                $Model_photousefor = (new \photouseforModel)
                    ->column([
                        'photousefor.starttime',
                    ])
                    ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
                    ->fetch();

                if ($Model_photousefor) {
                    if ($Model_photousefor['starttime'] && $Model_photousefor['starttime'] !== '0000-00-00 00:00:00' && time() < strtotime($Model_photousefor['starttime'])) {
                        $boolean = true;
                    }
                }
                break;

            case 'slot':
                $Model_photousefor = (new \photouseforModel)
                    ->column([
                        'photousefor.starttime',
                    ])
                    ->where([[[['photousefor.photo_id', '=', $photo_id]], 'and']])
                    ->fetchAll();

                if ($Model_photousefor) {
                    $before_start = true;
                    $timeliness = false;

                    foreach ($Model_photousefor as $v_0) {
                        if ($v_0['starttime'] && $v_0['starttime'] !== '0000-00-00 00:00:00') {
                            $timeliness = true;

                            if (strtotime($v_0['starttime']) <= time()) {
                                $before_start = false;
                                break;
                            }
                        }
                    }

                    if ($before_start && $timeliness) {
                        $boolean = true;
                    }
                }
                break;
        }

        return $boolean;
    }

    static function slotPhotoUsefor($photo_id, $user_id, $device_id)
    {
        $photousefor_user_id = null;

        $Model_photousefor = (new \photouseforModel)
            ->column([
                '`count`',
                'endtime',
                'photousefor_id',
                'starttime',
            ])
            ->where([[[['photo_id', '=', $photo_id], ['`count`', '>', 0]], 'and']])
            ->lock('for update')
            ->fetchAll();

        if ($Model_photousefor) {
            $a_drawbox = [];
            $count = 0;
            foreach ($Model_photousefor as $v_0) {
                $time = time();

                if ($v_0['starttime'] && $v_0['starttime'] !== '0000-00-00 00:00:00' && strtotime($v_0['starttime']) > $time) {
                    continue;
                }

                if ($v_0['endtime'] && $v_0['endtime'] !== '0000-00-00 00:00:00' && strtotime($v_0['endtime']) < $time) {
                    continue;
                }

                $a_drawbox = array_pad($a_drawbox, $count += $v_0['count'], $v_0['photousefor_id']);
            }
            shuffle($a_drawbox);

            $photousefor_id = $a_drawbox[mt_rand(0, $count - 1)];

            $Model_photousefor = $Model_photousefor[array_search($photousefor_id, array_column($Model_photousefor, 'photousefor_id'))];

            (new \photouseforModel)
                ->where([[[['photousefor_id', '=', $Model_photousefor['photousefor_id']]], 'and']])
                ->edit(['`count`' => $Model_photousefor['count'] - 1]);

            $photousefor_user_id = (new \photousefor_userModel)
                ->add([
                    'photousefor_id' => $Model_photousefor['photousefor_id'],
                    'user_id' => $user_id,
                    'device_id' => $device_id,
                    '`count`' => $Model_photousefor['count'],
                    'calculate' => -1,
                    'state' => 'pretreat',
                ]);
        }

        return $photousefor_user_id;
    }
}