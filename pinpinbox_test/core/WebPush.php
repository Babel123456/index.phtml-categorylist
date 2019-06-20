<?php
//2017-08-30 Lion: 發送給推播服務後, 即會在服務的佇列等候裝置接收, 故需記錄是否發送, 避免重複撈而卡住

namespace Core;

class WebPush
{
    static function send(array $param)
    {
        if ($param) {
            $chs = [];
            $mh = curl_multi_init();

            foreach ($param as $k0 => $v0) {
                $chs[$k0] = curl_init();

                switch ($v0['browser']) {
                    case 'chrome':
                        //2016-11-17 Lion: 藉由 post 參數 registration_ids 可一次傳送 1 ~ 1000 個目標, 但不採用是因為需要各別從 curl 取得回傳判斷裝置是否可用
                        curl_setopt($chs[$k0], CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                        curl_setopt($chs[$k0], CURLOPT_POST, true);
                        curl_setopt($chs[$k0], CURLOPT_HTTPHEADER, ['Authorization: key=' . Model('settings')->getByKeyword('GOOGLE_FCM_KEY'), 'Content-Type: application/json']);
                        curl_setopt($chs[$k0], CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($chs[$k0], CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($chs[$k0], CURLOPT_POSTFIELDS, json_encode(['to' => $v0['endpoint']]));
                        break;

                    case 'firefox':
                        curl_setopt($chs[$k0], CURLOPT_URL, 'https://updates.push.services.mozilla.com/wpush/v1/' . $v0['endpoint']);
                        curl_setopt($chs[$k0], CURLOPT_POST, true);
                        curl_setopt($chs[$k0], CURLOPT_HTTPHEADER, ['TTL: 60']);
                        curl_setopt($chs[$k0], CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($chs[$k0], CURLOPT_SSL_VERIFYPEER, false);
                        break;

                    case 'safari':
                        break;
                }

                curl_multi_add_handle($mh, $chs[$k0]);
            }

            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh);
            } while ($running > 0);

            foreach ($chs as $k0 => $v0) {
                $response = json_decode(curl_multi_getcontent($v0), true);

                switch ($param[$k0]['browser']) {
                    case 'chrome':
                        if ($response['failure']) $param[$k0]['enabled'] = false;
                        break;

                    case 'firefox':// 參考 http://autopush.readthedocs.io/en/latest/http.html#error-codes
                        if ($response['errno']) $param[$k0]['enabled'] = false;
                        break;

                    case 'safari':
                        break;
                }

                curl_multi_remove_handle($mh, $v0);
            }

            curl_multi_close($mh);
        }

        return $param;
    }
}