<?php

class photoModel extends Model
{
    protected $database = 'site';
    protected $table = 'photo';
    protected $memcache = 'site';
    protected $join_table = ['album', 'cooperation', 'user'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'albumModel'],
            ['class' => 'photouseforModel'],
            ['class' => 'photousefor_userModel'],
        ];
    }

    function ableToDeleteAudio($photo_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_photo = Model('photo')->column([
            'album.album_id',
            'album.user_id album$user_id',
            'album.act',
            'photo.user_id photo$user_id',
            'photo.usefor',
            'photo.audio_refer',
        ])->join([['left join', 'album', 'USING(album_id)']])->where([[[['photo.photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])->fetch();

        if ($m_photo == null) {
            $result = 0;
            $message = _('The photo does not exist.');
            goto _return;
        } else {
            if ($m_photo['usefor'] == 'none') {
                $result = 0;
                $message = _('The photo does not exist.');
                goto _return;
            } elseif ($m_photo['album_id'] == null) {
                $result = 0;
                $message = _('Album does not exist.');
                goto _return;
            } elseif ($m_photo['act'] == 'none') {
                $result = 0;
                $message = _('[Album] occur exception, please contact us.');
                goto _return;
            } elseif ($m_photo['act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                goto _return;
            } elseif ($m_photo['album$user_id'] != $user_id) {
                $m_cooperation = Model('cooperation')->column(['identity'])->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $m_photo['album_id']]], 'and']])->fetch();
                if ($m_cooperation == null) {
                    $result = 0;
                    $message = _('You can not DIY this photo.');
                    goto _return;
                } else {
                    switch ($m_cooperation['identity']) {
                        case 'admin':
                            break;

                        case 'approver':
                        case 'editor':
                        case 'viewer':
                            if ($m_photo['photo$user_id'] != $user_id) {
                                $result = 0;
                                $message = _('You can not DIY this photo.');
                                goto _return;
                            }
                            break;

                        default:
                            $result = 0;
                            $message = _('Unknown case of identity.');
                            goto _return;
                            break;
                    }
                }
            }

            switch ($m_photo['audio_refer']) {
                case 'none':
                    $result = 0;
                    $message = _('The audio has been deleted.');
                    goto _return;
                    break;

                case 'embed':
                case 'file':
                case 'system':
                    break;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToDeletePhoto($photo_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_photo = (new photoModel())->column([
            'album.album_id',
            'album.user_id album$user_id',
            'album.act',
            'photo.user_id photo$user_id',
            'photo.usefor',
        ])->join([['left join', 'album', 'USING(album_id)']])->where([[[['photo.photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])->fetch();

        if ($m_photo == null) {
            $result = 0;
            $message = _('The photo does not exist.');
            goto _return;
        } else {
            switch ($m_photo['usefor']) {
                case 'none':
                    $result = 0;
                    $message = _('The photo does not exist.');
                    goto _return;
                    break;

                case 'exchange':
                case 'slot':
                case 'image':
                case 'video':
                    if ($m_photo['album_id'] == null) {
                        $result = 0;
                        $message = _('Album does not exist.');
                        goto _return;
                    } else {
                        if ($m_photo['act'] == 'none') {
                            $result = 0;
                            $message = _('[Album] occur exception, please contact us.');
                            goto _return;
                        } elseif ($m_photo['act'] == 'delete') {
                            $result = 0;
                            $message = _('Album does not exist.');
                            goto _return;
                        } elseif ($m_photo['album$user_id'] != $user_id) {
                            $m_cooperation = (new cooperationModel())->column(['identity'])->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $m_photo['album_id']]], 'and']])->fetch();
                            if ($m_cooperation == null) {
                                $result = 0;
                                $message = _('You can not DIY this photo.');
                                goto _return;
                            } else {
                                switch ($m_cooperation['identity']) {
                                    case 'admin':
                                        break;

                                    case 'approver':
                                    case 'editor':
                                    case 'viewer':
                                        if ($m_photo['photo$user_id'] != $user_id) {
                                            $result = 0;
                                            $message = _('You can not DIY this photo.');
                                            goto _return;
                                        }
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
                    break;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToDeleteVideo($photo_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_photo = (new \photoModel)
            ->column([
                'album.album_id',
                'album.user_id album$user_id',
                'album.act',
                'photo.user_id photo$user_id',
                'photo.usefor',
                'photo.video_refer',
            ])
            ->join([['left join', 'album', 'USING(album_id)']])
            ->where([[[['photo.photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])
            ->fetch();

        if ($m_photo == null) {
            $result = 0;
            $message = _('The photo does not exist.');
            goto _return;
        } else {
            if ($m_photo['usefor'] == 'none') {
                $result = 0;
                $message = _('The photo does not exist.');
                goto _return;
            } elseif ($m_photo['album_id'] == null) {
                $result = 0;
                $message = _('Album does not exist.');
                goto _return;
            } elseif ($m_photo['act'] == 'none') {
                $result = 0;
                $message = _('[Album] occur exception, please contact us.');
                goto _return;
            } elseif ($m_photo['act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                goto _return;
            } elseif ($m_photo['album$user_id'] != $user_id) {
                $m_cooperation = Model('cooperation')->column(['identity'])->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $m_photo['album_id']]], 'and']])->fetch();
                if ($m_cooperation == null) {
                    $result = 0;
                    $message = _('You can not DIY this photo.');
                    goto _return;
                } else {
                    switch ($m_cooperation['identity']) {
                        case 'admin':
                            break;

                        case 'approver':
                        case 'editor':
                        case 'viewer':
                            if ($m_photo['photo$user_id'] != $user_id) {
                                $result = 0;
                                $message = _('You can not DIY this photo.');
                                goto _return;
                            }
                            break;

                        default:
                            $result = 0;
                            $message = _('Unknown case of identity.');
                            goto _return;
                            break;
                    }
                }
            }

            switch ($m_photo['video_refer']) {
                case 'none':
                    $result = 0;
                    $message = _('The video has been deleted.');
                    goto _return;
                    break;

                case 'embed':
                case 'file':
                case 'system':
                    break;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToInsertAudio($album_id, $user_id)
    {
        return call_user_func('photoModel::ableToInsertPhoto', $album_id, $user_id);
    }

    function ableToInsertPhoto($album_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_album = (new albumModel())->column(['user_id', 'act'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
        if ($m_album == null) {
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
                $m_cooperation = (new cooperationModel())->column(['identity'])->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $album_id]], 'and']])->fetch();
                if ($m_cooperation == null) {
                    $result = 0;
                    $message = _('You can not DIY this album.');
                    goto _return;
                } else {
                    switch ($m_cooperation['identity']) {
                        case 'admin':
                        case 'approver':
                        case 'editor':
                            break;

                        case 'viewer':
                            $result = 0;
                            $message = _('You can not DIY this photo.');
                            goto _return;
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

        $c_photo = (new photoModel())->column(['COUNT(1)'])->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])->fetchColumn();

        //檢查限制張數
        $a_photos_per_album = json_decode(Core::settings('PHOTOS_PER_ALBUM'), true);
        $usergrade = Core::get_usergrade($user_id);
        if (array_key_exists($usergrade, $a_photos_per_album)) {
            if ($c_photo >= $a_photos_per_album[$usergrade]) {
                $result = 0;
                $message = _('The number of photos in a album reached the maximum.');
                goto _return;
            }
        } else {
            $result = 0;
            $message = _('Unknown case of user-grade.');
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToInsertUsefor_User($photo_id, $user_id, $device_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($photo_id)) $photo_id = null;
        if (empty($user_id)) $user_id = null;
        if (empty($device_id)) $device_id = null;

        if ($photo_id === null || $user_id === null || $device_id === null) {
            $result = 0;
            $message = 'Param error.';
            goto _return;
        }

        $m_photousefor = (new photouseforModel)
            ->column([
                'photousefor.photousefor_id',
                'photousefor.name',
                'photousefor.image',
                'photousefor.description',
                'photousefor_user.photousefor_user_id',
                'photousefor_user.state',
            ])
            ->join([['inner join', 'photousefor_user', 'using(photousefor_id)']])
            ->where([[[['photousefor.photo_id', '=', $photo_id], ['photousefor_user.user_id', '=', $user_id]], 'and']])
            ->fetch();

        if ($m_photousefor) {
            switch ($m_photousefor['state']) {
                case 'pretreat':
                    break;

                case 'success':
                    $result = 2;
                    $message = _('您已經領取了。');
                    $data = [
                        'photousefor' => [
                            'photousefor_id' => $m_photousefor['photousefor_id'],
                            'name' => $m_photousefor['name'],
                            'image' => $m_photousefor['image'],
                            'description' => $m_photousefor['description'],
                        ],
                        'photousefor_user' => [
                            'photousefor_user_id' => $m_photousefor['photousefor_user_id'],
                        ],
                    ];
                    goto _return;
                    break;

                default:
                    throw new \Exception('Unknown case');
                    break;
            }
        } else {
            $count = (new photouseforModel)->column(['COUNT(1)'])->where([[[['photo_id', '=', $photo_id], ['`count`', '>', 0]], 'and']])->fetchColumn();

            if ($count == 0) {
                $result = 3;
                $message = _('已發送完畢。');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function ableToInsertVideo($album_id, $user_id, $video_refer = 'file')
    {
        list ($result, $message) = array_decode_return(call_user_func('photoModel::ableToInsertPhoto', $album_id, $user_id));
        if ($result != 1) {
            goto _return;
        }

        if (!in_array($video_refer, ['embed', 'file', 'none', 'system'])) {
            $result = 0;
            $message = 'Param error, unknown case of video_refer.';

            goto _return;
        }

        switch ($video_refer) {
            case 'file':
                if (empty($_FILES['file'])) {
                    $result = 0;
                    $message = 'Input name must be [file].';

                    goto _return;
                } else {
                    if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                        if (!is_video($_FILES['file']['tmp_name'])) {
                            $result = 0;
                            $message = _('File\'s type is incorrect.');

                            goto _return;
                        }

                        $video = new \Core\Video();

                        $video->setFile($_FILES['file']['tmp_name']);

                        if (!in_array($video->getCodec(), ['h264', 'hevc', 'mpeg4'])) {
                            $result = 0;
                            $message = _('Upload file type only can be MP4.');

                            goto _return;
                        }

                        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                            $result = 0;
                            $message = _('檔案未經由正常途徑上傳。');

                            goto _return;
                        }
                    } else {
                        $result = 0;
                        $message = \Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']];

                        goto _return;
                    }
                }
                break;
        }

        _return:

        return array_encode_return($result, $message);
    }

    function ableToSortPhoto($album_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_album = Model('album')->column(['user_id', 'act'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
        if ($m_album == null) {
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
                            break;

                        case 'approver':
                        case 'editor':
                        case 'viewer':
                            $result = 0;
                            $message = _('You can not DIY this photo.');
                            goto _return;
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
        return array_encode_return($result, $message);
    }

    function ableToUpdateAudio($photo_id, $user_id)
    {
        return call_user_func('photoModel::ableToUpdatePhoto', $photo_id, $user_id);
    }

    function ableToUpdatePhoto($photo_id, $user_id)
    {
        $result = 1;
        $message = null;

        $m_photo = (new \photoModel)
            ->column([
                'album.album_id',
                'album.user_id album$user_id',
                'album.act',
                'photo.user_id photo$user_id',
                'photo.usefor',
            ])
            ->join([['left join', 'album', 'USING(album_id)']])
            ->where([[[['photo.photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])
            ->fetch();

        if ($m_photo == null) {
            $result = 0;
            $message = _('The photo does not exist.');
            goto _return;
        } else {
            switch ($m_photo['usefor']) {
                case 'none':
                    $result = 0;
                    $message = _('The photo does not exist.');
                    goto _return;
                    break;

                case 'exchange':
                case 'image':
                case 'slot':
                case 'video':
                    if ($m_photo['album_id'] == null) {
                        $result = 0;
                        $message = _('Album does not exist.');
                        goto _return;
                    } else {
                        if ($m_photo['act'] == 'none') {
                            $result = 0;
                            $message = _('[Album] occur exception, please contact us.');
                            goto _return;
                        } elseif ($m_photo['act'] == 'delete') {
                            $result = 0;
                            $message = _('Album does not exist.');
                            goto _return;
                        } elseif ($m_photo['album$user_id'] != $user_id) {
                            $m_cooperation = Model('cooperation')->column(['identity'])->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $m_photo['album_id']]], 'and']])->fetch();
                            if ($m_cooperation == null) {
                                $result = 0;
                                $message = _('You can not DIY this album.');
                                goto _return;
                            } else {
                                switch ($m_cooperation['identity']) {
                                    case 'admin':
                                        break;

                                    case 'approver':
                                    case 'editor':
                                    case 'viewer':
                                        if ($m_photo['photo$user_id'] != $user_id) {
                                            $result = 0;
                                            $message = _('You can not DIY this photo.');
                                            goto _return;
                                        }
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
                    break;

                default:
                    throw new \Exception('Unknown case');
                    break;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToUpdateVideo($photo_id, $user_id, $video_refer = 'file')
    {
        list ($result, $message) = array_decode_return(call_user_func('photoModel::ableToUpdatePhoto', $photo_id, $user_id));
        if ($result != 1) {
            goto _return;
        }

        if (!in_array($video_refer, ['embed', 'file', 'none', 'system'])) {
            $result = 0;
            $message = 'Param error, unknown case of video_refer.';

            goto _return;
        }

        switch ($video_refer) {
            case 'file':
                if (empty($_FILES['file'])) {
                    $result = 0;
                    $message = 'Input name must be [file].';

                    goto _return;
                } else {
                    if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                        if (!is_video($_FILES['file']['tmp_name'])) {
                            $result = 0;
                            $message = _('File\'s type is incorrect.');

                            goto _return;
                        }

                        $video = new \Core\Video();

                        $video->setFile($_FILES['file']['tmp_name']);

                        if (!in_array($video->getCodec(), ['h264', 'hevc', 'mpeg4'])) {
                            $result = 0;
                            $message = _('Upload file type only can be MP4.');

                            goto _return;
                        }

                        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
                            $result = 0;
                            $message = _('檔案未經由正常途徑上傳。');

                            goto _return;
                        }
                    } else {
                        $result = 0;
                        $message = \Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']];

                        goto _return;
                    }
                }
                break;
        }

        _return:

        return array_encode_return($result, $message);
    }

    function deleteAudio($photo_id)
    {
        if (is_array($photo_id)) {
            $where = [[[['photo_id', 'IN', $photo_id], ['photo.act', '=', 'open']], 'and']];
        } else {
            $where = [[[['photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']];
        }

        $m_photo = (new photoModel)
            ->column(['album_id', 'audio_refer', 'audio_target'])
            ->where($where)
            ->lock('for update')
            ->fetchAll();

        foreach ($m_photo as $v0) {
            switch ($v0['audio_refer']) {
                case 'embed':
                case 'system':
                    break;

                case 'file':
                    \Core\File::delete([PATH_UPLOAD . $v0['audio_target']]);
                    break;
            }
        }

        (new photoModel)
            ->where($where)
            ->edit([
                'audio_refer' => 'none',
                'audio_target' => '',
            ]);

        if ($m_photo) (new albumModel)->where([[[['album_id', '=', $m_photo[0]['album_id']]], 'and']])->edit(['state' => 'process']);
    }

    //2016-06-23 Lion: albumModel::deletePhoto 準備棄用, 改使用這個
    function deletePhoto($photo_id)
    {
        $m_photo = (new photoModel())
            ->column(['album_id', 'image', 'usefor', 'audio_refer', 'audio_target', 'video_refer', 'video_target'])
            ->where([[[['photo_id', '=', $photo_id], ['act', '=', 'open']], 'and']])
            ->lock('for update')
            ->fetch();

        switch ($m_photo['usefor']) {
            case 'none':
                break;

            case 'exchange':
            case 'slot':
                (new photoModel())
                    ->where([[[['photo_id', '=', $photo_id]], 'and']])
                    ->edit([
                        'act' => 'delete',
                    ]);
                break;

            case 'image':
            case 'video':
                \Core\File::delete([PATH_UPLOAD . $m_photo['image']]);

                (new photoModel())
                    ->where([[[['photo_id', '=', $photo_id]], 'and']])
                    ->edit([
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
                    ]);
                break;
        }

        if ($m_photo['audio_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_photo['audio_target']]);

        if ($m_photo['video_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_photo['video_target']]);

        //
        $albumModel = (new albumModel())
            ->column([
                'audio_mode',
                'preview'
            ])
            ->where([[[['album_id', '=', $m_photo['album_id']]], 'and']])
            ->lock('for update')
            ->fetch();

        //
        $a_preview = ($albumModel['preview']) ? json_decode($albumModel['preview'], true) : [];

        //
        $audio_mode = $albumModel['audio_mode'];

        if ($albumModel['audio_mode'] === 'plural') {
            $count = (new \photoModel())
                ->column(['COUNT(1)'])
                ->where([[[['album_id', '=', $m_photo['album_id']], ['audio_refer', 'IN', ['embed', 'file', 'system']]], 'and']])
                ->fetchColumn();

            if ($count == 0) {
                $audio_mode = 'none';
            }
        }

        (new albumModel())
            ->where([[[['album_id', '=', $m_photo['album_id']]], 'and']])
            ->edit([
                'audio_mode' => $audio_mode,
                'preview' => json_encode(array_values(array_diff($a_preview, [$m_photo['image']]))),
            ]);

        (new albumModel())->refreshPhoto($m_photo['album_id']);
    }

    function deleteVideo($photo_id)
    {
        return call_user_func('photoModel::deletePhoto', $photo_id);
    }

    function diyable($photo_id, $album_id, $user_id)
    {
        $result = 1;
        $message = null;

        $data = $m_photo = Model('photo')->column(['photo_id', 'album_id', 'user_id', 'image'])->where([[[['photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])->fetch();
        if ($m_photo == null) {
            $result = 0;
            $message = _('Photo does not exist.');
            $data = null;
            goto _return;
        } else {
            if ($m_photo['album_id'] == 0 && $m_photo['user_id'] == 0) goto _return;

            if ($m_photo['album_id'] != $album_id) {
                $result = 0;
                $message = _('This photo does not belong to the album.');
                $data = null;
                goto _return;
            }
        }

        $m_album = Model('album')->column(['user_id', 'act'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
        if ($m_album == null) {
            $result = 0;
            $message = _('Album does not exist.');
            $data = null;
            goto _return;
        } else {
            if ($m_album['act'] == 'none') {
                $result = 0;
                $message = _('[Album] occur exception, please contact us.');
                $data = null;
                goto _return;
            }

            if ($m_album['act'] == 'delete') {
                $result = 0;
                $message = _('Album does not exist.');
                $data = null;
                goto _return;
            }

            list($result1, $message1) = array_decode_return(Model('user')->usable($m_album['user_id']));
            if ($result1 != 1) {
                $result = 0;
                $message = $message1;
                $data = null;
                goto _return;
            }
        }

        if ($m_photo['user_id'] != $user_id) {
            $m_cooperation = Model('cooperation')->column(['identity'])->where([[[['`type`', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])->fetch();
            if ($m_cooperation == null) {
                $result = 0;
                $message = _('You can not DIY this photo.');
                $data = null;
                goto _return;
            } else {
                switch ($m_cooperation['identity']) {
                    case 'admin':
                        break;

                    case 'approver':
                    case 'editor':
                    case 'viewer':
                        $result = 0;
                        $message = _('You can not DIY this photo.');
                        $data = null;
                        goto _return;
                        break;

                    default:
                        $result = 0;
                        $message = _('Unknown case of identity.');
                        $data = null;
                        goto _return;
                        break;
                }
            }
        }

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function insertAudio($album_id, $user_id, $audio_refer, $audio_target)
    {
        $m_photo = (new \photoModel)
            ->column(['image', 'sequence'])
            ->where([[[['album_id', '=', $album_id], ['photo.act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        switch ($audio_refer) {
            case 'embed':
            case 'system':
                break;

            case 'file':
                if (is_file($audio_target)) {
                    \Extension\aws\S3::upload($audio_target);
                }

                $audio_target = fileinfo($audio_target)['suburl'];
                break;

            default:
                throw new \Exception('Unknown case');
                break;
        }

        $sequence = empty($m_photo) ? 0 : max(array_column($m_photo, 'sequence')) + 1;
        if ($sequence > 255) $sequence = 255;

        $param = [
            'album_id' => $album_id,
            'user_id' => $user_id,
            //^暫時不會跑到這部分, 以後如果是單獨新增 audio, 這裡就要補'image'=>,
            'usefor' => 'image',
            'audio_refer' => $audio_refer,
            'audio_target' => $audio_target,
            'state' => 'success',
            'sequence' => $sequence,
            'inserttime' => inserttime(),
        ];

        $photo_id = Model('photo')->column(['photo_id'])->where([[[['state', '=', 'pretreat'], ['act', '=', 'open']], 'and']])->lock('for update')->fetchColumn();
        ($photo_id) ? Model('photo')->where([[[['photo_id', '=', $photo_id]], 'and']])->edit($param) : Model('photo')->add($param);

        (new albumModel)->refreshPhoto($album_id);

        return;
    }

    function insertPhoto($album_id, $user_id, $file)
    {
        $m_photo = (new photoModel())
            ->column(['image', 'sequence'])
            ->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        /**
         * 處理 photo
         */
        $Image = new \Core\Image;

        $Image->set($file)->setQuality(100)->setSize(\Config\Image::S6, \Config\Image::S6);

        switch ($Image->getType()) {
            case IMAGETYPE_GIF:
                $Image->setType('gif');
                break;

            default:
                $Image->setType('jpg');
                break;
        }

        $file = $Image->save(null, true, true, false);

        if (is_file($file)) {
            \Extension\aws\S3::upload($file);
        }

        $sequence = empty($m_photo) ? 0 : max(array_column($m_photo, 'sequence')) + 1;
        if ($sequence > 255) $sequence = 255;

        $param = [
            'album_id' => $album_id,
            'user_id' => $user_id,
            'image' => $suburl = fileinfo($file)['suburl'],
            'usefor' => 'image',
            'state' => 'success',
            'act' => 'open',
            'sequence' => $sequence,
            'inserttime' => inserttime(),
        ];

        $photo_id = (new photoModel())->column(['photo_id'])->where([[[['state', '=', 'pretreat'], ['act', '=', 'open']], 'and']])->lock('for update')->fetchColumn();
        ($photo_id) ? (new photoModel())->where([[[['photo_id', '=', $photo_id]], 'and']])->edit($param) : (new photoModel())->add($param);

        /**
         * 2017-02-14 Lion:
         *     insert preview + photo, 先前使用 (new albumModel)->refreshPhoto 會在 app 一次上傳多張時發生資料丟失
         *     雖然有  lock for update, 但可能執行 sql 的順序先後而產生該情況
         */
        $m_album = (new albumModel)->column(['preview', 'photo'])->where([[[['album_id', '=', $album_id]], 'and']])->lock('for update')->fetch();

        $a_preview = ($m_album['preview']) ? json_decode($m_album['preview'], true) : [];
        $a_photo = ($m_album['photo']) ? json_decode($m_album['photo'], true) : [];

        $a_preview[] = $suburl;
        $a_photo[] = $suburl;

        (new albumModel)
            ->where([[[['album_id', '=', $album_id]], 'and']])
            ->edit([
                'cover' => empty($a_photo) ? '' : $a_photo[0],
                'cover_hex' => (new \Core\Image)->set($file)->getMainHex(),
                'photo' => json_encode($a_photo),
                'preview' => json_encode($a_preview),
                'state' => 'process',
            ]);

        return;
    }

    function insertUsefor_User($photo_id, $user_id, $device_id)
    {
        $m_photousefor = Model('photousefor')
            ->column([
                'photousefor.photousefor_id',
                'photousefor.name',
                'photousefor.image',
                'photousefor.description',
                'photousefor_user.photousefor_user_id',
            ])
            ->join([['inner join', 'photousefor_user', 'using(photousefor_id)']])
            ->where([[[['photousefor.photo_id', '=', $photo_id], ['photousefor_user.user_id', '=', $user_id]], 'and']])
            ->fetch();

        if ($m_photousefor) {
            $photousefor_user_id = $m_photousefor['photousefor_user_id'];
        } else {
            $usefor = Model('photo')->column(['usefor'])->where([[[['photo_id', '=', $photo_id]], 'and']])->fetchColumn();

            switch ($usefor) {
                case 'exchange':
                    $m_photousefor = Model('photousefor')->column(['photousefor_id', '`name`', 'image', 'description', '`count`'])->where([[[['photo_id', '=', $photo_id], ['`count`', '>', 0]], 'and']])->lock('for update')->fetch();
                    break;

                case 'slot':
                    $m_photousefor = Model('photousefor')->column(['photousefor_id', '`name`', 'image', 'description', '`count`'])->where([[[['photo_id', '=', $photo_id], ['`count`', '>', 0]], 'and']])->lock('for update')->fetchAll();

                    $a_drawbox = [];
                    $count = 0;
                    foreach ($m_photousefor as $v0) {
                        $a_drawbox = array_pad($a_drawbox, $count += $v0['count'], $v0['photousefor_id']);
                    }
                    shuffle($a_drawbox);
                    $photousefor_id = $a_drawbox[mt_rand(0, $count - 1)];

                    $m_photousefor = $m_photousefor[array_search($photousefor_id, array_column($m_photousefor, 'photousefor_id'))];
                    break;

                default:
                    throw new Exception('Unknown case');
                    break;
            }

            Model('photousefor')->where([[[['photousefor_id', '=', $m_photousefor['photousefor_id']]], 'and']])->edit(['`count`' => $m_photousefor['count'] - 1]);

            $photousefor_user_id = Model('photousefor_user')->add([
                'photousefor_id' => $m_photousefor['photousefor_id'],
                'user_id' => $user_id,
                'device_id' => $device_id,
                '`count`' => $m_photousefor['count'],
                'calculate' => -1,
                'state' => 'pretreat',
                'inserttime' => inserttime(),
            ]);
        }

        return [
            'photousefor' => [
                'photousefor_id' => $m_photousefor['photousefor_id'],
                'name' => $m_photousefor['name'],
                'image' => $m_photousefor['image'],
                'description' => $m_photousefor['description'],
            ],
            'photousefor_user' => [
                'photousefor_user_id' => $photousefor_user_id,
            ],
        ];
    }

    function insertVideo($album_id, $user_id, $video_refer, $video_target = '')
    {
        $m_photo = (new photoModel)
            ->column(['image', 'sequence'])
            ->where([[[['album_id', '=', $album_id], ['photo.act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        switch ($video_refer) {
            case 'embed':
                $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.jpg';

                fetch_remote_thumbnail($video_target, $path, fetch_video_platform($video_target));

                if (exif_imagetype($path) !== IMAGETYPE_JPEG) {
                    (new \Core\Image)
                        ->set($path)
                        ->setType('jpg')
                        ->save(null, true, true);
                }

                \Extension\aws\S3::upload($path);

                $image = fileinfo($path)['suburl'];
                break;

            case 'file':
                $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.' . strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

                move_uploaded_file($_FILES['file']['tmp_name'], $path);

                \Extension\aws\S3::upload($path);

                $video = new \Core\Video();

                if ($video->setFile($path)->saveScreenShot(null, false, false)) {
                    \Extension\aws\S3::upload($video->getOutPath());

                    $image = fileinfo($video->getOutPath())['suburl'];
                }

                $video_target = fileinfo($path)['suburl'];
                break;
        }

        $sequence = empty($m_photo) ? 0 : max(array_column($m_photo, 'sequence')) + 1;
        if ($sequence > 255) $sequence = 255;

        $param = [
            'album_id' => $album_id,
            'user_id' => $user_id,
            'image' => isset($image) ? $image : '',
            'usefor' => 'video',
            'video_refer' => $video_refer,
            'video_target' => $video_target,
            'state' => 'success',
            'sequence' => $sequence,
            'inserttime' => inserttime(),
        ];

        $photo_id = (new \photoModel)->column(['photo_id'])->where([[[['state', '=', 'pretreat'], ['act', '=', 'open']], 'and']])->lock('for update')->fetchColumn();
        ($photo_id) ? (new \photoModel)->where([[[['photo_id', '=', $photo_id]], 'and']])->edit($param) : Model('photo')->add($param);

        /**
         * 2017-02-14 Lion:
         *     insert preview + photo, 先前使用 (new albumModel)->refreshPhoto 會在 app 一次上傳多張時發生資料丟失
         *     雖然有  lock for update, 但可能執行 sql 的順序先後而產生該情況
         */
        $m_album = (new albumModel)
            ->column(['preview', 'photo'])
            ->where([[[['album_id', '=', $album_id]], 'and']])
            ->lock('for update')
            ->fetch();

        $a_preview = ($m_album['preview']) ? json_decode($m_album['preview'], true) : [];
        $a_photo = ($m_album['photo']) ? json_decode($m_album['photo'], true) : [];

        $a_preview[] = $image;
        $a_photo[] = $image;

        (new albumModel)
            ->where([[[['album_id', '=', $album_id]], 'and']])
            ->edit([
                'cover' => empty($a_photo) ? '' : $a_photo[0],
                'photo' => json_encode($a_photo),
                'preview' => json_encode($a_preview),
                'state' => 'process',
            ]);
    }

    function sortPhoto($album_id, array $a_photo_id)
    {
        if ($a_photo_id) {
            $editByCase = [];
            $tmp0 = [];
            foreach ($a_photo_id as $k0 => $v0) {
                $tmp0['when'][] = ['photo_id', '=', $v0, $k0];
            }
            $tmp0['else'] = 'sequence';
            $editByCase['sequence'] = $tmp0;
            Model('photo')->where([[[['album_id', '=', $album_id]], 'and']])->editByCase($editByCase);

            Model('album')->refreshPhoto($album_id);
        }

        return;
    }

    function updateAudio($photo_id, $audio_refer, $audio_target)
    {
        $m_photo = (new \photoModel)
            ->column(['album_id', 'audio_refer', 'audio_target'])
            ->where([[[['photo_id', '=', $photo_id], ['act', '=', 'open']], 'and']])
            ->lock('for update')
            ->fetch();

        if ($m_photo['audio_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_photo['audio_target']]);

        switch ($audio_refer) {
            case 'none':
            case 'embed':
            case 'system':
                break;

            case 'file':
                if (is_file($audio_target)) {
                    \Extension\aws\S3::upload($audio_target);
                }

                $audio_target = fileinfo($audio_target)['suburl'];
                break;

            default:
                throw new \Exception('Unknown case');
                break;
        }

        (new \photoModel)
            ->where([[[['photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])
            ->edit([
                'audio_refer' => $audio_refer,
                'audio_target' => $audio_target,
            ]);

        (new \albumModel)
            ->where([[[['album_id', '=', $m_photo['album_id']]], 'and']])
            ->edit(['state' => 'process']);

        return;
    }

    function updatePhoto($photo_id, array $param)
    {
        $m_photo = (new photoModel())->column(['album_id', 'image'])->where([[[['photo_id', '=', $photo_id]], 'and']])->lock('for update')->fetch();

        $edit = [];

        if (isset($param['description'])) $edit['description'] = trim($param['description']);//2016-08-31 Lion: 要能夠 update 為空字串

        if (isset($param['hyperlink'])) $edit['hyperlink'] = json_encode($param['hyperlink']);

        if (isset($param['image'])) {
            $Image = new \Core\Image;

            $Image->set(fileinfo($param['image'])['path'])->setQuality(100)->setSize(\Config\Image::S6, \Config\Image::S6);

            switch ($Image->getType()) {
                case IMAGETYPE_GIF:
                    $Image->setType('gif');
                    break;

                default:
                    $Image->setType('jpg');
                    break;
            }

            $file = $Image->save(null, true, true, false);

            if (is_file($file)) {
                \Extension\aws\S3::upload($file);
            }

            \Core\File::delete([PATH_UPLOAD . $m_photo['image']]);//刪除原檔案(包含所有尺寸)

            $edit['image'] = fileinfo($file)['suburl'];

            //2016-12-08 Lion: update preview
            $preview = (new albumModel())->column(['preview'])->where([[[['album_id', '=', $m_photo['album_id']]], 'and']])->lock('for update')->fetchColumn();

            $a_preview = ($preview) ? json_decode($preview, true) : [];

            foreach ($a_preview as &$v0) {
                if ($v0 === fileinfo($m_photo['image'])['suburl']) $v0 = $edit['image'];
            }

            (new albumModel())->where([[[['album_id', '=', $m_photo['album_id']]], 'and']])->edit([
                'preview' => json_encode($a_preview),
            ]);
        }

        if (isset($param['location'])) $edit['location'] = trim($param['location']);

        (new photoModel())
            ->where([[[['photo_id', '=', $photo_id]], 'and']])
            ->edit($edit);

        if (isset($param['image'])) (new albumModel())->refreshPhoto($m_photo['album_id']);
    }

    function updateVideo($photo_id, $video_refer, $video_target = '')
    {
        $m_photo = (new \photoModel)
            ->column(['album_id', 'image', 'video_refer', 'video_target'])
            ->where([[[['photo_id', '=', $photo_id], ['act', '=', 'open']], 'and']])
            ->lock('for update')
            ->fetch();

        \Core\File::delete([PATH_UPLOAD . $m_photo['image']]);//刪除原檔案(包含所有尺寸)

        if ($m_photo['video_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_photo['video_target']]);

        switch ($video_refer) {
            case 'embed':
                $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.jpg';

                fetch_remote_thumbnail($video_target, $path, fetch_video_platform($video_target));

                if (exif_imagetype($path) !== IMAGETYPE_JPEG) {
                    (new \Core\Image)
                        ->set($path)
                        ->setType('jpg')
                        ->save(null, true, true);
                }

                \Extension\aws\S3::upload($path);

                $image = fileinfo($path)['suburl'];
                break;

            case 'file':
                $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.' . strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

                move_uploaded_file($_FILES['file']['tmp_name'], $path);

                \Extension\aws\S3::upload($path);

                $video = new \Core\Video();

                if ($video->setFile($path)->saveScreenShot(null, false, false)) {
                    \Extension\aws\S3::upload($video->getOutPath());

                    $image = fileinfo($video->getOutPath())['suburl'];
                }

                $video_target = fileinfo($path)['suburl'];
                break;
        }

        (new \photoModel)
            ->where([[[['photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])
            ->edit([
                'image' => isset($image) ? $image : '',
                'usefor' => 'video',
                'video_refer' => $video_refer,
                'video_target' => $video_target,
            ]);

        (new \albumModel)
            ->refreshPhoto($m_photo['album_id']);
    }

    static function usable($photo_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        $album_id = (new \photoModel)
            ->column(['album_id'])
            ->where([[[['photo_id', '=', $photo_id]], 'and']])
            ->fetchColumn();

        list ($result, $message) = array_decode_return((new \albumModel)->usable_v2($album_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function menuOfDiy($album_id, $user_id)
    {
        $column = [
            'photo_id',
            'image',
            'usefor',
        ];
        $this->column($column);

        $where = [
            [[['album_id', '=', $album_id]], 'and']
        ];
        $this->where($where);

        $order = [
            'sequence' => 'asc'
        ];
        $this->order($order);

        return $this;
    }
}