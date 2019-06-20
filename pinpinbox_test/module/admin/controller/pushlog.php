<?php

class pushlogController extends backstageController
{
    function __construct()
    {
    }

    function index()
    {
        list($html, $js) = parent::$html->grid();
        parent::$data['index'] = $html;
        parent::$html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function form()
    {
        die;
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
                    'runtime',
                    'message',
                    'request',
                    '`return`',
                    'state',
                    'modifytime',
                ];

                list ($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = (new \pushlogModel)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    //request
                    $a_request = json_decode($v0['request'], true);

                    if (!empty($a_request['event_id'])) {
                        $string_event = (new \eventModel)
                            ->column([
                                'event_id',
                                'name'
                            ])
                            ->where([[[['event_id', '=', $a_request['event_id']]], 'and']])
                            ->fetch();
                    }

                    $a_hobby = [];
                    if (is_array($a_request['hobby'])) {
                        foreach ($a_request['hobby'] as $v1) {
                            $array0 = [];
                            if (!array_key_exists($v1, $array0)) {
                                $m_hobby = Model('hobby')->column(['hobby_id', 'name'])->fetchAll();
                                foreach ($m_hobby as $v2) {
                                    $array0[$v2['hobby_id']] = $v2['hobby_id'] . ' - ' . $v2['name'];
                                }
                            }
                            $a_hobby[] = $array0[$v1];
                        }
                    }

                    $array1 = [
                        _('Account') . ' : ' . $a_request['user-account'],
                        _('Cellphone') . ' : ' . $a_request['user-cellphone'],
                        _('Birthday') . ' : ' . $a_request['user-birthday-start'] . ' ~ ' . $a_request['user-birthday-end'],
                        _('Last Login Time') . ' : ' . $a_request['user-lastlogintime-start'] . ' ~ ' . $a_request['user-lastlogintime-end'],
                        _('Hobby') . ' : ' . implode(' | ', $a_hobby),
                        empty($string_event) ? null : _('Event') . ' : ' . $string_event['event_id'] . ' - ' . $string_event['name'],
                    ];
                    $v0['request'] = implode('<br>', $array1);

                    $v0['return'] = parent::grid_json_decode($v0['return']);
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = (new \pushlogModel)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;
        }

        die(json_encode($response));
    }
}