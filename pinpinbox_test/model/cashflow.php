<?php

class cashflowModel extends Model
{
    protected $database = 'cashflow';
    protected $table = 'cashflow';
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

    function pay2goMonthlyReport()
    {
        $result = 1;
        $message = null;

        $m_order = (new orderModel)
            ->column([
                'order.assets_info',
                'order.inserttime',
                'user.cellphone',
                'user.name',
                'userpoint.point',
            ])
            ->join([
                ['INNER JOIN', 'user', 'USING(user_id)'],
                ['INNER JOIN', 'userpoint', 'USING(user_id)'],
            ])
            ->where([[[
                ['order.platform', '=', 'web'],
                ['order.assets', '=', 'userpoint'],
                ['order.inserttime', 'BETWEEN', [date('Y-m-d 00:00:00', strtotime('first day of -1 month')), date('Y-m-d 23:59:59', strtotime('last day of -1 month'))]],
                ['order.state', '=', 'success'],
                ['userpoint.platform', '=', 'web']
            ], 'and']])
            ->order(['order.inserttime' => 'ASC'])
            ->fetchAll();

        $cell = [
            ['購買日期', '姓名', '電話', '購買點數', '目前剩餘點數'],//標題列
        ];

        //儲存格內容
        if ($m_order) {
            foreach ($m_order as $v0) {
                $a_assets_info = json_decode($v0['assets_info'], true);

                $cell[] = [
                    $v0['inserttime'],
                    $v0['name'],
                    $v0['cellphone'],
                    empty($a_assets_info['obtain']) ? 0 : $a_assets_info['obtain'],
                    $v0['point'],
                ];
            }
        } else {
            $cell[] = ['無資料'];
        }

        $excel = [
            [
                'SheetCell' => $cell,
            ],
        ];

        $PHPExcel = SDK('PHPExcel');

        $PHPExcel->getProperties()->setCreator('pinpinbox');

        foreach ($excel as $v0) {
            //設定要操作的Sheet
            $PHPExcel->setActiveSheetIndex(0);

            //自動欄寬
            for ($i = 0; $i <= 4; ++$i) {
                $PHPExcel->getActiveSheet()->getColumnDimension(toAlpha($i))->setAutoSize(true);
            }

            /**
             * 儲存格背景顏色
             */
            $PHPExcel->getActiveSheet()->getStyle('A1:E1')->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']]]);

            //儲存格內容
            foreach ($v0['SheetCell'] as $k1 => $v1) {
                foreach ($v1 as $k2 => $v2) {
                    $PHPExcel->getActiveSheet()->setCellValue(toAlpha($k2) . (int)($k1 + 1), $v2);
                }
            }
        }

        //export
        $filename = 'pinpinbox-' . date('Y') . '年' . date('m') . '月份購點資料.xlsx';
        $filename_big5 = iconv('UTF-8', 'Big5', $filename);

        $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');

        $objWriter->save(PATH_ROOT . $filename_big5);

        switch (SITE_EVN) {
            case 'production':
                $to = [
                    'chenamy@vmage.com.tw',
                    'it@vmage.com.tw',
                    'riskc@pay2go.com',
                ];
                break;

            default:
                $to = 'it@vmage.com.tw';
                break;
        }

        email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $to, $filename, '參閱附件', [['tmp_name' => PATH_ROOT . $filename_big5, 'name' => $filename]]);

        unlink(PATH_ROOT . $filename_big5);

        _return:
        return array_encode_return($result, $message);
    }
}