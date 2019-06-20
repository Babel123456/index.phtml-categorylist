<?php

class categoryarea_styleModel extends Model
{
    protected $database = 'site';
    protected $table = 'categoryarea_style';
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

    public function getCategoryarea_style($categoryarea_id)
    {
        $m_categoryarea_style = (new categoryarea_styleModel())
            ->where([[[['act', '=', 'open'], ['categoryarea_id', '=', $categoryarea_id]], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        return $m_categoryarea_style;
    }

    static function getCategoryArea_Style_v2($categoryarea_id)
    {
        $array = [];

        $Model_categoryarea_style = (new categoryarea_styleModel)
            ->column([
                'banner_type',
                'banner_type_data',
                'image',
            ])
            ->where([[[['categoryarea_id', '=', $categoryarea_id], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        foreach ($Model_categoryarea_style as $v_0) {
            $banner_type_data = null;

            switch ($v_0['banner_type']) {
                case 'creative':
                    if (!isset($array_category_id)) {
                        $array_category_id = \categoryModel::getCategoryByCategoryAreaId($categoryarea_id);
                    }

                    $array_user_id = empty($v_0['banner_type_data']) ? [] : json_decode($v_0['banner_type_data'], true);
                    $Model_user = [];
                    $num = 6;
                    $week = 0;
                    $where = [];

                    if ($array_user_id) {
                        $array_user_id = array_column(
                            (new \userModel)
                                ->column(['user_id'])
                                ->where([[[['user_id', 'IN', $array_user_id], ['act', '=', 'open']], 'and']])
                                ->fetchAll(),
                            'user_id'
                        );

                        $count_user_id = count($array_user_id);

                        if ($count_user_id < $num) {
                            $num -= $count_user_id;
                        }

                        $where = [[[['user.user_id', 'NOT IN', $array_user_id]], 'and']];
                    }

                    //至少需取得要求的創作人數量(預設為6), 避免版面上缺少創作人, 缺少時以周為單位往前推
                    if ($array_category_id) {
                        while (count($Model_user) < $num) {
                            $week++;

                            $etime = date('Y-m-d', strtotime('last friday')) . ' 23:59:59';
                            $stime = date('Y-m-d', strtotime("-$week week last friday")) . ' 00:00:00';

                            $Model_user = (new \userModel)
                                ->column([
                                    'user.user_id',
                                    'COUNT(1) `count`'
                                ])
                                ->join([
                                    ['INNER JOIN', 'album', 'ON album.user_id = user.user_id AND album.category_id IN (' . implode(',', array_map([(new \userModel), 'quote'], $array_category_id)) . ') AND album.act = \'open\' AND album.inserttime BETWEEN \'' . $stime . '\' AND \'' . $etime . '\'']
                                ])
                                ->where(array_merge([[[['user.act', '=', 'open']], 'and']], $where))
                                ->group(['album.user_id'])
                                ->order(['`count`' => 'DESC'])
                                ->limit('0,' . $num)
                                ->fetchAll();

                            if ($week == 26) break;
                        }
                    }

                    $array_user_id = array_merge($array_user_id, array_column($Model_user, 'user_id'));

                    if ($array_user_id) {
                        $Model_user = (new \userModel)
                            ->column([
                                '`name`',
                                'user_id',
                            ])
                            ->where([[[['user_id', 'IN', $array_user_id]], 'and']])
                            ->order(['FIELD(' . implode(',', array_merge(['user_id'], $array_user_id)) . ')' => 'ASC'])
                            ->fetchAll();

                        foreach ($Model_user as $v_1) {
                            $picture = PATH_STORAGE . \userModel::getPicture($v_1['user_id']);

                            $banner_type_data[] = [
                                'name' => $v_1['name'],
                                'picture' => is_image($picture) ? $picture : null,
                                'user_id' => $v_1['user_id'],
                            ];
                        }
                    }
                    break;

                case 'image':
                case 'video':
                    $banner_type_data = json_decode($v_0['banner_type_data'], true);
                    break;
            }

            $array[] = [
                'banner_type' => $v_0['banner_type'],
                'banner_type_data' => $banner_type_data,
                'image' => is_image(PATH_UPLOAD . $v_0['image']) ? PATH_UPLOAD . $v_0['image'] : null,
            ];
        }

        return $array;
    }
}