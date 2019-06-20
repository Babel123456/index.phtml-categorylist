<?php

class taskModel extends Model
{
    protected $database = 'site';
    protected $table = 'task';
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

    function ableToClearTask($task_for, $user_id, $platform, $type = null, $type_id = null)
    {
        $task = null;

        $type = (trim($type) === '') ? null : trim($type);
        $type_id = (trim($type_id) === '') ? null : $type_id;

        // 取得任務詳細資訊
        $m_task = (new taskModel)
            ->where([[[['act', '=', 'open'], ['task_for', '=', $task_for], ['platform', '=', $platform]], 'and']])
            ->fetchAll();

        $message = null;

        if (!$m_task) {
            $result = 0;
            $message = _('Task does not exist.');
            goto _return;
        }

        foreach ($m_task as $task) {
            $result = 1;

            //檢查任務有效日期
            if (time() < strtotime($task['starttime']) || time() > strtotime($task['endtime'])) {
                $result = 0;
                $message .= _('Task expire.');
                goto _jduge;
            }

            //檢查 "可執行的使用者條件(condition)"
            if (!self::task_condition($user_id, $task)) {
                $result = 0;
                $message .= _('不符合領取條件');
                goto _jduge;
            }

            //檢查 "領用限制(restriction)"
            if (!self::task_restriction($user_id, $task)) {
                $result = 0;
                $message .= _('達到任務領取限制');
                goto _jduge;
            }

            //檢查剩餘點數
            if (!self::task_upperlimit($task)) {
                $result = 0;
                $message .= _('剩餘點數不足發放');
                goto _jduge;
            }

            // 檢查是否"不在"黑名單
            if ($task['blacklist']) {
                $a_blacklist = explode(',', $task['blacklist']);
                if (in_array($type_id, $a_blacklist)) {
                    $result = 0;
                    $message .= _('黑名單條件');
                    goto _jduge;
                }
            }

            // 檢查是否"在"白名單
            if ($task['whitelist']) {
                $a_whitelist = explode(',', $task['whitelist']);
                if (!in_array($type_id, $a_whitelist)) {
                    $result = 0;
                    $message .= _('不在白名單條件');
                    goto _jduge;
                }
            }

            switch ($task_for) {
                case 'share_to_fb':
                case 'follow_user'://關注作者
                    if ($type === null || $type_id === null) {
                        $result = 0;
                        $message .= _('Param error.');
                        goto _jduge;
                    }

                    $count = (new \taskqueueModel)
                        ->column(['COUNT(1)'])
                        ->where([[[['user_id', '=', $user_id], ['task_for', '=', $task_for], ['`type`', '=', $type], ['type_id', '=', $type_id]], 'and']])
                        ->fetchColumn();

                    if ($count) {
                        $result = 2;

                        switch ($task_for) {
                            case 'share_to_fb':
                                $string = _('再次分享同一個作品不能取得 P 點。');
                                break;

                            case 'follow_user':
                                $string = _('再次關注同一個用戶不能取得 P 點。');
                                break;
                        }

                        $message .= $string;

                        goto _jduge;
                    }

                    break;
            }

            _jduge:
            if ($result == 1) break;

            //符合任務條件並已執行過相同任務, 則不可再次執行相同任務並跳脫
            if ($result == 2) {
                $result = 0;
                break;
            }
        }

        _return:
        return array_encode_return($result, $message, null, $task);
    }

    function checktaskcompleted($task_for, $user_id, $platform)
    {
        $result = 1;
        $message = _('The task has been completed.');

        $m_task = (new taskModel)->column(['task_for', 'restriction', 'restriction_value'])->where([[[['task_for', '=', $task_for], ['platform', '=', $platform], ['act', '=', 'open']], 'and']])->fetch();

        if (!$m_task) {
            $result = 0;
            $message = _('Task does not exist.');
            goto _return;
        }

        if ($this->task_restriction($user_id, $m_task)) {
            $result = 2;
            $message = _('The task is not yet complete.');
        }

        _return:
        return array_encode_return($result, $message);
    }

    function clear_task($task_for, $user_id, $platform, $param, $task)
    {
        $return = ['result' => false, 'message' => null];
        $feedback_msg = json_decode($task['feedback_message'], true);

        /**
         *  0711
         *  不同任務獎勵處理不同的function機制, 先在這邊取得task要用的reward function
         */
        switch ($task['reward']) {
            case 'point':
                $_func = 'set_userpoint';
                break;
            case 'grade':
                break;    // 0711 - 暫無功能
            default:
                throw new Exception('Unknown Task reward');
                break;
        }

        if (self::$_func($user_id, $task)) {
            if (self::set_taskqueue($user_id, $task, $param)) {
                $return = [
                    'result' => true,
                    'message' => $feedback_msg['success'],
                    'title' => $task['name'],
                    'event_url' => ($task['event_id']) ? \frontstageController::url('event', 'content', ['event_id' => $task['event_id']]) : null,
                ];

            } else {
                $return['message'] = _('[TaskQueue] occur exception, please contact us.');
            }
        } else {
            $return['message'] = _('[Set_Userpoint] occur exception, please contact us.');
        }

        return $return;
    }

    /**
     * 執行任務
     * v1.1 2018-02-01 調整回傳訊息格式
     * v1.0 2017-07-06  錯誤 / 已領訊息暫不顯示給user, 但會放在err_message提供Xhr回傳檢測用
     * @param string $task_for
     * @param number $user_id
     * @param string $platform
     * @param array $param
     * @return array $return
     */
    function doTask($task_for = null, $user_id = null, $platform = 'web', $param = [])
    {
        $return['task'] = ['result' => false, 'message' => '', 'err_message' => 'undoTask'];

        if (!is_null($task_for) || !is_null($user_id)) {

            $type = (isset($param['type'])) ? $param['type'] : null;
            $type_id = (isset($param['type_id'])) ? $param['type_id'] : null;

            /**
             *  180201 - Mars :  $task => 取回被執行的任務
             */
            list ($result, $message, , $task) = array_decode_return(self::ableToClearTask($task_for, $user_id, $platform, $type, $type_id));

            if ($result) {
                if (count($param) == 0) $param = ['type' => 'none', 'type_id' => 0];
                $clear_task = self::clear_task($task_for, $user_id, $platform, $param, $task);
                $return['task'] = $clear_task;
            } else {
                $return['task'] = [
                    'result' => $result,
                    'message' => '',
                    'err_message' => $message
                ];
            }
        }

        return $return;
    }

    //設置taskqueue
    function set_taskqueue($user_id, $task, $param)
    {
        $type = isset($param['type']) ? $param['type'] : 'none';
        $type_id = isset($param['type_id']) ? $param['type_id'] : 0;

        (new \taskqueueModel)
            ->add([
                'user_id' => $user_id,
                'task_for' => $task['task_for'],
                'task_id' => $task['task_id'],
                'platform' => $task['platform'],
                'reward' => $task['reward'],
                'reward_value' => $task['reward_value'],
                'type' => $type,
                'type_id' => $type_id,
            ]);

        return true;
    }

    //設置user_point
    function set_userpoint($user_id, $task)
    {
        $tmp0 = [
            'user_id' => $user_id,
            'trade' => 'task',
            'trade_id' => $task['task_id'],
            'platform' => $task['platform'],
            'point_free' => (int)$task['reward_value'],
        ];
        $return = (Core::set_userpoint($tmp0)) ? true : false;

        return $return;
    }

    //檢查條件
    function task_condition($user_id, $task)
    {
        $return = null;

        $m_user = Model('user')->where([[[['user_id', '=', $user_id]], 'and']])->fetch();

        if (empty($m_user)) {
            $return = false;
        } else {
            switch ($task['condition']) {
                /**
                 *  grade : 身分
                 *  level : 等級
                 *  unconditional : 不須領取條件
                 */
                case 'grade':
                    break;

                case 'level':
                    break;

                case 'unconditional':
                    $return = true;
                    break;

                default:

                    break;
            }
        }

        return $return;
    }

    //檢查限制
    function task_restriction($user_id, $task)
    {
        $return = null;

        switch ($task['restriction']) {
            /**
             * unlimit : 無限量
             * personal : 個人領取次數
             * total : 總共領取次數
             */
            case 'personal':
                /**
                 * 限制的數量是以 "任務內容(task_for)" 來判斷, 已完成數量(user_cleared_task_num) = web + google + apple
                 * 所以只要 task['restriction_value'] >=  user_cleared_task_num 即視為完成任務
                 * task['restriction_value'] => 次 / 人
                 */

                //取得已經完成次數
                $where = [[[['user_id', '=', $user_id], ['taskqueue.task_for', '=', $task['task_for']], ['task.act', '=', 'open']], 'and']];
                $join = [['left join', 'task', 'on task.task_id = taskqueue.task_id']];
                $user_cleared_task_num = Model('taskqueue')->column(['COUNT(1)'])->where($where)->join($join)->fetchColumn();
                $return = ($user_cleared_task_num < $task['restriction_value']) ? true : false;

                break;

            case 'total':
                $return = true;
                break;

            case 'unlimit':
                $return = true;
                break;

            default:
                # code...
                break;
        }

        return $return;
    }

    //檢查發放上限
    function task_upperlimit($task)
    {
        $result = true;

        if ($task['upperlimit'] != 0) {
            $upper_percent = 90; //達到上限的百分比
            $where = [[[['task_id', '=', $task['task_id']]], 'and']];
            $released = Model('taskqueue')->column(['SUM(`reward_value`)'])->where($where)->fetchColumn();
            $last_mail_time = Model('taskqueue')->column(['inserttime'])->where($where)->order(['inserttime' => 'desc'])->fetchAll();
            $last_mail_stamp = (count($last_mail_time) > 1) ? strtotime('+1 hour', strtotime($last_mail_time[1]['inserttime'])) : time();

            //超過上限的 upper_percent % 並且距離上一封警告信件超過一小時
            if ($released >= ceil(($task['upperlimit'] * $upper_percent) / 100) && $last_mail_stamp < time()) {
                $m_admin = Model('admin')->column(['account', 'email', 'name'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
                foreach ($m_admin as $k0 => $v0) {
                    $a_mail_to[] = $v0['email'];
                }

                if (!empty($a_mail_to)) {
                    $tmp1 = array(
                        _('Task ID') . '：' . $task['task_id'],
                        _('Task Name') . '：' . $task['name'],
                        _('Platform') . '：' . $task['platform'],
                        _('Task For') . '：' . $task['task_for'],
                        _('Upperlimit') . '：' . $task['upperlimit'],
                        _('Released Point') . '：' . $released,
                    );
                    $body = implode('<br>', $tmp1);
                    email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $a_mail_to, _('pinpinbox - 任務點數發放上限提示'), $body);
                }
            }

            //檢查剩餘點數
            $result = (($task['upperlimit'] - $released) >= $task['reward_value']) ? true : false;
        }

        return $result;
    }

    function usable($task_for, $platform)
    {
        $result = 1;
        $message = null;

        if (empty($task_for)) {
            $result = 0;
            $message = 'Param error. "task_for" is required.';
            goto _return;
        }

        if (empty($platform)) {
            $result = 0;
            $message = 'Param error. "platform" is required.';
            goto _return;
        } else {
            if (!in_array($platform, ['apple', 'google'])) {
                $result = 0;
                $message = 'Param error. "platform" is an invalid value.';
                goto _return;
            }
        }

        $m_task = (new taskModel)
            ->column([
                'act',
                'endtime',
                'starttime',
            ])
            ->where([[[
                ['task_for', '=', $task_for],
                ['platform', '=', $platform],
            ], 'and']])
            ->fetchAll();

        if (empty($m_task)) {
            $result = 0;
            $message = _('"任務"資料不存在。');
            goto _return;
        } else {
            if (!in_array('open', array_column($m_task, 'act'))) {//2017-07-26 Lion: 由於 app 僅能以 task_for 向 server 溝通，因此判斷全部同性質任務的 act
                $result = 0;
                $message = _('"任務"狀態為關閉。');
                goto _return;
            }

            $a_task = array_multiple_search($m_task, 'act', 'open')[0];

            $time = time();

            if ($time < strtotime($a_task['starttime']) || $time > strtotime($a_task['endtime'])) {
                $result = 0;
                $message = _('Task expire.');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }
}