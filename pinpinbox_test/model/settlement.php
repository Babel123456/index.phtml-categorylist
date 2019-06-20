<?php

class settlementModel extends Model
{
    protected $database = 'cashflow';
    protected $table = 'settlement';
    protected $memcache = 'cashflow';
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

    static function getSettlementList($user_id = null)
    {
        $return = [];

        if ($user_id) {
            if (\userModel::isDownlineOfBusinessUserOfCompany($user_id)) {
                $sum_album = (new \Model\split())
                    ->column([
                        'settlement.settlement_id',
                        'settlement.starttime',
                        'settlement.state',
                        'SUM(split.point) `point`',
                    ])
                    ->join([
                        ['INNER JOIN', 'settlement', 'ON settlement.settlement_id = split.settlement_id'],
                        ['INNER JOIN', 'exchange', 'ON exchange.exchange_id = split.exchange_id AND exchange.type = \'album\''],
                        ['INNER JOIN', 'album', 'ON album.album_id = exchange.id AND album.user_id = ' . (new \Model\split())->quote($user_id)],
                    ])
                    ->group(['split.settlement_id'])
                    ->order(['settlement.starttime' => 'DESC'])
                    ->fetchAll();

                foreach ($sum_album as $v_0) {
                    $return[$v_0['settlement_id']] = [
                        'starttime' => $v_0['starttime'],
                        'state' => $v_0['state'],
                        'point' => $v_0['point'],
                    ];
                }

                $sum_template = (new \Model\split())
                    ->column([
                        'settlement.settlement_id',
                        'settlement.starttime',
                        'settlement.state',
                        'SUM(split.point) `point`',
                    ])
                    ->join([
                        ['INNER JOIN', 'settlement', 'ON settlement.settlement_id = split.settlement_id'],
                        ['INNER JOIN', 'exchange', 'ON exchange.exchange_id = split.exchange_id AND exchange.type = \'template\''],
                        ['INNER JOIN', 'template', 'ON template.template_id = exchange.id AND template.user_id = ' . (new \Model\split())->quote($user_id)],
                    ])
                    ->group(['split.settlement_id'])
                    ->order(['settlement.starttime' => 'DESC'])
                    ->fetchAll();

                foreach ($sum_template as $v_0) {
                    if (isset($return[$v_0['settlement_id']])) {
                        $return[$v_0['settlement_id']]['point'] += $v_0['point'];
                    } else {
                        $return[$v_0['settlement_id']] = [
                            'starttime' => $v_0['starttime'],
                            'state' => $v_0['state'],
                            'point' => $v_0['point'],
                        ];
                    }
                }

                $return = array_values($return);
            } else {
                $return = (new \settlementModel)
                    ->column([
                        '(point_album + point_template) `point`',
                        'starttime',
                        'state',
                    ])
                    ->where([[[['user_id', '=', $user_id]], 'and']])
                    ->order(['starttime' => 'DESC'])
                    ->fetchAll();
            }
        }

        return $return;
    }

    function importData(array $param)
    {
        $result = 1;
        $message = null;

        $starttime = (isset($param['starttime']) && trim($param['starttime']) !== '') ? trim($param['starttime']) : null;
        $endtime = (isset($param['endtime']) && trim($param['endtime']) !== '') ? trim($param['endtime']) : null;

        if ($starttime === null || $endtime === null) {
            $result = 0;
            $message = 'Param error';
            goto _return;
        }

        set_time_limit(0);

        //檢查 settlement 結算期間是否重疊
        if ((new \settlementModel)->column(['count(1)'])->where([[[['starttime', '<=', $endtime], ['endtime', '>=', $starttime]], 'and']])->fetchColumn()) {
            $result = 0;
            $message = 'Overlapping settlement cycle.';
            goto _return;
        }

        //exchange 預處理的 data
        $a_type = ['album', 'template'];
        $array_0 = [];
        foreach ($a_type as $type) {
            $splitModel = (new \Model\split())
                ->column([
                    'split.split_id',
                    'split.user_id',
                    'split.`point`',
                ])
                ->join([
                    ['INNER JOIN', 'exchange', 'ON exchange.exchange_id = split.exchange_id AND exchange.type = \'' . $type . '\''],
                    ['INNER JOIN', $type, 'ON ' . $type . '.' . $type . '_id = exchange.id AND ' . $type . '.act != \'delete\'']
                ])
                ->where([
                    [[['split.settlement_id', 'IS', null], ['split.inserttime', 'between', [$starttime, $endtime]]], 'and'],
                ])
                ->lock('for update')
                ->fetchAll();

            foreach ($splitModel as $v_1) {
                if (!isset($array_0[$v_1['user_id']][$type])) {
                    $array_0[$v_1['user_id']][$type] = [
                        'split_id' => [],
                        'point' => 0,
                    ];
                }

                $array_0[$v_1['user_id']][$type]['split_id'][] = $v_1['split_id'];
                $array_0[$v_1['user_id']][$type]['point'] += $v_1['point'];
            }
        }

        //處理 split, settlement
        $settlementCount = 0;
        $splitCount = 0;

        foreach ($array_0 as $user_id => $v_0) {
            $settlement_id = (new \settlementModel)
                ->add([
                    'user_id' => $user_id,
                    'point_album' => empty($v_0['album']['point']) ? 0 : $v_0['album']['point'],
                    'point_template' => empty($v_0['template']['point']) ? 0 : $v_0['template']['point'],
                    'starttime' => $starttime,
                    'endtime' => $endtime,
                    'state' => 'pretreat',
                ]);

            ++$settlementCount;

            foreach ($a_type as $type) {
                if (empty($v_0[$type]['split_id'])) continue;

                $where = [
                    [[['split_id', 'IN', $v_0[$type]['split_id']], ['settlement_id', 'IS', null]], 'and'],
                ];

                $splitCount += (new \Model\split())
                    ->column(['COUNT(1)'])
                    ->where($where)
                    ->fetchColumn();

                (new \Model\split())
                    ->where($where)
                    ->edit(['settlement_id' => $settlement_id]);
            }
        }

        //12 個月前的 settlement 廢棄
        (new \settlementModel)
            ->where([
                [[['income_id', '=', 0], ['endtime', '<=', date('Y-m-d 23:59:59', strtotime('last day of -1 month -1 year'))], ['state', '=', 'pretreat']], 'and'],
            ])
            ->edit(['state' => 'fail']);

        //success
        $message = 'Execute [Split] x ' . number_format($splitCount) . ', [Settlement] x ' . number_format($settlementCount) . '.';

        _return:
        return array_encode_return($result, $message);
    }
}