<?php

class albumexploreModel extends Model
{
    protected $database = 'site';
    protected $table = 'albumexplore';
    protected $memcache = 'site';
    protected $join_table = [];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        $return = array(
            ['class' => __CLASS__],
        );

        return $return;
    }

    function getByCategory($category_id, $range = 7)
    {
        $m_album = $a_album_id = [];
        $where = [[[['album.category_id', '=', $category_id], ['album.act', '=', 'open'], ['photo.act', '=', 'open']], 'and']];
        $join = [['left join', 'photo', 'using(`album_id`)']];
        $m_album = (new albumModel())->column(['album_id', 'count(1) as page'])->where($where)->join($join)->group(['album_id'])->fetchAll();

        foreach ($m_album as $k0 => $v0) {
            if ($v0['page'] > 1) $a_album_id[] = $v0['album_id'];
        }

        /**
         *  這邊的viewed為七天內的統計, 僅為排序用, 實際到前台顯示的viewed數量由 albumstatistics 內的viewed欄位提供
         */
        $column = [
            'album.album_id',
            'album.user_id',
            'album.name',
            'album.cover',
            'categoryarea_category.categoryarea_id',
            'user.name user_name',
            'SUM(`albumstatistics2viewed`.`viewed`) viewed',
        ];

        $starttime = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . "-$range days"));
        $endtime = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . "-1 days"));

        $where = [[[['albumstatistics2viewed.album_id', 'in', $a_album_id], ['albumstatistics2viewed.datatime', '>', $starttime], ['albumstatistics2viewed.datatime', '<', $endtime]], 'and']];
        $join = [
            ['left join', 'album', 'using(album_id)'],
            ['left join', 'user', 'using(user_id)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];

        $albumstatistics2viewed = (new albumstatistics2viewedModel())->column($column)->where($where)->join($join)->group(['album.album_id'])->order(['viewed' => 'desc'])->limit('4')->fetchAll();

        if ((4 - count($albumstatistics2viewed)) > 0) {
            $remainder = 4 - count($albumstatistics2viewed);
            $where = [[[['album_id', 'in', $a_album_id]], 'and']];
            $tmp = (new albumstatistics2viewedModel())->column($column)->where($where)->join($join)->group(['album.album_id'])->order(['viewed' => 'desc'])->limit($remainder)->fetchAll();

            foreach ($tmp as $k0 => $v0) {
                $albumstatistics2viewed[] = $v0;
            }
        }

        foreach ($albumstatistics2viewed as $k0 => $v0) {
            $albumstatistics2viewed[$k0]['viewed'] = (new albumstatisticsModel())->column(['viewed'])->where([[[['album_id', '=', $v0['album_id']]], 'and']])->fetchColumn();
        }

        return $albumstatistics2viewed;
    }

    function getByCategoryarea($categoryarea_id, $range = 7)
    {

        $where = [[[['categoryarea_id', '=', $categoryarea_id], ['act', '=', 'open']], 'and']];
        $m_category_id = (new categoryarea_categoryModel())
            ->column(['category_id'])
            ->where($where)
            ->fetchAll();
        $a_category_id = array_column($m_category_id, 'category_id');

        $where = [[[['album.act', '=', 'open'], ['photo.act', '=', 'open'], ['album.category_id', 'in', $a_category_id]], 'and']];
        $join = [['left join', 'photo', 'using(`album_id`)']];

        $m_album = (new albumModel())
            ->column(['album_id', 'count(1) as page'])
            ->where($where)
            ->join($join)
            ->group(['album_id'])
            ->fetchAll();

        foreach ($m_album as $k0 => $v0) {
            if ($v0['page'] > 1) $a_album_id[] = $v0['album_id'];
        }

        /**
         *  這邊的viewed為七天內的統計, 僅為排序用, 實際到前台顯示的viewed數量由 albumstatistics 內的viewed欄位提供
         */
        $column = [
            'album.album_id',
            'album.user_id',
            'album.name',
            'album.cover',
            'categoryarea_category.categoryarea_id',
            'user.name user_name',
            'SUM(`albumstatistics2viewed`.`viewed`) viewed',
        ];

        $starttime = date('Y-m-d 00:00:00', strtotime(date('Y-m-d') . "-$range days"));
        $endtime = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . "-1 days"));

        $where = [[[['albumstatistics2viewed.album_id', 'in', $a_album_id], ['albumstatistics2viewed.datatime', '>', $starttime], ['albumstatistics2viewed.datatime', '<', $endtime]], 'and']];
        $join = [
            ['left join', 'album', 'using(album_id)'],
            ['left join', 'user', 'using(user_id)'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];

        $albumstatistics2viewed = (new albumstatistics2viewedModel())
            ->column($column)
            ->where($where)
            ->join($join)
            ->group(['album.album_id'])
            ->order(['viewed' => 'desc'])
            ->limit('4')
            ->fetchAll();

        if ((4 - count($albumstatistics2viewed)) > 0) {
            $remainder = 4 - count($albumstatistics2viewed);
            $where = [[[['album_id', 'in', $a_album_id]], 'and']];

            $tmp = (new albumstatistics2viewedModel())
                ->column($column)
                ->where($where)
                ->join($join)
                ->group(['album.album_id'])
                ->order(['viewed' => 'desc'])
                ->limit($remainder)
                ->fetchAll();

            foreach ($tmp as $k0 => $v0) {
                $albumstatistics2viewed[] = $v0;
            }
        }

        foreach ($albumstatistics2viewed as $k0 => $v0) {
            $albumstatistics2viewed[$k0]['viewed'] = (new albumstatisticsModel())->column(['viewed'])->where([[[['album_id', '=', $v0['album_id']]], 'and']])->fetchColumn();
        }

        return $albumstatistics2viewed;
    }

    function getByCreative($categoryarea2explore_id, $creative_id)
    {
        $album = $a_category_id = [];

        if ($categoryarea2explore_id) {
            //主分類內的所有子分類
            $m_categoryarea_id = (new categoryarea_categoryModel())->column(['category_id'])->where([[[['categoryarea_id', '=', $categoryarea2explore_id]], 'and']])->fetchAll();
            foreach ($m_categoryarea_id as $k0 => $v0) {
                $a_category_id[] = $v0['category_id'];
            }

            $where = [[[['album.user_id', '=', $creative_id], ['album.category_id', 'in', $a_category_id], ['album.act', '=', 'open'], ['photo.act', '=', 'open']], 'and']];
        } else {
            $where = [[[['album.user_id', '=', $creative_id], ['album.act', '=', 'open'], ['photo.act', '=', 'open']], 'and']];
        }

        $column = [
            'album.album_id',
            'count(1) as page',
            'album.user_id',
            'album.name',
            'album.cover',
            'user.name user_name',
            'albumstatistics.viewed viewed',
            'categoryarea_category.categoryarea_id',
        ];

        $join = [
            ['left join', 'photo', 'using(`album_id`)'],
            ['left join', 'albumstatistics', 'using(`album_id`)'],
            ['left join', 'user', 'ON album.user_id = user.user_id'],
            ['left join', 'categoryarea_category', 'on categoryarea_category.category_id = album.category_id'],
        ];

        $album = (new albumModel())
            ->column($column)
            ->where($where)
            ->join($join)
            ->group(['album_id'])
            ->order(['album.publishtime' => 'desc'])
            ->limit(4)
            ->fetchAll();

        return $album;
    }
}