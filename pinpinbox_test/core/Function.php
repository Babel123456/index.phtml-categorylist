<?php
echo "<script>console.log(".json_encode("\core\\function.php:start(通用函式)".date('m/d/Y h:i:s', time())).");</script>";
/**
 * 這個架構制式的 array decode return
 * @param $array
 * @return array
 */
function array_decode_return($array)
{
    $result = $array['result'];
    $message = isset($array['message']) ? $array['message'] : null;
    $redirect = isset($array['redirect']) ? $array['redirect'] : null;
    $data = isset($array['data']) ? $array['data'] : null;

    return [$result, $message, $redirect, $data];
}

/**
 * v1.0 2013-12-17
 * 取得array的深度
 * @param array $array
 * @return number
 */
function array_depth(array $array)
{
    $max_depth = 1;
    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = array_depth($value) + 1;
            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }
    }

    return $max_depth;
}

/**
 * 這個架構制式的 return array
 * <p>v1.0 2015-12-31</p>
 * @param number $result
 * @param string $message
 * @param string $redirect
 * @param string /array $data
 * @return array
 */
function array_encode_return($result, $message = null, $redirect = null, $data = null)
{
    $return = [];
    $return['result'] = $result;
    if ($message !== null) $return['message'] = $message;
    if ($redirect !== null) $return['redirect'] = $redirect;
    if ($data !== null) $return['data'] = $data;

    return $return;
}

/**
 * 比較兩個 array 是否相同
 * <p>v1.0 2014-08-08</p>
 * @param array $param1
 * @param array $param2
 * @return boolean
 */
function array_equal(array $param1, array $param2)
{
    return !array_diff($param1, $param2) && !array_diff($param2, $param1);
}

/**
 * 多維陣列搜尋
 * <p>v1.1 2014-04-09: 參數 $value 可為 null, 如此將 key 值符合 $key 的所有 array 回傳</p>
 * <p>v1.0 2013-12-17</p>
 * @param array $array
 * @param string $key
 * @param string $value
 * @return array
 */
function array_multiple_search($array, $key, $value = null)
{
    $return = array();
    if (is_array($array)) {
        if (isset($array[$key])) {
            if (is_null($value)) {
                $return[] = $array;
            } elseif ($array[$key] == $value) {
                $return[] = $array;
            }
        }
        foreach ($array as $subarray) {
            $return = array_merge($return, array_multiple_search($subarray, $key, $value));
        }
    }

    return $return;
}

/**
 * v1.0 2014-01-24
 * 多維(同層數)陣列比較
 * @param array $array1
 * @param array $array2
 * @return multitype:multitype: unknown
 */
function array_multiple_diff(array $array1, array $array2)
{
    $return = array();
    if (is_array($array1) && is_array($array2)) {
        if (array_depth($array1) == array_depth($array2)) {
            foreach ($array1 as $v1) {
                $same = false;
                foreach ($array2 as $v2) {
                    if (array_depth($v1) == 1 && array_depth($v2) == 1) {
                        if (!array_diff($v1, $v2)) {
                            $same = true;
                            break;
                        }
                    } else {
                        $tmp1 = array();
                        $tmp1 = array_multiple_diff($v1, $v2);
                        if (empty($tmp1)) {
                            $same = true;
                            break;
                        }
                    }
                }
                if (!$same) $return[] = $v1;
            }
        } else {
            $return = $array1;
        }
    }

    return $return;
}

/**
 * array_push 多型別
 * @param array / string $param
 * @param mixed $var
 */
function array_push_p(&$param, $var)
{
    if (!is_array($param)) $param = ($param === null || $param === '') ? [] : [$param];

    array_push($param, $var);
}

/**
 * 將 array 轉換為 html attribute 字串
 * <p>v1.0 2016-02-26</p>
 * @param array $param
 * @return string
 */
function array2htmlattr(array $param)
{
    $a_attr = [];
    foreach ($param as $k0 => $v0) {
        if (is_bool($v0)) {
            $a_attr[] = $k0;
        } else {
            $a_attr[] = is_array($v0) ? $k0 . '="' . implode(' ', $v0) . '"' : $k0 . '="' . $v0 . '"';
        }
    }

    return implode(' ', $a_attr);
}

/**
 * 取得 number / string byte 長度
 * <p>v1.0 2017-03-10</p>
 * @param unknown $value
 * @return number
 */
function bytelen($value)
{
    return strlen($value);
}

/**
 * 取得 number / string char 長度
 * <p>v1.0 2017-03-10</p>
 * @param unknown $value
 * @return number
 */
function charlen($value)
{
    return mb_strlen($value, APP_CHARSET);
}

/**
 * 對本地或遠端檔案判斷是否存在，並可下載(之後刪除)
 * <p>v1.1 2014-08-22: is_file 必須有準確的檔名編碼才能判斷, 目前手邊沒有方法能 windows、linux 共存, 所以檔案在 server 上都要用英數字</p>
 * <p>v1.0 2013-12-19</p>
 * @param unknown $file
 * @param string $download
 * @param string $delete
 * @return boolean
 */
function check_remote_file($file, $download = false, $delete = false)
{
    $return = false;
    if (!empty($file)) {
        if (is_file($file)) {
            $return = true;
            if ($download) {
                $tmp1 = array();
                $tmp1 = pathinfo($file);
                $dirname = $tmp1['dirname'];
                $basename = $tmp1['basename'];
                $extension = $tmp1['extension'];
                $filename = $tmp1['filename'];
                switch (strtolower($extension)) {
                    case 'htm':
                    case 'html':
                        $ctype = 'text/html';
                        break;
                    case 'txt':
                    case 'php':
                        $ctype = 'text/plain';
                        break;
                    case 'pdf':
                        $ctype = 'application/pdf';
                        break;
                    case 'exe':
                        $ctype = 'application/octet-stream';
                        break;
                    case 'zip':
                        $ctype = 'application/zip';
                        break;
                    case 'doc':
                        $ctype = 'application/msword';
                        break;
                    case 'xls':
                        $ctype = 'application/vnd.ms-excel';
                        break;
                    case 'ppt':
                        $ctype = 'application/vnd.ms-powerpoint';
                        break;
                    case 'gif':
                        $ctype = 'image/gif';
                        break;
                    case 'png':
                        $ctype = 'image/png';
                        break;
                    case 'jpe':
                    case 'jpeg':
                    case 'jpg':
                        $ctype = 'image/jpg';
                        break;
                    default:
                        $ctype = 'application/force-download';
                        break;
                }
                $filesize = filesize($file);
            }
        } elseif (is_url($file)) {
            /**
             * CURLOPT_FOLLOWLOCATION : not necessary unless the file redirects (like the PHP example we're using here)
             * CURLOPT_NOBODY : don't download content
             * CURLOPT_FAILONERROR : 顯示HTTP狀態碼，默認行為是忽略編號小於等於400的HTTP信息
             * CURLOPT_RETURNTRANSFER : 將curl_exec()獲取的信息以文件流的形式返回，而不是直接輸出
             * CURLOPT_CONNECTTIMEOUT : 在發起連接前等待的時間，如果設置為0，則無限等待
             * CURLOPT_TIMEOUT : 設置cURL允許執行的最長秒數
             * CURLOPT_HEADER :
             * CURLOPT_FILE : write curl response to file
             */
            $ch = curl_init($file);
            $options = array();
            $options[CURLOPT_FOLLOWLOCATION] = true;
            $options[CURLOPT_NOBODY] = true;
            $options[CURLOPT_FAILONERROR] = true;
            $options[CURLOPT_RETURNTRANSFER] = true;
            $options[CURLOPT_CONNECTTIMEOUT] = 0.5;
            $options[CURLOPT_TIMEOUT] = 0.5;
            curl_setopt_array($ch, $options);
            if (curl_exec($ch) !== false) {
                $return = true;
                if ($download) {
                    //file info
                    $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    $filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                    $basename = basename($file);

                    /*
                     * 當 allow_url_fopen = On 時, 處理 file 的函式才能使用 url 做為文件名
                     * 當 allow_url_fopen = Off 時, 處理 file 的函式僅能使用文件路徑做為文件名
                     */
                    if (!ini_get('allow_url_fopen')) {
                        //先將上個 curl 資源釋放
                        curl_close($ch);

                        //create file
                        $uniqid_basename = uniqid() . '_' . $basename;
                        $path_uniqid_basename = PATH_UPLOAD . $uniqid_basename;
                        $fp = fopen($path_uniqid_basename, 'w');

                        //再次初始 curl, 檔案才會寫成功
                        $ch = curl_init($file);
                        $options = array();
                        $options[CURLOPT_FOLLOWLOCATION] = true;
                        $options[CURLOPT_FILE] = $fp;
                        curl_setopt_array($ch, $options);
                        curl_exec($ch);

                        //file close
                        fclose($fp);

                        //替換掉預備輸出的文件
                        $file = $path_uniqid_basename;

                        //將 tmp 裡的 file 刪除
                        $delete = true;
                    }
                }
            }
            curl_close($ch);
        }

        //下載
        if ($return && $download) {
            //required for IE, otherwise Content-Disposition may be ignored
            if (ini_get('zlib.output_compression')) ini_set('zlib.output_compression', 'Off');

            set_time_limit(0);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: $ctype");
            header('Content-Description: File Transfer');
            header("Content-Disposition: attachment; filename=\"" . $basename . "\";");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $filesize);
            ob_clean();
            flush();
            readfile($file);
        }

        //刪除
        if ($return && $delete) {
            unlink($file);
        }
    }

    return $return;
}

/**
 *  儲存相本時清除資料夾檔案
 * @param string $path_dir
 * @param boolean $bool
 */
function clean_dir($path_dir, $bool)
{
    if (is_dir($path_dir)) {
        $objects = scandir($path_dir);

        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($path_dir . "/" . $object) == "dir") {
                    clean_dir($path_dir . "/" . $object, false);
                } else {
                    unlink($path_dir . "/" . $object);
                }
            }
        }

        reset($objects);

        if ($bool != true) {
            rmdir($path_dir);
        }
    }
}

/**
 * v1.0 2015-12-24
 * @param string $url
 * @param string / array $param
 * @param string $method
 * @return Ambigous <boolean, mixed>
 */
function curl($url, $param = null, $method = 'post')
{
    switch (strtolower($method)) {
        case 'post':
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
            break;
        case 'get':
            $ch = curl_init($url . (empty($param) ? null : '?' . http_build_query($param, '', '&')));
            break;
    }
    if (SITE_EVN == 'development') {
        curl_setopt_array($ch, [
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ]);
    }
    curl_setopt_array($ch, [
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $return = curl_exec($ch);
    curl_close($ch);

    //因為 CURLOPT_RETURNTRANSFER = true 會爬成字串型態, 為避免誤判, 將 string 'true'/'false' 轉為 boolean true/false
    if ('true' === $return) $return = true;
    elseif ('false' === $return) $return = false;

    return $return;
}

/**
 * 將數字轉換成國際代號 千、萬、十萬 => K, 百萬、千萬、億 => M
 * v1.0 2017-09-26
 * @param int $n
 * @param int $p (precision)
 * @return int
 */
function custom_number_format($n, $p = 1)
{
    if ($n < 1000000) {
        $n_format = ($n < 10000) ? number_format($n) : number_format($n / 1000, $p) . 'K';
    } else if ($n < 1000000000) {
        $n_format = number_format($n / 1000000, $p) . 'M';
    }
    return str_replace(',', '', $n_format);
}

/**
 * 刪除一個目錄(包含目錄裡所有檔案)
 * v1.0 2014-01-16
 * @param unknown $dir
 */
function deletedirwithfile($dir)
{
    $it = new RecursiveDirectoryIterator($dir);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->getFilename() === '.' || $file->getFilename() === '..') {
            continue;
        }
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($dir);
}

/**
 * v1.0 2016-05-12
 * 計算兩點座標之間的距離, 參考 https://www.geodatasource.com/developers/php
 * @param number $lat1
 * @param number $lon1
 * @param number $lat2
 * @param number $lon2
 * @param string $unit
 * @return number
 */
function distance($lat1, $lon1, $lat2, $lon2, $unit = null)
{
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;

    switch (strtoupper($unit)) {
        case 'K':
            $miles = $miles * 1.609344;
            break;

        case 'N':
            $miles = $miles * 0.8684;
            break;
    }

    return $miles;
}

/**
 * v1.0 2014-01-24
 * 動態 table 名稱
 * @param unknown $table
 * @param unknown $suffix
 * @return string
 */
function dynamic_tablename($table, $suffix)
{
    return $table . '$' . $suffix;
}

/**
 * v1.0 2013-12-17
 * 郵件機器人(使用gmail)
 * @param $account
 * @param $password
 * @param $from_name
 * @param $to
 * @param $subject
 * @param $body
 * @param array $attachment
 * @return bool
 */
function email($account, $password, $from_name, $to, $subject, $body, $attachment = [])
{
    $boolean = true;

    $mail = new PHPMailer();  //create a new object

    try {
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP(); //enable SMTP
        $mail->isHTML();
        $mail->SMTPDebug = 0;  //debugging: 1 = errors and messages, 2 = messages only
        $mail->SMTPAuth = true;  //authentication enabled
        $mail->SMTPSecure = 'ssl'; //secure transfer enabled REQUIRED for Gmail
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->Username = $account;
        $mail->Password = $password;
        $mail->setFrom($account, $from_name);
        $mail->Subject = $subject;
        $mail->Body = $body;

        //收件者
        if (is_array($to)) {
            foreach ($to as $mailaddress) {
                $mail->addAddress($mailaddress);
            }
        } else {
            $mail->addAddress($to);
        }

        //附件
        if (!empty($attachment)) {
            foreach ($attachment as $v) {
                $mail->addAttachment($v['tmp_name'], $v['name']);
            }
        }

        //送出
        $mail->send();
    } catch (Exception $e) {
        $boolean = false;

        \userlogModel::setExceptionV2(\Lib\Exception::LEVEL_NOTICE, $mail->ErrorInfo);
    }

    return $boolean;
}

/**
 * 加密
 * <p>v1.1 2015-12-31 Lion: 將 http_build_query 換為 implode, 捨去 urlencode 的部分</p>
 * <p>v1.0 2014-01-24</p>
 * @param array $param
 * @param string $secret
 * @return string
 */
function encrypt(array $param, $secret = SITE_SECRET)
{
    ksort($param);

    //檢查用, 記得產出的檔案要刪掉
    /*
    $f = fopen('encrypt.log', 'a+');
    fwrite($f, "[".date('Y-m-d H:i:s')." ".M_METHOD."]".strtolower(http_build_query($param, '', '&')).$secret."\r\n");
    fclose($f);
    */
    $tmp1 = [];
    foreach ($param as $k0 => $v0) {
        $tmp1[] = $k0 . '=' . $v0;
    }

    return strtolower(md5(strtolower(implode('&', $tmp1)) . $secret));
}

/**
 * 異常資訊字串
 * <p>v1.0 2016-03-16</p>
 * @param Exception $e
 * @return string
 */
function exceptioninfostring(\Exception $e)
{
    $trace = explode("\n", $e->getTraceAsString());
    $trace = array_reverse($trace); // reverse array to make steps line up chronologically
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $array1 = [];
    for ($i = 0; $i < $length; ++$i) {
        $array1[] = ($i + 1) . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }
    $info = 'Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . '(' . $e->getLine() . '). ' . implode(' ', $array1);

    return $info;
}

/**
 * v1.0 2013-12-17
 * @param unknown $string
 * @param unknown $attr
 * @param unknown $quote
 * @return boolean|Ambigous <>
 */
function explode_attr($string, $attr, $quote)
{
    $tmp1 = explode($attr . '=' . $quote, $string);

    if (empty($tmp1[1])) return false;

    return explode($quote, $tmp1[1])[0];
}

/**
 * Extension 實例
 * <p>v1.0 2015-09-30</p>
 * @param string $extension
 * @return object
 */
function Extension($extension)
{
    static $instance = array();
    $extension = 'Extension\\' . $extension;
    $tmp0 = [];
    foreach (explode('\\', $extension) as $v0) {
        if ($v0 != null) $tmp0[] = $v0;
    }
    $extension = implode('\\', $tmp0);
    if (!isset($instance[$extension])) $instance[$extension] = new $extension;

    return $instance[$extension];
}

/**
 * 有些影片不提供 maxresdefault 尺寸的圖片
 * 若要求的尺寸找不到圖片 則向下個size尋找
 * @param string $url ,
 * @param string $output
 * @param string $size ,
 * @return String $img_url
 **/
function fetch_youtube_thumbnail_check($url, $output, $size)
{
    /*  size list :
        1. 'maxresdefault.jpg'  1280*720 影片大圖(最高解析度大圖)
        2. 'sddefault.jpg'      640*480 影片大圖
        3. 'mqdefault.jpg'		320*180 影片大圖 去黑邊
        4. 'hqdefault.jpg'		480*360 影片大圖
        5. 'default.jpg';		120*90  影片大圖
        6. '3.jpg';	break;		影片結尾
        7. '2.jpg';	break;		影片中間
        8. '1.jpg';	break; 		影片開始一點點
    */
    $return = null;
    $a_type = [null, 'maxresdefault', 'sddefault', 'mqdefault', 'hqdefault', 'default', '3', '2', '1'];

    //get youtube id
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);

    $video_id = $match[1];

    foreach ($a_type as $k0 => $v0) {
        $a_refer[] = [
            'http://i.ytimg.com/vi/' . $video_id . '/' . $v0 . '.jpg',
            'http://i.ytimg.com/vi/' . $video_id . '/' . $v0 . '_live.jpg',
            'http://img.youtube.com/vi/' . $video_id . '/' . $v0 . '.jpg',
            'http://img.youtube.com/vi/' . $video_id . '/' . $v0 . '_live.jpg',
        ];
    }

    //取得指定size圖片
    foreach ($a_refer[$size] as $k0 => $v0) {
        $handle = curl_init($v0);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        $response = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        //網頁存在即回傳
        if ($httpCode == 200) {
            return $v0;
        }
    }

    //找不到指定size, 往下一個size尋找
    if (is_null($return)) {
        foreach ($a_refer as $k0 => $v0) {
            if ($k0 == $size || $k0 == 0) continue;
            foreach ($v0 as $v1) {
                $handle = curl_init($v1);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
                $response = curl_exec($handle);
                $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                curl_close($handle);
                //網頁存在即回傳
                if ($httpCode == 200) {
                    return $v1;
                }
            }
        }
    }

    //截不到圖,用預設圖片
    $return = static_file('images/video_origin.jpg');

    return $return;
}

/**
 * 取得影片截圖
 *  20160825 - 調整Vimeo截圖路徑
 * @param string $url ,
 * @param string $output
 * @param string $platform
 * @param string $size ,
 * @return boolean
 **/
function fetch_remote_thumbnail($url, $output, $platform, $size = 1)
{
    //取得圖片連結
    $return = 1;
    $data = 0;
    switch ($platform) {
        case 'dailymotion' :
            $id = end(explode('/', $url));
            $data = file_get_contents("https://api.dailymotion.com/video/" . $id . "?fields=thumbnail_360_url");
            $result = json_decode($data);
            $img_url = $result->thumbnail_360_url;

            break;

        case 'facebook' :
            $a_url = explode('/', $url);
            if (false !== $key = array_search('videos', $a_url)) {
                if (is_numeric($a_url[$key + 1])) {
                    set_error_handler(
                        create_function(
                            '$severity, $message, $file, $line',
                            'throw new ErrorException($message, $severity, $severity, $file, $line);'
                        )
                    );
                    try {
                        $data = file_get_contents("https://graph.facebook.com/" . $a_url[$key + 1] . "?fields=format");
                    } catch (Exception $e) {
                        $return = 0;
                    }
                    restore_error_handler();
                    if ($data) {
                        $result = json_decode($data);
                        $count = count($result->format) - 1;
                        $thumbnail = $result->format[$count]->picture;
                        $img_url = str_replace('https', 'http', $thumbnail);
                    }
                } else {
                    $return = 0;
                }
            } else {
                $return = 0;
            }

            break;

        case 'youtube':
            $img_url = fetch_youtube_thumbnail_check($url, $output, $size);
            break;

        default:
            /**
             *  Vimeo 1280*720 影片大圖
             */
            $tags = get_meta_tags($url);
            $refer_url = str_replace('https', 'http', $tags['twitter:image']);
            $refer_video_code = explode('_', end(explode('/', $refer_url)));
            $img_url = 'http://i.vimeocdn.com/video/' . $refer_video_code[0] . '_1280x720.jpg';
            break;
    }

    if ($return) {
        //透過連結取得遠端圖片
        $fp_output = fopen($output, 'w');
        $ch = curl_init($img_url);
        curl_setopt($ch, CURLOPT_FILE, $fp_output);
        curl_exec($ch);
        curl_close($ch);
        exec('libreoffice', $out, $status);
    } else {
        //遠端截圖失敗則使用預設圖片
        copy(PATH_STATIC_FILE . M_PACKAGE . DIRECTORY_SEPARATOR . SITE_LANG . DIRECTORY_SEPARATOR . 'images/play_video_default.png', $output);
    }

    return true;
}

function fetch_video_platform($url)
{
    $platform = '';

    if (is_url($url)) {
        if ((preg_match('/(youtu.be)/i', $url) || preg_match('/(youtube.com)/i', $url)) && !preg_match('/(embed)/i', $url)) {
            $platform = 'youtube';
        } elseif (preg_match('/(vimeo)/i', $url)) {
            $platform = 'vimeo';
        } elseif (preg_match('/(facebook)/i', $url)) {
            $platform = 'facebook';
        } elseif (preg_match('/(dailymotion)/i', $url)) {
            $platform = 'dailymotion';
        } elseif (preg_match('/(pinpinbox)/i', $url)) {
            $platform = 'pinpinbox';
        }
    }

    return $platform;
}

/**
 * 架構檔案資訊
 * <p>v1.2 2015-07-15: 增加搜尋 static_file</p>
 * <p>v1.1 2015-07-08: path, url 分別取得</p>
 * <p>v1.0 2015-05-08</p>
 * @param string $file
 * @return array
 *     path: 檔案完整路徑, 如果沒有找到檔案, 僅回傳部分資訊
 *     url: 檔案完整網址, 如果沒有找到檔案, 僅回傳部分資訊
 *     rootpath: 檔案根目錄路徑, 如果 param $file 僅給予檔案目錄路徑, 則會試著搜尋以得出
 *     rooturl: 檔案根目錄網址, 如果 param $file 僅給予檔案目錄路徑, 則會試著搜尋以得出
 *     subpath: 檔案子目錄路徑
 *     suburl: 檔案子網址路徑
 *     dirname、basename、extension、filename: 同 pathinfo 資訊
 */
function fileinfo($file)
{
    $path = null;
    $url = null;
    $root = null;
    $rootpath = null;
    $rooturl = null;
    $sub = null;
    $subpath = null;
    $suburl = null;
    $dirname = null;
    $basename = null;
    $extension = null;
    $filename = null;
    if ($file) {
        $search = array(PATH_STATIC_FILE, URL_STATIC_FILE, PATH_STORAGE, URL_STORAGE, PATH_UPLOAD, URL_UPLOAD);
        $path2url = array(
            PATH_STATIC_FILE => URL_STATIC_FILE,
            PATH_STORAGE => URL_STORAGE,
            PATH_UPLOAD => URL_UPLOAD,
        );
        $url2path = array(
            URL_STATIC_FILE => PATH_STATIC_FILE,
            URL_STORAGE => PATH_STORAGE,
            URL_UPLOAD => PATH_UPLOAD,
        );

        //完整資訊
        foreach ($search as $v0) {
            $pos = strpos($file, $v0);
            if ($pos !== false) {
                $root = $v0;
                $sub = substr_replace($file, '', $pos, strlen($v0));
                break;
            }
        }

        //只有 sub 資訊, 回推
        if ($root == null) {
            foreach ($search as $v0) {
                if (strpos($v0, URL_ROOT) !== false) continue;//只找 path

                if (is_file($v0 . $file)) {
                    $root = $v0;
                    $sub = $file;
                    break;
                }
            }
            if ($root == null) $sub = $file;
        }

        //path
        $rootpath = array_key_exists($root, $url2path) ? $url2path[$root] : $root;
        $subpath = str_replace('/', DIRECTORY_SEPARATOR, $sub);
        $path = $rootpath . $subpath;

        //url
        $rooturl = array_key_exists($root, $path2url) ? $path2url[$root] : $root;
        $suburl = str_replace(DIRECTORY_SEPARATOR, '/', $sub);
        $url = $rooturl . $suburl;

        //pathinfo
        $pathinfo = pathinfo($path);
        $dirname = $pathinfo['dirname'];
        $basename = $pathinfo['basename'];
        if (isset($pathinfo['extension'])) $extension = $pathinfo['extension'];
        $filename = $pathinfo['filename'];
    }

    return array('path' => $path, 'url' => $url, 'rootpath' => $rootpath, 'rooturl' => $rooturl, 'subpath' => $subpath, 'suburl' => $suburl, 'dirname' => $dirname, 'basename' => $basename, 'extension' => $extension, 'filename' => $filename);
}

//ftp
function ftp()
{

}

/**
 * (nginx 沒有這個函式，留意自訂標頭名稱不能使用下底線(_)，否則不能解析)
 * <p>v1.0 2017-09-28</p>
 */
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

/**
 * 取得 http code
 * v1.0 2018-05-15
 * @param $url
 * @return mixed
 */
function gethttpcode($url)
{
    $handle = curl_init($url);

    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

    curl_exec($handle);

    $httpcode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

    curl_close($handle);

    return $httpcode;
}

/**
 * 依圖檔類型及目標寬、高產生縮圖
 * <p>v1.3 2015-05-08: 以 fileinfo 處理</p>
 * <p>v1.2 2014-08-29: 增加往  PATH_TMP_FILE 找檔案</p>
 * <p>v1.1 2014-05-20: 改為寬與高各別設定，以及個別檔名；數位圖像翻轉情況</p>
 * <p>v1.0 2013-12-17</p>
 * @param unknown $image
 * @param number $width_target
 * @param number $height_target
 * @return Ambigous <NULL, string>
 */
function getimageresize($image, $width_target = 100, $height_target = 100)
{
    $return = null;
    if ($image) {
        $image = image_source($image);

        $fileinfo = fileinfo($image);
        $image_path = $fileinfo['path'];

        //圖像是否存在
        if (is_image($image_path)) {
            $dirname = $fileinfo['dirname'];
            $basename = $fileinfo['basename'];
            $extension = $fileinfo['extension'];
            $filename = $fileinfo['filename'];

            list($width, $height, $type, $attr) = getimagesize($image_path);

            /**
             * 2014-07-29:
             *     有些圖像會有無法解讀的 exif, 因此用 @ 屏蔽
             * 2014-05-01:
             *     處理數位圖像翻轉的情況，其中正負 90 度(以及其倍數)翻轉的圖像，用 getimagesize 取得的 $width 和 $height 會和實際相反，因此對換；
             *     另外 exif_read_data 僅能支持 JPEG、TIFF
             */
            if ($type == 2) {
                $exif = @exif_read_data($image_path);

                if (isset($exif['Orientation'])) {
                    $Orientation = $exif['Orientation'];
                    switch ($Orientation) {
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                            $tmp2 = $height;
                            $height = $width;
                            $width = $tmp2;
                            break;
                    }
                }
            }

            $w_rate = ($width / $width_target < 1) ? 1 : $width / $width_target;
            $h_rate = ($height / $height_target < 1) ? 1 : $height / $height_target;
            $rate = ($h_rate > $w_rate) ? $h_rate : $w_rate;
            $width_new = round($width / $rate);
            $height_new = round($height / $rate);

            $image_thumbnail = (1 == $w_rate && 1 == $h_rate) ? $image_path : $dirname . '/' . $filename . '_' . $width_new . 'x' . $height_new . '.' . $extension;//如果比例不變, 則維持原圖

            $image_thumbnail_path = fileinfo($image_thumbnail)['path'];

            //縮圖是否存在
            if (!is_file($image_thumbnail_path)) {
                if (class_exists('imagick')) {
                    $imagick = new imagick($image_path);
                    if (isset($Orientation)) {
                        switch ($Orientation) {
                            case 0: // undefined?
                            case 1: // nothing
                                break;

                            case 2: // horizontal flip
                                $imagick->flopImage();
                                break;

                            case 3: // 180 rotate left
                                $imagick->rotateImage(new ImagickPixel(), 180);
                                break;

                            case 4: // vertical flip
                                $imagick->flipImage();
                                break;

                            case 5: // vertical flip + 90 rotate right
                                $imagick->flipImage();
                                $imagick->rotateImage(new ImagickPixel(), 90);
                                break;

                            case 6: // 90 rotate right
                                $imagick->rotateImage(new ImagickPixel(), 90);
                                break;

                            case 7: // horizontal flip + 90 rotate right
                                $imagick->flopImage();
                                $imagick->rotateImage(new ImagickPixel(), 90);
                                break;

                            case 8: // 90 rotate left
                                $imagick->rotateImage(new ImagickPixel(), -90);
                                break;

                            //170705 Mantis #1396 : 處理圖片得到65536 / 65537 造成異常, 日後取得此型態不進行圖片翻轉及拋出異常
                            // default:
                            // 	throw new Exception("[".__FUNCTION__."] Unknown case");
                            // 	break;
                        }
                        //^$imagick->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
                    }

                    $imagick->thumbnailImage($width_new, $height_new);
                    $imagick->writeImage($image_thumbnail_path);
                    $imagick->clear();
                } else {
                    /**
                     * 創建圖像標識符
                     * 2014-08-01: 應該不是判斷圖檔類型來使用 imagecreate 或 imagecreatetruecolor, 而是要判斷圖色構成(方法尋找中..)
                     */
                    $im_new = imagecreatetruecolor($width_new, $height_new);
                    $im_source = imagecreatefromX($image_path);

                    /**
                     * 輸出縮圖
                     * 1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
                     */
                    switch ($type) {
                        case 1:
                            imagecolortransparent($im_new, imagecolorallocatealpha($im_new, 0, 0, 0, 127));
                            imagealphablending($im_new, false);
                            imagesavealpha($im_new, true);
                            break;

                        case 2:
                            //在這裡也要把來源圖像做翻轉
                            if (isset($Orientation)) {
                                switch ($Orientation) {
                                    case 0: // undefined?
                                    case 1: // nothing
                                        break;

                                    case 2: // horizontal flip
                                        imageflip($im_source, 1);
                                        break;

                                    case 3: // 180 rotate left
                                        $im_source = imagerotate($im_source, 180, 0);
                                        break;

                                    case 4: // vertical flip
                                        imageflip($im_source, 2);
                                        break;

                                    case 5: // vertical flip + 90 rotate right
                                        imageflip($im_source, 2);
                                        $im_source = imagerotate($im_source, -90, 0);
                                        break;

                                    case 6: // 90 rotate right
                                        $im_source = imagerotate($im_source, -90, 0);
                                        break;

                                    case 7: // horizontal flip + 90 rotate right
                                        imageflip($im_source, 1);
                                        $im_source = imagerotate($im_source, -90, 0);
                                        break;

                                    case 8: // 90 rotate left
                                        $im_source = imagerotate($im_source, 90, 0);
                                        break;

                                    default:
                                        throw new Exception("[" . __FUNCTION__ . "] Unknown case");
                                        break;
                                }
                            }
                            break;

                        case 3:
                            imagecolortransparent($im_new, imagecolorallocatealpha($im_new, 0, 0, 0, 127));
                            imagealphablending($im_new, false);
                            imagesavealpha($im_new, true);
                            break;

                        default:
                            throw new Exception("[" . __FUNCTION__ . "] Unknown case");
                            break;
                    }

                    imagecopyresampled($im_new, $im_source, 0, 0, 0, 0, $width_new, $height_new, $width, $height);//複製圖像

                    imageX($im_new, $image_thumbnail_path);//輸出

                    //釋放與 image 關聯的內存
                    imagedestroy($im_source);
                    imagedestroy($im_new);
                }
            }
            $return = $image_thumbnail;
        }
    }

    return fileinfo($return)['suburl'];
}

/**
 * 回傳 pdf 檔案資訊
 * <p>v1.0 2016-11-03 only work on Linux</p>
 * @param string $file
 * @return int pages
 *
 * pdfinfo 回傳範例 :
 *    Title:          test1.pdf
 * Author:         John Smith
 * Creator:        PScript5.dll Version 5.2.2
 * Producer:       Acrobat Distiller 9.2.0 (Windows)
 * CreationDate:   01/09/13 19:46:57
 * ModDate:        01/09/13 19:46:57
 * Tagged:         yes
 * Form:           none
 * Pages:          13    <-- This is what we need
 * Encrypted:      no
 * Page size:      2384 x 3370 pts (A0)
 * File size:      17569259 bytes
 * Optimized:      yes
 * PDF version:    1.6
 */
function getPDFPages($file)
{
    $cmd = "pdfinfo"; // Linux
    exec("$cmd \"$file\"", $output);
    $pagecount = 0;
    foreach ($output as $op) {
        if (preg_match("/Pages:\s*(\d+)/i", $op, $matches) === 1) {
            $pagecount = intval($matches[1]);
            break;
        }
    }

    return $pagecount;
}

/**
 * 2018-06-28
 * 取得 web service 所有者名稱
 * @return mixed
 */
function get_process_owner()
{
    return posix_getpwuid(posix_geteuid())['name'];
}

/**
 * 將圖檔轉換格式，並可 resize
 * <p>v1.1 2015-06-10: 調整檔案判斷, 否則原檔存在將不會進行處理</p>
 * <p>v1.0 2014-09-10</p>
 * @param unknown $image_folder
 * @param string $extension
 * @param string $width_target
 * @param string $height_target
 * @throws Exception
 * @return Ambigous <Ambigous, NULL, mixed>
 */
function image_reformat($image_folder, $extension = 'jpg', $width_target = null, $height_target = null)
{
    $image_folder = image_source($image_folder);
    $extension = strtolower($extension);
    $fileinfo = fileinfo($image_folder);
    $image_path_reformat = $fileinfo['dirname'] . '/' . $fileinfo['filename'] . '.' . $extension;
    list($width, $height) = getimagesize($fileinfo['path']);
    if ($width_target === null) $width_target = $width;
    if ($height_target === null) $height_target = $height;
    if ($width_target != $width || $height_target != $height) $image_path_reformat = $fileinfo['dirname'] . '/' . $fileinfo['filename'] . '_' . $width_target . 'x' . $height_target . '.' . $extension;
    if (!file_exists($image_path_reformat)) {
        if (class_exists('imagick')) {
            $imagick = new imagick();
            $imagick->setResolution(108, 108);//max 300, 300
            $imagick->readimage($fileinfo['path']);
            $imagick->setImageFormat($extension);
            $imagick->resizeImage($width_target, $height_target, Imagick::FILTER_CATROM, 1);
            $imagick->writeImage($image_path_reformat);
            $imagick->clear();
        } else {
            /**
             * 創建圖像標識符
             * 2014-08-01: 應該不是判斷圖檔類型來使用 imagecreate 或 imagecreatetruecolor, 而是要判斷圖色構成(方法尋找中..)
             */
            $im_new = imagecreatetruecolor($width_target, $height_target);
            $im_source = imagecreatefromX($image_folder);

            /**
             * 輸出縮圖
             * 1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
             */
            switch ($extension) {
                case 'gif':
                case 'png':
                    imagecolortransparent($im_new, imagecolorallocatealpha($im_new, 0, 0, 0, 127));
                    imagealphablending($im_new, false);
                    imagesavealpha($im_new, true);
                    break;

                case 'jpg':
                case 'jpeg':
                    break;

                default:
                    throw new Exception("[" . __FUNCTION__ . "] Unknown case");
                    break;
            }

            //複製圖像
            imagecopyresampled($im_new, $im_source, 0, 0, 0, 0, $width_target, $height_target, $width, $height);

            //輸出
            imageX($im_new, $image_path_reformat);

            //釋放與 image 關聯的內存
            imagedestroy($im_source);
            imagedestroy($im_new);
        }
    }

    return fileinfo($image_path_reformat)['suburl'];
}

/**
 * image 去掉 resize 檔名部分
 * <p>v1.1 2015-07-01: 改用 preg_replace</p>
 * <p>v1.0 2014-09-04</p>
 * @param string $image_folder
 * @return string
 */
function image_source($image_folder)
{
    $pathinfo = pathinfo($image_folder);
    $image_folder = $pathinfo['dirname'] . '/' . preg_replace('/(_[0-9]+x[0-9]+)$/i', '', $pathinfo['filename']) . '.' . $pathinfo['extension'];

    return $image_folder;
}

/**
 * 判斷確切的圖檔類型來使用 image
 * <p>v1.2 2014-08-29: 增加往  PATH_TMP_FILE 找檔案</p>
 * <p>v1.1 2014-03-28: 加上 is_url</p>
 * <p>v1.0 2014-03-26</p>
 * @param unknown $image_resource image 資源
 * @param unknown $filename 輸出檔案
 * @throws Exception
 * @return boolean
 */
function imageX($image_resource, $image_path)
{
    $return = false;
    $extname = strtolower(pathinfo($image_path, PATHINFO_EXTENSION));
    switch ($extname) {
        case 'gif':
            //輸出
            $return = imagegif($image_resource, $image_path);
            break;

        case 'jpg':
        case 'jpeg':
            //輸出
            $return = imagejpeg($image_resource, $image_path, 100);
            break;

        case 'png':
            //輸出
            $return = imagepng($image_resource, $image_path);
            break;

        case 'bmp':
            //輸出
            $return = imagewbmp($image_resource, $image_path);
            break;

        default:
            throw new Exception("[" . __FUNCTION__ . "] Unknown case");
            break;
    }

    return $return;
}

/**
 * 判斷確切的圖檔類型來使用 imagecreatefrom
 * <p>v1.4 2015-05-12: 移除補強的處理, 一方面端正輸入參數, 另一方面在正常處理下也不用跑那麼多函式</p>
 * <p>v1.3 2015-05-08: 以 fileinfo 處理</p>
 * <p>v1.2 2014-08-29: 增加往  PATH_TMP_FILE 找檔案</p>
 * <p>v1.1 2014-03-28: 加上 is_url</p>
 * <p>v1.0 2014-03-18</p>
 * @param unknown $file
 * @throws Exception
 * @return Ambigous <boolean, resource>
 */
function imagecreatefromX($file)
{
    //1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
    switch (getimagesize($file)[2]) {
        case 1:
            $return = imagecreatefromgif($file);
            break;

        case 2:
            $return = imagecreatefromjpeg($file);
            break;

        case 3:
            $return = imagecreatefrompng($file);
            break;

        case 6:
            $return = imagecreatefromwbmp($file);
            break;

        default:
            //^ 用 imagecreatefromstring ?
            throw new Exception("[" . __FUNCTION__ . "] Unknown case");
            break;
    }

    return $return;
}

/**
 * v1.0 2013-12-17
 * @return string
 */
function insertdate()
{
    return date('Y-m-d');
}

/**
 * v1.0 2013-12-17
 * @param string $second
 * @return string
 */
function inserttime($second = null)
{
    return date('Y-m-d H:i:s', time() + (int)$second);
}

/**
 * 判斷是否為音頻檔
 * <p>v1.1 2016-08-12 Lion: 添加 is_file</p>
 * <p>v1.0 2016-01-25</p>
 * @param string $file 檔案絕對路徑
 * @return boolean
 */
function is_audio($file)
{
    static $finfo;
    if ($finfo === null) $finfo = new \finfo();
    return (is_file($file) && strpos($finfo->file($file, FILEINFO_MIME_TYPE), 'audio/') !== false) ? true : false;
}

/**
 *
 * <p>v1.0 2018-01-10</p>
 * @param $datetime
 * @param string $format
 * @return bool
 */
function is_datetime($datetime, $format = 'Y-m-d H:i:s')
{
    \DateTime::createFromFormat($format, $datetime);

    $errors = \DateTime::getLastErrors();

    return ($errors['warning_count'] || $errors['error_count']) ? false : true;
}

/**
 * 判斷連線是否為 https
 * <p>v1.0 2014-09-16</p>
 * @return boolean
 */
function is_https()
{
    $return = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
        $return = true;
    } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
        $return = true;
    }

    return $return;
}

/**
 * 判斷是否為圖檔
 * @param $file
 * @param array|null $allow
 * @return bool
 */
function is_image($file, array $allow = null)
{
    $return = false;

    $imagetype = exif_imagetype($file);

    if ($allow && in_array($imagetype, $allow)) {
        $return = true;
    } elseif ($imagetype) {
        $return = true;
    }

    return $return;
}

/**
 * 判斷是否為 json
 * <p>v1.0 2017-11-14</p>
 * @param $string
 * @return bool
 */
function is_json($string)
{
    $bool = false;

    if (is_string($string)) {
        json_decode($string);

        $bool = (json_last_error() == JSON_ERROR_NONE);
    }

    return $bool;
}

/**
 * 判斷是否為視頻檔
 * <p>v1.0 2016-01-25</p>
 * @param string $file 檔案絕對路徑
 * @return boolean
 */
function is_video($file)
{
    static $finfo;

    if ($finfo === null) $finfo = new \finfo();

    $MINE_video = strpos($finfo->file($file, FILEINFO_MIME_TYPE), 'video/');
    $MINE_application = strpos($finfo->file($file, FILEINFO_MIME_TYPE), 'application/');

    return ($MINE_video !== false || $MINE_application !== false) ? true : false;
}

/**
 * v1.0 2013-12-19
 * 判斷是否為 windows 的檔名(不能含有 \/:*?"<>|)
 * @param unknown $file
 * @return boolean
 */
function is_windows_filename($file)
{
    return !preg_match('/[\/\?\*:"<>|\\\]+/', basename($file));
}

/**
 * 判斷是否為 url
 * @param $value
 * @return mixed
 */
function is_url($value)
{
    return filter_var($value, FILTER_VALIDATE_URL);
}

/**
 * 判斷是否為 ajax
 * <p>v1.0 2013-12-17</p>
 * @return boolean
 */
function is_ajax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * 判斷是否為 post
 * <p>v1.0 2014-12-05</p>
 * @return boolean
 */
function is_post()
{
    return isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post';
}

/**
 * 這個架構制式的 json decode
 * <p>v1.0 2014-02-27</p>
 * @param unknown $json
 * @return multitype:Ambigous <NULL, unknown> mixed
 */
function json_decode_return($json)
{
    $tmp1 = json_decode($json, true);
    $result = $tmp1['result'];
    $message = isset($tmp1['message']) ? $tmp1['message'] : null;
    $redirect = isset($tmp1['redirect']) ? $tmp1['redirect'] : null;
    $data = isset($tmp1['data']) ? $tmp1['data'] : null;

    return [$result, $message, $redirect, $data];
}

/**
 * 這個架構制式的 return json
 * <p>v1.0 2015-12-31</p>
 * @param number $result
 * @param string $message
 * @param string $redirect
 * @param string /array $data
 * @return string
 */
function json_encode_return($result, $message = null, $redirect = null, $data = null)
{
    $return = [];
    $return['result'] = $result;
    if ($message !== null) $return['message'] = $message;
    if ($redirect !== null) $return['redirect'] = $redirect;
    if ($data !== null) $return['data'] = $data;

    Model('userlog')->setReturn($return);

    die(json_encode($return));
}

/**
 * ksort 多層陣列
 * <p>v1.0 2014-07-28</p>
 * @param array $array
 * @return unknown
 */
function ksort_multiple(array $array)
{
    ksort($array);
    foreach ($array as &$v1) {
        if (is_array($v1)) {
            $v1 = ksort_multiple($v1);
        }
    }

    return $array;
}

/**
 * Lib 實例
 * <p>v1.0 2015-10-19</p>
 * @param string $lib
 * @return object
 */
function Lib($lib)
{
    static $instance = array();
    $lib = 'Lib\\' . $lib;
    $tmp0 = [];
    foreach (explode('\\', $lib) as $v0) {
        if ($v0 != null) $tmp0[] = $v0;
    }
    $lib = implode('\\', $tmp0);
    if (!isset($instance[$lib])) $instance[$lib] = new $lib;

    return $instance[$lib];
}

/**
 * log file
 * <p>v1.0 2014-06-03</p>
 * @param unknown $model
 * @param unknown $param
 */
function log_file($model = array(), $param = array())
{
    //路徑
    $dir = PATH_LOG;
    foreach ($model as $v1) {
        $dir .= $v1 . '/';
        if (!is_dir($dir)) mkdir($dir, 0755);
    }

    //檔名
    $filename = date('Ymd') . '.log';

    //檔案完整路徑
    $dir_filename = $dir . $filename;

    ksort($param);
    $string = '[' . date('Y-m-d H:i:s') . ']';
    $index = 0;
    $count_param = count($param);
    foreach ($param as $k1 => $v1) {
        $string .= ($index + 1 == $count_param) ? $k1 . ' => ' . $v1 : $k1 . ' => ' . $v1 . ', ';
        ++$index;
    }

    $handle = fopen($dir_filename, 'a');
    fwrite($handle, $string . "\r\n");
    fclose($handle);
}

/**
 * 循環建立目錄
 * <p>v1.1 2015-04-10: 增加判斷如果路徑已存在則返回, 避免不必要的處理</p>
 * <p>v1.0 2014-04-15</p>
 * @param unknown $root
 * @param unknown $path
 * @return Ambigous <string, unknown>
 */
//^打算改版為 v2
function mkdir_p($root, $path)
{
    if (is_dir($root . $path)) {
        $root .= $path;
    } else {
        //斜線防呆
        $path = str_replace('\\', '/', $path);
        $a_path = explode('/', $path);
        foreach ($a_path as $v1) {
            if (empty($v1)) continue;
            $root .= $v1 . '/';
            if (!is_dir($root)) mkdir($root, 0755);
        }
    }

    return $root;
}

//^改版過度用
function mkdir_p_v2($path)
{
    $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);
    if (!is_dir($path)) {
        if (strpos($path, PATH_ROOT) === false) throw new Exception('Abnormal process');

        $a_path = explode(DIRECTORY_SEPARATOR, str_replace(PATH_ROOT, '', $path));
        $string0 = PATH_ROOT;
        foreach ($a_path as $v0) {
            if (empty($v0)) continue;

            $string0 .= $v0 . DIRECTORY_SEPARATOR;
            if (!is_dir($string0)) mkdir($string0, 0755);
        }
    }

    return $path;
}

/**
 * Model 實例
 * <p>v1.0 2015-08-14</p>
 * @param string $model
 * @return object
 */
function Model($model = null)
{
    static $instance = array();

    if ($model == null) {
        $model = 'Model';
        if (!isset($instance[$model])) $instance[$model] = new \Model;//使編輯器提供自動完成用
    } else {
        $model .= 'Model';
        if (!isset($instance[$model])) $instance[$model] = new $model;
    }

    return $instance[$model];
}

/**
 * 將臨時檔案搬移到正式位置
 * <p>v1.1 2014-09-24: 增加判斷 is_dir</p>
 * <p>v1.0 2014-09-01</p>
 * @param unknown $file
 */
function move_temporary_file($filename_folder)
{
    if (is_image($filename_folder)) {
        $pathinfo = pathinfo(image_source($filename_folder));
        $dir = mkdir_p(PATH_UPLOAD, $pathinfo['dirname']);
        foreach (glob(PATH_TMP_FILE . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '*.' . $pathinfo['extension']) as $v1) {
            rename($v1, $dir . pathinfo($v1, PATHINFO_BASENAME));
        }
    } else {
        $pathinfo = pathinfo($filename_folder);
        mkdir_p(PATH_UPLOAD, $pathinfo['dirname']);
        rename(PATH_TMP_FILE . $filename_folder, PATH_UPLOAD . $filename_folder);
    }

    //刪除 folder, glob 看來不會取得 linux 上的 ./ ../
    if (count(glob(PATH_TMP_FILE . $pathinfo['dirname'] . '/*')) == 0 && is_dir(PATH_TMP_FILE . $pathinfo['dirname'] . '/')) rmdir(PATH_TMP_FILE . $pathinfo['dirname'] . '/');
}

/**
 * 路徑轉網址
 * v1.0 2017-09-21
 * @param $path
 * @return mixed
 */
function path2url($path)
{
    //2018-06-28 Lion: 留意取代順序, 需先從越下層的路徑開始取代
    $search = [
        PATH_STATIC_FILE,
        PATH_STORAGE,
        PATH_UPLOAD,
        PATH_ROOT,
        DIRECTORY_SEPARATOR
    ];
    $replace = [
        URL_STATIC_FILE,
        URL_STORAGE,
        URL_UPLOAD,
        URL_ROOT,
        '/'
    ];

    return ($path === null) ? null : str_replace($search, $replace, $path);
}

/**
 * 要提交至 api-cashflow 時 product 的 array 規格化
 * <p>v1.0 2014-02-24</p>
 * @param unknown $id
 * @param unknown $amount
 * @param unknown $price
 * @param unknown $total
 * @param unknown $exchange
 * @return multitype:unknown
 */
function product_param_encode($id, $amount, $price, $total, $exchange)
{
    return array('product_id' => $id, 'amount' => $amount, 'price' => $price, 'total' => $total, 'exchange' => $exchange);
}

/**
 * 將 url 的 get urldecode 後 return 出 array
 * <p>v1.0 2014-02-13</p>
 * @return multitype:string
 */
function query_string_parse()
{
    $return = array();
    $tmp1 = array();
    parse_str($_SERVER['QUERY_STRING'], $tmp1);
    foreach ($tmp1 as $k1 => $v1) {
        $return[urldecode($k1)] = urldecode($v1);
    }

    return $return;
}

/**
 * 隨機密碼
 * <p>v1.0 2014-02-24</p>
 * @param number $length
 * @param string $size
 * @return Ambigous <NULL, string>
 */
function random_password($length = 8, $size = null)
{
    $alphabet = 'acdefghjkmnpqrstwxyACDEFGHJKMNPQRSTWXY345789';
    $pass = null;
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pass .= $alphabet[rand(0, $alphaLength)];
    }
    switch (strtolower($size)) {
        case 'l':
            $pass = strtoupper($pass);
            break;

        case 's':
            $pass = strtolower($pass);
            break;
    }

    return $pass;
}

/**
 * js 重導向
 * <p>v1.1 2014-02-13: 還是直接 die 好了, 既然都在 controller 使用, 也是少寫些字</p>
 * <p>v1.0 2013-12-17: 本來想直接用 die 輸出 js, 但考量到進程的控制應該屬於 controller, 因此還是 return</p>
 * @param unknown $url
 * @param string $message
 * @return string
 */
function redirect($url, $message = null)
{
    header('Content-type: text/html; charset=UTF-8');
    $return = '<script>';
    if (!empty($message)) $return .= 'alert("' . $message . '");';
    $return .= 'location.href = "' . $url . '"</script>';

    die($return);
}

/**
 * php 重導向
 * <p>v1.0 2014-02-13</p>
 * @param unknown $url
 */
function redirect_php($url)
{
    header('Content-type: text/html; charset=UTF-8');
    header('Location: ' . $url);

    die;
}

/**
 * 使用者 ip
 * <p>v1.1 2014-02-18: 由 ip() 改名為 remote_ip()</p>
 * <p>v1.0 2013-12-17</p>
 * @return string
 */
function remote_ip($port = false)
{
    if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip from proxy
        $ip = strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ',');
    } elseif (isset($_SERVER['HTTP_PROXY_USER']) && !empty($_SERVER['HTTP_PROXY_USER'])) {
        $ip = $_SERVER['HTTP_PROXY_USER'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($port && isset($_SERVER['REMOTE_PORT']) && !empty($_SERVER['REMOTE_PORT'])) $ip = $ip . ':' . $_SERVER['REMOTE_PORT'];

    return $ip;
}

/**
 * 替換所有空白字串
 * <p>v1.0 2017-06-19</p>
 * @param $str
 * @return string
 */
function replaceSpace($str)
{
    return trim(preg_replace('/\s+/u', ' ', $str));
}

/**
 * 以 rgb 取得 hex
 * @param $r
 * @param $g
 * @param $b
 * @return string
 */
function rgb2hex($r, $g, $b)
{
    $hex = "#";
    $hex .= str_pad(dechex($r), 2, "0", STR_PAD_LEFT);
    $hex .= str_pad(dechex($g), 2, "0", STR_PAD_LEFT);
    $hex .= str_pad(dechex($b), 2, "0", STR_PAD_LEFT);

    return $hex; // returns the hex value including the number sign (#)
}

/**
 * 運行時間(秒)
 * <p>v1.0 2016-02-02</p>
 * @return float
 */
function runtime()
{
    return (float)number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3);
}

/**
 * SDK 實例
 * <p>v1.0 2015-10-19</p>
 * @param string $sdk
 * @return object
 */
function SDK($sdk)
{
    static $instance = array();

    if (!isset($instance[$sdk])) $instance[$sdk] = new $sdk;

    return $instance[$sdk];
}

/**
 * server ip
 * <p>v1.0 2014-02-18</p>
 * @return string
 */
function server_ip()
{
    $ip = $_SERVER['SERVER_ADDR'];

    if (isset($_SERVER['SERVER_PORT']) && !empty($_SERVER['SERVER_PORT'])) $ip = $ip . ':' . $_SERVER['SERVER_PORT'];

    return $ip;
}

/**
 * 搭配 register_shutdown_function 用於獲取 error 訊息
 * <p>v1.0 2017-10-02</p>
 */
function shutdown()
{
    \userlogModel::setError();
}

/**
 * \Core\Solr 實例
 * <p>v1.0 2015-11-09</p>
 * @param string $core
 * @return object
 */
function Solr($core)
{
    static $instance = [];

    if (!isset($instance[$core])) $instance[$core] = new \Core\Solr($core);

    return $instance[$core];
}

/**
 * \Core\SphinxClient 實例
 * <p>v1.0 2015-08-13</p>
 * @param string $database
 * @return object
 */
function SphinxClient($database)
{
    static $instance = array();

    if (!isset($instance[$database])) $instance[$database] = new \Core\SphinxClient($database);

    return $instance[$database];
}

/**
 * 這個架構制式的 sql select param decode
 * <p>v1.0 2014-11-18</p>
 * @param array $param
 * @return multitype:Ambigous <NULL, string>
 */
function sql_select_decode(array $param = null)
{
    $column = isset($param['column']) ? $param['column'] : null;
    $join = isset($param['join']) ? $param['join'] : null;
    $where = isset($param['where']) ? $param['where'] : null;
    $group = isset($param['group']) ? $param['group'] : null;
    $having = isset($param['having']) ? $param['having'] : null;
    $order = isset($param['order']) ? $param['order'] : null;
    $limit = isset($param['limit']) ? $param['limit'] : null;

    return array($column, $join, $where, $group, $having, $order, $limit);
}

/**
 * 這個架構制式的 sql select param encode
 * <p>v1.0 2014-11-18</p>
 * @param array $column
 * @param array $join
 * @param array $where
 * @param array $group
 * @param string $having
 * @param array $order
 * @param string $limit
 * @return multitype:string
 */
function sql_select_encode(array $column = null, array $join = null, array $where = null, array $group = null, $having = null, array $order = null, $limit = null)
{
    $return = array();
    if (isset($column)) $return['column'] = $column;
    if (isset($join)) $return['join'] = $join;
    if (isset($where)) $return['where'] = $where;
    if (isset($group)) $return['group'] = $group;
    if (isset($having)) $return['having'] = $having;
    if (isset($order)) $return['order'] = $order;
    if (isset($limit)) $return['limit'] = $limit;

    return $return;
}

/**
 * 靜態檔路徑
 * <p>v1.0 2014-04-02</p>
 * @param unknown $file
 * @return string
 */
function static_file($file)
{
    return URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/' . $file;
}

/**
 * 將存放於 storage 的檔案名稱再構造, 作用為保護檔案
 * <p>v1.3 2015-04-30: 由 encrypt 改為 md5, 避免因為更動 SITE_SECRET 影響雜湊導致檔名不符</p>
 * <p>v1.2 2015-04-29: 增加參數 $timestamp, 作用為檔案變更控管</p>
 * <p>v1.1 2015-04-10: 增加 mkdir_p</p>
 * <p>v1.0 2015-01-28</p>
 */
function storagefile($file, $timestamp = null)
{
    $encrypt = md5($file . $timestamp);
    $tmp0 = pathinfo($file);
    $dirname = $tmp0['dirname'];
    $extension = $tmp0['extension'];
    $filename = $tmp0['filename'];
    mkdir_p(PATH_STORAGE, $dirname);

    return $dirname . DIRECTORY_SEPARATOR . $filename . '$' . substr($encrypt, 0, 2) . substr($encrypt, -2) . '.' . $extension;
}

/**
 * v1.0 2013-12-17
 * @param unknown $str
 * @param unknown $length
 * @param string $append
 * @return unknown|string
 */
function summary($str, $length, $append = true)
{
    if (strlen($str) <= $length) {
        return $str;
    } else {
        $i = 0;
        $upperCount = 0;
        while ($i < $length) {
            $stringTmp = substr($str, $i, 1);
            $ascii = ord($stringTmp);
            if ($ascii >= 224) {
                $stringTmp = substr($str, $i, 3);
                $i = $i + 3;
                $length += 1;
            } elseif ($ascii >= 192) {
                $stringTmp = substr($str, $i, 2);
                $i = $i + 2;
            } else {
                if ($ascii <= 90 && $ascii >= 65) {
                    ++$upperCount;
                }
                if ($upperCount > 0 && $upperCount % 3 === 0) {
                    $length -= 1;
                    $upperCount = 0;
                }
                $i = $i + 1;
            }
            $stringLast[] = $stringTmp;
        }
        $stringLast = implode('', $stringLast);
        if ($append && $stringLast != $str) {
            $stringLast .= "...";
        }

        return $stringLast;
    }
}

/**
 * v1.0 2013-12-17
 * @param unknown $data
 * @return Ambigous <string>|Ambigous <NULL, string>
 */
function toAlpha($data)
{
    $alphabet = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $alpha_flip = array_flip($alphabet);
    if ($data <= 25) {
        return $alphabet[$data];
    } elseif ($data > 25) {
        $dividend = ($data + 1);
        $alpha = null;
        $modulo;
        while ($dividend > 0) {
            $modulo = ($dividend - 1) % 26;
            $alpha = $alphabet[$modulo] . $alpha;
            $dividend = floor((($dividend - $modulo) / 26));
        }

        return $alpha;
    }
}

/**
 * v1.0 2013-12-17
 * @param unknown $data
 * @return number
 */
function toNum($data)
{
    $alphabet = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $alpha_flip = array_flip($alphabet);
    $num = -1;
    $length = strlen($data);
    for ($i = 0; $i < $length; ++$i) {
        $num += ($alpha_flip[$data[$i]] + 1) * pow(26, ($length - $i - 1));
    }

    return $num;
}

/**
 * 架構 url
 * <p>v1.1 2014-02-13: 加上 urlencode</p>
 * <p>v1.0 2013-12-17</p>
 * @param string $class
 * @param string $function
 * @param array $param
 * @return string
 */
function url($class = 'index', $function = 'index', array $param = array())
{
    $url = URL_ROOT;
    if ('admin' == M_PACKAGE) {
        $url .= 'admin/';
    }
    if ('index' != $function) {
        $url .= $class . '/';
        $url .= $function . '/';
    } elseif ('index' != $class) {
        $url .= $class . '/';
    }
    if (!empty($param)) {
        $tmp1 = array();
        foreach ($param as $k1 => $v1) {
            $tmp1[urlencode($k1)] = urlencode($v1);
        }
        $url .= '?' . http_build_query($tmp1, '', '&');
    }

    return $url;
}

/**
 * 網址轉路徑
 * v1.0 2018-05-04
 * @param $url
 * @return mixed|null
 */
function url2path($url)
{
    return ($url === null) ? null : str_replace([URL_ROOT, '/'], [PATH_ROOT, DIRECTORY_SEPARATOR], $url);
}

/**
 * 引用 view
 * <p>v1.1 2014-11-21: 判斷 mobile; 增加參數 $public 作為引用共有檔案</p>
 * <p>v1.0 2013-12-17</p>
 * @param unknown $package
 * @param unknown $class
 * @param unknown $function
 * @return string
 */
function view($package = M_PACKAGE, $class = M_CLASS, $function = M_FUNCTION, $public = null)
{
    $view = SDK('Mobile_Detect')->isMobile() ? 'view-mobile' : 'view';

    $file = ($class === null && $function === null) ? $public . '.phtml' : $class . '/' . $function . '.phtml';

    $return = PATH_ROOT . 'module/' . $package . '/' . $view . '/zh_TW/' . $file;

    if (!file_exists($return)) $return = PATH_ROOT . 'module/' . $package . '/view/zh_TW/' . $file;

    return $return;
}

function xml2array($url, $get_attributes = 1, $priority = 'tag')
{
    $contents = "";
    if (!function_exists('xml_parser_create')) {
        return array();
    }
    $parser = xml_parser_create('');
    if (!($fp = @ fopen($url, 'rb'))) {
        return array();
    }
    while (!feof($fp)) {
        $contents .= fread($fp, 8192);
    }
    fclose($fp);
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return; //Hmm...
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();
    $current = &$xml_array;
    $repeated_tag_index = array();
    foreach ($xml_values as $data) {
        unset ($attributes, $value);
        extract($data);
        $result = array();
        $attributes_data = array();
        if (isset ($value)) {
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset ($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open") {
            $parent[$level - 1] = &$current;
            if (!is_array($current) or (!in_array($tag, array_keys($current)))) {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = &$current[$tag];
            } else {
                if (isset ($current[$tag][0])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset ($current[$tag . '_attr'])) {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset ($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = &$current[$tag][$last_item_index];
            }
        } elseif ($type == "complete") {
            if (!isset ($current[$tag])) {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
            } else {
                if (isset ($current[$tag][0]) and is_array($current[$tag])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level]++;
                } else {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset ($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                }
            }
        } elseif ($type == 'close') {
            $current = &$parent[$level - 1];
        }
    }
    return ($xml_array);
}
echo "<script>console.log(".json_encode("\core\\function.php:end(通用函式)".date('m/d/Y h:i:s', time())).");</script>";