<?php

class albumstatistics2viewedModel extends Model
{
    protected $database = 'site';
    protected $table = 'albumstatistics2viewed';
    protected $memcache = 'site';
    protected $join_table = ['album', 'albumstatistics'];

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
        ];
    }

    function getAlbumViewedByAll($where, $order, $album_id)
    {
        $return = [];
        $viewedTotal = 0;

        $column = [
            'album.name album_name',
            'album.photo',
            'album.act',
            'album.point',
            'album_id',
            'categoryarea_category.categoryarea_id',
            'user.name user_name',
            'user_id',
            'SUM(`viewed`) `total`'
        ];
        $group = ['album_id'];
        $join = [
            ['left join', 'album', 'USING(`album_id`)'],
            ['left join', 'user', 'USING(`user_id`)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];

        //data
        $fetchAll = (new \albumstatistics2viewedModel)
            ->column($column)
            ->where($where)
            ->join($join)
            ->group($group)
            ->order($order)
            ->fetchAll();

        if (!empty($fetchAll)) {
            foreach ($fetchAll as $k0 => $v0) {
                $hasVideo = (new albumModel())->hasVideoPhoto($v0['album_id']);
                $hasAudio = (new albumModel())->hasAudio($v0['album_id']);

                $v0['album_name'] = '<a href="' . \frontstageController::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id']]) . '" target="_blank">' . $v0['album_name'] . '</a>';
                $v0['user_name'] = '<a href="' . \Core::get_creative_url($v0['user_id']) . '" target="_blank">' . $v0['user_name'] . '</a>';
                $v0['pages'] = count(json_decode($v0['photo'], true));
                $v0['video'] = ($hasVideo) ? 'V' : null;
                $v0['audio'] = ($hasAudio) ? 'V' : null;

                $return[] = $v0;
                $viewedTotal += $v0['total'];
            }

            $return[] = [
                'total' => '總計 : ' . $viewedTotal,
            ];
        }

        return $return;
    }

    function getAlbumViewedByAlbumId($where, $order, $album_id)
    {
        $return = [];
        $viewedTotal = 0;

        $column = [
            'album.name album_name',
            'album.photo',
            'album.act',
            'album.point',
            'album_id',
            'categoryarea_category.categoryarea_id',
            'user.name user_name',
            'user_id',
            'SUM(`viewed`) `total`'
        ];
        $group = ['album_id'];
        $join = [
            ['left join', 'album', 'USING(`album_id`)'],
            ['left join', 'user', 'USING(`user_id`)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];
        $where = array_merge($where, (array)[[[['albumstatistics2viewed.album_id', '=', $album_id]], 'and']]);

        //data
        $fetchAll = (new \albumstatistics2viewedModel)
            ->column($column)
            ->where($where)
            ->join($join)
            ->group($group)
            ->order($order)
            ->fetchAll();

        if (!empty($fetchAll)) {
            foreach ($fetchAll as $k0 => $v0) {
                $hasVideo = (new albumModel())->hasVideoPhoto($v0['album_id']);
                $hasAudio = (new albumModel())->hasAudio($v0['album_id']);
                $v0['album_name'] = '<a href="' . \frontstageController::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id']]) . '" target="_blank">' . $v0['album_name'] . '</a>';
                $v0['user_name'] = '<a href="' . \Core::get_creative_url($v0['user_id']) . '" target="_blank">' . $v0['user_name'] . '</a>';
                $v0['pages'] = count(json_decode($v0['photo'], true));
                $v0['video'] = ($hasVideo) ? 'V' : null;
                $v0['audio'] = ($hasAudio) ? 'V' : null;
                $return[] = $v0;
                $viewedTotal += $v0['total'];
            }
            $return[] = [
                'total' => '總計 : ' . $viewedTotal,
            ];
        }

        return $return;
    }

    function getAlbumViewedByCategoryarea($where, $order, $categoryarea_id)
    {
        $return = [];
        $viewedTotal = 0;

        $column = ['category_id'];
        $where0 = [[[['act', '=', 'open'], ['categoryarea_id', '=', $categoryarea_id]], 'and']];

        //data
        $m_categoryarea_category = Model('categoryarea_category')->column($column)->where($where0)->fetchAll();
        foreach ($m_categoryarea_category as $k0 => $v0) {
            $category_id[] = $v0['category_id'];
        }

        $column = ['album.name album_name', 'album.photo', 'album.act', 'album.point', 'album_id', 'user.name user_name', 'user_id', 'SUM(`viewed`) `total`'];
        $group = ['album_id'];
        $join = [['left join', 'album', 'USING(`album_id`)'], ['left join', 'user', 'USING(`user_id`)']];
        $where = array_merge($where, (array)[[[['album.category_id', 'in', $category_id]], 'and']]);

        //data
        $fetchAll = Model('albumstatistics2viewed')->column($column)->where($where)->join($join)->group($group)->order($order)->fetchAll();

        if (!empty($fetchAll)) {
            foreach ($fetchAll as $k0 => $v0) {
                $hasVideo = (new albumModel())->hasVideoPhoto($v0['album_id']);
                $hasAudio = (new albumModel())->hasAudio($v0['album_id']);
                $v0['album_name'] = '<a href="' . \frontstageController::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $categoryarea_id]) . '" target="_blank">' . $v0['album_name'] . '</a>';
                $v0['user_name'] = '<a href="' . \Core::get_creative_url($v0['user_id']) . '" target="_blank">' . $v0['user_name'] . '</a>';
                $v0['pages'] = count(json_decode($v0['photo'], true));
                $v0['video'] = ($hasVideo) ? 'V' : null;
                $v0['audio'] = ($hasAudio) ? 'V' : null;
                $return[] = $v0;
                $viewedTotal += $v0['total'];
            }
            $return[] = [
                'total' => '總計 : ' . $viewedTotal,
            ];
        }

        return $return;
    }

    function getAlbumViewedByUserId($where, $order, $user_id)
    {
        $return = [];
        $viewedTotal = 0;

        $column = [
            'album.name album_name',
            'album.photo',
            'album.act',
            'album.point',
            'album_id',
            'categoryarea_category.categoryarea_id',
            'user.name user_name',
            'user_id',
            'SUM(`viewed`) `total`'
        ];
        $group = ['album_id'];
        $join = [
            ['left join', 'album', 'USING(`album_id`)'],
            ['left join', 'user', 'USING(`user_id`)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];
        $where = array_merge($where, (array)[[[['album.user_id', '=', $user_id]], 'and']]);

        //data
        $fetchAll = (new \albumstatistics2viewedModel)
            ->column($column)
            ->where($where)
            ->join($join)
            ->group($group)
            ->order($order)
            ->fetchAll();

        if (!empty($fetchAll)) {
            foreach ($fetchAll as $k0 => $v0) {
                $hasVideo = (new albumModel())->hasVideoPhoto($v0['album_id']);
                $hasAudio = (new albumModel())->hasAudio($v0['album_id']);
                $v0['album_name'] = '<a href="' . \frontstageController::url('album', 'content', ['album_id' => $v0['album_id'], 'categoryarea_id' => $v0['categoryarea_id']]) . '" target="_blank">' . $v0['album_name'] . '</a>';
                $v0['user_name'] = '<a href="' . \Core::get_creative_url($v0['user_id']) . '" target="_blank">' . $v0['user_name'] . '</a>';
                $v0['pages'] = count(json_decode($v0['photo'], true));
                $v0['video'] = ($hasVideo) ? 'V' : null;
                $v0['audio'] = ($hasAudio) ? 'V' : null;
                $return[] = $v0;
                $viewedTotal += $v0['total'];
            }
            $return[] = [
                'total' => '總計 : ' . $viewedTotal,
            ];
        }

        return $return;
    }

    function getAlbumViewedByUserRate($user_id)
    {
        //基準為2017/1月
        $base = "2017-01-01";
        $today = date("Y-m-d");
        $data = [];

        function getMonthNum($date1, $date2, $tags = '-')
        {
            $date1 = explode($tags, $date1);
            $date2 = explode($tags, $date2);
            return abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
        }

        $monthNum = getMonthNum($base, $today) + 1;

        //基準年分-1, 故為2016
        $s_years = 2016;
        for ($i = 1; $i <= $monthNum; $i++) {
            $monthlyAlbumViewedRate = '~';
            $m = (($i % 12) == 0) ? 12 : $i % 12;
            if (strlen($m) == 1) $m = '0' . $m;
            if (($i % 12) == 1) {
                $s_years++;
            }

            $month = $s_years . '-' . $m;

            //單月相本製作數量及id
            $column = ['album_id'];
            $where = [[[['album.user_id', '=', $user_id], ['album.state', '!=', 'pretreat'], ['inserttime', 'LIKE', $month . '%']], 'and']];
            $monthly_album = Model('album')->column($column)->where($where)->fetchAll();

            //至x月的所有相本id
            $column = ['album_id'];
            $where = [[[['album.user_id', '=', $user_id], ['inserttime', '<', $month . '-31 23:59:59']], 'and']];
            $all_album = Model('album')->column($column)->where($where)->fetchAll();
            $a_album_id = [''];
            if ($all_album) {
                unset($a_album_id);
                foreach ($all_album as $k0 => $v0) {
                    $a_album_id[] = $v0['album_id'];
                }
            }

            $column = ['SUM(`viewed`) `total`'];
            $where = [[[['albumstatistics2viewed.album_id', 'in', $a_album_id], ['albumstatistics2viewed.datatime', '<', $month . '-31 23:59:59']], 'and']];
            $v_albumstatistics2viewed = Model('albumstatistics2viewed')->column($column)->where($where)->fetchColumn();

            $v_albumstatistics2viewed = ($v_albumstatistics2viewed) ? $v_albumstatistics2viewed : 0;

            $tmpStatistics2viewed[$i] = $v_albumstatistics2viewed;

            $monthlyNewAlbumViewed = ($i > 1) ? ($v_albumstatistics2viewed - $tmpStatistics2viewed[$i - 1]) : $v_albumstatistics2viewed;

            //該月瀏覽數 除 至上月總瀏覽數(%) 比例
            if ($i > 1) {
                $tmp = ($tmpStatistics2viewed[$i - 1] == 0) ? 1 : $tmpStatistics2viewed[$i - 1];
                $monthlyAlbumViewedRate = round((($v_albumstatistics2viewed - $tmpStatistics2viewed[$i - 1]) / $tmp), 2) * 100 . "%";
            }

            $data[] = [
                'month' => $month,
                'monthlyNewAlbum' => count($monthly_album),
                'monthlyAlbum_id' => $a_album_id,
                'monthlyNewAlbumViewed' => $monthlyNewAlbumViewed,
                'monthlyAlbumViewed' => $v_albumstatistics2viewed,
                'monthlyAlbumViewedRate' => $monthlyAlbumViewedRate,
            ];
        }

        return $data;
    }
}