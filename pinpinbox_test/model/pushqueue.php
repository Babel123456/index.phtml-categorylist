<?php

class pushqueueModel extends Model
{
    protected $database = 'site';
    protected $table = 'pushqueue';
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

    static function getByUserId($user_id, $limit = '0,8')
    {
        $data = [];

        $Model_pushqueue = (new \pushqueueModel)
            ->column([
                'album.template_id albumXtemplate_id',//2016-11-07 Lion: 由於 template 沒有為 0 的資料存在, 如果取 template.template_id 會為 null, 因此取 album.template_id
                'cooperation.identity',
                'pushqueue.inserttime',
                'pushqueue.message',
                'pushqueue.target2type',
                'pushqueue.target2type_id',
                'pushqueue.url',
                'pushqueue.user_id',
                'template.template_id',
            ])
            ->join([
                ['LEFT JOIN', 'cooperation', 'ON cooperation.user_id = pushqueue.user_id AND CONCAT(cooperation.type, \'cooperation\') = pushqueue.target2type AND cooperation.type_id = pushqueue.target2type_id'],
                ['LEFT JOIN', 'album', 'ON pushqueue.target2type = \'albumcooperation\' AND album.album_id = pushqueue.target2type_id'],
                ['LEFT JOIN', 'template', 'ON pushqueue.target2type = \'templatecooperation\' AND template.template_id = pushqueue.target2type_id'],
            ])
            ->where([[[['pushqueue.user_id', '=', $user_id]], 'and']])
            ->order(['pushqueue.inserttime' => 'DESC'])
            ->limit($limit)
            ->fetchAll();

        if ($Model_pushqueue) {
            foreach ($Model_pushqueue as $v0) {
                $a_cooperation = null;
                $a_template = null;

                if ($v0['identity']) {
                    $a_cooperation = [
                        'identity' => $v0['identity'],
                    ];
                }

                switch ($v0['target2type']) {
                    case 'albumcooperation':
                        if ($v0['albumXtemplate_id'] !== null) {//2016-11-07 Lion: 可能為 0, 表示快速建立
                            $a_template = [
                                'template_id' => $v0['albumXtemplate_id']
                            ];
                        }
                        break;

                    case 'templatecooperation':
                        if ($v0['template_id']) {
                            $a_template = [
                                'template_id' => $v0['template_id']
                            ];
                        }
                        break;
                }

                $data[] = [
                    'cooperation' => $a_cooperation,
                    'pushqueue' => [
                        'inserttime' => $v0['inserttime'],
                        'message' => $v0['message'],
                        'target2type' => $v0['target2type'],
                        'target2type_id' => $v0['target2type_id'],
                        'url' => $v0['url'],
                        'user_id' => $v0['user_id'],
                    ],
                    'template' => $a_template,
                ];
            }

            //2017-11-01 Lion: 如果在 crontab 執行發送之前就開啟通知列表, 也把 send2web、receive5web 寫為 true
            (new \pushqueueModel)
                ->where([[[['user_id', '=', $user_id]], 'and']])
                ->edit([
                    'receive5web' => true,
                    'send2web' => true,
                    'viewed' => true
                ]);
        }

        return $data;
    }

    static function hasUnviewed($user_id)
    {
        $count = (new \pushqueueModel)
            ->column(['COUNT(1)'])
            ->where([[[['user_id', '=', $user_id], ['viewed', '=', false]], 'and']])
            ->fetchColumn();

        return $count > 0 ? true : false;
    }
}