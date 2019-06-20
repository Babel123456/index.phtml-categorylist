<?php

class pay2go
{
    /**
     * 傳過去的參數 pay2go api param(@為表單必填)
     * @callback : 處理網址
     * @MerchantID : 商店代號
     * @RespondType : 回傳格式 (JSON/String)
     * @CheckValue : 檢查碼
     * @TimeStamp : 時間
     * @Version : 串接版本( 1.2 )
     * @MerchantOrderNo : 訂單編號
     * @Amt : 金額
     * @ItemDesc : 商品資訊
     * @LoginType : 是否需登入智付寶會員 1/0
     *  CREDIT : 信用卡(一次付清)
     *  VACC : ATM
     *  CVS : 超商代碼繳費
     *  BARCODE : 條碼繳費
     *  HashKey : 金鑰，產生CheckValue用
     *  HashIV : 金鑰，產生CheckValue用
     *  Email : 付款人信箱，於交易完成或付款完成時，通知付款人使用。
     * UNIONPAY : 銀聯卡
     * WEBATM : 網路 ATM
     *
     *  170414 Mars :
     *  注意 pay2go 改為 spgateway, 原本的pay2go已經變成另一個新的支付系統, 目前沒用到pay2go了
     *  網址為 : https://www.spgateway.com/
     *  串接方式一樣, 但Hashkey、Hashiv 有變動, 已重新取得公司帳號及設定hash值
     */
    private static
        $barcode,
        $callback,
        $credit,
        $cvs,
        $hashiv,
        $hashkey,
        $logintype,
        $merchantid,
        $respondtype,
        $unionpay,
        $vacc,
        $version,
        $webatm;

    function __construct()
    {
        $m_cashflow = (new cashflowModel)->where([[[['cashflow_id', '=', __CLASS__]], 'and']])->fetch();

        if (empty($m_cashflow)) throw new \Exception("Setting error.");

        $a_customize = json_decode($m_cashflow['customize'], true);

        $customize = [
            'barcode',
            'callback',
            'credit',
            'cvs',
            'hashiv',
            'hashkey',
            'logintype',
            'merchantid',
            'respondtype',
            'unionpay',
            'vacc',
            'version',
            'webatm',
        ];

        foreach ($customize as $v0) {
            if (empty($tmp0 = array_multiple_search($a_customize, 'key', $v0))) {
                throw new \Exception("Setting error.");
            }

            self::$$v0 = $tmp0[0]['value'];
        }
    }

    function encrypt(array $param)
    {
        $check = [
            'Amt',
            'ItemDesc',
            'MerchantOrderNo',
            'TimeStamp',
        ];

        foreach ($check as $v_0) {
            if (!isset($param[$v_0])) throw new \Exception("Param error. \"" . $v_0 . "\" is required.");
        }

        //2017-07-20 Lion: 順序不得更動
        $encrypt = [
            'MerchantID' => self::$merchantid,
            'RespondType' => self::$respondtype,
            'TimeStamp' => $param['TimeStamp'],
            'Version' => self::$version,
            'MerchantOrderNo' => $param['MerchantOrderNo'],
            'Amt' => $param['Amt'],
            'ItemDesc' => $param['ItemDesc'],
        ];

        $string = http_build_query($encrypt);
        $len = strlen($string);
        $pad = 32 - ($len % 32);
        $string .= str_repeat(chr($pad), $pad);

        $TradeInfo = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, self::$hashkey, $string, MCRYPT_MODE_CBC, self::$hashiv)));

        $TradeSha = strtoupper(hash("sha256", 'HashKey=' . self::$hashkey . '&' . $TradeInfo . '&HashIV=' . self::$hashiv));

        return [$TradeInfo, $TradeSha];
    }

    function index(array $param)
    {
        $order_id = !empty($param['order_id']) ? $param['order_id'] : null;
        $total = !empty($param['total']) ? $param['total'] : null;
        $assets_info = !empty($param['assets_info']) ? $param['assets_info'] : null;
        $redirectAlbumId = !empty($param['redirectAlbumId']) ? $param['redirectAlbumId'] : null;

        if ($order_id == null || $total == null || $assets_info == null) {
            throw new \Exception("Param error.");
        }

        //更新 order.request
        $tmp1 = [
            'merchantorderno' => $order_id,
            'amt' => $total,
            'respondtype' => self::$respondtype,
            'itemdesc' => $assets_info,
            'notify_url' => $notify_url = frontstageController::url('cashflow', 'feedback', array('cashflow_id' => __CLASS__)),
            'return' => $return = frontstageController::url('cashflow', 'receive', ['cashflow_id' => __CLASS__, 'redirectAlbumId' => $redirectAlbumId]),
        ];

        if (!empty($param['buy'])) {
            $tmp1['currency_code'] = $param['buy']['currency'];
            $tmp1['item_name'] = $param['buy']['name'];
        }

        if (!empty($param['user'])) {
            $tmp1['email'] = $param['user']['email'];
        }

        ksort($tmp1);

        (new orderModel)
            ->where([[[['order_id', '=', $order_id]], 'and']])
            ->edit([
                'callback' => self::$callback,
                'request' => json_encode($tmp1),
            ]);

        switch (self::$version) {
            case '1.2':
                $mer_array = [
                    'Amt' => $total,
                    'MerchantID' => self::$merchantid,
                    'MerchantOrderNo' => $order_id,
                    'TimeStamp' => time(),
                    'Version' => self::$version,
                ];

                ksort($mer_array);

                $check_merstr = http_build_query($mer_array);
                $CheckValue_str = 'HashKey=' . self::$hashkey . '&' . $check_merstr . '&HashIV=' . self::$hashiv;
                $CheckValue = strtoupper(hash('sha256', $CheckValue_str));

                //form
                $form = '<form action="' . self::$callback . '" method="post">';
                $form .= '<input type="hidden" name="Amt" value="' . $total . '">';
                $form .= '<input type="hidden" name="BARCODE" value="' . self::$barcode . '">';
                $form .= '<input type="hidden" name="CheckValue" value="' . $CheckValue . '">';
                $form .= '<input type="hidden" name="CREDIT" value="' . self::$credit . '">';
                $form .= '<input type="hidden" name="CVS" value="' . self::$cvs . '">';
                $form .= '<input type="hidden" name="ItemDesc" value="' . $assets_info . '">';
                $form .= '<input type="hidden" name="LoginType" value="' . self::$logintype . '">';
                $form .= '<input type="hidden" name="MerchantID" value="' . self::$merchantid . '">';
                $form .= '<input type="hidden" name="MerchantOrderNo" value="' . $order_id . '">';
                $form .= '<input type="hidden" name="NotifyURL" value="' . $tmp1['notify_url'] . '">';
                $form .= '<input type="hidden" name="RespondType" value="' . self::$respondtype . '">';
                $form .= '<input type="hidden" name="ReturnURL" value="' . $tmp1['return'] . '">';
                $form .= '<input type="hidden" name="TimeStamp" value="' . time() . '">';
                $form .= '<input type="hidden" name="UNIONPAY" value="' . self::$unionpay . '">';
                $form .= '<input type="hidden" name="VACC" value="' . self::$vacc . '">';
                $form .= '<input type="hidden" name="Version" value="' . self::$version . '">';
                $form .= '<input type="hidden" name="WEBATM" value="' . self::$webatm . '">';

                if (!empty($param['user'])) {
                    $form .= '<input type="hidden" name="Email" value="' . $param['user']['email'] . '">';
                }
                $form .= '</form>';
                break;

            default:
                /**
                 * v1.4
                 */
                $TimeStamp = time();

                list ($TradeInfo, $TradeSha) = $this->encrypt([
                    'Amt' => $total,
                    'ItemDesc' => $assets_info,
                    'MerchantOrderNo' => $order_id,
                    'TimeStamp' => $TimeStamp,
                ]);

                //form
                $form = '<form action="' . self::$callback . '" ' . (($redirectAlbumId === null) ? 'target="_blank"' : '') . ' method="post">';
                $form .= '<input type="hidden" name="Amt" value="' . $total . '">';
                $form .= '<input type="hidden" name="BARCODE" value="' . self::$barcode . '">';
                $form .= '<input type="hidden" name="CREDIT" value="' . self::$credit . '">';
                $form .= '<input type="hidden" name="CVS" value="' . self::$cvs . '">';
                $form .= '<input type="hidden" name="Email" value="' . empty($param['user']) ? '' : $param['user']['email'] . '">';
                $form .= '<input type="hidden" name="ItemDesc" value="' . $assets_info . '">';
                $form .= '<input type="hidden" name="LoginType" value="' . self::$logintype . '">';
                $form .= '<input type="hidden" name="MerchantID" value="' . self::$merchantid . '">';
                $form .= '<input type="hidden" name="MerchantOrderNo" value="' . $order_id . '">';
                $form .= '<input type="hidden" name="NotifyURL" value="' . $tmp1['notify_url'] . '">';
                $form .= '<input type="hidden" name="RespondType" value="' . self::$respondtype . '">';
                $form .= '<input type="hidden" name="ReturnURL" value="' . $tmp1['return'] . '">';
                $form .= '<input type="hidden" name="TradeInfo" value="' . $TradeInfo . '">';
                $form .= '<input type="hidden" name="TradeSha" value="' . $TradeSha . '">';
                $form .= '<input type="hidden" name="TimeStamp" value="' . $TimeStamp . '">';
                $form .= '<input type="hidden" name="UNIONPAY" value="' . self::$unionpay . '">';
                $form .= '<input type="hidden" name="VACC" value="' . self::$vacc . '">';
                $form .= '<input type="hidden" name="Version" value="' . self::$version . '">';
                $form .= '<input type="hidden" name="WEBATM" value="' . self::$webatm . '">';
                $form .= '</form>';
                break;
        }
        return array_encode_return(1, null, null, $form);
    }

    function feedback()
    {
        $result = 0;
        $receive = $_POST;                                        //回傳String格式(array)
        $data = ['order_id' => $receive['MerchantOrderNo']]; //商店自訂訂單編號

        if ($receive['Status'] == 'SUCCESS') {
            switch (self::$version) {
                case '1.2':
                    $check_code = [
                        'MerchantID' => $receive['MerchantID'],
                        'Amt' => $receive['Amt'],
                        'MerchantOrderNo' => $receive['MerchantOrderNo'],
                        'TradeNo' => $receive['TradeNo'],
                    ];

                    ksort($check_code);

                    $check_str = http_build_query($check_code);
                    $CheckCode = strtoupper(hash('sha256', 'HashIV=' . self::$hashiv . '&' . $check_str . '&HashKey=' . self::$hashkey));

                    if ($CheckCode != $receive['CheckCode']) {
                        $result = 0;
                        $message = _('Validation code do not match, please try again.');
                    } else {
                        $result = 1;
                        $data['order_id'] = $receive['MerchantOrderNo'];//商店自訂訂單編號
                        $data['return'] = $_POST;
                        $message = $receive['Message'];                    //敘述此次交易狀態
                    }
                    break;

                default:
                    /**
                     * v1.4
                     *///%待官方回覆缺失函式
                    list ($TradeInfo, $TradeSha) = $this->encrypt([
                        'MerchantOrderNo' => $receive['MerchantOrderNo'],
                        'Amt' => $receive['Amt'],
                        'ItemDesc' => $receive['TradeNo'],
                    ]);

                    if ($receive['TradeInfo'] == $TradeInfo && $receive['TradeSha'] == $TradeSha) {
                        $result = 1;
                        $data['order_id'] = $receive['MerchantOrderNo'];//商店自訂訂單編號
                        $data['return'] = $_POST;
                        $message = $receive['Message'];                    //敘述此次交易狀態
                    } else {
                        $result = 0;
                        $message = _('Validation code do not match, please try again.');
                    }
                    break;
            }
        } else {
            $message = $receive['Message'];
        }

        return array_encode_return($result, $message, null, $data);
    }

    function receive()
    {
        return $this->feedback();
    }
}