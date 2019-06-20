<?php

class weightModel extends Model
{
    protected $database = 'site';
    protected $table = 'weight';
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

    /**
     * 2017-01-09 Lion: 此函式由 crontab 運行
     * @return array
     */
    function importDataToEachType($user_id = null)
    {
        $result = 1;
        $message = null;

        set_time_limit(0);

        if ($user_id) {
            $userModel = (new userModel)
                ->column([
                    'user.act',
                    'user.user_id',
                ])
                ->where([[[['user_id', '=', $user_id]], 'and']])
                ->lock('for update')
                ->fetch();
        } else {
            $userModel = (new userModel)
                ->column([
                    'user.act',
                    'user.user_id',
                ])
                ->join([['inner join', 'weightqueue', 'ON weightqueue.user_id = user.user_id AND weightqueue.state = \'pretreat\'']])
                ->order(['weightqueue.inserttime' => 'asc'])
                ->limit('0,1')
                ->lock('for update')
                ->fetch();
        }

        if ($userModel) {
            (new album2weightModel)->where([[[['user_id', '=', $userModel['user_id']]], 'and']])->delete();

            (new user2weightModel)->where([[[['user_id', '=', $userModel['user_id']]], 'and']])->delete();

            if ($userModel['act'] === 'open') {
                $days = (new \settingsModel)->getByKeyword('WEIGHT_CALCULATION_DAYS');

                if ($days === null) $days = 30;

                $publishtime = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));

                //weight
                $m_weight = (new weightModel)->column(['`type`', 'keyword', 'weight'])->fetchAll();

                $a_weight = [];

                foreach ($m_weight as $v0) {
                    $a_weight[$v0['type']][$v0['keyword']] = (double)$v0['weight'];
                }

                //hobby
                $m_album = (new albumModel)
                    ->column(['DISTINCT(album.album_id)', 'album.user_id'])
                    ->join([
                        ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                        ['INNER JOIN', 'hobby2category', 'USING(category_id)'],
                        ['INNER JOIN', 'hobby_user', 'ON hobby_user.hobby_id = hobby2category.hobby_id AND hobby_user.user_id = ' . (new \hobby_userModel())->quote($userModel['user_id'])]
                    ])
                    ->where([[[['album.act', '=', 'open'], ['album.publishtime', '>=', $publishtime]], 'and']])
                    ->fetchAll();

                //albumqueue
                $a_albumqueue = array_column(
                    (new albumqueueModel)
                        ->column(['DISTINCT(albumqueue.album_id)'])
                        ->join([
                            ['INNER JOIN', 'album', 'ON album.album_id = albumqueue.album_id AND album.act = \'open\' AND album.publishtime >= ' . (new \albumModel)->quote($publishtime)],
                            ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                        ])
                        ->where([[[['albumqueue.user_id', '=', $userModel['user_id']], ['albumqueue.visible', '=', true]], 'and']])
                        ->fetchAll(),
                    'album_id');

                //followto
                $a_followto = array_column(
                    (new followtoModel)
                        ->column(['`to`'])
                        ->where([[[['user_id', '=', $userModel['user_id']]], 'and']])
                        ->fetchAll(),
                    'to');

                //sum usercategoryview
                $sum_usercategoryview = (new usercategoryviewModel)
                    ->column(['SUM(`count`)'])
                    ->where([[[['user_id', '=', $userModel['user_id']]], 'and']])
                    ->fetchColumn();

                //usersearch
                $m_usersearch = (new usersearchModel)
                    ->column(['searchtype', 'searchkey', '`count`'])
                    ->where([[[['user_id', '=', $userModel['user_id']]], 'and']])
                    ->fetchAll();

                $sum_usersearch = (new usersearchModel)
                    ->column(['searchtype', 'SUM(`count`) `count`'])
                    ->where([[[['user_id', '=', $userModel['user_id']]], 'and']])
                    ->group(['searchtype'])
                    ->fetchAll();

                $a_sum_usersearch = [];

                foreach ($sum_usersearch as $v0) {
                    $a_sum_usersearch[$v0['searchtype']] = $v0['count'];
                }

                /**
                 * album2weight
                 */
                $a_album2weight = [];

                if ($a_albumqueue) {
                    //albumqueue X category
                    $a_album = array_column(
                        (new albumModel)
                            ->column(['DISTINCT(album.album_id)'])
                            ->join([
                                ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                                ['INNER JOIN', 'album', 'album_2nd ON album_2nd.category_id = album.category_id AND album_2nd.album_id IN (' . implode(',', array_map(function ($v) {
                                        return (new \albumModel())->quote($v);
                                    }, $a_albumqueue)) . ')'],
                                ['INNER JOIN', 'category', 'ON category.category_id = album_2nd.category_id AND category.act = \'open\''],
                            ])
                            ->where([[[['album.act', '=', 'open'], ['album.publishtime', '>=', $publishtime]], 'and']])
                            ->fetchAll(),
                        'album_id');

                    foreach ($a_album as $v0) {
                        if (!isset($a_album2weight[$v0])) $a_album2weight[$v0] = 0;

                        if (isset($a_weight['album']['ALBUMQUEUE_CATEGORY'])) $a_album2weight[$v0] += $a_weight['album']['ALBUMQUEUE_CATEGORY'];
                    }

                    //albumqueue X user
                    $a_album = array_column(
                        (new albumModel)
                            ->column(['DISTINCT(album.album_id)'])
                            ->join([
                                ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                                ['INNER JOIN', 'album', 'album_2nd ON album_2nd.user_id = album.user_id AND album_2nd.album_id IN (' . implode(',', array_map(function ($v) {
                                        return (new \albumModel())->quote($v);
                                    }, $a_albumqueue)) . ')']
                            ])
                            ->where([[[['album.act', '=', 'open'], ['album.publishtime', '>=', $publishtime]], 'and']])
                            ->fetchAll(),
                        'album_id');

                    foreach ($a_album as $v0) {
                        if (!isset($a_album2weight[$v0])) $a_album2weight[$v0] = 0;

                        if (isset($a_weight['album']['ALBUMQUEUE_USER'])) $a_album2weight[$v0] += $a_weight['album']['ALBUMQUEUE_USER'];
                    }
                }

                //usercategoryview
                $m_usercategoryview = (new usercategoryviewModel)
                    ->column(['album.album_id', 'usercategoryview.count'])
                    ->join([['INNER JOIN', 'album', 'ON album.category_id = usercategoryview.category_id AND album.publishtime >= ' . (new \albumModel())->quote($publishtime)]])
                    ->where([[[['usercategoryview.user_id', '=', $userModel['user_id']]], 'and']])
                    ->fetchAll();

                foreach ($m_usercategoryview as $v0) {
                    if (!isset($a_album2weight[$v0['album_id']])) $a_album2weight[$v0['album_id']] = 0;

                    if (isset($a_weight['album']['USERCATEGORYVIEW'])) $a_album2weight[$v0['album_id']] += ($v0['count'] / $sum_usercategoryview) * $a_weight['album']['USERCATEGORYVIEW'];
                }

                //usersearch
                foreach (array_multiple_search($m_usersearch, 'searchtype', 'album') as $v0) {
                    $s_album = Solr('album')->column(['album_id'])->where([[[['_text_', '=', $v0['searchkey']], ['publishtime', '>=', $publishtime]], 'and']])->fetchAll();

                    if ($s_album) {
                        foreach (array_column($s_album, 'album_id') as $v1) {
                            if (!isset($a_album2weight[$v1])) $a_album2weight[$v1] = 0;

                            if (isset($a_weight['album']['USERSEARCH']) && isset($a_sum_usersearch['album']) && $a_sum_usersearch['album'] > 0) {
                                $a_album2weight[$v1] += ($v0['count'] / $a_sum_usersearch['album']) * $a_weight['album']['USERSEARCH'];
                            }
                        }
                    }
                }

                //follow
                if ($a_followto) {
                    $a_album = array_column(
                        (new albumModel)
                            ->column(['album.album_id'])
                            ->join([
                                ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.user_id IN (' . implode(',', array_map(function ($v) {
                                        return (new \albumModel())->quote($v);
                                    }, $a_followto)) . ') AND user.act = \'open\''
                                ]
                            ])
                            ->where([[[['album.act', '=', 'open'], ['album.publishtime', '>=', $publishtime]], 'and']])
                            ->fetchAll(),
                        'album_id');

                    foreach ($a_album as $v0) {
                        if (!isset($a_album2weight[$v0])) $a_album2weight[$v0] = 0;

                        if (isset($a_weight['album']['FOLLOW'])) $a_album2weight[$v0] += $a_weight['album']['FOLLOW'];
                    }
                }

                //hobby
                foreach (array_column($m_album, 'album_id') as $v0) {
                    if (!isset($a_album2weight[$v0])) $a_album2weight[$v0] = 0;

                    if (isset($a_weight['album']['HOBBY2CATEGORY'])) $a_album2weight[$v0] += $a_weight['album']['HOBBY2CATEGORY'];
                }

                //排除已收藏的
                foreach ($a_albumqueue as $album_id) {
                    unset($a_album2weight[$album_id]);
                }

                //排除計為 0 分的
                foreach ($a_album2weight as $album_id => $v_0) {
                    if ($v_0 == 0) unset($a_album2weight[$album_id]);
                }

                $replace = [];

                foreach ($a_album2weight as $album_id => $weight) {
                    $replace[] = [
                        'user_id' => $userModel['user_id'],
                        'album_id' => $album_id,
                        'weight' => $weight,
                    ];
                }

                if ($replace) {
                    //2017-01-11 Lion: 依 weight desc 排序
                    usort($replace, function ($a, $b) {
                        if ($a['weight'] == $b['weight']) {
                            return 0;
                        }

                        return ($a['weight'] < $b['weight']) ? 1 : -1;
                    });

                    (new album2weightModel)->replace(array_slice($replace, 0, 1000));
                }

                /**
                 * user2weight
                 */
                $a_user2weight = [];

                //albumqueue
                if ($a_albumqueue) {
                    $a_album = array_column(
                        (new albumModel)
                            ->column(['DISTINCT(user_id)'])
                            ->where([[[['album_id', 'in', $a_albumqueue]], 'and']])
                            ->fetchAll(),
                        'user_id');

                    foreach ($a_album as $v0) {
                        if (!isset($a_user2weight[$v0])) $a_user2weight[$v0] = 0;

                        if (isset($a_weight['user']['ALBUMQUEUE'])) $a_user2weight[$v0] += $a_weight['user']['ALBUMQUEUE'];
                    }
                }

                //usercategoryview
                $m_usercategoryview = (new usercategoryviewModel)
                    ->column(['album.user_id', 'SUM(usercategoryview.count) `count`'])
                    ->join([['INNER JOIN', 'album', 'USING(category_id)']])
                    ->where([[[['usercategoryview.user_id', '=', $userModel['user_id']]], 'and']])
                    ->group(['album.user_id'])
                    ->fetchAll();

                foreach ($m_usercategoryview as $v0) {
                    if (!isset($a_user2weight[$v0['user_id']])) $a_user2weight[$v0['user_id']] = 0;

                    if (isset($a_weight['user']['USERCATEGORYVIEW'])) $a_user2weight[$v0['user_id']] += ($v0['count'] / $sum_usercategoryview) * $a_weight['user']['USERCATEGORYVIEW'];
                }

                //usersearch
                foreach (array_multiple_search($m_usersearch, 'searchtype', 'user') as $v0) {
                    $s_user = Solr('user')->column(['user_id'])->where([[[['_text_', '=', $v0['searchkey']]], 'and']])->fetchAll();

                    if ($s_user) {
                        foreach (array_column($s_user, 'user_id') as $v1) {
                            if (!isset($a_user2weight[$v1])) $a_user2weight[$v1] = 0;

                            if (isset($a_weight['user']['USERSEARCH']) && isset($a_sum_usersearch['user']) && $a_sum_usersearch['user'] > 0) {
                                $a_user2weight[$v1] += ($v0['count'] / $a_sum_usersearch['user']) * $a_weight['user']['USERSEARCH'];
                            }
                        }
                    }
                }

                //hobby
                foreach (array_unique(array_column($m_album, 'user_id')) as $v0) {
                    if (!isset($a_user2weight[$v0])) $a_user2weight[$v0] = 0;

                    if (isset($a_weight['user']['HOBBY2CATEGORY'])) $a_user2weight[$v0] += $a_weight['user']['HOBBY2CATEGORY'];
                }

                //排除已關注的
                foreach ($a_followto as $user_id) {
                    unset($a_user2weight[$user_id]);
                }

                //排除計為 0 分的
                foreach ($a_user2weight as $user_id => $v_0) {
                    if ($v_0 == 0) unset($a_user2weight[$user_id]);
                }

                $replace = [];

                foreach ($a_user2weight as $user_id => $weight) {
                    $replace[] = [
                        'user_id' => $userModel['user_id'],
                        'user_id4weight' => $user_id,
                        'weight' => $weight,
                    ];
                }

                if ($replace) {
                    //2017-01-11 Lion: 依 weight desc 排序
                    usort($replace, function ($a, $b) {
                        if ($a['weight'] == $b['weight']) {
                            return 0;
                        }

                        return ($a['weight'] < $b['weight']) ? 1 : -1;
                    });

                    (new user2weightModel)->replace(array_slice($replace, 0, 1000));
                }
            }

            (new weightqueueModel)->where([[[['user_id', '=', $userModel['user_id']]], 'and']])->edit(['state' => 'success']);
        }

        //將最舊的一筆 success 寫為 pretreat, 等待執行
        $user_id = (new weightqueueModel)->column(['user_id'])->where([[[['state', '=', 'success']], 'and']])->order(['inserttime' => 'asc'])->limit('0,1')->fetchColumn();

        if ($user_id) {
            (new weightqueueModel)->where([[[['user_id', '=', $user_id]], 'and']])->edit(['state' => 'pretreat']);
        }

        _return:
        return array_encode_return($result, $message);
    }
}