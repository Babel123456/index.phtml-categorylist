<?php

class eventModel extends Model
{
    protected $database = 'site';
    protected $table = 'event';
    protected $memcache = 'site';
    protected $join_table = ['album', 'eventjoin', 'event_templatejoin', 'event_companyjoin', 'eventstatistics'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['adModel'],
            ['albumModel'],
            ['eventjoinModel'],
            ['eventstatisticsModel'],
            ['event_templatejoinModel'],
            ['event_event_companyjoinModel'],
        ];
    }

    function getJoinableByTemplate($template_id)
    {
        $column = [
            'event.event_id',
            'event.name event_name',
            'event_templatejoin.template_id',
        ];
        $where = [[[
            [Model('event')->quote(date('Y-m-d H:i:s', time())), 'between', ['event.starttime', 'event.endtime'], false],
            ['event.act', '=', 'open'],
        ], 'and']];
        $m_event = Model('event')->column($column)->join([['left join', 'event_templatejoin', 'using(event_id)']])->where($where)->fetchAll();

        $data = [];

        //為綁定版型
        if (in_array($template_id, array_column($m_event, 'template_id'))) {
            foreach ($m_event as $v0) {
                if ($v0['template_id'] == $template_id) {
                    $data[] = [
                        'event' => [
                            'event_id' => $v0['event_id'],
                            'name' => $v0['event_name'],
                        ]
                    ];
                }
            }
        } //不為綁定版型
        else {
            foreach ($m_event as $v0) {
                if ($v0['template_id'] === null) {
                    $data[] = [
                        'event' => [
                            'event_id' => $v0['event_id'],
                            'name' => $v0['event_name'],
                        ]
                    ];
                }
            }
        }

        return $data;
    }

    function getPinGirl($user_id, $starttime, $endtime)
    {
        $m_album = null;

        $column = ['album.user_id create_user_id', 'SUM(`exchange`.`point`+`exchange`.`point_free`) `total`', '`user`.`name` AS `user_name`',];

        $join = [['left join', 'albumqueue', 'using(album_id)'], ['left join', 'exchange', 'ON `albumqueue`.`exchange_id` = `exchange`.exchange_id'],
            ['left join', 'user', 'ON `album`.`user_id` = `user`.user_id']];

        $where = [[[
            ['album.user_id', 'in', $user_id], ['album.act', '=', 'open'], ['album.state', '=', 'success'],
            ['albumqueue.modifytime', '>', $starttime], ['albumqueue.modifytime', '<', $endtime],
        ], 'and']];

        $group = ['create_user_id'];
        $order = ['total' => 'desc'];

        $m_album = (new \albumModel())->column($column)->join($join)->where($where)->order($order)->group($group)->fetchAll();

        return $m_album;
    }

    static function getPopularity($event_id)
    {
        return (int)
            (new \eventjoinModel)->column(['SUM(`count`)'])->where([[[['event_id', '=', $event_id]], 'and']])->fetchColumn() +
            (new \eventstatisticsModel)->column(['viewed'])->where([[[['event_id', '=', $event_id]], 'and']])->fetchColumn() +
            (new \eventjoinModel)->column(['SUM(albumstatistics.viewed)'])->join([['left join', 'albumstatistics', 'using(`album_id`)']])->where([[[['event_id', '=', $event_id]], 'and']])->fetchColumn();
    }

    /**
     * 取得用戶於活動的剩餘可用票數
     * @param $event_id
     * @param $user_id
     * @return int
     */
    static function getVoteLeft($event_id, $user_id)
    {
        //取得可投票數
        $vote = (new \eventModel())
            ->column(['vote'])
            ->where([[[['event_id', '=', $event_id]], 'and']])
            ->fetchColumn();

        //取得已投票數
        $voteCount = (new \eventvoteModel())
            ->column(['COUNT(1)'])
            ->where([[[['event_id', '=', $event_id], ['user_id', '=', $user_id], ['inserttime', '>=', date('Y-m-d 00:00:00')]], 'and']])
            ->fetchColumn();

        return (int)($vote - $voteCount);
    }

    //2017-10-11 Lion: 預計取代 eventModel::is_vote
    static function hasVoted($event_id, $album_id, $user_id)
    {
        $count = (new \eventvoteModel)
            ->column(['COUNT(1)'])
            ->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id], ['user_id', '=', $user_id], ['inserttime', '>=', date('Y-m-d 00:00:00')]], 'and']])
            ->fetchColumn();

        return ($count) ? true : false;
    }

    function is_contribution($event_id, $album_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($event_id)) $event_id = null;
        if (empty($album_id)) $album_id = null;

        if ($event_id === null || $album_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        $m_eventjoin = Model('eventjoin')->column(['count(1)'])->where([[[['eventjoin.event_id', '=', $event_id], ['eventjoin.album_id', '=', $album_id]], 'and']])->fetchColumn();

        $contributionstatus = ($m_eventjoin) ? true : false;

        $data = [
            'event' => [
                'contributionstatus' => $contributionstatus
            ],
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    //2017-10-11 Lion: 預計以 eventModel::hasVoted 取代
    function is_vote($event_id, $album_id, $user_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($event_id)) $event_id = null;
        if (empty($album_id)) $album_id = null;
        if (empty($user_id)) $user_id = null;

        if ($event_id === null || $album_id === null || $user_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        $m_eventvote = Model('eventvote')->column(['count(1)'])->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])->fetchColumn();

        $votestatus = ($m_eventvote) ? true : false;

        $data = [
            'event' => [
                'votestatus' => $votestatus
            ],
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function menu(array $where = null, array $order = null, $limit = null)
    {
        $column = [
            'event.event_id',
            'event.name event_name',
        ];

        $where = array_merge([[[['event.act', '=', 'open'], ['event.starttime', '<=', date('Y-m-d H:i:s')]], 'and']], (array)$where);

        return Model('event')->column($column)->where($where)->order($order)->limit($limit)->fetchAll();
    }

    function switchStatusOfContribution($event_id, $album_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($event_id)) $event_id = null;
        if (empty($album_id)) $album_id = null;

        if ($event_id === null || $album_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        list($result1, $message1, $redirect1, $data1) = array_decode_return(Model('event')->usable($event_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        list($result2, $message2, $redirect2, $data2) = array_decode_return(Model('album')->usable('album', $album_id));
        if ($result2 != 1) {
            $result = $result2;
            $message = $message2;
            goto _return;
        }

        list($result3, $message3, $redirect3, $data3) = array_decode_return(Model('event')->is_contribution($event_id, $album_id));
        if ($result3 != 1) {
            $result = $result3;
            $message = $message3;
            goto _return;
        }

        //處理退稿
        if ($data3['event']['contributionstatus']) {
            Model('eventjoin')->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->delete();
            Model('eventvote')->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->delete();

            $contributionstatus = false;
        } //處理投稿
        else {
            $join = [
                ['inner join', 'album', 'using(album_id)'],
            ];
            $where = [[[
                ['eventjoin.event_id', '=', $event_id],
                ['album.user_id', '=', $data2['album']['user_id']],
                ['album.act', '=', 'open'],
                ['album.zipped', '=', true],
            ], 'and']];
            $m_eventjoin = Model('eventjoin')->column(['count(1)'])->join($join)->where($where)->fetchColumn();

            if ($data1['event']['contribution'] <= $m_eventjoin) {
                $result = 0;
                $message = _('The quantity you submitted is beyond the limit.');
                goto _return;
            }

            Model('eventjoin')->add(['event_id' => $event_id, 'album_id' => $album_id]);

            $contributionstatus = true;
        }

        $data = [
            'event' => [
                'contributionstatus' => $contributionstatus
            ],
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function switchStatusOfVote($event_id, $album_id, $user_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($event_id)) $event_id = null;
        if (empty($album_id)) $album_id = null;
        if (empty($user_id)) $user_id = null;

        if ($event_id === null || $album_id === null || $user_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        list($result1, $message1, $redirect1, $data1) = array_decode_return(Model('event')->usable($event_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        list($result2, $message2, $redirect2, $data2) = array_decode_return(Model('album')->usable('album', $album_id));
        if ($result2 != 1) {
            $result = $result2;
            $message = $message2;
            goto _return;
        }

        list($result3, $message3, $redirect3, $data3) = array_decode_return(Model('user')->usable($user_id));
        if ($result3 != 1) {
            $result = $result3;
            $message = $message3;
            goto _return;
        }

        list($result4, $message4, $redirect4, $data4) = array_decode_return(Model('event')->is_vote($event_id, $album_id, $user_id));
        if ($result4 != 1) {
            $result = $result4;
            $message = $message4;
            goto _return;
        }

        //處理退票
        if ($data4['event']['votestatus']) {
            Model('eventvote')->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id], ['user_id', '=', $user_id]], 'and']])->delete();

            Model('eventjoin')->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->edit(['`count`' => ['count - 1', false]]);

            $votestatus = false;
        } //處理投票
        else {
            $m_eventvote = Model('eventvote')->column(['count(1)'])->where([[[['event_id', '=', $event_id], ['user_id', '=', $user_id]], 'and']])->fetchColumn();

            if ($data1['event']['vote'] <= $m_eventvote) {
                $result = 0;
                $message = _('Number of votes exceeds the limit.');
                goto _return;
            }

            $add = [
                'event_id' => $event_id,
                'album_id' => $album_id,
                'user_id' => $user_id,
            ];
            Model('eventvote')->add($add);

            Model('eventjoin')->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->edit(['`count`' => ['count + 1', false]]);

            $votestatus = true;
        }

        $count = Model('eventjoin')->column(['`count`'])->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])->fetchColumn();

        $data = [
            'event' => [
                'votestatus' => $votestatus
            ],
            'eventjoin' => [
                'count' => empty($count) ? 0 : $count,
            ],
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function usable($event_id, $refer = null)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($event_id)) $event_id = null;

        if ($event_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        $m_event = (new \eventModel)
            ->column([
                'act',
                'contribute_endtime',
                'contribute_starttime',
                'contribution',
                'endtime',
                'exchange_page',
                'image_750x630',
                'image_960x540',
                'name',
                'prefix_text',
                'starttime',
                'title',
                'vote',
                'vote_endtime',
                'vote_starttime',
            ])
            ->where([[[['event_id', '=', $event_id]], 'and']])
            ->fetch();

        if (empty($m_event)) {
            $result = 0;
            $message = _('Event does not exist.');
            goto _return;
        } else {
            if ($m_event['act'] === 'close') {
                $result = 0;
                $message = _('Event is closed.');
                goto _return;
            } elseif ($m_event['act'] === 'none') {
                $result = 0;
                $message = _('[Event] occur exception, please contact us.');
                goto _return;
            } elseif ($m_event['endtime'] < date('Y-m-d H:i:s', time())) {
                /**
                 *  160909 api呼叫時若為"活動結束"仍需要回傳資訊(issue#932), 故以$refer為參考在api呼叫時不中斷查詢活動資料 - Mars
                 */
                if ($refer != 'api') {
                    $result = 0;
                    $message = _('The event has ended.');
                    goto _return;
                } else {
                    $result = 2;
                }
            }
        }

        $m_event_templatejoin = (new \event_templatejoinModel)
            ->column(['event_templatejoin.template_id'])
            ->join([['inner join', 'template', 'using(template_id)']])
            ->where([[[['event_templatejoin.event_id', '=', $event_id], ['template.state', '=', 'success'], ['template.act', '=', 'open']], 'and']])
            ->fetchAll();

        //2017-02-13 Lion: 如果 event 可投稿，但沒有設定關聯 template，則以 template = 0 回傳
        if ($m_event['contribution'] > 0 && empty($m_event_templatejoin)) {
            $m_event_templatejoin[]['template_id'] = 0;
        }

        $data = [
            'event' => [
                'contribute_endtime' => $m_event['contribute_endtime'],
                'contribute_starttime' => $m_event['contribute_starttime'],
                'contribution' => $m_event['contribution'],
                'endtime' => $m_event['endtime'],
                'exchange_page' => $m_event['exchange_page'],
                'image_750x630' => $m_event['image_750x630'],
                'image_960x540' => $m_event['image_960x540'],
                'name' => $m_event['name'],
                'popularity' => \eventModel::getPopularity($event_id),
                'prefix_text' => $m_event['prefix_text'],
                'starttime' => $m_event['starttime'],
                'title' => $m_event['title'],
                'vote' => $m_event['vote'],
                'vote_endtime' => $m_event['vote_endtime'],
                'vote_starttime' => $m_event['vote_starttime'],
            ],
            'event_templatejoin' => array_column($m_event_templatejoin, 'template_id'),
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    function usable_v2($event_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        if (empty($event_id)) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "event_id" is required.';
            goto _return;
        }

        $eventModel = (new \eventModel)
            ->column([
                'act',
                'endtime',
                'starttime',
            ])
            ->where([[[['event_id', '=', $event_id]], 'and']])
            ->fetch();

        if (empty($eventModel)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('Event does not exist.');
            goto _return;
        } else {
            if ($eventModel['act'] === 'close') {
                $result = \Lib\Result::USER_ERROR;
                $message = _('Event is closed.');
                goto _return;
            } elseif ($eventModel['starttime'] > date('Y-m-d H:i:s', time())) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('The event has not yet started.');
                goto _return;
            } elseif ($eventModel['endtime'] < date('Y-m-d H:i:s', time())) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('The event has ended.');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }
}