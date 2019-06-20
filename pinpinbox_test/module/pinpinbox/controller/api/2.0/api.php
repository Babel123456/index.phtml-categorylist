<?php

namespace Controller\v2_0;

class api extends \frontstageController
{
    static $result = \Lib\Result::SYSTEM_OK;
    static $message = null;
    static $data = null;

    function __checkParamIsSet(array $param)
    {
        foreach ($param as $v0) {
            if (!isset($_POST[$v0])) {
                self::$result = \Lib\Result::SYSTEM_ERROR;

                switch (SITE_EVN) {
                    case 'production':
                        self::$message = 'Param error.';
                        break;

                    default:
                        self::$message = 'Param error. "' . $v0 . '" is required.';
                        break;
                }

                json_encode_return(self::$result, self::$message);
                break;
            }
        }
    }

    function __checkSign(array $param)
    {
        $tmp0 = [];

        foreach ($param as $v0) {
            $tmp0[$v0] = $_POST[$v0];
        }

        if ($_POST['sign'] != encrypt($tmp0)) {
            self::$result = \Lib\Result::SYSTEM_ERROR;
            self::$message = 'Sign error.';

            json_encode_return(self::$result, self::$message);
        }
    }

    function __checkToken()
    {
        $this->__checkParamIsSet(['token', 'user_id']);

        $token = $_POST['token'];
        $user_id = $_POST['user_id'];

        $m_token = (new \tokenModel)->column(['inserttime', 'token'])->where([[[['user_id', '=', $user_id]], 'and']])->fetch();

        if (empty($m_token)) {
            self::$result = \Lib\Result::TOKEN_ERROR;
            self::$message = _('請登入以繼續。');
            goto _return;
        } else {
            if ($m_token['token'] != $token) {
                self::$result = \Lib\Result::TOKEN_ERROR;
                self::$message = _('請登入以繼續。');
                goto _return;
            }
        }

        _return:

        if (self::$result != \Lib\Result::SYSTEM_OK) {
            json_encode_return(self::$result, self::$message);
        }
    }

    static function __resultTransform()
    {
        switch (self::$result) {
            case 0:
                self::$result = \Lib\Result::SYSTEM_ERROR;
                break;

            case 1:
                self::$result = \Lib\Result::SYSTEM_OK;
                break;
        }
    }

    function albumsettings()
    {
        $this->__checkParamIsSet(['album_id', 'settings', 'token', 'user_id']);
        $this->__checkToken();

        $album_id = $_POST['album_id'];
        $settings = json_decode($_POST['settings'], true);
        $user_id = $_POST['user_id'];

        list (self::$result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            self::$message = $message;
            goto _return;
        }

        list (self::$result, $message) = array_decode_return((new \albumModel)->settingsable_v2($album_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            self::$message = $message;
            goto _return;
        }

        $edit = [];

        //audio
        if (isset($settings['audio_mode'])) {
            switch ($settings['audio_mode']) {
                case 'none':
                    $edit['audio_mode'] = 'none';
                    break;

                case 'plural':
                    $edit['audio_mode'] = 'plural';
                    break;

                case 'singular':
                    $edit['audio_mode'] = 'singular';
                    $edit['audio_refer'] = 'system';
                    $edit['audio_target'] = $settings['audio'];
                    break;
            }
        }

        if (isset($settings['secondpaging'])) $edit['category_id'] = $settings['secondpaging'];
        if (isset($settings['title'])) $edit['name'] = $settings['title'];
        if (isset($settings['description'])) $edit['description'] = $settings['description'];
        if (!empty($settings['preview'])) $edit['preview'] = explode(',', str_replace(' ', '', $settings['preview']));
        if (isset($settings['location'])) $edit['location'] = $settings['location'];
        if (!empty($settings['weather'])) $edit['weather'] = $settings['weather'];
        if (!empty($settings['mood'])) $edit['mood'] = $settings['mood'];
        if (isset($settings['point'])) $edit['point'] = $settings['point'];
        if (!empty($settings['act'])) $edit['act'] = $settings['act'];

        (new \albumModel)->updateSettings($album_id, $edit);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function businesssubuserfastregister()
    {
        $this->__checkParamIsSet(['businessuser_id', 'facebook_id', 'param', 'sign', 'timestamp']);
        $this->__checkSign(['businessuser_id', 'facebook_id', 'timestamp']);

        $businessuser_id = $_POST['businessuser_id'];
        $facebook_id = $_POST['facebook_id'];
        $paramArray = json_decode($_POST['param'], true);
        $timestamp = $_POST['timestamp'];

        if (time() > $timestamp + 600) {
            self::$result = \Lib\Result::SYSTEM_ERROR;
            self::$message = 'Param error. The timestamp has been over 600 seconds.';
            goto _return;
        }

        $param = [
            'account' => isset($paramArray['account']) ? $paramArray['account'] : null,
            'birthday' => isset($paramArray['birthday']) ? $paramArray['birthday'] : null,
            'businessuser_id' => $businessuser_id,
            'coordinate' => isset($paramArray['coordinate']) ? $paramArray['coordinate'] : null,
            'name' => isset($paramArray['name']) ? $paramArray['name'] : null,
            'gender' => isset($paramArray['gender']) ? $paramArray['gender'] : null,
            'way' => 'facebook',
            'way_id' => $facebook_id,
        ];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->ableToRegister($param));

        if (self::$result == 1) {//2017-09-25 Lion: 相容
            self::$result = \Lib\Result::SYSTEM_OK;
        } else {
            $user_facebookModel = (new \user_facebookModel())
                ->column([
                    'token.user_id',
                    'token.token',
                ])
                ->join([
                    ['LEFT JOIN', 'token', 'ON token.user_id = user_facebook.user_id']
                ])
                ->where([[[['user_facebook.facebook_id', '=', $facebook_id]], 'and']])
                ->fetch();

            if ($user_facebookModel) {
                self::$result = \Lib\Result::USER_EXISTS;
                self::$data = [
                    'token' => [
                        'token' => $user_facebookModel['token'],
                        'user_id' => $user_facebookModel['user_id'],
                    ]
                ];
            }

            goto _return;
        }

        (new \Model)->beginTransaction();

        $return = (new \userModel)->register_v2($param);

        (new \Model)->commit();

        self::$data = [
            'token' => [
                'token' => $return['token'],
                'user_id' => $return['id'],
            ]
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function buyalbum()
    {
        $this->__checkParamIsSet(['album_id', 'platform', 'point', 'token', 'user_id']);
        $this->__checkToken();

        list (self::$result, self::$message) = array_decode_return((new \albumModel)->ableToExchange($_POST));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $reward = isset($_POST['reward']) ? json_decode($_POST['reward'], true) : [];

        (new \Model)->beginTransaction();

        $data = (new \albumModel)->exchange($_POST['album_id'], $_POST, $reward);

        (new \Model)->commit();

        $cover = PATH_UPLOAD . (new \albumModel)
                ->column(['cover'])
                ->where([[[
                    ['album_id', '=', $_POST['album_id']],
                ], 'and']])
                ->fetchColumn();

        self::$data = [
            'coverurl' => is_image($cover) ? fileinfo((new \Core\Image)->set($cover)->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
            'download_id' => $data['download_id'],
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function deletealbumindex()
    {
        $this->__checkParamIsSet(['album_id', 'index', 'token', 'user_id']);
        $this->__checkToken();

        list (self::$result, self::$message) = array_decode_return((new \albumindexModel)->ableToDelete($_POST));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        (new \albumindexModel)->deleteAlbumIndex($_POST);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function exchangephotousefor()
    {
        $this->__checkParamIsSet(['identifier', 'photo_id', 'token', 'user_id']);
        $this->__checkToken();

        $identifier = $_POST['identifier'];
        $photo_id = $_POST['photo_id'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\photouseforModel::ableToExchangePhotoUsefor($photo_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            switch (self::$result) {
                case \Lib\Result::PHOTOUSEFOR_USER_HAS_EXCHANGED:
                case \Lib\Result::PHOTOUSEFOR_USER_HAS_GAINED:
                    $photousefor_user_id = (new \photousefor_userModel)
                        ->column(['photousefor_user.photousefor_user_id'])
                        ->join([
                            ['INNER JOIN', 'photousefor', 'ON photousefor.photousefor_id = photousefor_user.photousefor_id AND photousefor.photo_id = ' . (new \photouseforModel)->quote($photo_id)]
                        ])
                        ->where([[[['photousefor_user.user_id', '=', $user_id]], 'and']])
                        ->fetchColumn();

                    self::$data = [
                        'photousefor_user' => [
                            'photousefor_user_id' => $photousefor_user_id,
                        ]
                    ];
                    break;
            }

            goto _return;
        }

        $device_id = (new \deviceModel)->getDevice_id($user_id, $identifier);

        (new \Model)->beginTransaction();

        $photousefor_user_id = \photouseforModel::exchangePhotoUsefor($photo_id, $user_id, $device_id);

        (new \Model)->commit();

        self::$data = [
            'photousefor_user' => [
                'photousefor_user_id' => $photousefor_user_id,
            ]
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function gainphotousefor_user()
    {
        $this->__checkParamIsSet(['param', 'photousefor_user_id', 'token', 'user_id']);
        $this->__checkToken();

        $user_id = $_POST['user_id'];
        $obj_param = json_decode($_POST['param'], true);
        $photousefor_user_id = $_POST['photousefor_user_id'];

        list (self::$result, self::$message) = array_decode_return(\photousefor_userModel::ableToGain($photousefor_user_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        \photousefor_userModel::gain($photousefor_user_id, $user_id, $obj_param);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function getalbum2likeslist()
    {
        $this->__checkParamIsSet(['album_id', 'limit', 'token', 'user_id']);
        $this->__checkToken();

        $album_id = $_POST['album_id'];
        $limit = $_POST['limit'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $list = \album2likesModel::getAlbum2LikesList($user_id, $album_id, $limit);

        self::$data = [];

        $Image = new \Core\Image;

        foreach ($list as $v_0) {
            $user = $v_0['user'];

            self::$data[] = [
                'user' => [
                    'discuss' => $user['discuss'],
                    'is_follow' => $user['is_follow'],
                    'name' => $user['name'],
                    'picture' => $user['picture'] === null ? null : path2url($Image->set($user['picture'])->setSize(160, 160)->save()),
                    'user_id' => $user['user_id'],
                ],
            ];
        }

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getalbumsettings()
    {
        $this->__checkParamIsSet(['album_id', 'token', 'user_id']);
        $this->__checkToken();

        $album_id = $_POST['album_id'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \albumModel)->settingsable_v2($album_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $m_album = (new \albumModel)
            ->column([
                'album.display_num_of_collect',
                'album.user_id',
                'album.name',
                'album.description',
                'album.audio_mode',
                'album.audio_refer',
                'album.audio_target',
                'album.location',
                'album.weather',
                'album.mood',
                'album.point',
                'album.reward_after_collect',
                'album.reward_description',
                'album.act',
                'categoryarea_category.categoryarea_id',
                'categoryarea_category.category_id',
            ])
            ->join([
                ['left join', 'categoryarea_category', 'using(category_id)']
            ])
            ->where([
                [[['album.album_id', '=', $album_id]], 'and'],
            ])
            ->fetch();

        if ($m_album['audio_mode'] == 'singular') {
            if ($m_album['audio_refer'] == 'file') {
                $audio_target = URL_UPLOAD . $m_album['audio_target'];
            } else {
                $audio_target = $m_album['audio_target'];
            }
        } else {
            $audio_target = null;
        }

        self::$data = [
            'act' => $m_album['act'],
            'albumindex' => array_column((new \albumindexModel)->column(['`index`'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchAll(), 'index'),
            'audio_mode' => $m_album['audio_mode'],
            'audio_refer' => $m_album['audio_refer'],
            'audio_target' => $audio_target,
            'category_id' => $m_album['category_id'],
            'categoryarea_id' => $m_album['categoryarea_id'],
            'description' => strip_tags($m_album['description']),
            'display_num_of_collect' => (bool)$m_album['display_num_of_collect'],
            'location' => $m_album['location'],
            'mood' => $m_album['mood'],
            'name' => $m_album['name'],
            'point' => $m_album['point'],
            'reward_after_collect' => (bool)$m_album['reward_after_collect'],
            'reward_description' => $m_album['reward_description'],
            'weather' => $m_album['weather'],
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getbookmarklist()
    {
        $this->__checkParamIsSet(['token', 'user_id']);
        $this->__checkToken();

        $user_id = $_POST['user_id'];

        self::$data = \Model\bookmark::getBookmarkList($user_id);

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getcategoryarea()
    {
        $this->__checkParamIsSet(['categoryarea_id', 'token', 'user_id']);
        $this->__checkToken();

        $categoryarea_id = $_POST['categoryarea_id'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\categoryareaModel::usable($categoryarea_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        self::$data = [
            'albumexplore' => [],
            'categoryarea' => [
                'categoryarea' => [],
                'user' => []
            ],
        ];

        $object_categoryarea = \categoryareaModel::getCategoryArea($categoryarea_id);

        $array_picture = [];
        $Image = new \Core\Image;

        //
        $array_albumexplore = $object_categoryarea['albumexplore'];

        foreach ($array_albumexplore as $v_1) {
            //
            $array_album = [];

            if (isset($v_1['album'])) {
                foreach ($v_1['album'] as $v_2) {
                    $array_2_album = $v_2['album'];
                    $array_2_user = $v_2['user'];

                    //album - cover
                    $cover = null;
                    $cover_height = null;
                    $cover_width = null;

                    if (is_image(PATH_UPLOAD . $array_2_album['cover'])) {
                        $Image->set(PATH_UPLOAD . $array_2_album['cover']);

                        $cover = fileinfo($Image->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'];
                        $cover_height = $Image->getHeightTarget();
                        $cover_width = $Image->getWidthTarget();
                    }

                    //user - picture
                    if (!array_key_exists($array_2_user['user_id'], $array_picture)) {
                        $picture = PATH_STORAGE . \userModel::getPicture($array_2_user['user_id']);
                        $array_picture[$array_2_user['user_id']] = is_image($picture) ? fileinfo($Image->set($picture)->setSize(160, 160)->save())['url'] : null;
                    }

                    $array_album[] = [
                        'album' => [
                            'album_id' => $array_2_album['album_id'],
                            'cover' => $cover,
                            'cover_height' => $cover_height,
                            'cover_hex' => $array_2_album['cover_hex'],
                            'cover_width' => $cover_width,
                            'name' => $array_2_album['name'],
                            'usefor' => \albumModel::getUseForInfo($array_2_album['album_id']),
                        ],
                        'user' => [
                            'name' => $array_2_user['name'],
                            'picture' => $array_picture[$array_2_user['user_id']],
                            'user_id' => $array_2_user['user_id'],
                        ],
                    ];
                }
            }

            //
            $array_0_albumexplore = [];

            if (isset($v_1['albumexplore'])) {
                $array_0_albumexplore = $v_1['albumexplore'];
            }

            //
            self::$data['albumexplore'][] = [
                'album' => $array_album,
                'albumexplore' => $array_0_albumexplore,
            ];
        }

        //
        $object_2_categoryarea = $object_categoryarea['categoryarea'];

        self::$data['categoryarea']['categoryarea'] = [
            'name' => $object_2_categoryarea['categoryarea']['name'],
        ];

        foreach ($object_2_categoryarea['user'] as $v_0) {
            self::$data['categoryarea']['user'][] = [
                'name' => $v_0['name'],
                'picture' => $v_0['picture'],
                'user_id' => $v_0['user_id'],
            ];
        }

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function geteventvotelist()
    {
        $this->__checkParamIsSet(['event_id', 'limit', 'token', 'user_id']);
        $this->__checkToken();

        $event_id = $_POST['event_id'];
        $limit = $_POST['limit'];
        $user_id = $_POST['user_id'];

        //非必填
        $searchkey = (isset($_POST['searchkey']) && trim($_POST['searchkey']) !== '') ? $_POST['searchkey'] : null;

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        list (self::$result, self::$message) = array_decode_return((new \eventModel)->usable_v2($event_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        //eventjoin
        $eventjoinModel = \eventjoinModel::getEventJoinList($event_id, $user_id, null, $limit, $searchkey);

        $eventjoinArray = [];
        $Image = new \Core\Image;
        $pictureArray = [];

        foreach ($eventjoinModel as $v_0) {
            $albumArray = $v_0['album'];
            $eventjoinArray_1 = $v_0['eventjoin'];
            $userArray = $v_0['user'];

            //album - cover
            $cover = null;
            $cover_height = null;
            $cover_width = null;

            if (is_image(PATH_UPLOAD . $albumArray['cover'])) {
                $Image->set(PATH_UPLOAD . $albumArray['cover']);

                $cover = fileinfo($Image->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'];
                $cover_height = $Image->getHeightTarget();
                $cover_width = $Image->getWidthTarget();
            }

            //user - picture
            if (!array_key_exists($userArray['user_id'], $pictureArray)) {
                $picture = PATH_STORAGE . \Core::get_userpicture($userArray['user_id']);
                $pictureArray[$userArray['user_id']] = is_image($picture) ? fileinfo($Image->set($picture)->setSize(160, 160)->save())['url'] : null;
            }

            $eventjoinArray[] = [
                'album' => [
                    'album_id' => $albumArray['album_id'],
                    'cover' => $cover,
                    'cover_height' => $cover_height,
                    'cover_hex' => $albumArray['cover_hex'],
                    'cover_width' => $cover_width,
                    'has_voted' => \eventModel::hasVoted($event_id, $albumArray['album_id'], $user_id),
                    'name' => $albumArray['name'],
                    'usefor' => \albumModel::getUseForInfo($albumArray['album_id']),
                ],
                'eventjoin' => [
                    'count' => $eventjoinArray_1['count'],
                ],
                'user' => [
                    'name' => $userArray['name'],
                    'picture' => $pictureArray[$userArray['user_id']],
                    'user_id' => $userArray['user_id'],
                ],
            ];
        }

        self::$data = [
            'event' => [
                'vote_left' => \eventModel::getVoteLeft($event_id, $user_id),
            ],
            'eventjoin' => $eventjoinArray,
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getfollowfromlist()
    {
        $this->__checkParamIsSet(['limit', 'token', 'user_id']);
        $this->__checkToken();

        $limit = $_POST['limit'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\followfromModel::ableToGetFollowFromList($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $list = \followfromModel::getFollowFromList($user_id, $limit);

        self::$data = [];

        $Image = new \Core\Image;

        foreach ($list as $v_0) {
            $user = $v_0['user'];

            self::$data[] = [
                'user' => [
                    'discuss' => $user['discuss'],
                    'is_follow' => $user['is_follow'],
                    'name' => $user['name'],
                    'picture' => $user['picture'] === null ? null : path2url($Image->set($user['picture'])->setSize(160, 160)->save()),
                    'user_id' => $user['user_id'],
                ],
            ];
        }

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function gethotlist()
    {
        $this->__checkParamIsSet(['limit', 'token', 'user_id']);
        $this->__checkToken();

        $limit = $_POST['limit'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $list = \userModel::getHotList($limit);

        self::$data = [];

        foreach ($list as $v_0) {
            $follow = $v_0['follow'];
            $user = $v_0['user'];

            $picture = \userModel::getPicture($user['user_id']);

            self::$data[] = [
                'follow' => [
                    'count_from' => $follow['count_from'],
                    'follow' => \Core::get_follow($user_id, $user['user_id']),
                ],
                'user' => [
                    'cover' => path2url(PATH_STORAGE . \Core::get_usercover($user['user_id'])),
                    'creative_name' => $user['creative_name'],
                    'description' => strip_tags($user['description']),
                    'inserttime' => date('Y-m-d', strtotime($user['inserttime'])),
                    'name' => $user['name'],
                    'picture' => is_image(PATH_STORAGE . $picture) ? path2url((new \Core\Image)->set(PATH_STORAGE . $picture)->setSize(\Config\Image::S3, \Config\Image::S3)->save()) : null,
                    'user_id' => $user['user_id'],
                ],
            ];
        }

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getnewjoinlist()
    {
        $this->__checkParamIsSet(['limit', 'token', 'user_id']);
        $this->__checkToken();

        $limit = $_POST['limit'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $list = \userModel::getNewJoinList($limit);

        self::$data = [];

        foreach ($list as $v_0) {
            $follow = $v_0['follow'];
            $user = $v_0['user'];

            $picture = \userModel::getPicture($user['user_id']);

            self::$data[] = [
                'follow' => [
                    'count_from' => $follow['count_from'],
                    'follow' => \Core::get_follow($user_id, $user['user_id']),
                ],
                'user' => [
                    'cover' => path2url(PATH_STORAGE . \Core::get_usercover($user['user_id'])),
                    'creative_name' => $user['creative_name'],
                    'description' => strip_tags($user['description']),
                    'inserttime' => date('Y-m-d', strtotime($user['inserttime'])),
                    'name' => $user['name'],
                    'picture' => is_image(PATH_STORAGE . $picture) ? path2url((new \Core\Image)->set(PATH_STORAGE . $picture)->setSize(\Config\Image::S3, \Config\Image::S3)->save()) : null,
                    'user_id' => $user['user_id'],
                ],
            ];
        }

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getphotousefor()
    {
        $this->__checkParamIsSet(['photo_id', 'token', 'user_id']);
        $this->__checkToken();

        $photo_id = $_POST['photo_id'];
        $user_id = $_POST['user_id'];

        $Model_photousefor = \photouseforModel::getPhotoUsefor($photo_id);

        $object_photo = $Model_photousefor['photo'];

        if (\photousefor_userModel::has_gained($photo_id, $user_id)) {
            self::$result = \Lib\Result::PHOTOUSEFOR_USER_HAS_GAINED;
            self::$message = _('您已經領取了。');
        } elseif ($object_photo['usefor'] === 'exchange' && \photousefor_userModel::has_exchanged($photo_id, $user_id)) {
            self::$result = \Lib\Result::PHOTOUSEFOR_USER_HAS_EXCHANGED;
            self::$message = _('您已經兌換了。');
        } elseif ($object_photo['usefor'] === 'slot' && \photousefor_userModel::has_slotted($photo_id, $user_id)) {
            self::$result = \Lib\Result::PHOTOUSEFOR_USER_HAS_SLOTTED;
            self::$message = _('您已經抽獎了。');
        } elseif (\photouseforModel::has_expired($photo_id)) {
            self::$result = \Lib\Result::PHOTOUSEFOR_HAS_EXPIRED;
            self::$message = _('已過期。');
        } elseif (\photouseforModel::notYetStarted($photo_id)) {
            self::$result = \Lib\Result::PHOTOUSEFOR_NOT_YET_STARTED;
            self::$message = _('尚未開始。');
        } elseif (\photouseforModel::has_sent_finished($photo_id)) {
            self::$result = \Lib\Result::PHOTOUSEFOR_HAS_SENT_FINISHED;
            self::$message = _('已發送完畢。');
        }

        self::$data = [
            'bookmark' => [
                'is_existing' => \Model\bookmark::is_existing($user_id, $photo_id),
            ],
            'photousefor' => $Model_photousefor['photousefor'],
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getsponsorlist()
    {
        $this->__checkParamIsSet(['limit', 'token', 'user_id']);
        $this->__checkToken();

        $limit = $_POST['limit'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $list = \exchangeModel::getSponsorList($user_id, $limit);

        self::$data = [];

        $Image = new \Core\Image;

        foreach ($list as $v_0) {
            $user = $v_0['user'];

            self::$data[] = [
                'user' => [
                    'discuss' => $user['discuss'],
                    'is_follow' => $user['is_follow'],
                    'name' => $user['name'],
                    'picture' => $user['picture'] === null ? null : path2url($Image->set($user['picture'])->setSize(160, 160)->save()),
                    'point' => $user['point'],
                    'user_id' => $user['user_id'],
                ],
            ];
        }

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getthemearea()
    {
        $this->__checkParamIsSet(['token', 'user_id']);
        $this->__checkToken();

        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        self::$data = [
            'albumexplore' => [],
            'themearea' => [],
        ];

        $object_themearea = \themearea\Model::getThemeArea();

        $array_picture = [];
        $Image = new \Core\Image;

        //
        $array_albumexplore = $object_themearea['albumexplore'];

        foreach ($array_albumexplore as $v_0) {
            //
            $array_album = [];

            if (isset($v_0['album'])) {
                foreach ($v_0['album'] as $v_1) {
                    $array_2_album = $v_1['album'];
                    $array_2_user = $v_1['user'];

                    //album - cover
                    $cover = null;
                    $cover_height = null;
                    $cover_width = null;

                    if (is_image(PATH_UPLOAD . $array_2_album['cover'])) {
                        $Image->set(PATH_UPLOAD . $array_2_album['cover']);

                        $cover = fileinfo($Image->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'];
                        $cover_height = $Image->getHeightTarget();
                        $cover_width = $Image->getWidthTarget();
                    }

                    //user - picture
                    if (!array_key_exists($array_2_user['user_id'], $array_picture)) {
                        $picture = PATH_STORAGE . \userModel::getPicture($array_2_user['user_id']);
                        $array_picture[$array_2_user['user_id']] = is_image($picture) ? fileinfo($Image->set($picture)->setSize(160, 160)->save())['url'] : null;
                    }

                    $array_album[] = [
                        'album' => [
                            'album_id' => $array_2_album['album_id'],
                            'cover' => $cover,
                            'cover_height' => $cover_height,
                            'cover_hex' => $array_2_album['cover_hex'],
                            'cover_width' => $cover_width,
                            'name' => $array_2_album['name'],
                            'usefor' => \albumModel::getUseForInfo($array_2_album['album_id']),
                        ],
                        'user' => [
                            'name' => $array_2_user['name'],
                            'picture' => $array_picture[$array_2_user['user_id']],
                            'user_id' => $array_2_user['user_id'],
                        ],
                    ];
                }
            }

            //
            $array_0_albumexplore = [];

            if (isset($v_0['albumexplore'])) {
                $array_0_albumexplore = $v_0['albumexplore'];
            }

            self::$data['albumexplore'][] = [
                'album' => $array_album,
                'albumexplore' => $array_0_albumexplore,
            ];
        }

        //
        self::$data['themearea'] = $object_themearea['themearea'];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function insertalbumindex()
    {
        $this->__checkParamIsSet(['album_id', 'index', 'token', 'user_id']);
        $this->__checkToken();

        list (self::$result, self::$message) = array_decode_return((new \albumindexModel)->ableToInsert($_POST));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        (new \albumindexModel)->insertAlbumIndex($_POST);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function insertbookmark()
    {
        $this->__checkParamIsSet(['photo_id', 'token', 'user_id']);
        $this->__checkToken();

        $photo_id = $_POST['photo_id'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\Model\bookmark::ableToInsertBookmark($user_id, $photo_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        \Model\bookmark::insertBookmark($user_id, $photo_id);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function insertvideoofdiy()
    {
        $this->__checkParamIsSet(['album_id', 'token', 'user_id', 'video_refer', 'video_target']);
        $this->__checkToken();

        $album_id = $_POST['album_id'];
        $user_id = $_POST['user_id'];
        $video_refer = $_POST['video_refer'];
        $video_target = $_POST['video_target'];

        list (self::$result, self::$message) = array_decode_return((new \photoModel)->ableToInsertVideo($album_id, $user_id, $video_refer));

        self::__resultTransform();

        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        (new \Model)->beginTransaction();

        (new \photoModel)->insertVideo($album_id, $user_id, $video_refer, $video_target);

        (new \Model)->commit();

        self::$data = \albumModel::getDataOfDiyForApp($album_id);

        _return:

        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function refreshtoken()
    {
        $this->__checkParamIsSet(['sign', 'user_id']);
        $this->__checkSign(['user_id']);

        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $token = (new \tokenModel)->refreshToken($user_id);

        _return:
        json_encode_return(self::$result, self::$message, null, ['token' => ['token' => $token]]);
    }

    function requestsmspwdforupdatecellphone()
    {
        $this->__checkParamIsSet(['cellphone', 'token', 'user_id']);
        $this->__checkToken();

        $cellphone = $_POST['cellphone'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\smspasswordModel::ableToRequestSMSPasswordForUpdateCellphone($user_id, $cellphone));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        \smspasswordModel::requestSMSPasswordForUpdateCellphone($user_id, $cellphone);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function setusercover()
    {
        $this->__checkParamIsSet(['token', 'user_id']);
        $this->__checkToken();

        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\userModel::ableToSetUserCover($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        \userModel::setUserCover($user_id);

        self::$data = [
            'user' => [
                'cover' => \userModel::getUserCoverUrl($user_id),
            ],
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function slotphotousefor()
    {
        $this->__checkParamIsSet(['identifier', 'photo_id', 'token', 'user_id']);
        $this->__checkToken();

        $identifier = $_POST['identifier'];
        $photo_id = $_POST['photo_id'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\photouseforModel::ableToSlotPhotoUsefor($photo_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            switch (self::$result) {
                case \Lib\Result::PHOTOUSEFOR_USER_HAS_GAINED:
                case \Lib\Result::PHOTOUSEFOR_USER_HAS_SLOTTED:
                    $photousefor_user_id = (new \photousefor_userModel)
                        ->column(['photousefor_user.photousefor_user_id'])
                        ->join([
                            ['INNER JOIN', 'photousefor', 'ON photousefor.photousefor_id = photousefor_user.photousefor_id AND photousefor.photo_id = ' . (new \photouseforModel)->quote($photo_id)]
                        ])
                        ->where([[[['photousefor_user.user_id', '=', $user_id]], 'and']])
                        ->fetchColumn();

                    self::$data = [
                        'bookmark' => [
                            'is_existing' => \Model\bookmark::is_existing($user_id, $photo_id),
                        ],
                        'photousefor' => \photouseforModel::getPhotoUseforByUserId($photo_id, $user_id)['photousefor'],
                        'photousefor_user' => [
                            'photousefor_user_id' => $photousefor_user_id,
                        ]
                    ];
                    break;
            }

            goto _return;
        }

        $device_id = (new \deviceModel)->getDevice_id($user_id, $identifier);

        (new \Model)->beginTransaction();

        $photousefor_user_id = \photouseforModel::slotPhotoUsefor($photo_id, $user_id, $device_id);

        (new \Model)->commit();

        self::$data = [
            'bookmark' => [
                'is_existing' => \Model\bookmark::is_existing($user_id, $photo_id),
            ],
            'photousefor' => \photouseforModel::getPhotoUseforByUserId($photo_id, $user_id)['photousefor'],
            'photousefor_user' => [
                'photousefor_user_id' => $photousefor_user_id,
            ]
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function updatealbumsettings()
    {
        $this->__checkParamIsSet(['album_id', 'settings', 'token', 'user_id']);
        $this->__checkToken();

        $album_id = $_POST['album_id'];
        $settings = json_decode($_POST['settings'], true);
        $user_id = $_POST['user_id'];

        list (self::$result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            self::$message = $message;
            goto _return;
        }

        list (self::$result, $message) = array_decode_return((new \albumModel)->settingsable_v2($album_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            self::$message = $message;
            goto _return;
        }

        $edit = [];

        //audio
        if (isset($settings['audio_mode'])) {
            $edit['audio_mode'] = $settings['audio_mode'];
            $edit['audio_refer'] = isset($settings['audio_refer']) ? $settings['audio_refer'] : 'none';

            if ($edit['audio_mode'] == 'singular') {
                if ($edit['audio_refer'] == 'file') {
                    if (empty($_FILES['file'])) {
                        self::$result = \Lib\Result::SYSTEM_ERROR;
                        self::$message = 'Input name must be [file].';

                        goto _return;
                    } else {
                        if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                            $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.' . strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

                            if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
                                $Audio = new \Core\Audio();

                                $Audio->setFile($path);

                                if ($Audio->getType() !== 'mp3') {
                                    $path = $Audio->setType('mp3')->save(null, true, true);
                                }

                                $edit['audio_target'] = $path;
                            } else {
                                self::$result = \Lib\Result::SYSTEM_ERROR;
                                self::$message = _('Upload failed, please try again.');

                                goto _return;
                            }
                        } else {
                            self::$result = \Lib\Result::SYSTEM_ERROR;
                            self::$message = \Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']];

                            goto _return;
                        }
                    }
                } else {
                    $edit['audio_target'] = $settings['audio_target'];
                }
            }
        }

        if (isset($settings['category_id'])) $edit['category_id'] = $settings['category_id'];
        if (isset($settings['display_num_of_collect'])) $edit['display_num_of_collect'] = (bool)$settings['display_num_of_collect'];
        if (isset($settings['name'])) $edit['name'] = $settings['name'];
        if (isset($settings['description'])) $edit['description'] = $settings['description'];
        if (!empty($settings['preview'])) $edit['preview'] = explode(',', str_replace(' ', '', $settings['preview']));
        if (isset($settings['location'])) $edit['location'] = $settings['location'];
        if (!empty($settings['weather'])) $edit['weather'] = $settings['weather'];
        if (!empty($settings['mood'])) $edit['mood'] = $settings['mood'];
        if (isset($settings['point'])) $edit['point'] = $settings['point'];
        if (isset($settings['reward_after_collect'])) $edit['reward_after_collect'] = (bool)$settings['reward_after_collect'];
        if (isset($settings['reward_description'])) $edit['reward_description'] = $settings['reward_description'];
        if (!empty($settings['act'])) $edit['act'] = $settings['act'];

        (new \albumModel)->updateSettings($album_id, $edit);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function updatephotousefor_user()
    {
        $this->__checkParamIsSet(['param', 'photousefor_user_id', 'token', 'user_id']);
        $this->__checkToken();

        $user_id = $_POST['user_id'];
        $obj_param = json_decode($_POST['param'], true);
        $photousefor_user_id = $_POST['photousefor_user_id'];

        list (self::$result, self::$message) = array_decode_return(\photousefor_userModel::ableToUpdateUsefor_User($photousefor_user_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        \photousefor_userModel::updateUsefor_User($photousefor_user_id, $user_id, $obj_param);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function updatevideoofdiy()
    {
        $this->__checkParamIsSet(['album_id', 'photo_id', 'token', 'user_id', 'video_refer', 'video_target']);
        $this->__checkToken();

        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];
        $user_id = $_POST['user_id'];
        $video_refer = $_POST['video_refer'];
        $video_target = $_POST['video_target'];

        list (self::$result, self::$message) = array_decode_return((new \photoModel)->ableToUpdateVideo($photo_id, $user_id, $video_refer));

        self::__resultTransform();

        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        (new \Model)->beginTransaction();

        (new \photoModel)->updateVideo($photo_id, $video_refer, $video_target);

        (new \Model)->commit();

        self::$data = \albumModel::getDataOfDiyForApp($album_id);

        _return:

        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function updatecellphone()
    {
        $this->__checkParamIsSet(['cellphone', 'smspassword', 'token', 'user_id']);
        $this->__checkToken();

        $cellphone = $_POST['cellphone'];
        $smspassword = $_POST['smspassword'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\userModel::ableToUpdateCellphone($user_id, $cellphone, $smspassword));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        (new \Model)->beginTransaction();

        \userModel::updateCellphone($user_id, $cellphone);

        (new \Model)->commit();

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function updateuser()
    {
        $this->__checkParamIsSet(['token', 'user_id', 'param']);
        $this->__checkToken();

        $user_id = $_POST['user_id'];
        $param = json_decode($_POST['param'], true);

        //2017-09-18 Lion: 相容
        if (isset($param['birthday'])) {
            $param['birthday'] = date('Y-m-d', strtotime($param['birthday']));
        }

        if (isset($param['gender'])) {
            switch ($param['gender']) {
                case 0:
                    $param['gender'] = 'female';
                    break;

                case 1:
                    $param['gender'] = 'male';
                    break;

                case 2:
                    $param['gender'] = 'none';
                    break;
            }
        }

        if (isset($param['sociallink'])) {
            $array_0 = [];

            foreach (\Schema\user::$sociallink as $v_0) {
                $array_0[$v_0] = isset($param['sociallink'][$v_0]) ? $param['sociallink'][$v_0] : null;
            }

            $param['sociallink'] = json_encode($array_0);
        }

        list (self::$result, self::$message) = array_decode_return((new \userModel)->ableToUpdate($user_id, $param));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        (new \userModel())->updateUser($user_id, $param);

        (new \userModel)->setSession($user_id);

        _return:
        json_encode_return(self::$result, self::$message);
    }

    function vote()
    {
        $this->__checkParamIsSet(['album_id', 'event_id', 'token', 'user_id']);
        $this->__checkToken();

        $album_id = $_POST['album_id'];
        $event_id = $_POST['event_id'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\eventvoteModel::ableToVote($event_id, $album_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        (new \Model)->beginTransaction();

        \eventvoteModel::vote($event_id, $album_id, $user_id);

        (new \Model)->commit();

        self::$data = [
            'event' => [
                'vote_left' => \eventModel::getVoteLeft($event_id, $user_id),
            ],
        ];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }
}