<?php

class albumModel extends Model
{
    protected $database = 'site';
    protected $table = 'album';
    protected $memcache = 'site';
    protected $join_table = ['audio', 'albumindex', 'albumqueue', 'albumstatistics', 'category', 'categoryarea', 'categoryarea_category', 'cooperation', 'follow', 'photo', 'template', 'user', 'event_templatejoin', 'eventjoin', 'reward'];

    function __construct()
    {
        parent::__construct_child();
    }

    function ableToExchange(array $param)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        /**
         * 必填
         */
        $album_id = isset($param['album_id']) ? $param['album_id'] : null;
        $point = isset($param['point']) ? $param['point'] : null;
        $platform = isset($param['platform']) ? $param['platform'] : null;
        $user_id = isset($param['user_id']) ? $param['user_id'] : null;

        if ($album_id === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "album_id" is required.';
            goto _return;
        } else {
            $m_album = (new albumModel)
                ->column([
                    'album.act album_act',
                    'album.cover',
                    'album.point',
                    'user.user_id',
                    'user.act user_act',
                ])
                ->join([['left join', 'user', 'using(user_id)']])
                ->where([[[['album.album_id', '=', $album_id]], 'and']])
                ->fetch();

            if (empty($m_album)) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('Album does not exist.');
                goto _return;
            } else {
                if ($m_album['album_act'] == 'close') {
                    $result = \Lib\Result::USER_ERROR;
                    $message = _('Album is not open.');
                    goto _return;
                }

                if ($m_album['album_act'] == 'delete') {
                    $result = \Lib\Result::USER_ERROR;
                    $message = _('Album does not exist.');
                    goto _return;
                }

                if ($m_album['user_act'] == 'close') {
                    $result = \Lib\Result::USER_ERROR;
                    $message = _('Author is not open.');
                    goto _return;
                }

                if ($m_album['user_id'] == $user_id) {
                    $result = \Lib\Result::USER_OWNS_THE_ALBUM;
                    $message = _('You are the author of this album.');
                    goto _return;
                } else {
                    $m_cooperation = (new cooperationModel)
                        ->column(['COUNT(1)'])
                        ->where([[[['`type`', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])
                        ->fetchColumn();

                    if ($m_cooperation) {
                        $result = \Lib\Result::USER_OWNS_THE_ALBUM;
                        $message = _('您正參與這本相簿的共用。');
                        goto _return;
                    } else {
                        $m_albumqueue = (new albumqueueModel)
                            ->column(['COUNT(1)'])
                            ->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])
                            ->fetchColumn();

                        if ($m_albumqueue) {
                            $result = \Lib\Result::USER_OWNS_THE_ALBUM;
                            $message = _('You already have this album.');
                            goto _return;
                        }
                    }
                }
            }
        }

        if ($point === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "point" is required.';
            goto _return;
        } else {
            if ($m_album['point'] > $point) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('您支付的 P 點不足。');
                goto _return;
            }

            $userpoint = (new userModel)->getPoint($user_id, $platform);

            if ($point > $userpoint) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('您剩餘的 P 點不足。');
                goto _return;
            }
        }

        if ($platform === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "platform" is required.';
            goto _return;
        } else {
            if (!in_array($platform, array_diff(\Schema\userpoint::$platform, ['none']))) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. "platform" is an invalid value.';
                goto _return;
            }
        }

        if ($user_id === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "user_id" is required.';
            goto _return;
        } else {
            list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
            if ($result != \Lib\Result::SYSTEM_OK) {
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function exchange($album_id, array $param, array $reward)
    {
        list (, , , $data) = array_decode_return(Core::exchange($param['user_id'], $param['platform'], 'album', $album_id, $param['point']));

        if ($reward) {
            (new rewardModel)
                ->add([
                    'user_id' => $param['user_id'],
                    'exchange_id' => $data['exchange_id'],
                    'type' => 'album',
                    'type_id' => $album_id,
                    'recipient' => $reward['recipient'],
                    'recipient_address' => $reward['recipient_address'],
                    'recipient_tel' => $reward['recipient_tel'],
                    'recipient_text' => $reward['recipient_text'],
                    'inserttime' => inserttime(),
                ]);
        }

        return $data;
    }

    /**
     * @param $album_id
     * @param $user_id
     * @return array
     * @deprecated 請改用 ableToExchange
     */
    function buyable($album_id, $user_id)
    {
        $result = 1;
        $message = null;

        $column = [
            'album.cover',
            'album.act album_act',
            'user.user_id',
            'user.act user_act',
        ];
        $m_album = (new \albumModel)
            ->column($column)
            ->join([['left join', 'user', 'using(user_id)']])
            ->where([[[['album.album_id', '=', $album_id]], 'and']])
            ->fetch();

        if (empty($m_album)) {
            $result = 0;
            $message = _('Album does not exist.');
            $m_album = null;
            goto _return;
        } else {
            if ($m_album['album_act'] == 'none') {
                $result = 0;
                $message = _('[Album] occur exception, please contact us.');
                $m_album = null;
                goto _return;
            }

            if ($m_album['album_act'] == 'close') {
                $result = 0;
                $message = _('Album is not open.');
                $m_album = null;
                goto _return;
            }

            if ($m_album['album_act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                $m_album = null;
                goto _return;
            }

            if ($m_album['user_act'] == 'close') {
                $result = 0;
                $message = _('Author is not open.');
                $m_album = null;
                goto _return;
            }

            if ($m_album['user_id'] == $user_id) {
                $result = 2;
                $message = _('You are the author of this album.');
                $m_album = null;
                goto _return;
            } else {
                $m_cooperation = Model('cooperation')->column(['count(1)'])->where([[[['`type`', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])->fetchColumn();
                if ($m_cooperation) {
                    $result = 2;
                    $message = _('您正參與這本相簿的共用。');
                    $m_album = null;
                    goto _return;
                } else {
                    $m_albumqueue = Model('albumqueue')->column(['count(1)'])->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->fetchColumn();
                    if ($m_albumqueue) {
                        $result = 2;
                        $message = _('You already have this album.');
                        $m_album = null;
                        goto _return;
                    }
                }
            }
        }

        _return:
        return array_encode_return($result, $message, null, $m_album);
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'albumstatisticsModel'],
            ['class' => 'exchangeModel'],
            ['class' => 'photoModel'],
            ['class' => 'photouseforModel'],
            ['class' => 'photousefor_userModel'],
            ['class' => 'categoryarea_categoryModel'],
            ['class' => 'event_templatejoinModel'],
            ['class' => 'eventjoinModel'],
            ['class' => 'userModel'],
        ];
    }

    function content($album_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        $user = (new userModel)->getSession();

        $join = [
            ['left join', 'user', 'using(user_id)'],
            ['left join', 'albumstatistics', 'using(album_id)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];

        $m_album = $this
            ->column([
                'album.category_id',
                'album.display_num_of_collect',
                'album.template_id',
                'album.name album_name',
                'album.description',
                'album.cover',
                'album.preview',
                'album.preview_page_num',
                'album.photo',
                'album.location',
                'album.weather',
                'album.mood',
                'album.rating',
                'album.reward_after_collect',
                'album.reward_description',
                'album.point',
                'album.audio_mode',
                'album.audio_loop',
                'album.audio_refer',
                'album.audio_target',
                'album.act album_act',
                'album.state album_state',
                'album.inserttime album_inserttime',
                'album.publishtime album_publishtime',
                'user.user_id',
                'user.name user_name',
                'user.act user_act',
                'albumstatistics.count',
                'albumstatistics.likes',
                'albumstatistics.messageboard',
                'albumstatistics.viewed',
                'categoryarea_category.categoryarea_id',
            ])
            ->join($join)
            ->where([[[['album.album_id', '=', $album_id]], 'and']])
            ->fetch();

        if (empty($m_album)) {
            $result = 0;
            $message = _('Album does not exist.');
            $m_album = null;
            goto _return;
        }

        if ($m_album['album_act'] == 'close' || $m_album['album_state'] != 'success') {
            if (empty($user) || empty($user['user_id'])) {
                $result = 0;
                $message = _('Album is not open.');
                $m_album = null;
                goto _return;
            }

            $m_cooperation = (new cooperationModel)->column(['COUNT(1)'])->where([[[['user_id', '=', $user['user_id']], ['`type`', '=', 'album'], ['type_id', '=', $album_id]], 'and']])->fetchColumn();

            if (empty($m_cooperation)) {
                $result = 0;
                $message = _('Album is not open.');
                $m_album = null;
                goto _return;
            }
        }

        if ($m_album['album_act'] == 'delete') {
            $result = 0;
            $message = _('Album does not exist.');
            $m_album = null;
            goto _return;
        }

        if (empty($m_album['user_id'])) {
            $result = 0;
            $message = _('User does not exist.');
            $m_album = null;
            goto _return;
        }

        if ($m_album['user_act'] == 'close') {
            $result = 0;
            $message = _('User is not open.');
            $m_album = null;
            goto _return;
        }

        //album's user picture
        $picture_path = PATH_STORAGE . \userModel::getPicture($m_album['user_id']);

        $picture_url = is_image($picture_path) ? fileinfo((new \Core\Image)->set($picture_path)->setSize(160, 160)->save())['url'] : null;

        //
        $data = [
            'album' => [
                'category_id' => $m_album['category_id'],
                'display_num_of_collect' => (boolean)$m_album['display_num_of_collect'],
                'template_id' => $m_album['template_id'],
                'name' => $m_album['album_name'],
                'description' => $m_album['description'],
                'cover' => $m_album['cover'],
                'preview' => $m_album['preview'],
                'preview_page_num' => $m_album['preview_page_num'],
                'photo' => $m_album['photo'],
                'location' => $m_album['location'],
                'rating' => $m_album['rating'],
                'reward_after_collect' => (boolean)$m_album['reward_after_collect'],
                'reward_description' => $m_album['reward_description'],
                'audio_mode' => $m_album['audio_mode'],
                'audio_loop' => (boolean)$m_album['audio_loop'],
                'audio_target' => $m_album['audio_target'],
                'audio_refer' => $m_album['audio_refer'],
                'point' => $m_album['point'],
                'act' => $m_album['album_act'],
                'inserttime' => $m_album['album_inserttime'],
                'publishtime' => $m_album['album_publishtime'],
            ],
            'albumstatistics' => [
                'count' => $m_album['count'],
                'exchange' => \albumstatisticsModel::getCountOfExchange($album_id),
                'likes' => $m_album['likes'],
                'messageboard' => $m_album['messageboard'],
                'viewed' => $m_album['viewed'],
            ],
            'categoryarea' => [
                'categoryarea_id' => $m_album['categoryarea_id'],
            ],
            'user' => [
                'act' => $m_album['user_act'],
                'name' => $m_album['user_name'],
                'picture' => $picture_url,
                'user_id' => $m_album['user_id'],
            ],
        ];

        _return:

        return array_encode_return($result, $message, null, $data);
    }

    function cooperation($user_id)
    {
        $column = [
            'album.album_id',
            'album.name album_name',
            'album.description',
            'album.cover',
            'album.location',
            'album.point',
            'album.zipped',
            'album.act',
            'album.inserttime',
            'albumstatistics.viewed',
            'user.user_id',
            'user.name user_name',
            'categoryarea_category.categoryarea_id',
            'cooperation.identity',
        ];
        $this->column($column);

        $join = [
            ['inner join', 'user', 'using(user_id)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],//2018-08-21 Lion: 未公開前, category_id 可能為 0
            ['inner join', 'albumstatistics', 'using(album_id)'],
            ['inner join', 'cooperation', 'on cooperation.type = \'album\' and cooperation.type_id = album.album_id and cooperation.identity != \'admin\''],
        ];
        $this->join($join);

        $where = [
            [[['album.state', 'in', ['pretreat', 'process', 'success']], ['album.act', 'in', ['close', 'open']], ['user.act', '=', 'open'], ['cooperation.user_id', '=', $user_id]], 'and'],
        ];
        $this->where($where);

        return $this;
    }

    function cooperation_v2($user_id, array $where = null, array $order = null, $limit = null)
    {
        $column = [
            'album.album_id',
            'album.audio_mode',
            'album.template_id',//2016-04-14 Lion: 由於 template 沒有為 0 的資料存在, 如果取 template.template_id 會為 null, 因此取 album.template_id
            'album.name album_name',
            'album.description',
            'album.cover',
            'album.location',
            'album.photo',
            'album.point',
            'album.zipped',
            'album.act',
            'album.inserttime',
            'albumstatistics.viewed',
            'user.user_id',
            'user.name user_name',
            'categoryarea_category.categoryarea_id',
            'cooperation.identity',
        ];
        $join = [
            ['inner join', 'user', 'using(user_id)'],
            ['inner join', 'albumstatistics', 'using(album_id)'],
            ['inner join', 'cooperation', 'on cooperation.type = \'album\' and cooperation.type_id = album.album_id and cooperation.identity != \'admin\''],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],//2018-08-21 Lion: 未公開前, category_id 可能為 0
        ];
        $where = array_merge([[[['album.state', 'in', ['pretreat', 'process', 'success']], ['album.act', 'in', ['close', 'open']], ['user.act', '=', 'open'], ['cooperation.user_id', '=', $user_id]], 'and']], (array)$where);

        return (new \albumModel)->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
    }

    function deleteAlbum($album_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_album = Model('album')->column(['user_id', 'act'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
        if (empty($m_album)) {
            $result = 0;
            $message = _('Album does not exist.');
            goto _return;
        }

        if ($m_album['act'] == 'none') {
            $result = 0;
            $message = _('[Album] occur exception, please contact us.');
            goto _return;
        }

        if ($m_album['user_id'] != $user_id) {
            $result = 0;
            $message = _('您不能刪除此相本。');
            goto _return;
        }

        if ($m_album['act'] == 'delete') {
            $result = 2;
            $message = _('您已刪除此相本。');
            goto _return;
        }

        Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit(['act' => 'delete']);

        _return:
        return array_encode_return($result, $message);
    }

    function deleteAudio($album_id)
    {
        $m_album = (new albumModel)
            ->column(['audio_refer', 'audio_target'])
            ->where([[[['album_id', '=', $album_id]], 'and']])
            ->lock('for update')
            ->fetch();

        switch ($m_album['audio_refer']) {
            case 'embed':
            case 'file':
            case 'system':
                if ($m_album['audio_refer'] === 'file') {
                    \Core\File::delete([PATH_UPLOAD . $m_album['audio_target']]);
                }

                (new albumModel)
                    ->where([[[['album_id', '=', $album_id]], 'and']])
                    ->edit([
                        'audio_loop' => 0,
                        'audio_refer' => 'none',
                        'audio_target' => '',
                        'state' => 'process',
                    ]);
                break;
        }
    }

    //2016-06-23 Lion: 這個準備棄用, 改使用 photoModel::deletePhoto
    function deletePhoto($photo_id, $album_id, $user_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($photo_id)) $photo_id = null;
        if (empty($album_id)) $album_id = null;
        if (empty($user_id)) $user_id = null;

        if ($photo_id === null || $album_id === null || $user_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        $m_photo = Model('photo')->column(['image', 'usefor', 'audio_refer', 'audio_target', 'video_refer', 'video_target'])->where([[[['photo_id', '=', $photo_id], ['act', '=', 'open']], 'and']])->fetch();

        switch ($m_photo['usefor']) {
            case 'none':
                $result = 0;
                $message = _('The photo does not exist.');
                goto _return;
                break;

            case 'exchange':
            case 'slot':
                $result = 0;
                $message = _('Photo cannot be deleted.');
                goto _return;
                break;

            case 'image':
            case 'video':
                if (is_file(PATH_UPLOAD . $m_photo['image'])) \Core\File::delete([PATH_UPLOAD . $m_photo['image']]);

                $edit = [
                    'album_id' => 0,
                    'user_id' => 0,
                    'name' => '',
                    'description' => '',
                    'image' => '',
                    'location' => '',
                    'usefor' => 'none',
                    'hyperlink' => '',
                    'audio_loop' => 0,
                    'audio_refer' => 'none',
                    'audio_target' => '',
                    'video_refer' => 'none',
                    'video_target' => '',
                    'state' => 'pretreat',
                    'duration' => 0,
                    'sequence' => 255,
                    'inserttime' => inserttime(),
                ];
                Model('photo')->where([[[['photo_id', '=', $photo_id]], 'and']])->edit($edit);
                break;
        }

        if ($m_photo['audio_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_photo['audio_target']]);

        if ($m_photo['video_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_photo['video_target']]);

        Model('album')->refreshPhoto($album_id);

        _return:
        return array_encode_return($result, $message);
    }

    function diyable($album_id, $user_id)
    {
        $result = 1;
        $message = null;

        $column = [
            'album.audio_mode',
            'album.user_id',
            'album.template_id',//2016-04-25 Lion: 由於 template 沒有為 0 的資料存在, 如果取 template.template_id 會為 null, 因此取 album.template_id
            'album.act',
            'album.photo',
            'album.preview_page_num',
            'template.name template_name',
        ];
        $join = [
            ['left join', 'template', 'using(template_id)'],//2016-04-25 Lion: 由於 template 沒有為 0 的資料存在, 但 album.template_id 可能為 0, 因此用 left join
        ];
        $m_album = $this->column($column)->join($join)->where([[[['album.album_id', '=', $album_id]], 'and']])->fetch();

        if (empty($m_album)) {
            $result = 0;
            $message = _('Album does not exist.');
            goto _return;
        } else {
            if ($m_album['act'] == 'none') {
                $result = 0;
                $message = _('[Album] occur exception, please contact us.');
                goto _return;
            } elseif ($m_album['act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                goto _return;
            } elseif ($m_album['user_id'] != $user_id) {
                $m_cooperation = Model('cooperation')->column(['identity'])->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $album_id]], 'and']])->fetch();
                if ($m_cooperation == null) {
                    $result = 0;
                    $message = _('You can not DIY this album.');
                    goto _return;
                } else {
                    switch ($m_cooperation['identity']) {
                        case 'admin':
                        case 'approver':
                        case 'editor':
                        case 'viewer':
                            break;

                        default:
                            $result = 0;
                            $message = _('Unknown case of identity.');
                            goto _return;
                            break;
                    }
                }
            }
        }

        _return:
        return array_encode_return($result, $message, null, $m_album);
    }

    function downloadable($album_id, $user_id)
    {
        $result = 1;
        $message = null;

        if (!is_file(PATH_STORAGE . storagefile(SITE_LANG . '/album/' . $album_id . '.zip'))) {
            $result = 0;
            $message = _('Album\'s file does not exist.');
            goto _return;
        }

        $column = [
            'album.act album_act',
            'user.user_id',
            'user.act user_act',
        ];
        $m_album = Model('album')->column($column)->join([['left join', 'user', 'using(user_id)']])->where([[[['album.album_id', '=', $album_id]], 'and']])->fetch();
        if (empty($m_album)) {
            $result = 0;
            $message = _('Album does not exist.');
            goto _return;
        } else {
            if ($m_album['album_act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                goto _return;
            }

            if ($m_album['user_act'] == 'close') {
                $result = 0;
                $message = _('Author is not open.');
                goto _return;
            }

            if ($m_album['user_id'] != $user_id) {
                $m_cooperation = Model('cooperation')->column(['count(1)'])->where([[[['`type`', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])->fetchColumn();
                if (!$m_cooperation) {
                    $m_albumqueue = Model('albumqueue')->column(['count(1)'])->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->fetchColumn();
                    if (!$m_albumqueue) {
                        $result = 0;
                        $message = _('You do not have this album.');
                        goto _return;
                    }
                }
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function exting_cooperation($album_id, $user_id)
    {
        $result = (new cooperationModel())->where([[[['type', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])->delete();

        return $result;
    }

    function follow($album_id)
    {
        $result = 1;
        $message = null;

        $column = [
            'album.act album_act',
            'album.album_id',
            'album.cover',
            'album.description album_description',
            'album.location',
            'album.name album_name',
            'album.preview',
            'albumstatistics.count',
            'follow.count_from',
            'user.act user_act',
            'user.name user_name',
            'user.user_id',
        ];
        $join = [
            ['left join', 'albumstatistics', 'using(album_id)'],
            ['left join', 'follow', 'using(user_id)'],
            ['left join', 'user', 'using(user_id)'],
        ];
        $where = [
            [[['album.album_id', '=', $album_id]], 'and']
        ];
        $data = $m_album = Model('album')->column($column)->join($join)->where($where)->fetch();
        if ($m_album == null) {
            $result = 0;
            $message = _('Album does not exist.');
            $data = null;
            goto _return;
        } else {
            if ($m_album['album_act'] == 'close') {
                $result = 0;
                $message = _('Album is not open.');
                $data = null;
                goto _return;
            } elseif ($m_album['album_act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                $data = null;
                goto _return;
            }
        }

        if ($m_album['user_id'] == null) {
            $result = 0;
            $message = _('User does not exist.');
            $data = null;
            goto _return;
        } elseif ($m_album['user_act'] != 'open') {
            $result = 0;
            $message = _('User is not open.');
            $data = null;
            goto _return;
        }

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    static function getCategoryAreaId($album_id)
    {
        return (new \albumModel)
            ->column(['categoryarea_category.categoryarea_id'])
            ->join([
                ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.category_id = album.category_id']
            ])
            ->where([[[['album.album_id', '=', $album_id]], 'and']])
            ->fetchColumn();
    }

    function getCreative(array $where = null, array $order = null, $limit = null)
    {
        return (new albumModel)
            ->column([
                'album.album_id',
                'album.name',
                'album.description',
                'album.cover',
                'album.cover_hex',
                'album.inserttime',
                'album.audio_mode',
            ])
            ->join([
                ['inner join', 'user', 'using(user_id)'],
            ])
            ->where(array_merge([[[['album.act', '=', 'open'], ['album.zipped', '=', 1], ['user.act', '=', 'open']], 'and']], (array)$where))
            ->order($order)
            ->limit($limit)
            ->fetchAll();
    }

    static function getDataOfDiyForApp($album_id)
    {
        $m_album = (new albumModel)->column(['user_id', 'preview'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();

        $m_photo = (new photoModel)
            ->column([
                'audio_refer',
                'audio_target',
                'description',
                'hyperlink',
                'image',
                'location',
                'photo_id',
                'user_id',
                'video_refer',
                'video_target',
            ])
            ->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        $Image = new \Core\Image;

        $a_photo = [];
        foreach ($m_photo as $v0) {
            $audio_url = null;
            $image_url = null;
            $image_url_operate = null;
            $image_url_thumbnail = null;
            $video_url = null;

            if (is_image(PATH_UPLOAD . $v0['image'])) {
                $Image->set(PATH_UPLOAD . $v0['image']);

                $image_url = fileinfo(PATH_UPLOAD . $v0['image'])['url'];
                $image_url_operate = fileinfo($Image->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'];
                $image_url_thumbnail = fileinfo($Image->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'];
            }

            switch ($v0['audio_refer']) {
                case 'embed':
                    break;

                case 'file':
                    $audio_url = fileinfo(PATH_UPLOAD . $v0['audio_target'])['url'];
                    break;

                case 'system':
                    break;
            }

            switch ($v0['video_refer']) {
                case 'embed':
                    $video_url = $v0['video_target'];
                    break;

                case 'file':
                    $video_url = fileinfo(PATH_UPLOAD . $v0['video_target'])['url'];
                    break;

                case 'system':
                    break;
            }

            $a_photo[] = [
                'audio_url' => $audio_url,
                'description' => strip_tags($v0['description']),
                'hyperlink' => json_decode($v0['hyperlink'], true),
                'image_url' => $image_url,
                'image_url_operate' => $image_url_operate,
                'image_url_thumbnail' => $image_url_thumbnail,
                'is_preview' => ($m_album['preview'] !== '' && in_array($v0['image'], json_decode($m_album['preview'], true))) ? true : false,
                'location' => $v0['location'],
                'photo_id' => $v0['photo_id'],
                'user_id' => $v0['user_id'],
                'video_url' => $video_url,
            ];
        }

        $data = [
            'photo' => $a_photo,
            'usergrade' => [
                'photo_limit_of_album' => usergradeModel::getPhotoLimitOfAlbum($m_album['user_id']),
            ],
        ];

        return $data;
    }

    function getEventInfo($album_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($album_id)) $album_id = null;

        if ($album_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        $now = date('Y-m-d H:i:s', time());

        $column = [
            'event.event_id',
            'event.name event_name',
        ];
        $where = [
            [[['event.act', '=', 'open'], ['event.starttime', '<=', $now], ['event.endtime', '>=', $now], ['eventjoin.album_id', '=', $album_id]], 'and']
        ];
        $m_event = Model('event')->column($column)->join([['inner join', 'eventjoin', 'using(event_id)']])->where($where)->fetch();

        $a_event = null;
        if ($m_event) {
            $a_event = [
                'event_id' => $m_event['event_id'],
                'name' => $m_event['event_name'],
            ];
        }

        $data = [
            'event' => $a_event,
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function getFollow($user_id, array $where = null, $limit = null)
    {
        $array_album_id = self::getWaterfallAlbumId($user_id, $limit);

        $m_album = (new albumModel)
            ->column([
                'album.album_id',
                'album.audio_mode',
                'album.cover',
                'album.cover_hex',
                'album.location',
                'album.name album_name',
                'album.preview',
                'album.publishtime',
                'albumstatistics.count',
                'albumstatistics.likes',
                'albumstatistics.viewed',
                'follow.count_from + userstatistics.followfrom_manual count_from',
                'user.name user_name',
                'user.user_id',
            ])
            ->join([
                ['INNER JOIN', 'notice', 'ON notice.type = \'album\' AND notice.id = album.album_id AND notice.act = \'open\''],
                ['INNER JOIN', 'noticequeue', 'ON noticequeue.user_id = ' . (new albumModel)->quote($user_id) . ' AND noticequeue.notice_id = notice.notice_id'],
                ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                ['LEFT JOIN', 'user', 'ON user.user_id = album.user_id'],
                ['LEFT JOIN', 'userstatistics', 'ON userstatistics.user_id = album.user_id'],
            ])
            ->where(array_merge([[[['album.act', '=', 'open'], ['album.zipped', '=', true], ['user.act', '=', 'open']], 'and']], (array)$where))
            ->order(['FIELD(' . implode(',', array_merge(['album.album_id'], $array_album_id)) . ')' => 'ASC'])
            ->limit($limit)
            ->fetchAll();

        $data = [];

        foreach ($m_album as $v0) {
            $data[] = [
                'album' => [
                    'album_id' => $v0['album_id'],
                    'audio_mode' => $v0['audio_mode'],
                    'cover' => $v0['cover'],
                    'cover_hex' => $v0['cover_hex'],
                    'location' => $v0['location'],
                    'name' => $v0['album_name'],
                    'preview' => $v0['preview'],
                    'publishtime' => $v0['publishtime'],
                ],
                'albumstatistics' => [
                    'count' => $v0['count'],
                    'likes' => $v0['likes'],
                    'viewed' => $v0['viewed'],
                ],
                'follow' => [
                    'count_from' => $v0['count_from'],
                ],
                'user' => [
                    'name' => $v0['user_name'],
                    'user_id' => $v0['user_id'],
                ],
            ];
        }

        return $data;
    }

    static function getFree($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null, $limit = null)
    {
        $albumModel = (new albumModel);

        $s_user = (new userModel)->getSession();

        if ($s_user) {
            $albumModel
                ->order(['FIELD(' . implode(',', array_merge(['album.album_id'], self::getWaterfallAlbumId($s_user['user_id'], $limit))) . ')' => 'ASC']);
        } else {
            $albumModel
                ->order(['album.publishtime' => 'DESC']);
        }

        //
        $join = [];
        $where = [];

        if ($categoryarea_id !== null && $category_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.category_id = ' . $albumModel->quote($category_id) . ' AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        } elseif ($categoryarea_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        }

        if ($album_id) {
            if (is_array($album_id)) {
                $where[] = [[['album.album_id', 'in', $album_id]], 'and'];
            }
        }

        if ($user_id) {
            if (is_array($user_id)) {
                $where[] = [[['album.user_id', 'in', $user_id]], 'and'];
            }
        }

        $m_album = $albumModel
            ->column([
                'album.album_id',
                'album.audio_mode',
                'album.cover',
                'album.cover_hex',
                'album.description',
                'album.location',
                'album.name album_name',
                'album.preview',
                'album.publishtime',
                'albumstatistics.count',
                'albumstatistics.likes',
                'albumstatistics.viewed',
                'follow.count_from',
                'user.name user_name',
                'user.user_id',
            ])
            ->join(array_merge(
                [
                    ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                    ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ],
                $join
            ))
            ->where(array_merge([[[['album.act', '=', 'open'], ['album.state', '=', 'success'], ['album.zipped', '=', true], ['album.point', '=', 0]], 'and']], $where))
            ->limit($limit)
            ->fetchAll();

        $data = [];

        foreach ($m_album as $v0) {
            $data[] = [
                'album' => [
                    'album_id' => $v0['album_id'],
                    'audio_mode' => $v0['audio_mode'],
                    'cover' => $v0['cover'],
                    'cover_hex' => $v0['cover_hex'],
                    'description' => $v0['description'],
                    'location' => $v0['location'],
                    'name' => $v0['album_name'],
                    'preview' => $v0['preview'],
                    'publishtime' => $v0['publishtime'],
                ],
                'albumstatistics' => [
                    'count' => $v0['count'],
                    'likes' => $v0['likes'],
                    'viewed' => $v0['viewed'],
                ],
                'follow' => [
                    'count_from' => $v0['count_from'],
                ],
                'user' => [
                    'name' => $v0['user_name'],
                    'user_id' => $v0['user_id'],
                ],
            ];
        }

        return $data;
    }

    static function getFreeCount($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null)
    {
        $albumModel = (new albumModel);

        //
        $join = [];
        $where = [];

        if ($categoryarea_id !== null && $category_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.category_id = ' . $albumModel->quote($category_id) . ' AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        } elseif ($categoryarea_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        }

        if ($album_id) {
            if (is_array($album_id)) {
                $where[] = [[['album.album_id', 'in', $album_id]], 'and'];
            }
        }

        if ($user_id) {
            if (is_array($user_id)) {
                $where[] = [[['album.user_id', 'in', $user_id]], 'and'];
            }
        }

        return $albumModel
            ->column(['COUNT(1)'])
            ->join(array_merge(
                [
                    ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                    ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ],
                $join
            ))
            ->where(array_merge(
                [[[['album.act', '=', 'open'], ['album.state', '=', 'success'], ['album.zipped', '=', true], ['album.point', '=', 0]], 'and']],
                $where
            ))
            ->fetchColumn();
    }

    static function getHot($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null, $limit = null)
    {
        $join = [];
        $where = [];

        if ($categoryarea_id !== null && $category_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . (new \albumModel)->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.category_id = ' . (new \albumModel)->quote($category_id) . ' AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        } elseif ($categoryarea_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . (new \albumModel)->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        }

        if ($album_id) {
            if (is_array($album_id)) {
                $where[] = [[['album.album_id', 'in', $album_id]], 'and'];
            }
        }

        if ($user_id) {
            if (is_array($user_id)) {
                $where[] = [[['album.user_id', 'in', $user_id]], 'and'];
            }
        }

        $m_album = (new \albumModel)
            ->column([
                'album.album_id',
                'album.audio_mode',
                'album.cover',
                'album.cover_hex',
                'album.description',
                'album.location',
                'album.name album_name',
                'album.preview',
                'album.publishtime',
                'albumstatistics.count',
                'albumstatistics.likes',
                'albumstatistics.viewed',
                'follow.count_from',
                'user.name user_name',
                'user.user_id',
            ])
            ->join(array_merge(
                [
                    ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                    ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ],
                $join
            ))
            ->where(array_merge([[[['album.act', '=', 'open'], ['album.state', '=', 'success'], ['album.zipped', '=', true]], 'and']], (array)$where))
            ->order(['albumstatistics.viewed' => 'DESC', 'album.publishtime' => 'DESC'])
            ->limit($limit)
            ->fetchAll();

        $a_album2weight = [];

        if ($m_album) {
            $s_user = (new userModel)->getSession();

            if ($s_user) {
                $m_album2weight = (new album2weightModel)
                    ->column(['album_id', 'weight'])
                    ->where([[[['user_id', '=', $s_user['user_id']], ['album_id', 'IN', array_column($m_album, 'album_id')]], 'and']])
                    ->fetchAll();

                foreach ($m_album2weight as $v0) {
                    $a_album2weight[$v0['album_id']] = $v0['weight'];
                }
            }
        }

        $data = [];

        foreach ($m_album as $v0) {
            $data[] = [
                'album' => [
                    'album_id' => $v0['album_id'],
                    'audio_mode' => $v0['audio_mode'],
                    'cover' => $v0['cover'],
                    'cover_hex' => $v0['cover_hex'],
                    'description' => $v0['description'],
                    'location' => $v0['location'],
                    'name' => $v0['album_name'],
                    'preview' => $v0['preview'],
                    'publishtime' => $v0['publishtime'],
                ],
                'albumstatistics' => [
                    'count' => $v0['count'],
                    'likes' => $v0['likes'],
                    'viewed' => $v0['viewed'],
                ],
                'album2weight' => [
                    'weight' => isset($a_album2weight[$v0['album_id']]) ? (float)$a_album2weight[$v0['album_id']] : 0,
                ],
                'follow' => [
                    'count_from' => $v0['count_from'],
                ],
                'user' => [
                    'name' => $v0['user_name'],
                    'user_id' => $v0['user_id'],
                ],
            ];
        }

        //2017-03-21 Lion: 權重排序必須在最後才介入, 不然會發生僵固的情況
        usort($data, function ($a, $b) {
            if ($a['album2weight']['weight'] < $b['album2weight']['weight']) {
                $return = 1;
            } elseif ($a['album2weight']['weight'] == $b['album2weight']['weight']) {
                if ($a['albumstatistics']['viewed'] < $b['albumstatistics']['viewed']) {
                    $return = 1;
                } elseif ($a['albumstatistics']['viewed'] == $b['albumstatistics']['viewed']) {
                    if ($a['album']['publishtime'] < $b['album']['publishtime']) {
                        $return = 1;
                    } elseif ($a['album']['publishtime'] == $b['album']['publishtime']) {
                        $return = ($a['album']['album_id'] < $b['album']['album_id']) ? 1 : -1;
                    } else {
                        $return = -1;
                    }
                } else {
                    $return = -1;
                }
            } else {
                $return = -1;
            }

            return $return;
        });

        return $data;
    }

    static function getHotCount($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null)
    {
        return self::getLatestCount($categoryarea_id, $category_id, $album_id, $user_id);
    }

    static function getLatest($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null, $limit = null)
    {
        $albumModel = (new albumModel);

        $s_user = (new userModel)->getSession();

        if ($s_user) {
            $albumModel
                ->order(['FIELD(' . implode(',', array_merge(['album.album_id'], self::getWaterfallAlbumId($s_user['user_id'], $limit))) . ')' => 'ASC']);
        } else {
            $albumModel
                ->order(['album.publishtime' => 'DESC']);
        }

        //
        $join = [];
        $where = [];

        if ($categoryarea_id !== null && $category_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.category_id = ' . $albumModel->quote($category_id) . ' AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        } elseif ($categoryarea_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        }

        if ($album_id) {
            if (is_array($album_id)) {
                $where[] = [[['album.album_id', 'in', $album_id]], 'and'];
            }
        }

        if ($user_id) {
            if (is_array($user_id)) {
                $where[] = [[['album.user_id', 'in', $user_id]], 'and'];
            }
        }

        //
        $m_album = $albumModel
            ->column([
                'album.album_id',
                'album.audio_mode',
                'album.cover',
                'album.cover_hex',
                'album.description',
                'album.location',
                'album.name album_name',
                'album.preview',
                'album.publishtime',
                'albumstatistics.count',
                'albumstatistics.likes',
                'albumstatistics.viewed',
                'follow.count_from',
                'user.name user_name',
                'user.user_id',
            ])
            ->join(array_merge(
                [
                    ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                    ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ],
                $join
            ))
            ->where(array_merge([[[['album.act', '=', 'open'], ['album.state', '=', 'success'], ['album.zipped', '=', true]], 'and']], $where))
            ->limit($limit)
            ->fetchAll();

        $data = [];

        foreach ($m_album as $v0) {
            $data[] = [
                'album' => [
                    'album_id' => $v0['album_id'],
                    'audio_mode' => $v0['audio_mode'],
                    'cover' => $v0['cover'],
                    'cover_hex' => $v0['cover_hex'],
                    'description' => $v0['description'],
                    'location' => $v0['location'],
                    'name' => $v0['album_name'],
                    'preview' => $v0['preview'],
                    'publishtime' => $v0['publishtime'],
                ],
                'albumstatistics' => [
                    'count' => $v0['count'],
                    'likes' => $v0['likes'],
                    'viewed' => $v0['viewed'],
                ],
                'follow' => [
                    'count_from' => $v0['count_from'],
                ],
                'user' => [
                    'name' => $v0['user_name'],
                    'user_id' => $v0['user_id'],
                ],
            ];
        }

        return $data;
    }

    static function getLatestCount($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null)
    {
        $albumModel = (new albumModel);

        //
        $join = [];
        $where = [];

        if ($categoryarea_id !== null && $category_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.category_id = ' . $albumModel->quote($category_id) . ' AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        } elseif ($categoryarea_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        }

        if ($album_id) {
            if (is_array($album_id)) {
                $where[] = [[['album.album_id', 'in', $album_id]], 'and'];
            }
        }

        if ($user_id) {
            if (is_array($user_id)) {
                $where[] = [[['album.user_id', 'in', $user_id]], 'and'];
            }
        }

        return $albumModel
            ->column(['COUNT(1)'])
            ->join(array_merge(
                [
                    ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                    ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ],
                $join
            ))
            ->where(array_merge(
                [[[['album.act', '=', 'open'], ['album.state', '=', 'success'], ['album.zipped', '=', true]], 'and']],
                $where
            ))
            ->fetchColumn();
    }

    function getRecommended(array $where = null, array $order = null, $limit = null)
    {
        $a_album_id = [];

        /**
         * order 1st
         */
        //置頂
        $a_exhibit = array_column(
            (new indexpopularityModel)
                ->column(['exhibit'])
                ->where([[[['indexpopularity_id', '=', 2], ['act', '=', 'open']], 'and']])
                ->order(['sequence' => 'DESC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                ->fetchAll(),
            'exhibit'
        );

        $a_album_id_1st = [];

        foreach ($a_exhibit as $v0) {
            foreach (json_decode($v0, true) as $v1) {
                $a_album_id_1st[] = $v1;
            }
        }

        $order_1st = $a_album_id_1st ? ['FIELD(' . implode(',', array_merge(['album.album_id'], $a_album_id_1st)) . ')' => 'DESC'] : [];

        $a_album_id = array_merge($a_album_id, $a_album_id_1st);

        /**
         * order 2nd
         */
        //權重
        $order_2nd = [];

        $s_user = (new userModel)->getSession();

        if ($s_user) {
            //2017-01-17 Lion: 因為 album2weight 沒有所有的關聯資料, 不能以 join 方式處理
            $a_album_id_2nd = array_column(
                (new album2weightModel)
                    ->column(['album_id'])
                    ->where([[[['user_id', '=', $s_user['user_id']]], 'and']])
                    ->order(['weight' => 'ASC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                    ->fetchAll(),
                'album_id'
            );

            if ($a_album_id_2nd) {
                $order_2nd = ['FIELD(' . implode(',', array_merge(['album.album_id'], $a_album_id_2nd)) . ')' => 'DESC'];

                $a_album_id = array_merge($a_album_id, $a_album_id_2nd);
            }
        }

        /**
         * order 3th
         */
        //一個月內收藏數量由高至低
        $a_album_id_3th = array_column(
            (new albumqueueModel)
                ->column(['album_id'])
                ->where([[[['inserttime', '>=', date('Y-m-d 00:00:00', strtotime('last month'))]], 'and']])
                ->group(['album_id'])
                ->order(['COUNT(1)' => 'ASC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                ->fetchAll(),
            'album_id'
        );

        $order_3th = $a_album_id_3th ? ['FIELD(' . implode(',', array_merge(['album.album_id'], $a_album_id_3th)) . ')' => 'DESC'] : [];

        $a_album_id = array_merge($a_album_id, $a_album_id_3th);

        /**
         * order 4th
         */
        //一年內收藏數量由高至低
        $a_album_id_4th = array_column(
            (new albumqueueModel)
                ->column(['album_id'])
                ->where([[[['inserttime', '>=', date('Y-m-d 00:00:00', strtotime('last year'))]], 'and']])
                ->group(['album_id'])
                ->order(['COUNT(1)' => 'ASC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                ->fetchAll(),
            'album_id'
        );

        $order_4th = $a_album_id_4th ? ['FIELD(' . implode(',', array_merge(['album.album_id'], $a_album_id_4th)) . ')' => 'DESC'] : [];

        $a_album_id = array_merge($a_album_id, $a_album_id_4th);

        $data = [];

        if ($a_album_id) {
            $m_album = (new albumModel)
                ->column([
                    'album.album_id',
                    'album.audio_mode',
                    'album.name album_name',
                    'album.cover',
                    'album.cover_hex',
                    'album.point',
                    'album.publishtime',
                    'user.name user_name',
                ])
                ->join([
                    ['inner join', 'user', 'using(user_id)'],
                ])
                ->where(array_merge([[[['album.album_id', 'in', $a_album_id], ['album.act', '=', 'open'], ['album.zipped', '=', 1], ['user.act', '=', 'open']], 'and']], (array)$where))
                ->order(array_merge($order_1st, $order_2nd, $order_3th, $order_4th, (array)$order))
                ->limit($limit)
                ->fetchAll();

            foreach ($m_album as $v0) {
                $data[] = [
                    'album' => [
                        'album_id' => $v0['album_id'],
                        'audio_mode' => $v0['audio_mode'],
                        'cover' => $v0['cover'],
                        'cover_hex' => $v0['cover_hex'],
                        'name' => $v0['album_name'],
                        'point' => $v0['point'],
                        'publishtime' => $v0['publishtime'],
                    ],
                    'user' => [
                        'name' => $v0['user_name'],
                    ],
                ];
            }

            usort($data, function ($a, $b) {
                if ($a['album']['publishtime'] == $b['album']['publishtime']) {
                    return 0;
                }

                return ($a['album']['publishtime'] < $b['album']['publishtime']) ? 1 : -1;
            });
        }

        return $data;
    }

    static function getSponsored($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null, $limit = null)
    {
        $albumModel = (new albumModel);

        $s_user = (new userModel)->getSession();

        if ($s_user) {
            $albumModel
                ->order(['FIELD(' . implode(',', array_merge(['album.album_id'], self::getWaterfallAlbumId($s_user['user_id'], $limit))) . ')' => 'ASC']);
        } else {
            $albumModel
                ->order(['album.publishtime' => 'DESC']);
        }

        //
        $join = [];
        $where = [];

        if ($categoryarea_id !== null && $category_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.category_id = ' . $albumModel->quote($category_id) . ' AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        } elseif ($categoryarea_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        }

        if ($album_id) {
            if (is_array($album_id)) {
                $where[] = [[['album.album_id', 'in', $album_id]], 'and'];
            }
        }

        if ($user_id) {
            if (is_array($user_id)) {
                $where[] = [[['album.user_id', 'in', $user_id]], 'and'];
            }
        }

        $m_album = $albumModel
            ->column([
                'album.album_id',
                'album.audio_mode',
                'album.cover',
                'album.cover_hex',
                'album.description',
                'album.location',
                'album.name album_name',
                'album.preview',
                'album.publishtime',
                'albumstatistics.count',
                'albumstatistics.likes',
                'albumstatistics.viewed',
                'follow.count_from',
                'user.name user_name',
                'user.user_id',
            ])
            ->join(array_merge(
                [
                    ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                    ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ],
                $join
            ))
            ->where(array_merge([[[['album.act', '=', 'open'], ['album.state', '=', 'success'], ['album.zipped', '=', true], ['album.point', '>', 0]], 'and']], $where))
            ->limit($limit)
            ->fetchAll();

        $data = [];

        foreach ($m_album as $v0) {
            $data[] = [
                'album' => [
                    'album_id' => $v0['album_id'],
                    'audio_mode' => $v0['audio_mode'],
                    'cover' => $v0['cover'],
                    'cover_hex' => $v0['cover_hex'],
                    'description' => $v0['description'],
                    'location' => $v0['location'],
                    'name' => $v0['album_name'],
                    'preview' => $v0['preview'],
                    'publishtime' => $v0['publishtime'],
                ],
                'albumstatistics' => [
                    'count' => $v0['count'],
                    'likes' => $v0['likes'],
                    'viewed' => $v0['viewed'],
                ],
                'follow' => [
                    'count_from' => $v0['count_from'],
                ],
                'user' => [
                    'name' => $v0['user_name'],
                    'user_id' => $v0['user_id'],
                ],
            ];
        }

        return $data;
    }

    static function getSponsoredCount($categoryarea_id = null, $category_id = null, $album_id = null, $user_id = null)
    {
        $albumModel = (new albumModel);

        //
        $join = [];
        $where = [];

        if ($categoryarea_id !== null && $category_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.category_id = ' . $albumModel->quote($category_id) . ' AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        } elseif ($categoryarea_id !== null) {
            $join[] = ['INNER JOIN', 'categoryarea', 'ON categoryarea.categoryarea_id = ' . $albumModel->quote($categoryarea_id) . ' AND categoryarea.act = \'open\''];
            $join[] = ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.category_id = album.category_id AND categoryarea_category.act = \'open\''];
            $join[] = ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''];
        }

        if ($album_id) {
            if (is_array($album_id)) {
                $where[] = [[['album.album_id', 'in', $album_id]], 'and'];
            }
        }

        if ($user_id) {
            if (is_array($user_id)) {
                $where[] = [[['album.user_id', 'in', $user_id]], 'and'];
            }
        }

        return $albumModel
            ->column(['COUNT(1)'])
            ->join(array_merge(
                [
                    ['LEFT JOIN', 'albumstatistics', 'ON albumstatistics.album_id = album.album_id'],
                    ['LEFT JOIN', 'follow', 'ON follow.user_id = album.user_id'],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ],
                $join
            ))
            ->where(array_merge(
                [[[['album.act', '=', 'open'], ['album.state', '=', 'success'], ['album.zipped', '=', true], ['album.point', '>', 0]], 'and']],
                $where
            ))
            ->fetchColumn();
    }

    static function getUseForInfo($album_id)
    {
        $usefor_all = array_diff(array_merge(\Schema\photo::$usefor, ['audio']), ['none']);

        $useforArray = array_fill_keys($usefor_all, false);

        $usefor_inusedArray = array_column((new photoModel)->column(['DISTINCT(`usefor`)'])->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])->fetchAll(), 'usefor');

        foreach ($useforArray as $k_0 => &$v_0) {
            if (in_array($k_0, $usefor_inusedArray)) $v_0 = true;
        }

        $audio_mode = (new \albumModel())
            ->column(['audio_mode'])
            ->where([[[['album_id', '=', $album_id]], 'and']])
            ->fetchColumn();

        if ($audio_mode != 'none') $useforArray['audio'] = true;

        return $useforArray;
    }

    function getAlbumViewed($album_id)
    {
        $return = 0;

        if (!empty($album_id)) {
            $s_viewed = (new albumstatisticsModel())
                ->column(['viewed'])
                ->where([[[['album_id', '=', $album_id]], 'and']])
                ->fetchColumn();

            $return = (!$s_viewed) ? 0 : $s_viewed;
        }

        return $return;
    }

    static function getWaterfallAlbumId($user_id, $limit = '0,16')
    {
        if (explode(',', str_replace(' ', '', $limit))[0] == 0) {
            (new \weightModel)->importDataToEachType($user_id);
        }

        /**
         * 2017-11-27 Lion:
         * 1. 取出權重作品依時間(新 > 舊)排序
         * 2. 取出無權重作品依時間(新 > 舊)排序
         * 3. 合併(權重在前，無權重在後)
         * 4. 依據有無用戶 session，處理 where、order、limit
         */
        $array_album_id_with_weight = array_column(
            (new \albumModel())
                ->column(['album.album_id'])
                ->join([
                    ['INNER JOIN', 'album2weight', 'ON album2weight.album_id = album.album_id AND album2weight.user_id = ' . (new \albumModel())->quote($user_id)]
                ])
                ->order([
                    'album.publishtime' => "DESC"
                ])
                ->fetchAll(),
            'album_id'
        );

        $array_album_id_without_weight = array_column(
            (new \albumModel())
                ->column(['album.album_id'])
                ->order([
                    'album.publishtime' => "DESC"
                ])
                ->where([[[['album.album_id', 'NOT IN', $array_album_id_with_weight]], 'and']])
                ->fetchAll(),
            'album_id'
        );

        return array_merge($array_album_id_with_weight, $array_album_id_without_weight);
    }

    function hasAudio($album_id)
    {
        //audio條件會有兩個 : 相本單獨音效 or 相片各自設定音效
        $return = false;
        $m_photo = (new photoModel())->column(['COUNT(1)'])->where([[[['album_id', '=', $album_id], ['audio_refer', '!=', 'none']], 'and']])->fetchcolumn();
        $m_album = (new albumModel())->column(['COUNT(1)'])->where([[[['album_id', '=', $album_id], ['audio_refer', '!=', 'none']], 'and']])->fetchcolumn();

        if ($m_photo || $m_album) $return = true;

        return $return;
    }

    function hasGiftTags($album_id)
    {
        $giftTags = ['exchange', 'slot'];
        $return = false;
        $album_tags = array_column((new photoModel())->column(['DISTINCT(`usefor`)'])->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])->fetchAll(), 'usefor');
        foreach ($giftTags as $v0) {
            if (in_array($v0, $album_tags)) {
                $return = true;
                break;
            }
        }
        return $return;
    }

    function hasVideoPhoto($album_id)
    {
        $return = false;
        $m_photo = (new photoModel())
            ->column(['COUNT(1)'])
            ->where([[[['album_id', '=', $album_id], ['video_refer', '!=', 'none']], 'and']])
            ->fetchcolumn();

        if ($m_photo) $return = true;

        return $return;
    }

    function increaseViewed($album_id)
    {
        $md5 = md5(frontstageController::url('album', 'content', ['album_id' => $album_id]));

        if (!isset($_COOKIE[$md5])) {
            setcookie($md5, true, time() + 86400);

            $stime = date('Y-m-d H:00:00');
            $etime = date('Y-m-d H:00:00', strtotime('+1 hour'));
            $where = [[[['album_id', '=', $album_id], ['datatime', '>=', $stime], ['datatime', '<', $etime]], 'and']];

            $viewed = (new albumstatistics2viewedModel())->column(['viewed'])->where($where)->fetchcolumn();

            if (empty($viewed)) {
                (new albumstatistics2viewedModel())->add([
                    'album_id' => $album_id,
                    'viewed' => 1,
                    'datatime' => $stime,
                ]);
            } else {
                (new albumstatistics2viewedModel())
                    ->where($where)
                    ->edit(['viewed' => ++$viewed]);
            }

            (new albumstatisticsModel())->where([[[['album_id', '=', $album_id]], 'and']])->edit(['viewed' => ['viewed + 1', false]]);
        }

        return;
    }

    function initial($user_id, $template_id)
    {
        $data = [];

        //album
        $add = [
            'user_id' => $user_id,
            'template_id' => $template_id,
            'rating' => 'general',
            'zipped' => 0,
            'state' => 'pretreat',
            'act' => 'close',
            'act_ad' => 'close',
            'inserttime' => inserttime(),
        ];
        $album_id = $this->add($add);

        $data['album'] = $add;

        \Model\revision::setAlbum($user_id, $album_id, $data);

        //albumstatistics
        (new albumstatisticsModel)->add(['album_id' => $album_id]);

        //cooperation
        (new cooperationModel)->add(['`type`' => 'album', 'type_id' => $album_id, 'user_id' => $user_id, 'identity' => 'admin']);

        //topic
        $topicModel = new topicModel;

        $topicModel->build('albumcooperation', $album_id);
        $topicModel->build('albumqueue', $album_id);

        return array_encode_return(1, null, null, $album_id);
    }

    function is_own($album_id, $user_id)
    {
        $return = false;
        $m_album = $this->column(['count(1)'])->where([[[['album_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])->fetchColumn();
        if ($m_album) {
            $return = true;
        } else {
            $m_albumqueue = (new albumqueueModel)->column(['count(1)'])->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->fetchColumn();
            if ($m_albumqueue) $return = true;
        }

        return $return;
    }

    function menu(array $where = null, array $order = null, $limit = null)
    {
        return $this
            ->column([
                'album.album_id',
                'album.audio_mode',
                'album.name album_name',
                'album.description',
                'album.cover',
                'album.cover_hex',
                'album.point',
                'album.inserttime',
                'user.user_id',
                'user.name user_name',
                'albumstatistics.viewed',
            ])
            ->join([
                ['inner join', 'albumstatistics', 'using(album_id)'],
                ['left join', 'user', 'using(user_id)'],
                ['left join', 'category', 'using(category_id)'],
                ['left join', 'categoryarea_category', 'using(category_id)'],
            ])
            ->where(array_merge([[[['album.act', '=', 'open'], ['album.zipped', '=', 1], ['user.act', '=', 'open']], 'and']], (array)$where))
            ->order($order)
            ->limit($limit)
            ->fetchAll();
    }

    function menuCount(array $where = null)
    {
        $join = [
            ['inner join', 'albumstatistics', 'using(album_id)'],
            ['left join', 'user', 'using(user_id)'],
            ['left join', 'category', 'using(category_id)'],
            ['left join', 'categoryarea_category', 'using(category_id)'],
        ];

        $where = array_merge([[[['album.act', '=', 'open'], ['album.zipped', '=', 1], ['user.act', '=', 'open']], 'and']], (array)$where);

        return count(Model('album')->column(['album.album_id'])->join($join)->where($where)->fetchAll());
    }

    function mine($user_id)
    {
        $column = [
            'album.album_id',
            'album.name album_name',
            'album.description',
            'album.cover',
            'album.location',
            'album.point',
            'album.zipped',
            'album.act',
            'album.inserttime',
            'user.user_id',
            'user.name user_name',
            'albumstatistics.viewed',
            'categoryarea_category.categoryarea_id',
        ];
        $this->column($column);

        $join = [
            ['inner join', 'user', 'using(user_id)'],
            ['inner join', 'albumstatistics', 'using(album_id)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],//2018-08-21 Lion: 未公開前, category_id 可能為 0
        ];
        $this->join($join);

        $where = [
            [[['album.user_id', '=', $user_id], ['album.state', 'in', ['pretreat', 'process', 'success']], ['album.act', 'in', ['close', 'open']], ['user.act', '=', 'open']], 'and'],
        ];
        $this->where($where);

        return $this;
    }

    function mine_v2($user_id, array $where = null, array $order = null, $limit = null)
    {
        $column = [
            'album.album_id',
            'album.audio_mode',
            'album.template_id',//2016-04-14 Lion: 由於 template 沒有為 0 的資料存在, 如果取 template.template_id 會為 null, 因此取 album.template_id
            'album.name album_name',
            'album.description',
            'album.cover',
            'album.location',
            'album.photo',
            'album.point',
            'album.zipped',
            'album.act',
            'album.inserttime',
            'user.user_id',
            'user.name user_name',
            'albumstatistics.viewed',
            'categoryarea_category.categoryarea_id',
        ];
        $join = [
            ['inner join', 'user', 'using(user_id)'],
            ['inner join', 'albumstatistics', 'using(album_id)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],//2018-08-21 Lion: 未公開前, category_id 可能為 0
        ];
        $where = array_merge([[[['album.user_id', '=', $user_id], ['album.state', 'in', ['pretreat', 'process', 'success']], ['album.act', 'in', ['close', 'open']], ['user.act', '=', 'open']], 'and']], (array)$where);

        return (new \albumModel)->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
    }

    function other($user_id)
    {
        $m_albumqueue = Model('albumqueue')->column(['album_id'])->where([[[['user_id', '=', $user_id], ['visible', '=', 1]], 'and']])->fetchAll();
        $tmp0 = array_column($m_albumqueue, 'album_id');

        $m_cooperation = Model('cooperation')->column(['type_id'])->where([[[['`type`', '=', 'album'], ['user_id', '=', $user_id]], 'and']])->fetchAll();
        $tmp1 = array_column($m_cooperation, 'type_id');

        //column
        $column = [
            'album.album_id',
            'album.name album_name',
            'album.description',
            'album.cover',
            'album.location',
            'album.point',
            'album.zipped',
            'album.act',
            'album.inserttime',
            'user.user_id',
            'user.name user_name',
            'albumstatistics.viewed',
            'categoryarea_category.categoryarea_id',
        ];
        $this->column($column);

        //join
        $join = [
            ['inner join', 'user', 'using(user_id)'],
            ['inner join', 'albumstatistics', 'using(album_id)'],
            ['inner join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];
        $this->join($join);

        //where
        $a_album_id = array_diff($tmp0, $tmp1);
        if ($a_album_id == null) {
            $where = [
                [[['album.album_id', '=', 0]], 'and'],
            ];
        } else {
            asort($a_album_id);
            $where = [
                [[['album.album_id', 'in', $a_album_id], ['album.user_id', '!=', $user_id], ['album.state', 'in', ['process', 'success']], ['album.act', 'in', ['close', 'open']], ['user.act', '=', 'open']], 'and'],
            ];
        }
        $this->where($where);

        return $this;
    }

    function other_v2($user_id, array $where = null, array $order = null, $limit = null)
    {
        $m_albumqueue = (new \albumqueueModel)
            ->column(['album_id'])
            ->where([[[['user_id', '=', $user_id], ['visible', '=', 1]], 'and']])
            ->fetchAll();
        $tmp0 = array_column($m_albumqueue, 'album_id');

        $m_cooperation = (new \cooperationModel)
            ->column(['type_id'])
            ->where([[[['`type`', '=', 'album'], ['user_id', '=', $user_id]], 'and']])
            ->fetchAll();
        $tmp1 = array_column($m_cooperation, 'type_id');

        $return = [];
        $a_album_id = array_diff($tmp0, $tmp1);
        if ($a_album_id) {
            asort($a_album_id);
            $where = array_merge([[[['album.album_id', 'in', $a_album_id], ['album.user_id', '!=', $user_id], ['album.state', 'in', ['process', 'success']], ['album.act', 'in', ['close', 'open']], ['user.act', '=', 'open']], 'and']], (array)$where);
            $return = (new \albumModel)
                ->column([
                    'album.album_id',
                    'album.audio_mode',
                    'album.template_id',//2016-04-14 Lion: 由於 template 沒有為 0 的資料存在, 如果取 template.template_id 會為 null, 因此取 album.template_id
                    'album.name album_name',
                    'album.description',
                    'album.cover',
                    'album.location',
                    'album.photo',
                    'album.point',
                    'album.zipped',
                    'album.act',
                    'album.inserttime',
                    'user.user_id',
                    'user.name user_name',
                    'albumstatistics.viewed',
                ])
                ->join([
                    ['inner join', 'user', 'using(user_id)'],
                    ['inner join', 'albumstatistics', 'using(album_id)'],
                ])
                ->where($where)
                ->order($order)
                ->limit($limit)
                ->fetchAll();
        }

        return $return;
    }

    function pretreat($user_id, $template_id)
    {
        $m_album = (new \albumModel)
            ->column(['album_id'])
            ->where([[[['user_id', '=', $user_id], ['state', '=', 'pretreat'], ['act', 'in', ['close', 'open']]], 'and']])
            ->fetch();

        if (empty($m_album)) {
            list ($result, $message, $redirect, $album_id) = array_decode_return((new \albumModel)->initial($user_id, $template_id));
        } else {
            $album_id = $m_album['album_id'];

            (new \albumModel)
                ->where([[[['album_id', '=', $album_id]], 'and']])
                ->edit(['template_id' => $template_id, 'inserttime' => inserttime()]);
        }

        return array_encode_return(1, null, null, $album_id);
    }

    function process($user_id)
    {
        $column = [
            'album.album_id',
            'album.template_id',
            'album.state',
        ];
        $this->column($column);

        $where = [
            [[['album.user_id', '=', $user_id], ['album.act', 'in', ['close', 'open']], ['album.state', '=', 'process']], 'and']
        ];
        $this->where($where);

        return $this;
    }

    function process2($user_id)
    {
        $m_album = Model('album')->column(['album_id'])->where([[[['user_id', '=', $user_id], ['act', 'in', ['close', 'open']], ['state', '=', 'process']], 'and']])->order(['modifytime' => 'desc'])->fetch();
        if (empty($m_album)) {
            $result = 0;
            $album_id = null;
        } else {
            $result = 1;
            $album_id = $m_album['album_id'];
        }

        return array_encode_return($result, null, null, $album_id);
    }

    function process3($user_id)
    {
        return (new \albumModel)
            ->column([
                'album_id',
                'template_id',//2016-04-14 Lion: 由於 template 沒有為 0 的資料存在, 如果取 template.template_id 會為 null, 因此取 album.template_id
            ])
            ->where([[[['user_id', '=', $user_id], ['act', 'in', ['close', 'open']], ['state', '=', 'process']], 'and']])
            ->order(['modifytime' => 'desc'])
            ->fetchAll();
    }

    function refreshPhoto($album_id)
    {
        $m_photo = (new photoModel())
            ->column(['image'])
            ->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        $a_image = array_column($m_photo, 'image');

        (new albumModel())
            ->where([[[['album_id', '=', $album_id]], 'and']])
            ->edit([
                'cover' => empty($a_image) ? '' : $a_image[0],
                'cover_hex' => empty($a_image[0]) ? null : (new \Core\Image)->set(PATH_UPLOAD . $a_image[0])->getMainHex(),
                'photo' => json_encode($a_image),
                'state' => 'process',
            ]);
    }

    function refreshPhotoInAlbum($album_id, $old_photo, $new_photo)
    {
        $m_album = (new albumModel())->column(['preview', 'cover'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
        $edit = [];
        if ($m_album) {
            foreach ($m_album as $k0 => $v0) {
                if (is_string($v0) && is_array(json_decode($v0, true))) {
                    $array = json_decode($v0, true);
                    foreach ($array as $k1 => $v1) {
                        if ($old_photo == $v1) $v1 = $new_photo;
                        $new_array[] = $v1;
                    }
                    $edit[$k0] = json_encode($new_array);
                } else {
                    if ($old_photo == $v0) $edit[$k0] = $new_photo;
                }
            }

            (new albumModel())->where([[[['album_id', '=', $album_id]], 'and']])->edit($edit);
        }
        return;
    }

    function save($album_id, array $preview_id = [], $preview_page_num = null, $preview_type = 'all')
    {
        $result = 0;
        $message = _('Abnormal process, please try again.');

        $m_album = (new \albumModel)
            ->column(['`name`', 'photo', 'state', 'user_id', 'zipped', '`act`'])
            ->where([[[['album_id', '=', $album_id]], 'and']])
            ->lock('for update')
            ->fetch();

        switch ($m_album['state']) {
            case 'process'://有兩種情況: 1. 新增的相本 2. success 的相本再編輯
                $m_user = (new \userModel)->getSession();

                $subpathname_storage = SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $m_album['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $album_id;
                $pathname_storage = mkdir_p(PATH_STORAGE, $subpathname_storage);
                clean_dir($pathname_storage, false);

                //QRcode
                $qrcode_path = PATH_STORAGE . storagefile($subpathname_storage . DIRECTORY_SEPARATOR . 'qrcode.jpg');
                $app_qrcode_path = PATH_STORAGE . storagefile($subpathname_storage . DIRECTORY_SEPARATOR . 'adjust_app_qrcode.jpg');

                $QRcode = new \Core\QRcode();

                $QRcode
                    ->setTextUrl(frontstageController::url('album', 'content', ['album_id' => $album_id, 'autoplay' => 1, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]))
                    ->setLevel(1)
                    ->setSize(5)
                    ->save($qrcode_path);

                if (is_file($qrcode_path)) {
                    \Extension\aws\S3::upload($qrcode_path);
                }

                $QRcode
                    ->setTextUrl(frontstageController::url('index', 'adjustapp', ['album_id' => $album_id]))
                    ->setLevel(1)
                    ->setSize(5)
                    ->save($app_qrcode_path);

                if (is_file($app_qrcode_path)) {
                    \Extension\aws\S3::upload($app_qrcode_path);
                }

                $a_photo = json_decode($m_album['photo'], true);

                //resize to 1336 x 2004，存放在 storage
                /* 2016-08-02 Lion: 暫時用不到的樣子
                $Image = new \Core\Image();
                foreach ($a_photo as $k0 => $v0) {
                    if (is_image(PATH_UPLOAD.$v0)) $Image->setImage(PATH_UPLOAD.$v0)->setSize(1336, 2004)->save(PATH_STORAGE.storagefile($subpathname_storage.DIRECTORY_SEPARATOR.$k0.'.jpg'));
                }
                */

                //重新編輯的相本不再做notice處理
                if ($m_album['zipped'] == 0) {
                    //建立notice --> 先取得follow list
                    $m_followfrom = (new \followfromModel)
                        ->column(['`from`'])
                        ->where([[[['user_id', '=', $m_album['user_id']]], 'and']])
                        ->fetchAll();

                    if ($m_followfrom) {
                        //填入notice
                        $add = [
                            '`type`' => 'album',
                            'id' => $album_id,
                            'state' => 'success',
                            'act' => 'open',
                            'inserttime' => inserttime(),
                        ];
                        $notice_id = (new \noticeModel)->add($add);

                        //填入noticequeue
                        $add = [];
                        foreach ($m_followfrom as $v0) {
                            $add[] = [
                                'user_id' => $v0['from'],
                                'notice_id' => $notice_id,
                            ];
                        }
                        (new \noticequeueModel)->add($add);
                    }
                }

                //2016-08-02 Lion: photo.usefor = exchange 的資料之 photo.exchange update 為 true, 做為不能再修改的判斷
                $where = [];
                $where[] = [[['album_id', '=', $album_id], ['usefor', '=', 'exchange'], ['act', '=', 'open']], 'and'];
                if ($m_user) $where[] = [[['user_id', '=', $m_user['user_id']]], 'and'];
                (new \photoModel)->where($where)->edit(['exchange' => true]);

                //album -> zip
                (new \albumModel)->zip($album_id);

                $edit = [
                    'zipped' => 1,
                    'state' => 'success',
                ];

                //album : preview + zipped + state
                //20161101 Mars: 預覽圖設定提前至編輯器中, 故此處需參考Controller::diy送過來的 previer_id處理
                if ($preview_id) {
                    $preview_photo = (new \photoModel)
                        ->column(['image'])
                        ->where([[[['album_id', '=', $album_id], ['photo_id', 'in', $preview_id]], 'and']])
                        ->fetchAll();
                    $a_previewTmp = [];
                    $a_preview = [];

                    if (count($preview_photo) == 0) {
                        $a_preview[] = (new \albumModel)
                            ->column(['cover'])
                            ->where([[[['album_id', '=', $album_id]], 'and']])
                            ->fetchColumn();
                    } else {
                        foreach ($preview_photo as $k => $v) {
                            $a_previewTmp[] = $v['image'];
                        }

                        $a_preview = (count($a_previewTmp) > Core::settings('ALBUM_PREVIEW_LIMIT')) ? array_slice($a_previewTmp, 0, Core::settings('ALBUM_PREVIEW_LIMIT')) : $a_previewTmp;
                    }

                    $edit['preview'] = json_encode($a_preview);
                    $edit['preview_page_num'] = count($a_preview);
                    $edit['preview_type'] = $preview_type;
                }

                (new \albumModel)->where([[[['album_id', '=', $album_id]], 'and']])->edit($edit);

                if ($m_album['zipped'] != 0) {
                    $SNSparam = Core::getSNSParams([
                        'trigger' => [
                            'user_id' => $m_user['user_id'],
                            'type' => 'album',
                            'typeId' => $album_id,
                            'refer' => 'UserUpdateAlbum',
                        ],
                        'targetId' => null,
                        'typeOfSNS' => 'albumqueue',
                    ]);
                    (new \topicModel)->publish($m_user['user_id'], 'albumcooperation', $album_id, $SNSparam['message'], 'albumqueue', $album_id, $SNSparam);
                    (new \topicModel)->publish($m_user['user_id'], 'albumqueue', $album_id, $SNSparam['message'], 'albumqueue', $album_id, $SNSparam);
                }

                $result = 1;
                $message = _('Added successfully.');
                break;

            case 'pretreat':
                $result = 1;
                $message = _('Added successfully.');
                break;

            case 'success':
                //album : preview + zipped + state
                //20161101 Mars: 預覽圖設定提前至編輯器中, 故此處需參考Controller::diy送過來的 previer_id處理
                if ($preview_id) {
                    $preview_photo = (new \photoModel)
                        ->column(['image'])
                        ->where([[[['album_id', '=', $album_id], ['photo_id', 'in', $preview_id]], 'and']])
                        ->fetchAll();

                    $a_previewTmp = [];
                    $a_preview = [];
                    if (count($preview_photo) == 0) {
                        $a_preview[] = (new \albumModel)
                            ->column(['cover'])
                            ->where([[[['album_id', '=', $album_id]], 'and']])
                            ->fetchColumn();
                    } else {
                        foreach ($preview_photo as $k => $v) {
                            $a_previewTmp[] = $v['image'];
                        }
                        $a_preview = (count($a_previewTmp) > Core::settings('ALBUM_PREVIEW_LIMIT')) ? array_slice($a_previewTmp, 0, Core::settings('ALBUM_PREVIEW_LIMIT')) : $a_previewTmp;
                    }

                    $edit = [
                        'preview' => json_encode($a_preview),
                    ];

                    $edit['preview_page_num'] = count($a_preview);
                    $edit['preview_type'] = $preview_type;

                    (new \albumModel)
                        ->where([[[['album_id', '=', $album_id]], 'and']])
                        ->edit($edit);
                }

                $result = 1;
                $message = _('Added successfully.');
                break;
        }

        return array_encode_return($result, $message);
    }

    function settingsable($album_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_album = (new albumModel)->column(['user_id', 'act'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();

        if (empty($m_album)) {
            $result = 0;
            $message = _('Album does not exist.');
        } else {
            if ($m_album['act'] == 'none') {
                $result = 0;
                $message = _('[Album] occur exception, please contact us.');
            } elseif ($m_album['act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
            } elseif ($m_album['user_id'] != $user_id) {
                $result = 0;
                $message = _('您不能編輯此相本。');
            }
        }

        return array_encode_return($result, $message);
    }

    function settingsable_v2($album_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        $m_album = (new albumModel)->column(['user_id', 'act'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();

        if (empty($m_album)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('"相本"資料不存在。');
            goto _return;
        } else {
            if ($m_album['act'] == 'delete') {
                $result = \Lib\Result::USER_ERROR;
                $message = _('"相本"資料已刪除。');
                goto _return;
            } elseif ($m_album['user_id'] != $user_id) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('您不能編輯此相本。');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function sortWaterfall(array $param)
    {
        /**
         * 2017-11-23 Lion:
         * 1. 撈取所有作品
         *     a. 權重 desc
         *     b. 發布時間 desc
         * 2. 再排序
         *     a. 含有權重作品在前，依發布時間排序
         *     b. 不含有權重作品在後，依發布時間排序
         */
        usort($param, function ($a, $b) {
            if ($a['album2weight']['weight'] === null) {
                if ($b['album2weight']['weight'] === null) {
                    if ($a['album']['publishtime'] < $b['album']['publishtime']) {
                        $return = 1;
                    } elseif ($a['album']['publishtime'] == $b['album']['publishtime']) {
                        $return = ($a['album']['album_id'] < $b['album']['album_id']) ? 1 : -1;
                    } else {
                        $return = -1;
                    }
                } else {
                    $return = 1;
                }
            } else {
                if ($b['album2weight']['weight'] === null) {
                    $return = -1;
                } else {
                    if ($a['album']['publishtime'] < $b['album']['publishtime']) {
                        $return = 1;
                    } elseif ($a['album']['publishtime'] == $b['album']['publishtime']) {
                        $return = ($a['album']['album_id'] < $b['album']['album_id']) ? 1 : -1;
                    } else {
                        $return = -1;
                    }
                }
            }

            return $return;
        });

        return $param;
    }

    function template(array $where = null, array $order = null, $limit = null)
    {
        $column = [
            'album.album_id',
            'album.name',
            'album.cover'
        ];

        $join = [
            ['inner join', 'user', 'using(user_id)'],
            ['inner join', 'template', 'using(template_id)'],
        ];

        $where = array_merge([[[['album.zipped', '=', true], ['album.act', '=', 'open'], ['user.act', '=', 'open'], ['template.act', '=', 'open']], 'and']], (array)$where);

        return Model('album')->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
    }

    function updateSettings($album_id, array $param)
    {
        if ($param) {
            $data = [];

            $m_album = (new albumModel)
                ->column([
                    'act',
                    'audio_mode',
                    'audio_refer',
                    'audio_target',
                    'category_id',
                    'description',
                    'location',
                    'mood',
                    'name',
                    'point',
                    'publishtime',
                    'user_id',
                    'weather',
                ])
                ->where([[[['album_id', '=', $album_id]], 'and']])
                ->fetch();

            $edit = $param;

            //audio_mode
            if (isset($param['audio_mode'])) {
                if (!isset($param['audio_refer'])) {
                    $edit['audio_refer'] = $param['audio_refer'] = 'none';
                }

                switch ($param['audio_mode']) {
                    case 'none':
                        if ($m_album['audio_mode'] === 'plural') {
                            (new photoModel)->deleteAudio(array_column(
                                    (new photoModel)->column(['photo_id'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchAll(),
                                    'photo_id')
                            );
                        } elseif ($m_album['audio_mode'] === 'singular') {
                            (new albumModel)->deleteAudio($album_id);
                        }
                        break;

                    case 'plural':
                        if ($m_album['audio_mode'] === 'singular') {
                            (new albumModel)->deleteAudio($album_id);
                        }
                        break;

                    case 'singular':
                        if ($m_album['audio_mode'] === 'plural') {
                            (new photoModel)->deleteAudio(array_column(
                                    (new photoModel)->column(['photo_id'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchAll(),
                                    'photo_id')
                            );
                        }

                        if ($param['audio_refer'] == 'file') {
                            if (is_file($param['audio_target'])) {
                                \Extension\aws\S3::upload($param['audio_target']);

                                $edit['audio_target'] = fileinfo($param['audio_target'])['suburl'];

                                if ($m_album['audio_refer'] === 'file') {
                                    (new albumModel)->deleteAudio($album_id);
                                }
                            }
                        } else {
                            $edit['audio_target'] = $param['audio_target'];

                            if ($m_album['audio_refer'] === 'file') {
                                (new albumModel)->deleteAudio($album_id);
                            }
                        }
                        break;
                }
            }

            //
            if (isset($param['display_num_of_collect'])) $edit['display_num_of_collect'] = (bool)$param['display_num_of_collect'];

            //mood
            if (isset($edit['mood']) && !in_array($edit['mood'], \Schema\album::$mood)) $edit['mood'] = 'none';

            //2016-11-24 Lion: (array)preview 裡的是 photo_id
            if (!empty($param['preview'])) {
                $array_preview = array_column(
                    (new photoModel)
                        ->column(['image'])
                        ->where([[[['album_id', '=', $album_id], ['photo_id', 'in', $param['preview']]], 'and']])
                        ->order(['FIELD(' . implode(',', array_merge(['photo_id'], $param['preview'])) . ')' => 'asc'])
                        ->fetchAll(),
                    'image'
                );

                $edit['preview'] = json_encode($array_preview);
                $edit['preview_page_num'] = count($array_preview);
            }

            if ($m_album['publishtime'] === '0000-00-00 00:00:00' && isset($param['act']) && $param['act'] === 'open') {
                $edit['publishtime'] = inserttime();

                $SNSparam = Core::getSNSParams([
                    'trigger' => [
                        'user_id' => $m_album['user_id'],
                        'type' => 'album',
                        'typeId' => $album_id,
                        'refer' => 'UserCreateAlbum',
                    ],
                    'targetId' => null,
                    'typeOfSNS' => 'follow',
                ]);

                (new topicModel)->publish($m_album['user_id'], 'follow', $m_album['user_id'], $SNSparam['message'], 'albumqueue', $album_id, $SNSparam);
            }

            //
            if (isset($param['reward_after_collect'])) $edit['reward_after_collect'] = (bool)$param['reward_after_collect'];

            //
            if (isset($param['reward_description'])) $edit['reward_description'] = $param['reward_description'];

            //weather
            if (isset($edit['weather']) && !in_array($edit['weather'], \Schema\album::$weather)) $edit['weather'] = 'none';

            (new albumModel)
                ->where([[[['album_id', '=', $album_id], ['user_id', '=', $m_album['user_id']]], 'and']])
                ->edit($edit);

            //判斷異動欄位
            $array_0 = array_diff(
                $edit,
                $m_album
            );

            if ($array_0) $data['album'] = $array_0;

            \Model\revision::setAlbum($m_album['user_id'], $album_id, $data);//2017-12-07 Lion: 目前僅有用戶本人可修改資訊，故暫時傳遞 album.user_id

            //
            albumModel::zipAlbumPartRefresh($album_id);
        }
    }

    /**
     * @deprecated 請改用 usable_v2
     */
    function usable($model, $id, $user_id = null)
    {
        $result = 1;
        $message = null;
        $data = null;

        switch ($model) {
            case 'album':
                $where = [
                    [[['album.album_id', '=', $id], ['album.zipped', '=', true], ['user.act', '=', 'open']], 'and']
                ];
                $m = (new albumModel)->column(['album.album_id', 'album.user_id', 'album.act'])->join([['inner join', 'user', 'using(user_id)']])->where($where)->fetch();
                break;

            case 'photo':
                $join = [
                    ['inner join', 'album', 'using(album_id)'],
                    ['inner join', 'user', 'on user.user_id = album.user_id'],
                ];
                $where = [
                    [[['photo.photo_id', '=', $id], ['album.zipped', '=', true], ['user.act', '=', 'open'], ['photo.act', '=', 'open']], 'and']
                ];
                $m = (new photoModel)->column(['album.album_id', 'album.user_id', 'album.act'])->join($join)->where($where)->fetch();
                break;

            case 'photousefor':
                $join = [
                    ['inner join', 'photo', 'using(photo_id)'],
                    ['inner join', 'album', 'using(album_id)'],
                    ['inner join', 'user', 'on user.user_id = album.user_id'],
                ];
                $where = [
                    [[['photousefor.photousefor_id', '=', $id], ['album.zipped', '=', true], ['user.act', '=', 'open']], 'and']
                ];
                $m = (new photouseforModel)->column(['album.album_id', 'album.user_id', 'album.act'])->join($join)->where($where)->fetch();
                break;

            case 'photousefor_user':
                $join = [
                    ['inner join', 'photousefor', 'using(photousefor_id)'],
                    ['inner join', 'photo', 'using(photo_id)'],
                    ['inner join', 'album', 'using(album_id)'],
                    ['inner join', 'user', 'on user.user_id = album.user_id'],
                ];
                $where = [
                    [[['photousefor_user.photousefor_user_id', '=', $id], ['album.zipped', '=', true], ['user.act', '=', 'open']], 'and']
                ];
                $m = (new photousefor_userModel)->column(['album.album_id', 'album.user_id', 'album.act'])->join($join)->where($where)->fetch();
                break;

            default:
                throw new Exception('Unknown case');
                break;
        }

        if (empty($m)) {
            $result = 0;
            $message = _('Album does not exist.');
            goto _return;
        } else {
            if ($m['act'] == 'close') {
                if ($user_id === null) {
                    $result = 0;
                    $message = _('Album is not open.');
                    goto _return;
                }

                $m_cooperation = (new cooperationModel)->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $m['album_id']]], 'and']])->fetch();

                if (empty($m_cooperation)) {
                    $result = 0;
                    $message = _('Album is not open.');
                    goto _return;
                }
            } elseif ($m['act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                goto _return;
            } elseif ($m['act'] == 'none') {
                $result = 0;
                $message = _('[Album] occur exception, please contact us.');
                goto _return;
            }
        }

        $data = [
            'album' => [
                'user_id' => $m['user_id']
            ],
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function usable_v2($album_id, $user_id = null)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        $m_album = (new albumModel)
            ->column([
                'album.act album_act',
                'album.album_id',
                'album.user_id',
                'album.zipped',
                'user.act user_act',
            ])
            ->join([['INNER JOIN', 'user', 'USING(user_id)']])
            ->where([[[['album.album_id', '=', $album_id]], 'AND']])
            ->fetch();

        if (empty($m_album)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('Album does not exist.');
            goto _return;
        } else {
            switch ($m_album['album_act']) {
                case 'close':
                    if ($user_id === null) {
                        $result = \Lib\Result::USER_ERROR;
                        $message = _('Album is not open.');
                        goto _return;
                    } else {
                        $m_cooperation = (new cooperationModel)
                            ->column(['COUNT(1)'])
                            ->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $album_id]], 'AND']])
                            ->fetch();

                        if (empty($m_cooperation)) {
                            $result = \Lib\Result::USER_ERROR;
                            $message = _('Album is not open.');
                            goto _return;
                        }
                    }
                    break;

                case 'delete':
                    $result = \Lib\Result::USER_ERROR;
                    $message = _('Album does not exist.');
                    goto _return;
                    break;
            }

            if ($m_album['zipped'] == false) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('作品未曾壓製。');
                goto _return;
            }

            if ($m_album['user_act'] == 'close') {
                $result = \Lib\Result::USER_ERROR;
                $message = _('創作人帳號已關閉。');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

//2016-08-29 Lion: 可能棄用，改用 getFollow、getFree、getHot、getLatest、getSponsored
    function waterfall($user_id = null, array $where = null, array $order = null, $limit)
    {
        list($offset, $row_count) = explode(',', $limit);

        $return = [];

        $column = [
            'album.act album_act',
            'album.album_id',
            'album.cover',
            'album.description album_description',
            'album.inserttime',
            'album.location',
            'album.name album_name',
            'album.preview',
            'albumstatistics.count',
            'follow.count_from',
            'user.act user_act',
            'user.name user_name',
            'user.user_id',
        ];

        $join = [
            ['left join', 'albumstatistics', 'using(album_id)'],
            ['left join', 'follow', 'using(user_id)'],
            ['left join', 'user', 'using(user_id)'],
        ];

        $where = array_merge([[[['album.zipped', '=', true], ['album.act', '=', 'open'], ['user.act', '=', 'open']], 'and']], (array)$where);

        //如有 user 資訊, 先進 notice 取樣
        $a_album_id = [];
        if ($user_id) {
            $where0 = [
                [[['notice.type', '=', 'album'], ['notice.act', '=', 'open'], ['noticequeue.user_id', '=', $user_id]], 'and']
            ];
            $m_notice = Model('notice')->column(['notice.id'])->join([['inner join', 'noticequeue', 'using(notice_id)']])->where($where0)->order(['notice.inserttime' => 'desc'])->fetchAll();
            if ($m_notice) {
                $a_album_id = array_column($m_notice, 'id');

                $where1 = array_merge($where, [[[['album.album_id', 'in', $a_album_id]], 'and']]);

                $order1 = array_merge((array)$order, ['FIELD(' . implode(',', array_merge(['album.album_id'], $a_album_id)) . ')' => 'asc']);

                //不使用 limit 是因為要得知總筆數, 推得下一階段進 album 的 offset
                $m_album = Model('album')->column($column)->join($join)->where($where1)->order($order1)->fetchAll();

                $c_album = count($m_album);

                if ($offset < $c_album) {
                    $return = array_merge($return, array_slice($m_album, $offset, $row_count));
                }

                $offset = ($offset - $c_album <= 0) ? 0 : $offset - $c_album;//扣除略過筆數, 為下一階段進 album 的 offset
                $row_count -= count($return);//扣除已取樣筆數, 為下一階段進 album 的 row_count
            }
        }

        //如果有餘, 再進 album 取樣
        if ($row_count > 0) {
            //album
            if ($a_album_id) $where = array_merge($where, [[[['album.album_id', 'not in', $a_album_id]], 'and']]);

            $m_album = Model('album')->column($column)->join($join)->where($where)->order($order)->limit($offset . ',' . $row_count)->fetchAll();

            $return = array_merge($return, $m_album);
        }

        return $return;
    }

    function zip($album_id)
    {
        $column = [
            'album.album_id',
            'album.name album_name',
            'album.description',
            'album.location',
            'album.point',
            'album.audio_mode',
            'album.audio_loop',
            'album.audio_refer',
            'album.audio_target',
            'album.inserttime',
            'user.user_id',
            'user.name user_name',
        ];
        $join = [
            ['left join', 'user', 'using(user_id)'],
        ];
        $where = [
            [[['album.album_id', '=', $album_id]], 'and']
        ];
        //Get album
        $m_album = (new \albumModel)
            ->column($column)
            ->join($join)
            ->where($where)
            ->fetch();

        if (empty($m_album)) return array_encode_return(0);

        $subpathname_user_storage = SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $m_album['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $m_album['album_id'];
        $pathname_user_storage = mkdir_p(PATH_STORAGE, $subpathname_user_storage);

        //Get photo
        $column = [
            'photo_id',
            'name',
            'description',
            'image',
            'location',
            'usefor',
            'hyperlink',
            'audio_loop',
            'audio_refer',
            'audio_target',
            'video_refer',
            'video_target',
            'duration',
        ];
        $m_photo = Model('photo')->column($column)->where([[[['album_id', '=', $m_album['album_id']], ['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();

        //Get audio
        $m_audio = Model('audio')->column(['audio_id', '`file`'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
        $a_audio = [];
        foreach ($m_audio as $k0 => $v0) {
            $a_audio[$v0['audio_id']] = $v0['file'];
        }

        $zip = new PclZip(PATH_STORAGE . iconv('UTF-8', 'Big5', storagefile(SITE_LANG . '/album/' . $m_album['album_id'] . '.zip')));
        $zip_box = [];

        /**
         *  bgm.mp3
         *  若 mode = none / singular => 控制權為 album ;  若為 plural => 為 photo 個別 bgm
         */
        $album_audio_target = null;
        switch ($m_album['audio_mode']) {
            case 'none':
            case 'plural':
                break;

            case 'singular':
                $file = null;
                switch ($m_album['audio_refer']) {
                    case 'embed':
                        $album_audio_target = $m_album['audio_target'];
                        break;

                    case 'file':
                        $file = PATH_UPLOAD . $m_album['audio_target'];
                        $album_audio_target = 'bgm.mp3';
                        if (is_file($file)) {
                            copy($file, PATH_STORAGE . storagefile($subpathname_user_storage . DIRECTORY_SEPARATOR . $album_audio_target));
                        }
                        break;

                    case 'none':
                        break;

                    case 'system':
                        $file = PATH_STATIC_FILE . $a_audio[$m_album['audio_target']];
                        $album_audio_target = 'bgm.mp3';
                        break;

                    default:
                        throw new Exception('Unknown case');
                        break;
                }
                if (is_file($file)) {
                    $zip_box[] = [
                        PCLZIP_ATT_FILE_NAME => $album_audio_target,
                        PCLZIP_ATT_FILE_CONTENT => file_get_contents($file)
                    ];
                }
                break;
        }

        /**
         * audio + hyperlink(icon) + video + info.txt
         */
        $a_photo = [];
        foreach ($m_photo as $k0 => $v0) {
            //audio
            switch ($v0['audio_refer']) {
                case 'embed':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = $v0['audio_target'];
                    break;

                case 'file':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = $k0 . '.mp3';

                    if (is_file(PATH_UPLOAD . $v0['audio_target'])) {
                        copy(PATH_UPLOAD . $v0['audio_target'], PATH_STORAGE . storagefile($subpathname_user_storage . DIRECTORY_SEPARATOR . $audio_target));

                        $zip_box[] = [
                            PCLZIP_ATT_FILE_NAME => $audio_target,
                            PCLZIP_ATT_FILE_CONTENT => file_get_contents(PATH_UPLOAD . $v0['audio_target'])
                        ];
                    }
                    break;

                case 'none':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = null;
                    break;

                case 'system':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = $k0 . '.mp3';
                    if (is_file(PATH_STATIC_FILE . $a_audio[$v0['audio_target']])) {
                        $zip_box[] = [
                            PCLZIP_ATT_FILE_NAME => $audio_target,
                            PCLZIP_ATT_FILE_CONTENT => file_get_contents(PATH_STATIC_FILE . $a_audio[$v0['audio_target']])
                        ];
                    }
                    break;

                default:
                    throw new Exception("[" . __FUNCTION__ . "] Unknown case");
                    break;
            }

            //hyperlink
            $a_hyperlink = null;
            if (!empty($v0['hyperlink'])) {
                foreach (json_decode($v0['hyperlink'], true) as $k1 => $v1) {
                    $icon = $k0 . '-hyperlink-' . $k1 . '.png';

                    if (is_file(PATH_UPLOAD . $v1['icon'])) {
                        copy(PATH_UPLOAD . $v1['icon'], PATH_STORAGE . storagefile($subpathname_user_storage . DIRECTORY_SEPARATOR . $icon));

                        $zip_box[] = [
                            PCLZIP_ATT_FILE_NAME => $icon,
                            PCLZIP_ATT_FILE_CONTENT => file_get_contents(PATH_UPLOAD . $v1['icon'])
                        ];
                    }

                    $a_hyperlink[] = [
                        'icon' => $icon,
                        'text' => $v1['text'],
                        'url' => $v1['url'],
                    ];
                }
            }

            //image
            if (is_file(PATH_UPLOAD . $v0['image'])) {
                $zip_box[] = [
                    PCLZIP_ATT_FILE_NAME => $k0 . '.jpg',
                    PCLZIP_ATT_FILE_CONTENT => file_get_contents(PATH_UPLOAD . $v0['image'])
                ];
            }

            //video
            switch ($v0['video_refer']) {
                case 'embed':
                    $video_refer = $v0['video_refer'];
                    $video_target = $v0['video_target'];
                    break;

                case 'file':
                    $video_refer = $v0['video_refer'];
                    $video_target = URL_UPLOAD . $v0['video_target'];
                    break;

                case 'none':
                    $video_refer = $v0['video_refer'];
                    $video_target = null;
                    break;

                case 'system':
                    //^尚無
                    break;

                default:
                    throw new Exception("[" . __FUNCTION__ . "] Unknown case");
                    break;
            }

            $a_photo[] = [
                'audio_loop' => $v0['audio_loop'],
                'audio_refer' => $audio_refer,
                'audio_target' => $audio_target,
                'description' => $v0['description'],
                'duration' => $v0['duration'],
                'hyperlink' => $a_hyperlink,
                'location' => $v0['location'],
                'name' => $v0['name'],
                'photo_id' => $v0['photo_id'],
                'usefor' => $v0['usefor'],
                'video_refer' => $video_refer,
                'video_target' => $video_target,
            ];
        }

        //info.txt
        $info = iconv('UTF-8', 'Big5', $pathname_user_storage . DIRECTORY_SEPARATOR . 'info.txt');
        $handle = fopen($info, 'w');
        fwrite($handle, json_encode([
            'albumid' => $m_album['album_id'],
            'audio_mode' => $m_album['audio_mode'],
            'audio_loop' => $m_album['audio_loop'],
            'audio_refer' => $m_album['audio_refer'],
            'audio_target' => $album_audio_target,
            'author' => $m_album['user_name'],
            'description' => strip_tags($m_album['description']),
            'inserttime' => $m_album['inserttime'],
            'location' => $m_album['location'],
            'photo' => $a_photo,
            'picfileurl' => URL_STORAGE . Core::get_userpicture($m_album['user_id']),
            'price' => $m_album['point'],
            'title' => $m_album['album_name'],
        ]));
        fclose($handle);
        $zip_box[] = [
            PCLZIP_ATT_FILE_NAME => 'info.txt',
            PCLZIP_ATT_FILE_CONTENT => file_get_contents($info),
        ];
        unlink($info);

        //製作 zip
        $zip->create($zip_box, PCLZIP_OPT_REMOVE_PATH, $pathname_user_storage);

        return array_encode_return(1);
    }

    static function zipAlbumPartRefresh($album_id)
    {
        $column = [
            'album.album_id',
            'album.name album_name',
            'album.description',
            'album.audio_mode',
            'album.audio_loop',
            'album.audio_refer',
            'album.audio_target',
            'album.location',
            'album.point',
            'album.inserttime',
            'user.user_id',
            'user.name user_name',
        ];
        $join = [
            ['left join', 'user', 'using(user_id)'],
        ];
        $where = [
            [[['album.album_id', '=', $album_id]], 'and']
        ];
        $m_album = Model('album')->column($column)->join($join)->where($where)->fetch();

        if (empty($m_album)) return array_encode_return(0);

        $subpathname_user_storage = SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $m_album['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $m_album['album_id'];
        $pathname_user_storage = mkdir_p(PATH_STORAGE, $subpathname_user_storage);

        $zip = new PclZip(PATH_STORAGE . iconv('UTF-8', 'Big5', storagefile(SITE_LANG . '/album/' . $m_album['album_id'] . '.zip')));
        $zip_box = [];

        //刪除 zip 裡舊的 bgm.mp3 + info.txt
        $zip->delete(PCLZIP_OPT_BY_NAME, 'bgm.mp3');
        $zip->delete(PCLZIP_OPT_BY_NAME, 'info.txt');

        //bgm.mp3
        $album_audio_target = null;
        switch ($m_album['audio_mode']) {
            case 'none':
            case 'plural':
                break;

            case 'singular':
                $file = null;
                switch ($m_album['audio_refer']) {
                    case 'embed':
                        $album_audio_target = $m_album['audio_target'];
                        break;

                    case 'file':
                        $file = PATH_UPLOAD . $m_album['audio_target'];
                        $album_audio_target = 'bgm.mp3';
                        if (is_file($file)) {
                            copy($file, PATH_STORAGE . storagefile($subpathname_user_storage . DIRECTORY_SEPARATOR . $album_audio_target));
                        }
                        break;

                    case 'none':
                        break;

                    case 'system':
                        $file = PATH_STATIC_FILE . Model('audio')->column(['`file`'])->where([[[['audio_id', '=', $m_album['audio_target']]], 'and']])->fetchColumn();
                        $album_audio_target = 'bgm.mp3';
                        break;

                    default:
                        throw new Exception('Unknown case');
                        break;
                }
                if (is_file($file)) {
                    $zip_box[] = [
                        PCLZIP_ATT_FILE_NAME => $album_audio_target,
                        PCLZIP_ATT_FILE_CONTENT => file_get_contents($file)
                    ];
                }
                break;
        }

        /**
         * audio + hyperlink(icon) + video + info.txt
         */
        $column = [
            'photo_id',
            'name',
            'description',
            'location',
            'usefor',
            'hyperlink',
            'audio_loop',
            'audio_refer',
            'audio_target',
            'video_refer',
            'video_target',
            'duration',
        ];
        $m_photo = Model('photo')->column($column)->where([[[['album_id', '=', $m_album['album_id']], ['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();
        $a_photo = [];
        foreach ($m_photo as $k0 => $v0) {
            //audio
            switch ($v0['audio_refer']) {
                case 'embed':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = $v0['audio_target'];
                    break;

                case 'file':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = $k0 . '.mp3';
                    break;

                case 'none':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = null;
                    break;

                case 'system':
                    $audio_refer = $v0['audio_refer'];
                    $audio_target = $k0 . '.mp3';
                    break;

                default:
                    throw new Exception('Unknown case');
                    break;
            }

            //hyperlink
            $a_hyperlink = null;
            if (!empty($v0['hyperlink'])) {
                foreach (json_decode($v0['hyperlink'], true) as $k1 => $v1) {
                    $a_hyperlink[] = [
                        'icon' => $k0 . '-hyperlink-' . $k1 . '.png',
                        'text' => $v1['text'],
                        'url' => $v1['url'],
                    ];
                }
            }

            //video
            switch ($v0['video_refer']) {
                case 'embed':
                    $video_refer = $v0['video_refer'];
                    $video_target = $v0['video_target'];
                    break;

                case 'file':
                    $video_refer = $v0['video_refer'];
                    $video_target = URL_UPLOAD . $v0['video_target'];
                    break;

                case 'none':
                    $video_refer = $v0['video_refer'];
                    $video_target = null;
                    break;

                case 'system':
                    //^尚無
                    break;

                default:
                    throw new Exception("[" . __FUNCTION__ . "] Unknown case");
                    break;
            }

            $a_photo[] = [
                'audio_loop' => $v0['audio_loop'],
                'audio_refer' => $audio_refer,
                'audio_target' => $audio_target,
                'description' => $v0['description'],
                'duration' => $v0['duration'],
                'hyperlink' => $a_hyperlink,
                'location' => $v0['location'],
                'name' => $v0['name'],
                'photo_id' => $v0['photo_id'],
                'usefor' => $v0['usefor'],
                'video_refer' => $video_refer,
                'video_target' => $video_target,
            ];
        }

        //info.txt
        $info = iconv('UTF-8', 'Big5', $pathname_user_storage . DIRECTORY_SEPARATOR . 'info.txt');
        $handle = fopen($info, 'w');
        fwrite($handle, json_encode([
            'albumid' => $m_album['album_id'],
            'audio_mode' => $m_album['audio_mode'],
            'audio_loop' => $m_album['audio_loop'],
            'audio_refer' => $m_album['audio_refer'],
            'audio_target' => $album_audio_target,
            'author' => $m_album['user_name'],
            'description' => strip_tags($m_album['description']),
            'inserttime' => $m_album['inserttime'],
            'location' => $m_album['location'],
            'photo' => $a_photo,
            'picfileurl' => URL_STORAGE . Core::get_userpicture($m_album['user_id']),
            'price' => $m_album['point'],
            'title' => $m_album['album_name'],
        ]));
        fclose($handle);
        $zip_box[] = [
            PCLZIP_ATT_FILE_NAME => 'info.txt',
            PCLZIP_ATT_FILE_CONTENT => file_get_contents($info),
        ];
        unlink($info);

        //建立 zip 裡新的 bgm.mp3 + info.txt
        $zip->add($zip_box, PCLZIP_OPT_REMOVE_PATH, $pathname_user_storage);

        return array_encode_return(1);
    }

    function getqrcode($type, $type_id, $is_cooperation, $is_follow)
    {

        $array_0 = ['type' => $type, 'type_id' => $type_id, 'is_cooperation' => $is_cooperation, 'is_follow' => $is_follow];
        $url = \frontstageController::url('highway', 'index', array_merge($array_0, ['sign' => encrypt($array_0)]));
        $QRcode = new \Core\QRcode();
        $data = $QRcode->setTextUrl($url)->setLevel(1)->getBase64();

        return $data;
    }
}