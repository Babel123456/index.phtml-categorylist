<?php

class creativeModel extends Model
{
    protected $database = 'site';
    protected $table = 'creative';
    protected $memcache = 'site';
    protected $join_table = ['categoryarea'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        $return = array(
            ['class' => __CLASS__],
            ['class' => 'categoryareaModel'],
        );

        return $return;
    }

    /**
     * user屬於哪一類職人
     */
    function creative_belong($user_id)
    {
        $return = null;

        $m_user_belong = (new \categoryarea_categoryModel)
            ->join([
                ['inner join', 'album', 'using(category_id)']
            ])
            ->where([[[['categoryarea_category.act', '=', 'open'], ['album.act', '=', 'open'], ['album.user_id', '=', $user_id]], 'and']])
            ->column([
                'categoryarea_category.categoryarea_id',
                'COUNT(1) AS count',
            ])
            ->group(['categoryarea_id'])
            ->order(['count' => 'desc'])
            ->limit(1)
            ->fetch();

        $return = $m_user_belong['categoryarea_id'];

        return $return;
    }

    /**
     * 每一類的排名(所有相本統計)
     * @param array $group
     * @param int $num
     * @return Array = [
     * int    categoryarea_id
     * string    categoryarea_name
     * array    sort = [
     * array = [
     * int    user_id
     * string    name
     * int    count_from
     * int    album_count
     * string    picture
     * string    url
     * int    viewed
     * ],
     * ....
     * ]
     * ]
     *
     */
    function creative_group($group = array(), $num = 6)
    {
        $return = array();
        $tmp = array();

        //取得 categoryarea 對應的 category Array
        $column = [
            'categoryarea_category.categoryarea_id',
            'categoryarea.name categoryarea_name',
            'GROUP_CONCAT(categoryarea_category.category_id) AS `a_category_id`'
        ];
        $join = [
            ['inner join', 'categoryarea', 'using(categoryarea_id)']
        ];
        $where = [[[['categoryarea_category.act', '=', 'open'], ['categoryarea.act', '=', 'open'], ['categoryarea_id', 'in', $group]], 'and']];
        $m_categoryarea_group_id = Model('categoryarea_category')->join($join)->where($where)->column($column)->group(['categoryarea_id'])->fetchAll();

        //取得各分類作品發布量排名 =$num 的user
        foreach ($m_categoryarea_group_id as $k0 => $v0) {
            $tmp[$k0]['categoryarea_id'] = $v0['categoryarea_id'];
            $tmp[$k0]['categoryarea_name'] = $v0['categoryarea_name'];
            $column = [
                'album.user_id',
                'user.name',
                'follow.count_from',
                'COUNT(*) AS `album_count`',
            ];
            $join = [
                ['inner join', 'user', 'using(user_id)'],
                ['inner join', 'follow', 'using(user_id)'],
            ];
            $where = [[[['album.category_id', 'in', explode(',', $v0['a_category_id'])], ['album.act', '=', 'open'], ['user.act', '=', 'open']], 'and']];
            $m_album_user_sort = Model('album')->column($column)->join($join)->where($where)->group(['user_id'])->order(['album_count' => 'desc'])->limit($num)->fetchAll();

            foreach ($m_album_user_sort as $k1 => $v1) {
                $m_album_user_sort[$k1]['picture'] = URL_STORAGE . Core::get_userpicture($v1['user_id']);
                $m_album_user_sort[$k1]['url'] = Core::get_creative_url($v1['user_id']);
                $m_album_user_sort[$k1]['viewed'] = (new \userModel)->getUserViewed($v1['user_id']);
            }
            $tmp[$k0]['sort'] = $m_album_user_sort;
        }

        $return = $tmp;
        return $return;
    }

    /**
     * 每一類的排名(每個週五為單位)
     * @param array $group
     * @param int $num
     * @return Array = [
     * int    categoryarea_id
     * string    categoryarea_name
     * array    sort = [
     * array = [
     * int    user_id
     * string    name
     * int    count_from
     * int    album_count
     * string    picture
     * string    url
     * int    viewed
     * ],
     * ....
     * ]
     * ]
     *
     */
    function creative_group_by_friday($group = array(), $num = 6)
    {
        $return = array();
        $tmp = array();

        //取得 categoryarea 對應的 category Array
        $column = [
            'categoryarea_category.categoryarea_id',
            'categoryarea.name categoryarea_name',
            'GROUP_CONCAT(categoryarea_category.category_id) AS `a_category_id`'
        ];
        $join = [
            ['inner join', 'categoryarea', 'using(categoryarea_id)']
        ];
        $where = [[[['categoryarea_category.act', '=', 'open'], ['categoryarea.act', '=', 'open'], ['categoryarea_id', 'in', $group]], 'and']];
        $m_categoryarea_group_id = Model('categoryarea_category')->join($join)->where($where)->column($column)->group(['categoryarea_id'])->fetchAll();

        //取得各分類作品發布量排名 =$num 的user
        foreach ($m_categoryarea_group_id as $k0 => $v0) {
            $column = array();
            $where = array();
            $join = array();
            $m_album_user_sort = [];
            $week = 0;
            $tmp[$k0]['categoryarea_id'] = $v0['categoryarea_id'];
            $tmp[$k0]['categoryarea_name'] = $v0['categoryarea_name'];
            $column = [
                'album.user_id',
                'user.name',
                'follow.count_from',
                'COUNT(*) AS `album_count`',
            ];
            $join = [
                ['inner join', 'user', 'using(user_id)'],
                ['inner join', 'follow', 'using(user_id)'],
            ];

            //至少需取得要求的創作人數量(預設為6), 避免版面上缺少創作人, 缺少時以周為單位往前推
            while (count($m_album_user_sort) < $num) {
                $week++;
                $stime = date('Y-m-d', strtotime("-$week week last friday")) . ' 00:00:00';
                $etime = date('Y-m-d', strtotime('last friday')) . ' 23:59:59';
                $where = [[[['album.category_id', 'in', explode(',', $v0['a_category_id'])], ['album.inserttime', '>', $stime], ['album.inserttime', '<', $etime], ['album.act', '=', 'open'], ['user.act', '=', 'open']], 'and']];

                $m_album_user_sort = (new \albumModel)
                    ->column($column)
                    ->join($join)
                    ->where($where)
                    ->group(['user_id'])
                    ->order(['album_count' => 'desc'])
                    ->limit($num)
                    ->fetchAll();

                if ($week == 26) break;
            }
            $m_album_user_sort = array_splice($m_album_user_sort, 0, $num);

            foreach ($m_album_user_sort as $k1 => $v1) {
                $m_album_user_sort[$k1]['picture'] = URL_STORAGE . Core::get_userpicture($v1['user_id']);
                $m_album_user_sort[$k1]['url'] = Core::get_creative_url($v1['user_id']);
                $m_album_user_sort[$k1]['viewed'] = (new \userModel)->getUserViewed($v1['user_id']);
            }

            $tmp[$k0]['sort'] = $m_album_user_sort;

        }

        $return = $tmp;
        return $return;
    }

    /**
     * 後台預設要顯示在Banner上的使用者名單, 從此處取回頁面顯示用的使用者資料
     * @param array $user_ids
     * @return Array();
     */
    public function assign_creative($user_ids = array())
    {
        $column = [
            'user.user_id',
            'user.name',
            'follow.count_from',
        ];

        $join = [['inner join', 'follow', 'using(user_id)']];

        $where = [[[['user_id', 'in', $user_ids]], 'and']];
        $m_user = (new userModel())->column($column)->join($join)->where($where)->fetchAll();
        foreach ($m_user as $k1 => $v1) {
            $m_user[$k1]['picture'] = URL_STORAGE . Core::get_userpicture($v1['user_id']);
            $m_user[$k1]['url'] = Core::get_creative_url($v1['user_id']);
            $m_user[$k1]['viewed'] = (new \userModel)->getUserViewed($v1['user_id']);
        }

        return $m_user;
    }

    /**
     * 取得符合以下條件的有效用戶名單
     * 1. 有填寫關於我內容
     * 2. 有上傳一本相本
     * 3. 有上傳頭像
     * 4. 有上傳專區背景圖片
     * @return array user_id;
     */
    function getActiveCreator()
    {
        $activeCreator = [];

        // 取得有填寫 "關於我" 及 "至少製作一本作品" 的用戶
        $m_users = (new userModel)
            ->column(['DISTINCT(`user_id`)', 'user.description'])
            ->join([['LEFT JOIN', 'album', 'USING(`user_id`)']])
            ->where([[[['user.act', '=', 'open'], ['user.description', '!=', ''], ['album.act', '=', 'open'], ['album.state', '=', 'success']], 'and']])
			->fetchAll();

        foreach ($m_users as $user) {
        	// 過濾掉只有圖片沒有文字的關於我內容
			$filter_img_tag = trim(preg_replace('/<p><img[^>]*>.*?<\/p>/i', "", $user['description']));
            if (file_exists(PATH_STORAGE . Core::get_usercover($user['user_id'])) && file_exists(PATH_STORAGE . \userModel::getPicture($user['user_id'])) && $filter_img_tag ) {
                $activeCreator[] = $user['user_id'];
            }
        }

        return $activeCreator;
    }
}