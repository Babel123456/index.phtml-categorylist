<?php

class categoryareaModel extends Model
{
    protected $database = 'site';
    protected $table = 'categoryarea';
    protected $memcache = 'site';
    protected $join_table = ['album', 'categoryarea_category', 'albumstatistics'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'albumModel'],
            ['class' => 'albumstatisticsModel'],
            ['class' => 'categoryarea_categoryModel'],
        ];
    }

    static function getCategoryArea($categoryarea_id)
    {
        $return = [
            'albumexplore' => [],
            'categoryarea' => [
                'categoryarea' => [],
                'user' => []
            ],
        ];

        $creativeModel = (new \creativeModel)
            ->creative_group_by_friday([$categoryarea_id]);

        if (isset($creativeModel[0])) {
            $return['categoryarea']['categoryarea'] = [
                'name' => $creativeModel[0]['categoryarea_name'],
            ];

            if (isset($creativeModel[0]['sort'])) {
                foreach ($creativeModel[0]['sort'] as $v_0) {
                    $return['categoryarea']['user'][] = [
                        'name' => $v_0['name'],
                        'picture' => $v_0['picture'],
                        'user_id' => $v_0['user_id'],
                    ];
                }
            }
        }

        //
        $albumexploreModel = (new \albumexploreModel)
            ->column([
                'albumexplore_id',
                'basis',
                'basis_id',
                'exhibit',
                'name',
                'url',
            ])
            ->where([[[['categoryarea2explore_id', '=', $categoryarea_id], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        if ($albumexploreModel) {
            $array_album_id = [];
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

                    case 'manual':
                        $array_exhibit = json_decode($v_0['exhibit'], true);
                        break;

                    case 'creative':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCreative($categoryarea_id, $v_0['basis_id']), 'album_id');
                        break;
                }

                $array_album_id = array_merge($array_album_id, $array_exhibit);

                $array_albumexplore[$v_0['albumexplore_id']] = [
                    'exhibit' => $array_exhibit,
                    'name' => $v_0['name'],
                    'url' => empty($v_0['url']) ? null : $v_0['url'],
                ];
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

    static function getCategoryArea_v2($categoryarea_id)
    {
        $return = [
            'albumexplore' => [],
            'categoryarea' => [],
            'categoryarea_style' => [],
        ];

        //
        $Model_categoryarea = (new \categoryareaModel)
            ->column(['`name`'])
            ->where([[[['categoryarea_id', '=', $categoryarea_id], ['act', '=', 'open']], 'and']])
            ->fetch();

        if ($Model_categoryarea) {
            $return['categoryarea']['name'] = $Model_categoryarea['name'];
        }

        //
        $return['categoryarea_style'] = \categoryarea_styleModel::getCategoryArea_Style_v2($categoryarea_id);

        //
        $albumexploreModel = (new \albumexploreModel)
            ->column([
                'albumexplore_id',
                'basis',
                'basis_id',
                'exhibit',
                'name',
                'url',
            ])
            ->where([[[['categoryarea2explore_id', '=', $categoryarea_id], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        if ($albumexploreModel) {
            $array_album_id = [];
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

                    case 'manual':
                        $array_exhibit = json_decode($v_0['exhibit'], true);
                        break;

                    case 'creative':
                        $array_exhibit = array_column((new \albumexploreModel())->getByCreative($categoryarea_id, $v_0['basis_id']), 'album_id');
                        break;
                }

                $array_album_id = array_merge($array_album_id, $array_exhibit);

                $array_albumexplore[$v_0['albumexplore_id']] = [
                    'exhibit' => $array_exhibit,
                    'name' => $v_0['name'],
                    'url' => empty($v_0['url']) ? null : $v_0['url'],
                ];
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

    static function usable($categoryarea_id, $user_id = null)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        if (empty($categoryarea_id)) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "categoryarea_id" is required.';
            goto _return;
        } else {
            $categoryareaModel = (new \categoryareaModel())
                ->column([
                    'act',
                ])
                ->where([[[['categoryarea_id', '=', $categoryarea_id]], 'and']])
                ->fetch();

            if (empty($categoryareaModel)) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('"類別區域"資料不存在。');
                goto _return;
            } else {
                if ($categoryareaModel['act'] == 'close') {
                    $result = \Lib\Result::USER_ERROR;
                    $message = _('"類別區域"資料不可用。');
                    goto _return;
                }
            }
        }

        list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }
}