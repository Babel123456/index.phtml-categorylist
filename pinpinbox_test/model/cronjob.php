<?php

class cronjobModel extends Model
{
    protected $database = 'site';
    protected $table = 'cronjob';
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

    function setException($e)
    {
        if (defined('CRONJOB_ID')) {
            $exception = Model('cronjob')->column(['exception'])->where([[[['cronjob_id', '=', CRONJOB_ID]], 'and']])->fetchColumn();

            $exception = empty(trim($exception)) ? exceptioninfostring($e) : $exception . "\r\n" . exceptioninfostring($e);

            Model('cronjob')->where([[[['cronjob_id', '=', CRONJOB_ID]], 'and']])->edit([
                'exception' => $exception,
            ]);
        }
    }

    static function createPointMonthlyReport()
    {
        $result = 1;
        $message = null;

        $date = date('Y-m', strtotime('last month'));

        $PHPExcel = new \PHPExcel;

        $PHPExcel->getProperties()->setCreator('pinpinbox');

        //未使用 P 點
        $Model_userpoint = (new \userpointModel())
            ->column([
                'SUM(`point`) `point`',
                'SUM(`point_free`) `point_free`',
            ])
            ->fetch();

        $cell = [
            ['未使用付費點', '未使用免費點'],
            [$Model_userpoint['point'], $Model_userpoint['point_free']],
        ];

        //設定要操作的Sheet
        $PHPExcel->setActiveSheetIndex(0);

        //設定頁籤名稱
        $PHPExcel->getActiveSheet()->setTitle('未使用 P 點');

        //設定欄寬
        for ($i = 0; $i <= 1; ++$i) {
            $PHPExcel->getActiveSheet()->getColumnDimension(toAlpha($i))->setWidth(16);
        }

        //儲存格背景顏色
        $PHPExcel->getActiveSheet()->getStyle('A1:B1')->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']]]);

        //儲存格內容
        foreach ($cell as $k_0 => $v_0) {
            foreach ($v_0 as $k_1 => $v_1) {
                $PHPExcel->getActiveSheet()->setCellValue(toAlpha($k_1) . (int)($k_0 + 1), $v_1);
            }
        }

        /**
         *
         */
        $first_day_of_last_month = date('Y-m-d 00:00:00', strtotime('first day of last month'));
        $last_day_of_last_month = date('Y-m-d 23:59:59', strtotime('last day of last month'));

        //
        $PHPExcel->createSheet(1);

        //設定要操作的Sheet
        $PHPExcel->setActiveSheetIndex(1);

        $PHPExcel->getActiveSheet()->setTitle('P 點流向');

        //設定欄寬
        for ($i = 0; $i <= 5; ++$i) {
            $PHPExcel->getActiveSheet()->getColumnDimension(toAlpha($i))->setWidth(24);
        }

        //儲存格背景顏色
        $PHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']]]);
        $PHPExcel->getActiveSheet()->getStyle('A2:E2')->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'C9C8E8']]]);

        //儲存格內容
        $cell = [
            ['付費點'],
            ['User ID', 'Account', 'Name', '收得付費點', '分潤比%']
        ];

        //付費點流向
        $Model_user = (new \userModel())
            ->column([
                'SUM(exchange.point) `point`',
                'user.account',
                'user.name',
                'user.user_id',
            ])
            ->join([
                ['INNER JOIN', 'split', 'ON split.user_id = user.user_id AND split.inserttime BETWEEN ' . (new \Model\split())->quote($first_day_of_last_month) . ' AND ' . (new \Model\split())->quote($last_day_of_last_month)],
                ['INNER JOIN', 'exchange', 'ON exchange.exchange_id = split.exchange_id']
            ])
            ->group([
                'split.user_id'
            ])
            ->having([
                ['`point`', '>', 0]
            ])
            ->order([
                '`point`' => 'DESC'
            ])
            ->limit('0,10')
            ->fetchAll();

        foreach ($Model_user as $v_0) {
            if (\businessuser\Model::isUpline($v_0['user_id'])) {
                $split = "
                    本帳號拆分比: " . \Model\split::getRatio($v_0['user_id'], 'album') . "\r\n
                    由經紀帳號拆分: " . (\Model\split::getRatioForBusinessuser($v_0['user_id']) * 100) . "%
                    ";
            } else {
                $split = \Model\split::getRatio($v_0['user_id'], 'album');
            }

            $cell[] = [
                $v_0['user_id'],
                $v_0['account'],
                $v_0['name'],
                $v_0['point'],
                $split
            ];
        }

        foreach ($cell as $k_0 => $v_0) {
            foreach ($v_0 as $k_1 => $v_1) {
                $PHPExcel->getActiveSheet()->setCellValue(toAlpha($k_1) . (int)($k_0 + 1), $v_1);
            }
        }

        //從第 15 行開始
        $second_part = 15;

        //儲存格背景顏色
        $PHPExcel->getActiveSheet()->getStyle('A' . $second_part . ':A' . $second_part)->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']]]);
        $PHPExcel->getActiveSheet()->getStyle('A' . ($second_part + 1) . ':E' . ($second_part + 1))->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'C9C8E8']]]);

        //儲存格內容
        $cell = [
            ['免費點'],
            ['User ID', 'Account', 'Name', '收得免費點', '分潤比%']
        ];

        //免費點流向
        $Model_user = (new \userModel())
            ->column([
                'SUM(exchange.point_free) `point_free`',
                'user.account',
                'user.name',
                'user.user_id',
            ])
            ->join([
                ['INNER JOIN', 'split', 'ON split.user_id = user.user_id AND split.inserttime BETWEEN ' . (new \Model\split())->quote($first_day_of_last_month) . ' AND ' . (new \Model\split())->quote($last_day_of_last_month)],
                ['INNER JOIN', 'exchange', 'ON exchange.exchange_id = split.exchange_id']
            ])
            ->group([
                'split.user_id'
            ])
            ->having([
                ['`point_free`', '>', 0]
            ])
            ->order([
                '`point_free`' => 'DESC'
            ])
            ->limit('0,10')
            ->fetchAll();

        foreach ($Model_user as $v_0) {
            if (\businessuser\Model::isUpline($v_0['user_id'])) {
                $split = "
                    本帳號拆分比: " . \Model\split::getRatio($v_0['user_id'], 'album') . "\r\n
                    由經紀帳號拆分: " . (\Model\split::getRatioForBusinessuser($v_0['user_id']) * 100) . "%
                    ";
            } else {
                $split = \Model\split::getRatio($v_0['user_id'], 'album');
            }

            $cell[] = [
                $v_0['user_id'],
                $v_0['account'],
                $v_0['name'],
                $v_0['point_free'],
                $split
            ];
        }

        foreach ($cell as $k_0 => $v_0) {
            foreach ($v_0 as $k_1 => $v_1) {
                $cell_coordinate = toAlpha($k_1) . (int)($k_0 + $second_part);

                $PHPExcel->getActiveSheet()->setCellValue($cell_coordinate, $v_1);

                $PHPExcel->getActiveSheet()->getStyle($cell_coordinate)->getAlignment()->setWrapText(true);//實現 cell 內跳行
            }
        }

        //從第 29 行開始
        $PHPExcel->getActiveSheet()->mergeCells('A29:E29');

        $PHPExcel->getActiveSheet()->getRowDimension(29)->setRowHeight(48);

        $PHPExcel->getActiveSheet()->setCellValue('A29', "備註:\r\n(收得付費點 + 收得免費點) * 分潤比 = 獲得積分\r\n積分 / 2 - 30 (匯款手續費) = 匯款金額");

        $PHPExcel->getActiveSheet()->getStyle('A29')->getAlignment()->setWrapText(true);//實現 cell 內跳行

        //save
        $filename = $date . ' point monthly report.xlsx';
        $file = PATH_ROOT . $filename;

        \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007')->save($file);

        if (!file_exists($file)) {
            $result = 0;
            $message = '檔案 "' . $file . '" 不存在。';
            goto _return;
        }

        $filename = $subject = explode('.', parse_url(URL_ROOT, PHP_URL_HOST))[0] . ' - ' . date('Y', strtotime($date)) . ' 年 ' . date('m', strtotime($date)) . ' 月份 P 點月報表.xlsx';

        $success = email(
            EMAIL_ACCOUNT_INTRANET,
            EMAIL_PASSWORD_INTRANET,
            'pinpinbox',

            SITE_EVN === 'development' ?
                'lion@vmage.com.tw'
                :
                [
                    'cailum@vmage.com.tw',
                    'monica@vmage.com.tw',
                    'pinpinbox888@gmail.com',
                    'sung@vmage.com.tw',
                ],
            $subject,
            '參閱附件',
            [
                [
                    'tmp_name' => $file,
                    'name' => $filename
                ]
            ]
        );

        if (!$success) {
            $result = 0;
            $message = 'Email "' . $filename . '" 寄送失敗。';
            goto _return;
        }

        unlink($file);

        _return:
        return array_encode_return($result, $message);
    }
}