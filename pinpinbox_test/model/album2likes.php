<?php

class album2likesModel extends Model
{
    protected $database = 'site';
    protected $table = 'album2likes';
    protected $memcache = 'site';
    protected $join_table = ['user', 'album'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'userModel'],
            ['class' => 'albumModel'],
        ];
    }

    function addLikes($user_id = null, $album_id = null)
    {
        if (!is_null($user_id) && !is_null($album_id)) {
            $this->replace([
                'user_id' => $user_id,
                'album_id' => $album_id,
            ]);

            (new albumstatisticsModel)->replace([
                'album_id' => $album_id,
                'likes' => $this->countlikes($album_id) + 1
            ]);

            //用戶訂閱該作品\版型
            subscriptionModel::build($user_id, 'albumqueue', $album_id);

            $targetId = (new albumModel)->column(['user_id'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();

            if ($targetId != $user_id) {
                $SNSparam = Core::getSNSParams([
                    'trigger' => [
                        'user_id' => $user_id,
                        'type' => 'album',
                        'typeId' => $album_id,
                        'refer' => 'UserLikeAlbum',
                    ],
                    'targetId' => $targetId,
                    'typeOfSNS' => 'albumqueue',
                ]);
                (new \topicModel)->publish($user_id, 'user', $targetId, $SNSparam['message'], 'albumqueue', $album_id, $SNSparam);
            }
        }
    }

    function cancelLikes($user_id = null, $album_id = null)
    {
        if (!is_null($user_id) && !is_null($album_id)) {
            $this->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->delete();

            (new albumstatisticsModel)->replace([
                'album_id' => $album_id,
                'likes' => $this->countlikes($album_id) - 1
            ]);
        }
    }

    function countlikes($album_id = null)
    {
        $return = 0;

        if (!is_null($album_id)) {
            $return = (new albumstatisticsModel)->column(['likes'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();
        }

        return $return;
    }

    /**
     * 2018-06-25 Lion: 此函式由 crontab 運行
     * @return array
     */
    static function importToIndexPopularity()
    {
        $result = 1;
        $message = null;

        $num = 8;

        $array_album_id = array_column(
            (new \album2likesModel)
                ->column([
                    'album2likes.album_id',
                    'COUNT(1) as `count`',
                ])
                ->join([
                    ['INNER JOIN', 'album', 'ON album.album_id = album2likes.album_id AND album.act = \'open\''],
                    ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                ])
                ->where([[[['album2likes.inserttime', 'BETWEEN', [date('Y-m-d 00:00:00', strtotime("last week monday")), date('Y-m-d 23:59:59', strtotime("last week sunday"))]]], 'and']])
                ->group(['album2likes.album_id'])
                ->order([
                    '`count`' => 'DESC',
                    'album2likes.inserttime' => 'DESC'
                ])
                ->limit('0,' . $num)
                ->fetchAll(),
            'album_id'
        );

        $count = count($array_album_id);

        if ($num > $count) {
            $last = $num - $count;

            $array_album_id = array_merge(
                $array_album_id,
                array_column(
                    (new \album2likesModel)
                        ->column([
                            'album2likes.album_id',
                            'COUNT(1) as `count`',
                        ])
                        ->join([
                            ['INNER JOIN', 'album', 'ON album.album_id = album2likes.album_id AND album.act = \'open\''],
                            ['INNER JOIN', 'user', 'ON user.user_id = album.user_id AND user.act = \'open\''],
                        ])
                        ->where([[[['album2likes.album_id', 'NOT IN', $array_album_id]], 'and']])
                        ->group(['album2likes.album_id'])
                        ->order([
                            '`count`' => 'DESC',
                            'album2likes.inserttime' => 'DESC'
                        ])
                        ->limit('0,' . $last)
                        ->fetchAll(),
                    'album_id'
                )
            );
        }

        (new \indexpopularityModel)
            ->where([[[['indexpopularity_id', '=', 1]], 'and']])
            ->edit([
                'exhibit' => json_encode($array_album_id)
            ]);

        //預設封面圖
        $array_without_indexpopularity_cover = (new \albumModel)
            ->column([
                'album_id',
                'cover',
            ])
            ->where([[[['album_id', 'IN', $array_album_id], ['indexpopularity_cover', '=', '']], 'and']])
            ->fetchAll();

        foreach ($array_without_indexpopularity_cover as $v_0) {
            if (is_file(PATH_UPLOAD . $v_0['cover'])) {
                $sub_dirname = 'admin' . DIRECTORY_SEPARATOR . 'indexpopularity' . DIRECTORY_SEPARATOR . date('Ymd');

                mkdir_p_v2(PATH_UPLOAD . $sub_dirname);

                //2018-06-28 Lion: 由於是 crontab 執行，故建立的目錄所有者會是 root 而發生權限問題，因此修改
                chown(PATH_UPLOAD . $sub_dirname, 'nginx');
                chgrp(PATH_UPLOAD . $sub_dirname, 'nginx');

                $sub_dest = $sub_dirname . DIRECTORY_SEPARATOR . pathinfo($v_0['cover'], PATHINFO_BASENAME);

                if (copy(PATH_UPLOAD . $v_0['cover'], PATH_UPLOAD . $sub_dest)) {
                    //2018-07-02 Lion: 由於是 crontab 執行，故複製的檔案所有者會是 root 而發生權限問題，因此修改
                    chown(PATH_UPLOAD . $sub_dirname, 'nginx');
                    chgrp(PATH_UPLOAD . $sub_dirname, 'nginx');

                    \Extension\aws\S3::upload(PATH_UPLOAD . $sub_dest);
                }

                (new \albumModel)
                    ->where([[[['album_id', '=', $v_0['album_id']]], 'and']])
                    ->edit([
                        'indexpopularity_cover' => $sub_dest
                    ]);
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function getAlbum2LikesList($user_id, $album_id, $limit = '0,4')
    {
        $list = [];

        $userModel = (new \userModel())
            ->column([
                'user.discuss',
                'user.name',
                'user.user_id',
            ])
            ->join([
                ['INNER JOIN', 'album2likes', 'ON album2likes.user_id = user.user_id AND album2likes.album_id = ' . (new \album2likesModel())->quote($album_id)]
            ])
            ->where([[[['user.act', '=', 'open']], 'and']])
            ->order([
                'album2likes.inserttime' => 'DESC'
            ])
            ->limit($limit)
            ->fetchAll();

        foreach ($userModel as $v_0) {
            $picture = PATH_STORAGE . \userModel::getPicture($v_0['user_id']);

            $list[] = [
                'user' => [
                    'discuss' => $v_0['discuss'] === 'open' ? true : false,
                    'is_follow' => \followModel::is_follow($user_id, $v_0['user_id']),
                    'name' => $v_0['name'],
                    'picture' => is_image($picture) ? $picture : null,
                    'user_id' => $v_0['user_id'],
                ]
            ];
        }

        return $list;
    }

    function hasLikes($user_id = null, $album_id = null)
    {
        $return = 0;

        if (!is_null($user_id) && !is_null($album_id)) {
            $m_likes = $this->column(['COUNT(1)'])->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->fetchColumn();
            $return = $m_likes;
        }

        return $return;
    }
}