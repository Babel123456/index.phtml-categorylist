<?php

class cashflowController extends frontstageController
{
    function __construct()
    {
    }

    function index()
    {
        die;
    }

    /**
     * 金流交易發生時的調用地址
     * @throws Exception
     */
    function feedback()
    {
        $result = 1;
        $message = null;

        //2016-01-29 Lion: 因為有的金流方式不能將 order_id 隨 GET 帶回, 所以這裡最好以固定值運行, order_id 取得方式則依各金流實作
        $cashflow_id = empty($_GET['cashflow_id']) ? null : $_GET['cashflow_id'];

        if ($cashflow_id == null) {
            $result = 0;
            $message = 'Exception: Empty variable of "cashflow_id"';
            goto _return;
        }

        $a_feedback = Core::extension('cashflow', $cashflow_id)->feedback();

        switch ($a_feedback['result']) {
            case 0://金流失敗
            case 1://金流成功
            case 2://金流處理中
            case 3://金流退還
                $order_id = $a_feedback['data']['order_id'];

                if (empty($order_id)) {
                    $result = 0;
                    $message = 'Exception: Empty variable of "order_id"';
                    goto _return;
                }

                (new Model)->beginTransaction();

                $m_order = (new orderModel)->where([[[['order_id', '=', $order_id]], 'and']])->lock('for update')->fetch();

                if (empty($m_order)) {
                    (new Model)->rollBack();

                    $result = 0;
                    $message = 'Exception: Empty data of "order"';
                    goto _return;
                }

                if (0 == $a_feedback['result']) {//金流失敗
                    if (isset($a_feedback['message'])) $message = $a_feedback['message'];

                    $state = 'fail';
                } elseif (1 == $a_feedback['result']) {//金流成功
                    if ($m_order['fulfill'] === 'success') {
                        goto _return;
                    }

                    $state = 'success';

                    switch ($m_order['assets']) {
                        case 'usergrade':
                            $days = json_decode($m_order['assets_info'], true)['obtain'];

                            //取得使用者的usergradequeue內最後的身分時間
                            $user_grade_lastest = (new usergradequeueModel)->where([[[['user_id', '=', $m_order['user_id']]], 'and']])->order(['endtime' => 'desc'])->fetch();

                            //有usergradequeue紀錄
                            if (!empty($user_grade_lastest)) {
                                if ($user_grade_lastest['endtime'] < date('Y-m-d 00:00:00')) {
                                    //紀錄已過期，此筆訂單的身分從今天開始算
                                    $starttime = date('Y-m-d 00:00:00');
                                    $endtime = date('Y/m/d 23:59:59', strtotime('+' . $days . 'day'));
                                } else {
                                    //有未生效的身分，從未生效的身分之後開始算此筆訂單的身分時間
                                    $starttime = date('Y/m/d 00:00:00', strtotime($user_grade_lastest['endtime'] . "+1 day"));
                                    $endtime = date('Y/m/d 23:59:59', strtotime($user_grade_lastest['endtime'] . "+" . $days . "day"));
                                }
                            } else {
                                //沒有usergradequeue紀錄，此筆訂單的身分從今天開始算
                                $starttime = date('Y-m-d 00:00:00');
                                $endtime = date('Y/m/d 23:59:59', strtotime('+' . $days . 'day'));
                            }

                            $tmp0 = [
                                'user_id' => $m_order['user_id'],
                                'order_id' => $m_order['order_id'],
                                'grade' => json_decode($m_order['assets_info'], true)['assets_item'],
                                'total' => $m_order['total'],
                                'starttime' => $starttime,
                                'endtime' => $endtime,
                            ];

                            if (Core::set_usergrade($tmp0)) {
                                $fulfill = 'success';
                            } else {
                                $fulfill = 'fail';

                                $result = 0;
                                $message = 'Exception: Empty variable';
                                goto _return;
                            }
                            break;

                        case 'userpoint':
                            $tmp0 = [
                                'user_id' => $m_order['user_id'],
                                'trade' => 'order',
                                'trade_id' => $m_order['order_id'],
                                'platform' => $m_order['platform'],
                                'point' => json_decode($m_order['assets_info'], true)['obtain'],
                            ];

                            if (Core::set_userpoint($tmp0)) {
                                $fulfill = 'success';
                            } else {
                                $fulfill = 'fail';

                                $result = 0;
                                $message = 'Exception: Empty variable';
                                goto _return;
                            }
                            break;

                        default:
                            $fulfill = 'fail';

                            $result = 0;
                            $message = 'Exception: Unknown case';
                            goto _return;
                            break;
                    }
                } elseif (2 == $a_feedback['result']) {//金流處理中
                    $state = 'process';
                } elseif (3 == $a_feedback['result']) {//金流退還
                    //^要把 assets 還原嗎
                    $state = 'refund';
                }

                $edit = ['state' => $state];

                if (isset($fulfill)) {
                    $edit['fulfill'] = $fulfill;
                }

                if (isset($a_feedback['data']['return'])) {
                    ksort($a_feedback['data']['return']);
                    $edit['`return`'] = json_encode($a_feedback['data']['return']);
                }

                if (isset($a_feedback['data']['verify'])) {
                    $edit['verify'] = $a_feedback['data']['verify'];
                }

                if (isset($a_feedback['data']['verify_request'])) {
                    ksort($a_feedback['data']['verify_request']);
                    $edit['verify_request'] = json_encode($a_feedback['data']['verify_request']);
                }

                if (isset($a_feedback['data']['verify_return'])) {
                    ksort($a_feedback['data']['verify_return']);
                    $edit['verify_return'] = json_encode($a_feedback['data']['verify_return']);
                }

                (new orderModel)->where([[[['order_id', '=', $order_id]], 'and']])->edit($edit);

                (new Model)->commit();
                break;

            default:
                $result = 0;
                $message = 'Exception: Unknown case';
                goto _return;
                break;
        }

        _return:
        json_encode_return($result, $message);
    }

    /**
     * 金流操作結束後的頁面返回地址
     * @throws Exception
     */
    function receive()
    {
        $cashflow_id = empty($_GET['cashflow_id']) ? null : $_GET['cashflow_id'];
        if ($cashflow_id == null) {
            throw new Exception('Param error');
        }
        $user = parent::user_get();
        $a_receive = Core::extension('cashflow', $cashflow_id)->receive();
        $redirectAlbumId = (isset($_GET['redirectAlbumId']) && $_GET['redirectAlbumId'] != '') ? $_GET['redirectAlbumId'] : null;
		$redirectToAlbumContent = (!is_null($redirectAlbumId)) ? ['album_id'=>$redirectAlbumId, 'autobuy' => true,] : null ;

        switch ($a_receive['result']) {
            case 0://金流失敗
            case 1://金流成功
            case 2://金流處理中
            case 3://金流退還
                $m_order = Model('order')->where([[[['order_id', '=', $a_receive['data']['order_id']]], 'and']])->fetch();

                //金流失敗
                if (0 == $a_receive['result']) {
                    switch ($m_order['assets']) {
                        case 'usergrade':
                            $redirect = parent::url('user', 'grade');
                            $message = _('Transaction fail.');
                            break;

                        case 'userpoint':
                            $redirect = (is_null($redirectAlbumId)) ? parent::url('user', 'point') : parent::url('album', 'content', $redirectToAlbumContent) ;

                            $message = _('Transaction fail.');
                            break;

                        default:
                            throw new Exception('Unknown case');
                            break;
                    }

                    //除了制式訊息，並附上金流失敗的原因(過期、餘額不足等)
                    if (!empty($a_receive['message'])) $message .= '（' . $a_receive['message'] . '）';
                } //金流成功
                elseif (1 == $a_receive['result']) {
                    switch ($m_order['assets']) {
                        case 'usergrade':
                            $redirect = parent::url('user', 'grade');
                            $message = _('Transaction complete.');
                            break;

                        case 'userpoint':
							$redirect = (is_null($redirectAlbumId)) ? parent::url('user', 'point') : parent::url('album', 'content', $redirectToAlbumContent) ;
                            $message = _('Transaction complete.');

                            /**
                             *  0704 - 執行任務-首次購點
                             */
                            $data = [];
                            $result = false;
                            $task_for = 'firsttime_buy_point';
                            $user = parent::user_get();
                            $user_id = $user['user_id'];
                            $data = model('task')->doTask($task_for, $user_id, 'web');
                            if ($data['task']['result']) {
                                echo '<script>alert("' . $data['task']['message'] . '");</script>';
                            }
                            break;

                        default:
                            throw new Exception('Unknown case');
                            break;
                    }
                } //金流處理中
                elseif (2 == $a_receive['result']) {
                    switch ($m_order['assets']) {
                        case 'usergrade':
                            $redirect = parent::url('user', 'grade');
                            $message = _('Transaction is in process. System will guide you to the Member Center.');
                            break;

                        case 'userpoint':
							$redirect = (is_null($redirectAlbumId)) ? parent::url('user', 'point') : parent::url('album', 'content', $redirectToAlbumContent) ;
                            $message = _('Transaction is in process. System will guide you to the Member Center.');
                            break;

                        default:
                            throw new Exception('Unknown case');
                            break;
                    }
                } //金流退還
                elseif (3 == $a_receive['result']) {
                    switch ($m_order['assets']) {
                        case 'usergrade':
                            $redirect = parent::url('user', 'grade');
                            $message = _('Transaction refund. System will guide you to the Member Center.');
                            break;

                        case 'userpoint':
							$redirect = (is_null($redirectAlbumId)) ? parent::url('user', 'point') : parent::url('album', 'content', $redirectToAlbumContent) ;
                            $message = _('Transaction refund. System will guide you to the Member Center.');
                            break;

                        default:
                            throw new Exception('Unknown case');
                            break;
                    }
                }
                break;

            default:
                $message = _('Transaction fail, abnormal process.');
                break;
        }

		redirect($redirect, $message);
    }
}