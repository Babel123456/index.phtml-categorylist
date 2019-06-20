<?php

namespace themearea;

class Model extends \Model
{
    protected $database = 'site';
    protected $table = 'businessuser';
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

    static function getThemeArea()
    {
        $return = [
            'albumexplore' => [],
            'themearea' => [],
        ];

        //
        $return['themearea'] = [
            'colorhex' => '#36ACC1',
            'name' => 'P好康',
        ];

        //
        $albumexploreModel = (new \albumexploreModel)
            ->column([
                'albumexplore_id',
                'basis',
                'basis_id',
                'categoryarea2explore_id',
                'exhibit',
                'name',
                'url',
            ])
            ->where([[[['categoryarea2explore_id', '=', 0], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        if ($albumexploreModel) {
            $array_albumexplore = [];

            foreach ($albumexploreModel as $v_0) {
                $array_exhibit = [];

                switch ($v_0['basis']) {
                    case 'category':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCategory($v_0['basis_id']), 'album_id');
                        break;

                    case 'categoryarea':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCategoryarea($v_0['basis_id']), 'album_id');
                        break;

                    case 'creative':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCreative($v_0['categoryarea2explore_id'], $v_0['basis_id']), 'album_id');
                        break;

                    case 'manual':
                        $array_exhibit = json_decode($v_0['exhibit'], true);
                        break;
                }

                $array_albumexplore[$v_0['albumexplore_id']] = [
                    'exhibit' => $array_exhibit,
                    'name' => $v_0['name'],
                    'url' => empty($v_0['url']) ? null : $v_0['url'],
                ];
            }

            $array_album_id = [];

            $array_exhibit = array_column($array_albumexplore, 'exhibit');

            foreach ($array_exhibit as $v_0) {
                $array_album_id = array_merge($array_album_id, $v_0);
            }

            $albumModel = (new \albumModel())
                ->column([
                    'album.album_id',
                    'album.cover',
                    'album.cover_hex',
                    'album.name album_name',
                    'user.name user_name',
                    'user.user_id',
                ])
                ->join([
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id']
                ])
                ->where([[[['album.album_id', 'IN', $array_album_id], ['album.act', '=', 'open']], 'and']])
                ->fetchAll();

            $array_album = [];

            foreach ($albumModel as $v_0) {
                $array_album[$v_0['album_id']] = [
                    'album' => [
                        'album_id' => $v_0['album_id'],
                        'cover' => $v_0['cover'],
                        'cover_hex' => $v_0['cover_hex'],
                        'name' => $v_0['album_name'],
                    ],
                    'user' => [
                        'name' => $v_0['user_name'],
                        'user_id' => $v_0['user_id'],
                    ],
                ];
            }

            foreach ($array_albumexplore as $albumexplore_id => $v_0) {
                $array_1_album = [];

                foreach ($array_album as $album_id => $v_1) {
                    if (in_array($album_id, $v_0['exhibit'])) {
                        $array_1_album[] = $v_1;
                    }
                }

                $return['albumexplore'][] = [
                    'album' => $array_1_album,
                    'albumexplore' => [
                        'name' => $v_0['name'],
                        'url' => $v_0['url'],
                    ],
                ];
            }
        }

        return $return;
    }

    static function getThemeArea_v2()
    {
        $return = [
            'albumexplore' => [],
            'categoryarea_style' => [],
            'themearea' => [],
        ];

        //
        $THEMEAREA_IMAGE_360X360 = (new \settingsModel)->getByKeyword('THEMEAREA_IMAGE_360X360');

        $return['themearea'] = [
            'colorhex' => '#36ACC1',
            'image_360x360' => is_file(PATH_UPLOAD . $THEMEAREA_IMAGE_360X360) ? path2url(PATH_UPLOAD . $THEMEAREA_IMAGE_360X360) : null,
            'name' => 'P好康',
        ];

        //
        $albumexploreModel = (new \albumexploreModel)
            ->column([
                'albumexplore_id',
                'basis',
                'basis_id',
                'categoryarea2explore_id',
                'exhibit',
                'name',
                'url',
            ])
            ->where([[[['categoryarea2explore_id', '=', 0], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        if ($albumexploreModel) {
            $array_albumexplore = [];

            foreach ($albumexploreModel as $v_0) {
                $array_exhibit = [];

                switch ($v_0['basis']) {
                    case 'category':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCategory($v_0['basis_id']), 'album_id');
                        break;

                    case 'categoryarea':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCategoryarea($v_0['basis_id']), 'album_id');
                        break;

                    case 'creative':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCreative($v_0['categoryarea2explore_id'], $v_0['basis_id']), 'album_id');
                        break;

                    case 'manual':
                        $array_exhibit = json_decode($v_0['exhibit'], true);
                        break;
                }

                $array_albumexplore[$v_0['albumexplore_id']] = [
                    'exhibit' => $array_exhibit,
                    'name' => $v_0['name'],
                    'url' => empty($v_0['url']) ? null : $v_0['url'],
                ];
            }

            $array_album_id = [];

            $array_exhibit = array_column($array_albumexplore, 'exhibit');

            foreach ($array_exhibit as $v_0) {
                $array_album_id = array_merge($array_album_id, $v_0);
            }

            $albumModel = (new \albumModel())
                ->column([
                    'album.album_id',
                    'album.cover',
                    'album.cover_hex',
                    'album.name album_name',
                    'user.name user_name',
                    'user.user_id',
                ])
                ->join([
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id']
                ])
                ->where([[[['album.album_id', 'IN', $array_album_id], ['album.act', '=', 'open']], 'and']])
                ->fetchAll();

            $array_album = [];

            foreach ($albumModel as $v_0) {
                $array_album[$v_0['album_id']] = [
                    'album' => [
                        'album_id' => $v_0['album_id'],
                        'cover' => $v_0['cover'],
                        'cover_hex' => $v_0['cover_hex'],
                        'name' => $v_0['album_name'],
                    ],
                    'user' => [
                        'name' => $v_0['user_name'],
                        'user_id' => $v_0['user_id'],
                    ],
                ];
            }

            foreach ($array_albumexplore as $albumexplore_id => $v_0) {
                $array_1_album = [];

                foreach ($array_album as $album_id => $v_1) {
                    if (in_array($album_id, $v_0['exhibit'])) {
                        $array_1_album[] = $v_1;
                    }
                }

                $return['albumexplore'][] = [
                    'album' => $array_1_album,
                    'albumexplore' => [
                        'name' => $v_0['name'],
                        'url' => $v_0['url'],
                    ],
                ];
            }
        }

        //
        $return['categoryarea_style'] = \categoryarea_styleModel::getCategoryArea_Style_v2(0);

        return $return;
    }
}