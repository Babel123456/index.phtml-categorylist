<?php

class deviceModel extends Model
{
    protected $database = 'site';
    protected $table = 'device';
    protected $memcache = 'site';
    protected $join_table = [];

    function __construct()
    {
        parent::__construct_child();
    }

    static function ableToBuild(array $param)
    {
        $result = 1;
        $message = null;

        if (!isset($param['user_id']) || !is_numeric($param['user_id'])) {
            $result = 0;
            $message = 'Param error of "user_id"';
            goto _return;
        }

        if (!isset($param['identifier']) || trim($param['identifier']) === '') {
            $result = 0;
            $message = 'Param error of "identifier"';
            goto _return;
        }

        if (!isset($param['token']) || trim($param['token']) === '') {
            $result = 0;
            $message = 'Param error of "token"';
            goto _return;
        }

        if (isset($param['os']) && !in_array($param['os'], (new deviceModel)->fetchEnum('os'))) {
            $result = 0;
            $message = 'Param error of "os"';
            goto _return;
        }

        if (isset($param['browser']) && !in_array($param['browser'], (new deviceModel)->fetchEnum('browser'))) {
            $result = 0;
            $message = 'Param error of "browser"';
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function ableToDestroy(array $param)
    {
        $result = 1;
        $message = null;

        if (!isset($param['device_id']) && !isset($param['identifier'])) {
            $result = 0;
            $message = 'Param error';
            goto _return;
        }

        if (isset($param['device_id']) && !is_numeric($param['device_id'])) {
            $result = 0;
            $message = 'Param error of "device_id"';
            goto _return;
        }

        if (isset($param['identifier']) && trim($param['identifier']) === '') {
            $result = 0;
            $message = 'Param error of "identifier"';
            goto _return;
        }

        if (isset($param['device_id']) && !(new deviceModel)->column(['COUNT(1)'])->where([[[['device_id', '=', $param['device_id']]], 'and']])->fetchColumn()) {
            $result = 0;
            $message = 'Data of "device" does not exist';
            goto _return;
        }

        if (isset($param['identifier']) && !(new deviceModel)->column(['COUNT(1)'])->where([[[['identifier', '=', $param['identifier']]], 'and']])->fetchColumn()) {
            $result = 0;
            $message = 'Data of "device" does not exist';
            goto _return;
        }

        if (isset($param['browser'])) {
            if (!in_array($param['browser'], (new deviceModel)->fetchEnum('browser'))) {
                $result = 0;
                $message = 'Param error of "browser"';
                goto _return;
            }

            if (!isset($param['token']) || trim($param['token']) === '') {
                $result = 0;
                $message = 'Param error of "token"';
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function build(array $param)
    {
        $user_id = $param['user_id'];
        $identifier = $param['identifier'];
        $token = $param['token'];
        $os = isset($param['os']) ? $param['os'] : null;
        $browser = isset($param['browser']) ? $param['browser'] : null;

        if (in_array($os, ['android', 'ios'])) {
            (new deviceModel)->replace([
                'user_id' => $user_id,
                'identifier' => $identifier,
                'os' => $os,
                'token' => $token,
                'enabled' => true,
                'modifyadmin_id' => 0,//2016-11-30 Lion: 由於是系統變更, 因此改為 0
            ]);

            $m_device = (new deviceModel)
                ->column(['device_id'])
                ->where([[[['identifier', '=', $identifier]], 'and']])
                ->fetch();

            $m_cronjob = (new cronjobModel)
                ->column(['cronjob_id', 'mysql_connection_id', 'param', 'state'])
                ->where([[[['method', '=', 'subscriptionModel::buildByDevice'], ['state', 'in', ['pretreat', 'process']]], 'and']])
                ->fetchAll();

            foreach ($m_cronjob as $v0) {
                $a_param = json_decode($v0['param'], true);

                if (isset($a_param['device_id']) && $a_param['device_id'] == $m_device['device_id']) {
                    switch ($v0['state']) {
                        case 'pretreat':
                            (new cronjobModel)
                                ->where([[[['cronjob_id', '=', $v0['cronjob_id']]], 'and']])
                                ->edit([
                                    'state' => 'cancel',
                                ]);
                            break;

                        case 'process':
                            (new cronjobModel)->kill($v0['mysql_connection_id']);

                            (new cronjobModel)
                                ->where([[[['cronjob_id', '=', $v0['cronjob_id']]], 'and']])
                                ->edit([
                                    'state' => 'stop',
                                ]);
                            break;
                    }
                    break;
                }
            }

            (new cronjobModel)
                ->add([
                    'method' => 'subscriptionModel::buildByDevice',
                    'param' => json_encode([
                        'device_id' => $m_device['device_id'],
                        'protocol' => 'application',
                    ]),
                    'state' => 'pretreat',
                ]);
        }

        if (in_array($browser, ['chrome', 'firefox', 'safari'])) {
            (new deviceModel)
                ->where([[[['browser', '=', $browser], ['token', '=', $token]], 'and']])
                ->edit([
                    'token' => '',
                    'enabled' => false,
                    'modifyadmin_id' => 0,//2016-11-30 Lion: 由於是系統變更, 因此改為 0
                ]);

            (new deviceModel)
                ->replace([
                    'user_id' => $user_id,
                    'identifier' => $identifier,
                    'browser' => $browser,
                    'token' => $token,
                    'enabled' => true,
                    'modifyadmin_id' => 0,//2016-11-30 Lion: 由於是系統變更, 因此改為 0
                ]);
        }
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
        ];
    }

    static function destroy(array $param)
    {
        $device_id = isset($param['device_id']) ? $param['device_id'] : null;
        $identifier = isset($param['identifier']) ? $param['identifier'] : null;
        $token = isset($param['token']) ? $param['token'] : null;

        $where = ($identifier === null) ? [[[['device_id', '=', $device_id]], 'and']] : [[[['identifier', '=', $identifier]], 'and']];

        $m_device = (new deviceModel)
            ->column(['device_id', 'user_id', 'os', 'browser'])
            ->where($where)
            ->fetch();

        if ($m_device) {
            if (in_array($m_device['os'], ['android', 'ios'])) {
                (new deviceModel)
                    ->where([[[['device_id', '=', $m_device['device_id']]], 'and']])
                    ->edit([
                        'enabled' => false,
                        'modifyadmin_id' => 0,//2016-11-30 Lion: 由於是系統變更, 因此改為 0
                    ]);

                $m_cronjob = (new cronjobModel)
                    ->column(['cronjob_id', 'mysql_connection_id', 'param', 'state'])
                    ->where([[[['method', '=', 'subscriptionModel::destroyByDevice'], ['state', 'in', ['pretreat', 'process']]], 'and']])
                    ->fetchAll();

                foreach ($m_cronjob as $v0) {
                    $a_param = json_decode($v0['param'], true);

                    if (isset($a_param['device_id']) && $a_param['device_id'] == $m_device['device_id']) {
                        switch ($v0['state']) {
                            case 'pretreat':
                                (new cronjobModel)
                                    ->where([[[['cronjob_id', '=', $v0['cronjob_id']]], 'and']])
                                    ->edit([
                                        'state' => 'cancel',
                                    ]);
                                break;

                            case 'process':
                                (new cronjobModel)->kill($v0['mysql_connection_id']);

                                (new cronjobModel)
                                    ->where([[[['cronjob_id', '=', $v0['cronjob_id']]], 'and']])
                                    ->edit([
                                        'state' => 'stop',
                                    ]);
                                break;
                        }
                        break;
                    }
                }

                (new cronjobModel)
                    ->add([
                        'method' => 'subscriptionModel::destroyByDevice',
                        'param' => json_encode([
                            'device_id' => $m_device['device_id'],
                            'protocol' => 'application',
                        ]),
                        'state' => 'pretreat',
                    ]);
            }

            if (in_array($m_device['browser'], ['chrome', 'firefox', 'safari'])) {
                (new deviceModel)
                    ->where([[[['browser', '=', $m_device['browser']], ['token', '=', $token]], 'and']])
                    ->edit([
                        'token' => '',
                        'enabled' => false,
                        'modifyadmin_id' => 0,//2016-11-30 Lion: 由於是系統變更, 因此改為 0
                    ]);
            }
        }
    }

    function getDevice_id($user_id, $identifier)
    {
        $device_id = (new deviceModel)->column(['device_id'])->where([[[['identifier', '=', $identifier]], 'and']])->fetchColumn();

        if (!$device_id) {
            $add = [
                'user_id' => $user_id,
                'identifier' => $identifier
            ];
            $device_id = (new deviceModel)->add($add);
        }

        return $device_id;
    }

    static function enable($device_id, $enable = true)
    {
        $m_device = (new deviceModel)->column(['identifier', 'token', 'aws_sns_endpointarn'])->where([[[['device_id', '=', $device_id]], 'and']])->fetch();

        if ($m_device['aws_sns_endpointarn'] !== '') {
            Extension('aws\sns')->setEndpointAttributes(
                [
                    'CustomUserData' => $m_device['identifier'],
                    'Enabled' => ($enable === true) ? 'true' : 'false',
                    'Token' => $m_device['token'],
                ],
                $m_device['aws_sns_endpointarn']
            );
        }
    }
}