<?php

namespace Model;

class point extends \Model
{
    protected
        $database = 'analysis',
        $join_table = [],
        $memcache = 'analysis',
        $table = 'point';

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

    /**
     * 2017-12-20 Lion: 此函式由 crontab 運行
     * @return array
     */
    static function crontabForAnalysis()
    {
        $date = date('Y-m-d', strtotime('-1 day'));

        $starttime = $date . ' 00:00:00';
        $endtime = $date . ' 23:59:59';

        //order4sum
        $order4sum = 0;

        $orderModel = (new \orderModel())
            ->column(['assets_info'])
            ->where([[[['assets', '=', 'userpoint'], ['inserttime', 'BETWEEN', [$starttime, $endtime]], ['state', '=', 'success'], ['fulfill', '=', 'success']], 'and']])
            ->fetchAll();

        foreach ($orderModel as $v_0) {
            $Array_assets_info = json_decode($v_0['assets_info'], true);

            if (isset($Array_assets_info['obtain'])) $order4sum += $Array_assets_info['obtain'];
        }

        //exchange4sum & exchange_free4sum
        $exchangeModel = (new \exchangeModel())
            ->column([
                'SUM(`point`) `point`',
                'SUM(`point_free`) point_free',
            ])
            ->where([[[['inserttime', 'BETWEEN', [$starttime, $endtime]]], 'and']])
            ->fetch();

        $exchange4sum = ($exchangeModel['point'] === null) ? 0 : $exchangeModel['point'];
        $exchange_free4sum = ($exchangeModel['point_free'] === null) ? 0 : $exchangeModel['point_free'];

        //split4sum
        $split4sum = (new \Model\split)
            ->column(['SUM(`point`)'])
            ->where([[[['inserttime', 'BETWEEN', [$starttime, $endtime]]], 'and']])
            ->fetchColumn();

        if ($split4sum === null) $split4sum = 0;

        //
        (new \Model\point())->replace([
            'date' => $date,
            'order4sum' => $order4sum,
            'exchange4sum' => $exchange4sum,
            'exchange_free4sum' => $exchange_free4sum,
            'split4sum' => $split4sum,
        ]);

        email(
            EMAIL_ACCOUNT_INTRANET,
            EMAIL_PASSWORD_INTRANET,
            'pinpinbox crontab',
            [
                'cailum@vmage.com.tw',
                'sung@vmage.com.tw',
            ],
            $date . ' Point 回報',
            '
                購買 P 點：' . number_format($order4sum) . '<br><br>
                消費 P 點 -<br>
                &emsp;付費：' . number_format($exchange4sum) . '<br>
                &emsp;免費：' . number_format($exchange_free4sum) . '<br><br>
                獲得積分：' . number_format($split4sum)
        );

        _return:
        return array_encode_return(1);
    }
}