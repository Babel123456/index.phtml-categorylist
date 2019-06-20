<?php

class paymentModel extends Model
{
    protected $database = 'cashflow';
    protected $table = 'payment';
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

    static function cerateExcel($payment_id)
    {
        $m_income = (new \incomeModel)
            ->column([
                'income.income_id',
                'income.user_id',
                'income.total',
                'income.currency',
                'income.remittance_info',
                'income.inserttime',
                'user.name',
                'user.cellphone',
            ])
            ->join([
                ['left join', 'user', 'using(user_id)'],
            ])
            ->where([[[['payment_id', '=', $payment_id]], 'and']])
            ->fetchAll();

        $time = time();

        $array_0 = [
            ["pinpinbox\r\n" . date('Y', strtotime('last month')) . " 年 " . date('m', strtotime('last month')) . " 月份結匯報表\r\n民國 " . (date('Y', $time) - 1911) . " 年 " . date('m', $time) . " 月 " . date('d', $time) . " 日"],//標題列 - 1
            [null],
            [null, null, null, null, null, 'User', null, 'PayPal', 'Other', null, null, null, null],//標題列 - 2
            ['income_id', 'user_id', 'total', 'currency', 'inserttime',

                //user
                'name',
                'cellphone',

                //paypal
                'account',

                //other
                'bank',
                'branch',
                'account',
                'name',
                'remark',
            ],//標題列 - 3
        ];

        foreach ($m_income as $v_0) {
            //內容
            $a_remittance_info = json_decode($v_0['remittance_info'], true);
            $array_0[] = array(
                $v_0['income_id'],
                $v_0['user_id'],
                $v_0['total'],
                $v_0['currency'],
                $v_0['inserttime'],

                //user
                $v_0['name'],
                $v_0['cellphone'],

                //paypal
                empty($a_remittance_info['paypal_account']) ? null : $a_remittance_info['paypal_account'],

                //other
                empty($a_remittance_info['bank']) ? null : $a_remittance_info['bank'],
                empty($a_remittance_info['branch']) ? null : $a_remittance_info['branch'],
                empty($a_remittance_info['account']) ? null : $a_remittance_info['account'],
                empty($a_remittance_info['name']) ? null : $a_remittance_info['name'],
                empty($a_remittance_info['remark']) ? null : $a_remittance_info['remark'],
            );
        }

        $array_1 = array_merge($array_0, [
            [null],
            [
                "董\r\n事\r\n長",
                null,
                null,
                null,
                "主\r\n管",
                null,
                null,
                "經\r\n手\r\n人",
                null,
                null,
                "會\r\n計",
            ]
        ]);

        $excel = [
            $array_1,
            $array_0,
            $array_0
        ];

        $sn_0 = 0;

        $PHPExcel = new \PHPExcel;

        $PHPExcel->getProperties()->setCreator('pinpinbox');

        foreach ($excel as $k_0 => $v_0) {
            if ($k_0 > 0) {
                $PHPExcel->createSheet($k_0);
            }

            //設定要操作的Sheet
            $PHPExcel->setActiveSheetIndex($k_0);

            if ($k_0 == 0) {
                $PHPExcel->getActiveSheet()->setTitle('會簽');

                $PHPExcel->getActiveSheet()->getRowDimension(11)->setRowHeight(80);

                //董事長
                $PHPExcel->getActiveSheet()->mergeCells('A11:D11')->getStyle('A11:D11')
                    ->applyFromArray([
                        'alignment' => [
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,//置左
                            'wrap' => true//實現 cell 內跳行
                        ]
                    ]);

                //主管
                $PHPExcel->getActiveSheet()->mergeCells('E11:G11')->getStyle('E11:G11')
                    ->applyFromArray([
                        'alignment' => [
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,//置左
                            'wrap' => true//實現 cell 內跳行
                        ]
                    ]);

                //經手人
                $PHPExcel->getActiveSheet()->mergeCells('H11:J11')->getStyle('H11:J11')
                    ->applyFromArray([
                        'alignment' => [
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,//置左
                            'wrap' => true//實現 cell 內跳行
                        ]
                    ]);

                //會計
                $PHPExcel->getActiveSheet()->mergeCells('K11:M11')->getStyle('K11:M11')
                    ->applyFromArray([
                        'alignment' => [
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,//置左
                            'wrap' => true//實現 cell 內跳行
                        ]
                    ]);
            } else {
                $PHPExcel->getActiveSheet()->setTitle('無會簽 ' . ++$sn_0);
            }

            //儲存格合併
            $PHPExcel->getActiveSheet()->mergeCells('A1:M1');//標題列 - 1
            $PHPExcel->getActiveSheet()->mergeCells('F3:G3');//user
            $PHPExcel->getActiveSheet()->mergeCells('H3:H3');//paypal
            $PHPExcel->getActiveSheet()->mergeCells('I3:M3');//other

            //儲存格設定
            $PHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(48);

            $PHPExcel->getActiveSheet()->getStyle("A1:M1")
                ->applyFromArray([
                    'alignment' => [
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,//置中
                        'wrap' => true//實現 cell 內跳行
                    ]
                ]);

            $PHPExcel->getActiveSheet()->getStyle('F3:G3')->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => 'F2F2F2']]]);//背景顏色, user
            $PHPExcel->getActiveSheet()->getStyle('H3:H3')->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '24B1ED']]]);//背景顏色, paypal
            $PHPExcel->getActiveSheet()->getStyle('I3:M3')->applyFromArray(['fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => ['rgb' => '1BDE73']]]);//背景顏色, other

            //儲存格內容
            foreach ($v_0 as $k_1 => $v_1) {
                foreach ($v_1 as $k_2 => $v_2) {
                    $cell_coordinate = toAlpha($k_2) . (int)($k_1 + 1);

                    if (toAlpha($k_2) === 'K' && (int)($k_1 + 1) >= 3) {
                        $PHPExcel
                            ->getActiveSheet()
                            ->getcell($cell_coordinate)
                            ->setValueExplicit($v_2, PHPExcel_Cell_DataType::TYPE_STRING);
                    } else {
                        $PHPExcel->getActiveSheet()->setCellValue($cell_coordinate, $v_2);
                    }
                }
            }

            //自動欄寬
            for ($i = 0; $i <= 12; ++$i) {
                $PHPExcel->getActiveSheet()->getColumnDimension(toAlpha($i))->setAutoSize(true);
            }
        }

        $PHPExcel->setActiveSheetIndex(0);

        //save
        $date = (new \paymentModel())
            ->column(['`date`'])
            ->where([[[['payment_id', '=', $payment_id]], 'and']])
            ->fetchColumn();

        $filename = $date . ' payment report.xlsx';
        $file = PATH_ROOT . $filename;

        \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007')->save($file);

        return $file;
    }

    static function importData(array $param = null)
    {
        $result = 1;
        $message = null;

        $date = isset($param['date']) ? $param['date'] : date('Y-m', strtotime('last month'));

        $count = (new \paymentModel())
            ->column(['COUNT(1)'])
            ->where([[[['date', '=', $date]], 'and']])
            ->fetchColumn();

        if ($count) {
            $result = 0;
            $message = 'payment "' . $date . '"" 資料已執行過。';
            goto _return;
        }

        $payment_id = (new \paymentModel())
            ->add([
                'date' => $date,
                'state' => 'pretreat',
            ]);

        //用戶於介面點擊建立收益的時間區間
        $starttime = date('Y-m-10 00:00:00', strtotime('last month'));
        $endtime = date('Y-m-09 23:59:59', time());

        (new \incomeModel)
            ->where([[[['state', '=', 'pretreat'], ['inserttime', 'BETWEEN', [$starttime, $endtime]]], 'and']])
            ->edit([
                'payment_id' => $payment_id,
                'state' => 'process',
            ]);

        //
        $file = self::cerateExcel($payment_id);

        if (!file_exists($file)) {
            $result = 0;
            $message = '檔案 "' . $file . '" 不存在。';
            goto _return;
        }

        $filename = $subject = date('Y', strtotime($date)) . ' 年 ' . date('m', strtotime($date)) . ' 月份 pinpinbox 結匯報表.xlsx';

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
            $message = 'Email "' . $date . ' pinpinbox 結匯報表" 寄送失敗。';
            goto _return;
        }

        unlink($file);

        _return:
        return array_encode_return($result, $message);
    }
}