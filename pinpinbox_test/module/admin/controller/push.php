<?php

class pushController extends backstageController
{
    function __construct()
    {
    }

    function index()
    {
        $Html = new Lib\html();

        list($html, $js) = $Html->grid();
        parent::$data['index'] = $html;
        $Html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function form()
    {
        if (is_ajax()) {
            ksort($_POST['customize']);

            $a_customize = json_encode($_POST['customize']);
            $message = $_POST['message'];
            $mode = isset($_POST['mode']) ? $_POST['mode'] : null;
            $remark = $_POST['remark'];
            $target2type = null;
            $target2type_id = null;
            $url = null;

            switch ($mode) {
                case 'target2type':
                    if (isset($_POST['target2type'])) $target2type = $_POST['target2type'];
                    if (isset($_POST['target2type_id'])) $target2type_id = $_POST['target2type_id'];
                    break;

                case 'url':
                    if (isset($_POST['url'])) $url = $_POST['url'];
                    break;
            }

            switch ($_GET['act']) {
                //新增
                case 'add':
                    if ((new \pushModel)->column(['count(1)'])->where([[[['customize', '=', $a_customize]], 'and']])->fetchColumn()) {
                        json_encode_return(0, _('Data already exists by : ') . _('Customize'));
                    }

                    (new \pushModel)->add([
                        'customize' => $a_customize,
                        'message' => $message,
                        'mode' => $mode,
                        'remark' => $remark,
                        'target2type' => $target2type,
                        'target2type_id' => $target2type_id,
                        'url' => $url,
                        'inserttime' => inserttime(),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    if ((new \pushModel)->column(['count(1)'])->where([[[[M_CLASS . '_id', '!=', $M_CLASS_id], ['customize', '=', $a_customize]], 'and']])->fetchColumn()) {
                        json_encode_return(0, _('Data already exists by : ') . _('Customize'));
                    }

                    (new \pushModel)
                        ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])
                        ->edit([
                            'customize' => $a_customize,
                            'message' => $message,
                            'mode' => $mode,
                            'remark' => $remark,
                            'target2type' => $target2type,
                            'target2type_id' => $target2type_id,
                            'url' => $url,
                            'modifyadmin_id' => adminModel::getSession()['admin_id'],
                        ]);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        $Html = new Lib\html();

        //初始值-form
        $M_CLASS_id = null;
        $remark = null;
        $a_customize = [];
        $target2type = null;
        $target2type_id = null;
        $url = null;
        $message = null;
        $mode = null;
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;

        //form
        $column = [];
        $extra = null;

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'add']);
                break;

            //修改
            case 'edit':
                $M_CLASS_id = $_GET[M_CLASS . '_id'];

                $Model_push = (new \pushModel)
                    ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])
                    ->fetch();

                $remark = htmlspecialchars($Model_push['remark']);
                $a_customize = json_decode($Model_push['customize'], true);
                $message = htmlspecialchars($Model_push['message']);
                $target2type = $Model_push['target2type'];
                $target2type_id = $Model_push['target2type_id'];
                $url = $Model_push['url'];
                $inserttime = $Model_push['inserttime'];
                $mode = $Model_push['mode'];
                $modifytime = $Model_push['modifytime'];
                $modifyadmin_name = adminModel::getOne($Model_push['modifyadmin_id'])['name'];

                list($html, $js) = $Html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                $Html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'edit']);
                break;
        }

        if (isset($_POST['submit_act'])) {
            switch ($_POST['submit_act']) {
                case 1:
                    $message = htmlspecialchars($_POST['message']);
                    $remark = htmlspecialchars($_POST['remark']);
                    $a_customize = [
                        'event_id' => isset($_POST['event_id']) ? $_POST['event_id'] : null,
                        'user-account' => $_POST['user-account'],
                        'user-cellphone' => $_POST['user-cellphone'],
                        'user-birthday-start' => $_POST['user-birthday-start'],
                        'user-birthday-end' => $_POST['user-birthday-end'],
                        'user-lastlogintime-start' => $_POST['user-lastlogintime-start'],
                        'user-lastlogintime-end' => $_POST['user-lastlogintime-end'],
                        'hobby' => isset($_POST['hobby']) ? $_POST['hobby'] : null,
                    ];
                    break;
            }
        }

        $filter = [];
        $data = ['case' => 'user'];
        if ($a_customize) {
            if (!empty($a_customize['event_id'])) $data['event_id'] = $a_customize['event_id'];

            if ($a_customize['user-account'] != null) $filter[] = ['field' => 'account', 'operator' => 'eq', 'value' => $a_customize['user-account']];

            if ($a_customize['user-cellphone'] != null) $filter[] = ['field' => 'cellphone', 'operator' => 'eq', 'value' => $a_customize['user-cellphone']];

            if ($a_customize['user-birthday-start'] != null && $a_customize['user-birthday-end'] != null) {
                $filter[] = [
                    'filters' => [
                        ['field' => 'birthday', 'operator' => 'gte', 'value' => $a_customize['user-birthday-start']],
                        ['field' => 'birthday', 'operator' => 'lte', 'value' => $a_customize['user-birthday-end']],
                    ],
                    'logic' => 'and',
                ];
            } elseif ($a_customize['user-birthday-start'] != null) {
                $filter[] = ['field' => 'birthday', 'operator' => 'gte', 'value' => $a_customize['user-birthday-start']];
            } elseif ($a_customize['user-birthday-end'] != null) {
                $filter[] = ['field' => 'birthday', 'operator' => 'lte', 'value' => $a_customize['user-birthday-end']];
            }

            if ($a_customize['user-lastlogintime-start'] != null && $a_customize['user-lastlogintime-end'] != null) {
                $filter[] = [
                    'filters' => [
                        ['field' => 'lastlogintime', 'operator' => 'gte', 'value' => $a_customize['user-lastlogintime-start']],
                        ['field' => 'lastlogintime', 'operator' => 'lte', 'value' => $a_customize['user-lastlogintime-end']],
                    ],
                    'logic' => 'and',
                ];
            } elseif ($a_customize['user-lastlogintime-start'] != null) {
                $filter[] = ['field' => 'lastlogintime', 'operator' => 'gte', 'value' => $a_customize['user-lastlogintime-start']];
            } elseif ($a_customize['user-lastlogintime-end'] != null) {
                $filter[] = ['field' => 'lastlogintime', 'operator' => 'lte', 'value' => $a_customize['user-lastlogintime-end']];
            }

            if ($a_customize['hobby'] != null) $data['hobby'] = $a_customize['hobby'];
        }
        parent::$data['filter'] = json_encode($filter);
        parent::$data['data'] = json_encode($data);

        /**
         * List
         */
        $column1 = [];
        $extra1 = null;

        list($html, $js) = $Html->grid('user-grid');
        $column1[] = ['key' => _('User'), 'value' => $html];
        $Html->set_js($js);

        /**
         * customize
         */
        $column2 = [];
        $extra2 = null;
        //user-account
        list($html, $js) = $Html->text('id="user-account" name="user-account" data-group="customize" value="' . (isset($a_customize['user-account']) ? $a_customize['user-account'] : null) . '" size="64" maxlength="64"');
        $column2[] = ['key' => _('Account'), 'value' => $html];
        $Html->set_js($js);

        //user-cellphone
        list($html, $js) = $Html->text('id="user-cellphone" name="user-cellphone" data-group="customize" value="' . (isset($a_customize['user-cellphone']) ? $a_customize['user-cellphone'] : null) . '" size="32" maxlength="32"');
        $column2[] = ['key' => _('Cellphone'), 'value' => $html];
        $Html->set_js($js);

        //user-birthday
        list($html0, $js0) = $Html->date('id="user-birthday-start" name="user-birthday-start" data-group="customize" value="' . (isset($a_customize['user-birthday-start']) ? $a_customize['user-birthday-start'] : null) . '"');
        list($html1, $js1) = $Html->date('id="user-birthday-end" name="user-birthday-end" data-group="customize" value="' . (isset($a_customize['user-birthday-end']) ? $a_customize['user-birthday-end'] : null) . '"');
        $column2[] = ['key' => _('Birthday'), 'value' => 'Start : ' . $html0 . '&emsp;~&emsp;End : ' . $html1];
        $Html->set_js($js0 . $js1);

        //user.lastlogintime
        list($html0, $js0) = $Html->datetime('id="user-lastlogintime-start" name="user-lastlogintime-start" data-group="customize" value="' . (isset($a_customize['user-lastlogintime-start']) ? $a_customize['user-lastlogintime-start'] : null) . '"');
        list($html1, $js1) = $Html->datetime('id="user-lastlogintime-end" name="user-lastlogintime-end" data-group="customize" value="' . (isset($a_customize['user-lastlogintime-end']) ? $a_customize['user-lastlogintime-end'] : null) . '"');
        $column2[] = ['key' => _('Last Login Time'), 'value' => 'Start : ' . $html0 . '&emsp;~&emsp;End : ' . $html1];
        $Html->set_js($js0 . $js1);

        //hobby
        $m_hobby = (new \hobbyModel)->column(['hobby_id', 'name'])->order(['hobby_id' => 'asc'])->fetchAll();
        $s_hobby = [];
        foreach ($m_hobby as $v0) {
            $s_hobby[] = [
                'value' => $v0['hobby_id'],
                'text' => $v0['hobby_id'] . ' - ' . $v0['name'],
            ];
        }
        list($html, $js) = $Html->selectKit(['id' => 'hobby', 'name' => 'hobby[]', 'multiple' => true, 'data-group' => 'customize'], $s_hobby, (isset($a_customize['hobby']) ? $a_customize['hobby'] : null));
        $column2[] = ['key' => _('Hobby'), 'value' => $html];
        $Html->set_js($js);

        //event
        $Model_event = (new \eventModel)->column(['event_id', 'name'])->where([[[['act', '=', 'open']], 'and']])->order(['event_id' => 'DESC'])->fetchAll();
        $s_event = [];
        foreach ($Model_event as $v_0) {
            $s_event[] = [
                'value' => $v_0['event_id'],
                'text' => $v_0['event_id'] . ' - ' . $v_0['name'],
            ];
        }
        list($html, $js) = $Html->selectKit(['id' => 'event_id', 'name' => 'event_id', 'data-group' => 'customize'], $s_event, (isset($a_customize['event_id']) ? (int)$a_customize['event_id'] : null));
        $column2[] = ['key' => _('Event'), 'value' => $html];
        $Html->set_js($js);

        list($html, $js) = $Html->submit_act('id="filter" name="filter" value="Filter"', 1);
        $column2[] = ['key' => '&nbsp;', 'value' => $html];
        $Html->set_js($js);

        list($html, $js) = $Html->table('class="table"', $column2, $extra2);
        $column1[] = ['key' => _('Customize'), 'value' => $html];
        $Html->set_js($js);

        list($html, $js) = $Html->table('class="table"', $column1, $extra1);
        $column[] = ['key' => _('List'), 'value' => $html];
        $Html->set_js($js);

        list($html, $js) = $Html->textarea('id="message" name="message" style="width:400px; height:100px; font-size:14px;"', $message);
        $column[] = ['key' => _('Message'), 'value' => $html];
        $Html->set_js($js);

        //
        $column_1 = [];

        //
        $array_mode = [];

        foreach (['target2type' => 'Target Type', 'url' => 'Url'] as $k_0 => $v_0) {
            $array_mode[] = [
                'name' => 'mode',
                'value' => $k_0,
                'text' => $v_0,
            ];
        }

        list($html, $js) = $Html->radiotable('150px', '30px', 5, $array_mode, $mode);
        $column_1[] = ['key' => '模式', 'value' => $html];
        $Html->set_js($js);

        $column_1[] = [
            'key' => '設定',
            'value' => \Lib\Backstage\Html\Input::url([
                'id' => 'url',
                'maxlength' => 128,
                'size' => 128,
                'value' => $url,
            ]),
            'trattr' => 'id="direct-tr-url"'
        ];

        //
        $radio_array_target2type = [];

        foreach (\Schema\push::$target_type as $v_0) {
            $radio_array_target2type[] = [
                'name' => 'target2type',
                'text' => $v_0,
                'value' => $v_0,
            ];
        }

        list($html) = $Html->radiotable('150px', '30px', 5, $radio_array_target2type, $target2type);

        //
        $column_2 = [];

        $column_2[] = [
            'key' => 'Target Type',
            'value' => $html
        ];

        $column_2[] = [
            'key' => 'Target Type Id',
            'value' => \Lib\Backstage\Html\Input::number([
                'id' => 'target2type_id',
                'min' => 1,
                'value' => $target2type_id
            ]),
        ];

        list($html) = $Html->table('class="table"', $column_2);

        $column_1[] = [
            'key' => '設定',
            'value' => $html,
            'trattr' => 'id="direct-tr-target2type"'
        ];

        list($html) = $Html->button('id="direct-reset" value="Reset"');
        $column_1[] = ['key' => null, 'value' => $html];

        list($html, $js) = $Html->table('class="table"', $column_1);
        $column[] = ['key' => '導向', 'value' => $html];
        $Html->set_js($js);

        list($html, $js) = $Html->textarea('id="remark" name="remark" style="width:400px; height:100px; font-size:14px;"', $remark);
        $column[] = ['key' => _('Remark'), 'value' => $html];
        $Html->set_js($js);

        $column[] = ['key' => _('Insert Time'), 'value' => $inserttime];

        $column[] = ['key' => _('Modify Time'), 'value' => $modifytime];

        $column[] = ['key' => _('Modify Admin Name'), 'value' => $modifyadmin_name];

        list($html0, $js0) = $Html->submit('value="' . _('Submit') . '"');
        list($html1, $js1) = $Html->back('value="' . _('Back') . '"');
        $column[] = ['key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1];
        $Html->set_js($js0 . $js1);

        list($html, $js) = $Html->table('class="table"', $column, $extra);
        $a_tabs[0] = ['href' => '#tabs-0', 'name' => _('Form'), 'value' => $html];
        $Html->set_js($js);

        list($html, $js) = $Html->tabs($a_tabs);
        $formcontent = $html;
        $Html->set_js($js);

        list($html, $js) = $Html->form('id="form"', $formcontent);
        parent::$data['form'] = $html;
        $Html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function delete()
    {
        die;
    }

    function json()
    {
        $response = [];

        $case = isset($_POST['case']) ? $_POST['case'] : null;

        switch ($case) {
            default:
                //column
                $column = [
                    M_CLASS . '_id',
                    'remark',
                    'customize',
                    'message',
                    'modifytime',
                ];

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = (new pushModel)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $a_customize = json_decode($v0['customize'], true);
                    $a_hobby = [];
                    if (is_array($a_customize['hobby'])) {
                        foreach ($a_customize['hobby'] as $v1) {
                            $array0 = [];
                            if (!array_key_exists($v1, $array0)) {
                                $m_hobby = (new hobbyModel)->column(['hobby_id', 'name'])->fetchAll();
                                foreach ($m_hobby as $v2) {
                                    $array0[$v2['hobby_id']] = $v2['hobby_id'] . ' - ' . $v2['name'];
                                }
                            }
                            $a_hobby[] = $array0[$v1];
                        }
                    }
                    $array1 = [
                        _('Account') . ' : ' . $a_customize['user-account'],
                        _('Cellphone') . ' : ' . $a_customize['user-cellphone'],
                        _('Birthday') . ' : ' . $a_customize['user-birthday-start'] . ' ~ ' . $a_customize['user-birthday-end'],
                        _('Last Login Time') . ' : ' . $a_customize['user-lastlogintime-start'] . ' ~ ' . $a_customize['user-lastlogintime-end'],
                        _('Hobby') . ' : ' . implode(' | ', $a_hobby),
                    ];
                    $v0['customize'] = implode('<br>', $array1);
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = (new pushModel)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;

            case 'user':
                $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : null;

                $a_hobby = isset($_POST['hobby']) ? $_POST['hobby'] : null;

                //column
                $column = [
                    'user.user_id',
                    'user.account',
                    'user.name',
                    'user.cellphone',
                    'user.email',
                    'user.gender',
                    'user.birthday',
                    'user.creative',
                    'user.lastlogintime',
                    'user.way',
                    'user.act',
                    'userstatistics.viewed',
                ];

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                $join = [['inner join', 'userstatistics', 'using(user_id)']];

                if ($event_id) {
                    $join[] = ['INNER JOIN', 'eventjoin', 'ON eventjoin.event_id = ' . (new \userModel)->quote($event_id)];
                    $join[] = ['INNER JOIN', 'album', 'ON album.album_id = eventjoin.album_id AND album.user_id = user.user_id'];
                }

                if ($a_hobby != null) {
                    $join[] = ['INNER JOIN', 'hobby_user', 'ON hobby_user.user_id = user.user_id'];
                    $where[] = [[['hobby_user.hobby_id', 'in', $a_hobby]], 'and'];
                }

                if ($event_id || $a_hobby) {
                    $group[] = 'user.user_id';
                }

                //data
                $fetchAll = (new \userModel)
                    ->column($column)
                    ->join($join)
                    ->where($where)
                    ->group($group)
                    ->order($order)
                    ->limit($limit)
                    ->fetchAll();

                foreach ($fetchAll as &$v0) {
                    $v0['hobbyX'] = parent::get_grid_display('hobby', $v0['user_id']);
                }

                $response['data'] = $fetchAll;

                //total
                $response['total'] = count((new userModel)->column(['user.user_id'])->join($join)->where($where)->group($group)->fetchAll());
                break;
        }

        die(json_encode($response));
    }

    function extra0()
    {
        if (is_ajax()) {
            $M_CLASS_id = isset($_POST[M_CLASS . '_id']) ? $_POST[M_CLASS . '_id'] : null;
            $execute = isset($_POST['execute']) ? $_POST['execute'] : null;

            $result = 1;
            $message = _('執行成功。');

            if ($M_CLASS_id == null) {
                $result = 0;
                $message = 'Param error. "push_id" is required.';
                goto _return;
            }

            $m_push = (new \pushModel)
                ->column([
                    'customize',
                    'message',
                    'mode',
                    'target2type',
                    'target2type_id',
                    'url',
                ])
                ->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])
                ->fetch();

            if ($m_push == null) {
                $result = 0;
                $message = 'Data of "push" does not exist';
                goto _return;
            }

            $a_customize = json_decode($m_push['customize'], true);

            $join = [['inner join', 'userstatistics', 'using(user_id)']];
            $where = [];
            $group = [];

            if (!empty($a_customize['event_id'])) {
                $join[] = ['INNER JOIN', 'eventjoin', 'ON eventjoin.event_id = ' . (new \userModel)->quote($a_customize['event_id'])];
                $join[] = ['INNER JOIN', 'album', 'ON album.album_id = eventjoin.album_id AND album.user_id = user.user_id'];
            }

            if ($a_customize['hobby'] != null) {
                $join[] = ['INNER JOIN', 'hobby_user', 'ON hobby_user.user_id = user.user_id'];
                $where[] = [[['hobby_user.hobby_id', 'in', $a_customize['hobby']]], 'and'];
            }

            if (!empty($a_customize['event_id']) || !empty($a_customize['hobby'])) {
                $group[] = 'user.user_id';
            }

            if ($a_customize['user-account'] != null) $where[] = [[['user.account', '=', $a_customize['user-account']]], 'and'];

            if ($a_customize['user-cellphone'] != null) $where[] = [[['user.cellphone', '=', $a_customize['user-cellphone']]], 'and'];

            if ($a_customize['user-birthday-start'] != null && $a_customize['user-birthday-end'] != null) {
                $where[] = [[['user.birthday', 'between', [$a_customize['user-birthday-start'], $a_customize['user-birthday-end']]]], 'and'];
            } elseif ($a_customize['user-birthday-start'] != null) {
                $where[] = [[['user.birthday', '>=', $a_customize['user-birthday-start']]], 'and'];
            } elseif ($a_customize['user-birthday-end'] != null) {
                $where[] = [[['user.birthday', '<=', $a_customize['user-birthday-end']]], 'and'];
            }

            if ($a_customize['user-lastlogintime-start'] != null && $a_customize['user-lastlogintime-end'] != null) {
                $where[] = [[['user.lastlogintime', 'between', [$a_customize['user-lastlogintime-start'], $a_customize['user-lastlogintime-end']]]], 'and'];
            } elseif ($a_customize['user-lastlogintime-start'] != null) {
                $where[] = [[['user.lastlogintime', '>=', $a_customize['user-lastlogintime-start']]], 'and'];
            } elseif ($a_customize['user-lastlogintime-end'] != null) {
                $where[] = [[['user.lastlogintime', '<=', $a_customize['user-lastlogintime-end']]], 'and'];
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

            if ($execute) {
                $pushlog_id = (new \pushlogModel)->add([
                    'message' => $m_push['message'],
                    'request' => $m_push['customize'],
                    'state' => 'pretreat',
                    'target2type' => $m_push['target2type'],
                    'target2type_id' => $m_push['target2type_id'],
                    'url' => $m_push['url'],
                    'modifyadmin_id' => \adminModel::getSession()['admin_id'],
                ]);

                (new \cronjobModel)->add([
                    'method' => 'pushModel::pushApp',
                    'param' => json_encode([
                        'pushlog_id' => $pushlog_id,
                    ]),
                    'state' => 'pretreat',
                ]);
            } else {
                $a_message = [];

                //customize
                $a_customize = json_decode($m_push['customize'], true);

                if (!empty($a_customize['event_id'])) {
                    $string_event = (new \eventModel)
                        ->column([
                            'event_id',
                            'name'
                        ])
                        ->where([[[['event_id', '=', $a_customize['event_id']]], 'and']])
                        ->fetch();
                }

                $a_hobby = [];
                if (is_array($a_customize['hobby'])) {
                    foreach ($a_customize['hobby'] as $v1) {
                        $array0 = [];
                        if (!array_key_exists($v1, $array0)) {
                            $m_hobby = (new hobbyModel)->column(['hobby_id', 'name'])->fetchAll();
                            foreach ($m_hobby as $v2) {
                                $array0[$v2['hobby_id']] = $v2['hobby_id'] . ' - ' . $v2['name'];
                            }
                        }
                        $a_hobby[] = $array0[$v1];
                    }
                }

                $array1 = [
                    _('Account') . ' : ' . $a_customize['user-account'],
                    _('Cellphone') . ' : ' . $a_customize['user-cellphone'],
                    _('Birthday') . ' : ' . $a_customize['user-birthday-start'] . ' ~ ' . $a_customize['user-birthday-end'],
                    _('Last Login Time') . ' : ' . $a_customize['user-lastlogintime-start'] . ' ~ ' . $a_customize['user-lastlogintime-end'],
                    _('Hobby') . ' : ' . implode(' | ', $a_hobby),
                    empty($string_event) ? null : _('Event') . ' : ' . $string_event['event_id'] . ' - ' . $string_event['name'],
                ];
                $a_message[] = '[Customize]<br>' . implode('<br>', $array1);

                //message
                $a_message[] = '[Message]<br>' . $m_push['message'];

                $a_message[] = 'Total of users : ' . number_format(count($m_user));

                $message = implode('<br><br>', array_merge($a_message, [_('Are you sure to execute it?')]));
            }

            _return:
            json_encode_return($result, $message);
        }
        die;
    }
}