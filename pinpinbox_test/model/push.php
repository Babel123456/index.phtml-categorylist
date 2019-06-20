<?php

class pushModel extends Model
{
    protected $database = 'site';
    protected $table = 'push';
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

    static function cleanup()
    {
        (new pushqueueModel)->where([[[['inserttime', '<', date('Y-m-d H:i:s', strtotime('-1 month'))]], 'and']])->delete();
    }

    /**
     * 2016-12-27 Lion: 此函式由 crontab 運行
     * @param array $param
     * @return array
     */
    function pushApp(array $param)
    {
        $result = 1;
        $message = null;

        /**
         * 2016-12-01 Lion:
         * @param int pushlog_id
         */
        $pushlog_id = isset($param['pushlog_id']) ? $param['pushlog_id'] : null;

        if ($pushlog_id === null) {
            $result = 0;
            $message = 'Param error. "pushlog_id" is required.';
            goto _return;
        }

        $m_pushlog = (new \pushlogModel)
            ->column([
                'message',
                'request',
                'target2type',
                'target2type_id',
                'url',
            ])
            ->where([[[['pushlog_id', '=', $pushlog_id], ['state', '=', 'pretreat']], 'and']])
            ->order(['inserttime' => 'asc'])
            ->limit('0,1')
            ->lock('for update')
            ->fetch();

        if ($m_pushlog == null) {
            $result = 0;
            $message = 'Data of "pushlog" does not exist.';
            goto _return;
        }

        $a_request = json_decode($m_pushlog['request'], true);

        $join = [['inner join', 'userstatistics', 'using(user_id)']];
        $where = [];
        $group = [];

        if (!empty($a_request['event_id'])) {
            $join[] = ['INNER JOIN', 'eventjoin', 'ON eventjoin.event_id = ' . (new \userModel)->quote($a_request['event_id'])];
            $join[] = ['INNER JOIN', 'album', 'ON album.album_id = eventjoin.album_id AND album.user_id = user.user_id'];
        }

        if ($a_request['hobby'] != null) {
            $join[] = ['INNER JOIN', 'hobby_user', 'ON hobby_user.user_id = user.user_id'];
            $where[] = [[['hobby_user.hobby_id', 'in', $a_request['hobby']]], 'and'];
        }

        if (!empty($a_request['event_id']) || !empty($a_request['hobby'])) {
            $group[] = 'user.user_id';
        }

        if ($a_request['user-account'] != null) $where[] = [[['user.account', '=', $a_request['user-account']]], 'and'];

        if ($a_request['user-cellphone'] != null) $where[] = [[['user.cellphone', '=', $a_request['user-cellphone']]], 'and'];

        if ($a_request['user-birthday-start'] != null && $a_request['user-birthday-end'] != null) {
            $where[] = [[['user.birthday', 'between', [$a_request['user-birthday-start'], $a_request['user-birthday-end']]]], 'and'];
        } elseif ($a_request['user-birthday-start'] != null) {
            $where[] = [[['user.birthday', '>=', $a_request['user-birthday-start']]], 'and'];
        } elseif ($a_request['user-birthday-end'] != null) {
            $where[] = [[['user.birthday', '<=', $a_request['user-birthday-end']]], 'and'];
        }

        if ($a_request['user-lastlogintime-start'] != null && $a_request['user-lastlogintime-end'] != null) {
            $where[] = [[['user.lastlogintime', 'between', [$a_request['user-lastlogintime-start'], $a_request['user-lastlogintime-end']]]], 'and'];
        } elseif ($a_request['user-lastlogintime-start'] != null) {
            $where[] = [[['user.lastlogintime', '>=', $a_request['user-lastlogintime-start']]], 'and'];
        } elseif ($a_request['user-lastlogintime-end'] != null) {
            $where[] = [[['user.lastlogintime', '<=', $a_request['user-lastlogintime-end']]], 'and'];
        }

        $m_user = (new \userModel)
            ->column(['user.user_id'])
            ->join($join)
            ->where($where)
            ->group($group)
            ->fetchAll();

        if ($m_user == null) {
            $result = 0;
            $message = 'No data to be processed.';
            goto _return;
        }

        //process
        (new \pushlogModel)
            ->where([[[['pushlog_id', '=', $pushlog_id]], 'and']])
            ->edit(['state' => 'process']);

        //aws sns
        set_time_limit(0);

        foreach (array_column($m_user, 'user_id') as $v0) {
            (new \topicModel)
                ->publish(
                    null,
                    'user',
                    $v0,
                    $m_pushlog['message'],
                    $m_pushlog['target2type'],
                    $m_pushlog['target2type_id'],
                    [
                        'url' => $m_pushlog['url']
                    ]
                );
        }

        $message = 'Push is succeeded. Execute [User] x ' . number_format(count($m_user)) . '.';

        (new \pushlogModel)
            ->where([[[['pushlog_id', '=', $pushlog_id]], 'and']])
            ->edit([
                'runtime' => runtime(),
                '`return`' => json_encode(array_encode_return($result, $message)),
                'state' => 'success',
            ]);

        _return:
        return array_encode_return($result, $message);
    }

    /**
     * 2016-12-27 Lion: 此函式由 crontab 運行
     */
    function pushWeb()
    {
        $m_pushqueue = (new pushqueueModel)
            ->column([
                'device.device_id',
                'device.browser',
                'device.token as endpoint',
                'device.enabled',
                'pushqueue.pushqueue_id',
            ])
            ->join([['inner join', 'device', 'USING(user_id)']])
            ->where([[[['pushqueue.send2web', '=', false], ['device.browser', 'in', ['chrome', 'firefox']], ['device.enabled', '=', true]], 'and']])
            ->order(['pushqueue.inserttime' => 'asc'])
            ->limit('0,10')
            ->fetchAll();

        if ($m_pushqueue) {
            $response = \Core\WebPush::send($m_pushqueue);

            $replace = array_map(function ($param) {
                return [
                    'device_id' => $param['device_id'],
                    'enabled' => $param['enabled'],
                    'modifyadmin_id' => 0,//2016-11-24 Lion: 由於是系統變更, 因此改為 0
                ];
            }, $response);

            (new deviceModel)->replace($replace);

            //標記發送狀態
            $pushqueue_idArray = array_column($m_pushqueue, 'pushqueue_id');

            $editByCase = [];
            $array = [];
            foreach ($pushqueue_idArray as $v_0) {
                $array['when'][] = ['pushqueue_id', '=', $v_0, true];
            }
            $array['else'] = 'send2web';
            $editByCase['send2web'] = $array;

            (new pushqueueModel)
                ->where([[[['pushqueue_id', 'IN', $pushqueue_idArray]], 'and']])
                ->editByCase($editByCase);
        }
    }
}