<?php

namespace Controller\v2_1;

class api extends \frontstageController
{
    static $result = \Lib\Result::SYSTEM_OK;
    static $message = null;
    static $data = null;

    static function __checkParamIsSet(array $param)
    {
        return (new \Controller\v2_0\api)->__checkParamIsSet($param);
    }

    static function __checkSign(array $param)
    {
        return (new \Controller\v2_0\api)->__checkSign($param);
    }

    static function __checkToken()
    {
        return (new \Controller\v2_0\api)->__checkToken();
    }

    function getalbumsponsorlist()
    {
        self::__checkParamIsSet(['album_id', 'limit', 'token', 'user_id']);
        self::__checkToken();

        $album_id = $_POST['album_id'];
        $limit = $_POST['limit'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \albumModel)->usable_v2($album_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $list = \exchangeModel::getAlbumSponsorList($album_id, $user_id, $limit);

        self::$data = [];

        if ($list) {
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
        }

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getcategoryarea()
    {
        self::__checkParamIsSet(['categoryarea_id', 'token', 'user_id']);
        self::__checkToken();

        $categoryarea_id = $_POST['categoryarea_id'];
        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return(\categoryareaModel::usable($categoryarea_id, $user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        self::$data = [
            'albumexplore' => [],
            'categoryarea' => [],
            'categoryarea_style' => [],
        ];

        $object_categoryarea = \categoryareaModel::getCategoryArea_v2($categoryarea_id);

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

        self::$data['categoryarea'] = [
            'name' => $object_2_categoryarea['name'],
        ];

        //
        $array_categoryarea_style = $object_categoryarea['categoryarea_style'];

        foreach ($array_categoryarea_style as &$v_0) {
            switch ($v_0['banner_type']) {
                case 'creative':
                    foreach ($v_0['banner_type_data'] as &$v_1) {
                        if (!empty($v_1['picture'])) {
                            $v_1['picture'] = path2url((new \Core\Image)->set($v_1['picture'])->setSize(160, 160)->save());
                        }
                    }
                    break;
            }

            if (!empty($v_0['image'])) {
                $v_0['image'] = path2url($v_0['image']);
            }
        }

        self::$data['categoryarea_style'] = $array_categoryarea_style;

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }

    function getthemearea()
    {
        self::__checkParamIsSet(['token', 'user_id']);
        self::__checkToken();

        $user_id = $_POST['user_id'];

        list (self::$result, self::$message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if (self::$result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        self::$data = [
            'albumexplore' => [],
            'categoryarea_style' => [],
            'themearea' => [],
        ];

        $object_themearea = \themearea\Model::getThemeArea_v2();

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
        $array_categoryarea_style = $object_themearea['categoryarea_style'];

        foreach ($array_categoryarea_style as &$v_0) {
            switch ($v_0['banner_type']) {
                case 'creative':
                    foreach ($v_0['banner_type_data'] as &$v_1) {
                        if (!empty($v_1['picture'])) {
                            $v_1['picture'] = path2url((new \Core\Image)->set($v_1['picture'])->setSize(160, 160)->save());
                        }
                    }
                    break;
            }

            if (!empty($v_0['image'])) {
                $v_0['image'] = path2url($v_0['image']);
            }
        }

        self::$data['categoryarea_style'] = $array_categoryarea_style;

        //
        self::$data['themearea'] = $object_themearea['themearea'];

        _return:
        json_encode_return(self::$result, self::$message, null, self::$data);
    }
}