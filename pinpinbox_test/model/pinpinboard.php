<?php

class pinpinboardModel extends Model
{
    protected $database = 'site';
    protected $table = 'pinpinboard';
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

    function ableToInsertPinPinBoard(array $param)
    {
        $result = 1;
        $message = null;

        /**
         * 必填
         */
        $text = isset($param['text']) ? $param['text'] : null;
        $type = isset($param['type']) ? $param['type'] : null;
        $type_id = isset($param['type_id']) ? $param['type_id'] : null;
        $user_id = isset($param['user_id']) ? $param['user_id'] : null;

        if ($text === null) {
            $result = 0;
            $message = 'Param error. "text" is required.';
            goto _return;
        } else {
            $text = trim(preg_replace('/\s+/u', ' ', $text));

            if ($text === '') {
                $result = 0;
                $message = 'Param error. "text" is required.';
                goto _return;
            } elseif (bytelen($text) > 127) {
                $result = 0;
                $message = 'Param error. The string of "text" is longer than 127.';
                goto _return;
            }
        }

        if ($type === null) {
            $result = 0;
            $message = 'Param error. "type" is required.';
            goto _return;
        } else {
            if (!in_array($type, ['album', 'template', 'user'])) {
                $result = 0;
                $message = 'Param error. "type" is an invalid value.';
                goto _return;
            }
        }

        if ($type_id === null) {
            $result = 0;
            $message = 'Param error. "type_id" is required.';
            goto _return;
        }

        if ($user_id === null) {
            $result = 0;
            $message = 'Param error. "user_id" is required.';
            goto _return;
        }

        switch ($type) {
            case 'album':
                $albumModel = (new \albumModel)
                    ->column([
                        'album.act album_act',
                        'user.act user_act',
                        'user.user_id',
                    ])
                    ->join([['INNER JOIN', 'user', 'USING(user_id)']])
                    ->where([
                        [[['album.album_id', '=', $type_id]], 'and']
                    ])
                    ->fetch();

                if (empty($albumModel)) {
                    $result = 0;
                    $message = _('Album does not exist.');
                    goto _return;
                } else {
                    switch ($albumModel['album_act']) {
                        case 'close':
                            if ($albumModel['user_id'] != $user_id) {
                                $count = (new cooperationModel)
                                    ->column(['COUNT(1)'])
                                    ->where([[[['user_id', '=', $user_id], ['`type`', '=', 'album'], ['type_id', '=', $type_id]], 'and']])
                                    ->fetchColumn();

                                if ($count == 0) {
                                    $result = 0;
                                    $message = _('您不能對該作品留言。');
                                    goto _return;
                                }
                            }
                            break;

                        case 'delete':
                            $result = 0;
                            $message = _('Album does not exist.');
                            goto _return;
                            break;
                    }

                    if ($albumModel['user_act'] === 'close') {
                        $result = 0;
                        $message = _('該作品的作者帳號已關閉。');
                        goto _return;
                    }
                }
                break;

            case 'template':
                list ($result_0, $message_0) = array_decode_return((new templateModel)->usable($type_id, $user_id));
                if ($result_0 != 1) {
                    $result = $result_0;
                    $message = $message_0;
                    goto _return;
                }
                break;

            case 'user':
                list ($result_0, $message_0) = array_decode_return((new userModel)->usable($type_id));
                if ($result_0 != 1) {
                    $result = $result_0;
                    $message = $message_0;
                    goto _return;
                }
                break;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function addComment($user_id, $text, $type, $type_id, $push_notice_ids)
    {
        //comment
        $result = 1;

        $inserttime = inserttime();
        $add = [
            'user_id' => $user_id,
            'type' => $type,
            'type_id' => $type_id,
            'text' => $text,
            'act' => 'open',
            'level' => '0',
            'parent_id' => 0,
            'inserttime' => $inserttime,
        ];
        $pinpinboard_id = $this->add($add);
        if (!$pinpinboard_id) {
            $result = 0;
        } else {

            $data = [
                'pinpinboard_id' => $pinpinboard_id,
                'inserttime' => $inserttime,
            ];

            //在作品 or 專區留言 ($type)
            switch ($type) {
                case 'album':
                    (new albumstatisticsModel)->replace([
                        'album_id' => $type_id,
                        'messageboard' => $this->countComment('album', $type_id) + 1
                    ]);

                    $pushTargetId = (new albumModel)->column(['user_id'])->where([[[['album_id', '=', $type_id]], 'and']])->fetchColumn();
                    $pushReturnTarget = 'albumqueue@messageboard';
                    $pushReturnTarget_id = $type_id;
                    if ($pushTargetId != $user_id) {
                        $SNSparam = Core::getSNSParams([
                            'trigger' => [
                                'user_id' => $user_id,
                                'type' => 'album',
                                'typeId' => $pushReturnTarget_id,
                                'refer' => 'addComment',
                            ],
                            'targetId' => $pushTargetId,
                            'typeOfSNS' => $pushReturnTarget,
                        ]);
                        (new \topicModel)->publish($user_id, 'user', $pushTargetId, $SNSparam['message'], $pushReturnTarget, $pushReturnTarget_id, $SNSparam);
                    }
                    break;

                case 'user':

                    (new userstatisticsModel)->replace([
                        'user_id' => $type_id,
                        'messageboard' => $this->countComment('user', $type_id) + 1
                    ]);

                    $pushReturnTarget = 'user@messageboard';
                    $pushReturnTarget_id = $type_id;
					$pushTargetId = null;
                    //在自己專區留言不進行推播
                    if ($user_id != $type_id) {
                        $pushTargetId = $type_id;
                        $SNSparam = Core::getSNSParams([
                            'trigger' => [
                                'user_id' => $user_id,
                                'type' => 'user',
                                'typeId' => $pushTargetId,
                                'refer' => 'addComment',
                            ],
                            'targetId' => $pushTargetId,
                            'typeOfSNS' => $pushReturnTarget,
                        ]);

                        (new \topicModel)->publish($user_id, 'user', $pushTargetId, $SNSparam['message'], $pushReturnTarget, $pushReturnTarget_id, $SNSparam);
                    }
                    break;
            }

            //mention notice
            if ($push_notice_ids) {
                foreach ($push_notice_ids as $k0 => $v0) {
                    //發文者($user_id) tag 自己($v0)不發推播
                    //$tag的對象為該專區或作品作者時不推播, 避免一次收到兩則推播
                    if ($user_id != $v0 && $pushTargetId != $v0) {
                        $SNSparam = Core::getSNSParams([
                            'trigger' => [
                                'user_id' => $user_id,
                                'type' => $type,
                                'typeId' => $type_id,
                                'refer' => 'mention',
                            ],
                            'targetId' => null,
                            'typeOfSNS' => 'user@messageboard',
                        ]);

                        (new \topicModel)->publish($user_id, 'user', $v0, $SNSparam['message'], $pushReturnTarget, $pushReturnTarget_id, $SNSparam);
                    }
                }
            }
        }

        return array_encode_return($result, $data);
    }

    function countComment($type, $type_id)
    {
        $count = 0;

        switch ($type) {
            case 'album':
                $count = (new albumstatisticsModel)->column(['messageboard'])->where([[[['album_id', '=', $type_id]], 'and']])->fetchColumn();
                break;

            case 'user':
                $count = (new userstatisticsModel)->column(['messageboard'])->where([[[['user_id', '=', $type_id]], 'and']])->fetchColumn();
                break;
        }

        return $count;
    }

    function deleteComment($pinpinboard_id, $type_id)
    {
        $return = $this->where([[[['pinpinboard_id', '=', $pinpinboard_id]], 'and']])->edit([
            'act' => 'delete',
        ]);

        $m_pinpinboard = $this->column(['`type`', 'type_id'])->where([[[['pinpinboard_id', '=', $pinpinboard_id]], 'and']])->fetch();

        switch ($m_pinpinboard['type']) {
            case 'album':
                (new albumstatisticsModel)->replace([
                    'album_id' => $m_pinpinboard['type_id'],
                    'messageboard' => $this->countComment('album', $type_id) - 1
                ]);
                break;

            case 'user':
                (new userstatisticsModel)->replace([
                    'user_id' => $m_pinpinboard['type_id'],
                    'messageboard' => $this->countComment('user', $type_id) - 1
                ]);
                break;
        }

        return $return;
    }

    function getComment($type, $type_id, $user_id)
    {
        $column = [
            'user.name user_name',
            'pinpinboard.*',
        ];
        $m_pinpinboard = (new pinpinboardModel)->column($column)->join([['left join', 'user', 'using(user_id)']])->where([[[['type', '=', $type], ['type_id', '=', $type_id], ['pinpinboard.act', '=', 'open']], 'and']])->order(['pinpinboard.inserttime' => 'desc'])->fetchAll();

        $a_pinpinboard = [];
        if (!empty($m_pinpinboard)) {
            foreach ($m_pinpinboard as $k0 => $v0) {
                $a_pinpinboard[] = [
                    'pinpinboard_id' => $v0['pinpinboard_id'],
                    'user_id' => $v0['user_id'],
                    'authorName' => $v0['user_name'],
                    'authorUrl' => (new userModel())->getCreativeUrl($v0['user_id']),
                    'time' => $v0['inserttime'],
                    'text' => $v0['text'],
                    'mentionText' => $this->textToMention($v0['text']),
                    'picture' => URL_STORAGE . (new userModel())->getPicture( ($v0['user_id'])),
                    'act' => ($user_id == $v0['user_id']) ? '<span class="delete_box" onclick="delComment(\'' . $type . '\', ' . $type_id . ', ' . $v0['pinpinboard_id'] . ')" aria-hidden="true">&times;</span><span class="sr-only"></span>' : null,
                ];
            }
        }
        return $a_pinpinboard;
    }

    function getList($type, $type_id, array $where = null, $limit = null)
    {
        $m_pinpinboard = $this
            ->column([
                'pinpinboard.text',
                'pinpinboard.inserttime',
                'user.name',
                'user.user_id',
            ])
            ->join([
                ['INNER JOIN', 'user', 'USING(user_id)'],
            ])
            ->where(array_merge([[[['pinpinboard.type', '=', $type], ['pinpinboard.type_id', '=', $type_id], ['pinpinboard.act', '=', 'open'], ['user.act', '=', 'open']], 'and']], (array)$where))
            ->order(['pinpinboard.inserttime' => 'DESC'])
            ->limit($limit)
            ->fetchAll();

        $data = [];

        foreach ($m_pinpinboard as $v0) {
            $data[] = [
                'pinpinboard' => [
                    'text' => $v0['text'],
                    'inserttime' => $v0['inserttime'],
                ],
                'user' => [
                    'user_id' => $v0['user_id'],
                    'name' => $v0['name'],
                ],
            ];
        }

        return $data;
    }

    function textToMention($text)
    {
        $str = $text;

        preg_match_all("/\[(.+?)\]/", $str, $match);

        if ($match[1]) {
            foreach ($match[1] as $k0 => $v0) {
                $mentionUserId = substr($v0, 0, strpos($v0, ':'));
                $mentionUserName = substr($v0, strpos($v0, ':') + 1, strlen($v0));
                $replace = '<span class="mention"><a class="message_tag" target="_blank" href="' . Core::get_creative_url($mentionUserId) . '">' . $mentionUserName . '</a></span>';
                $str = str_replace($match[0][$k0], $replace, $str);
            }
        }

        return $str;
    }
}