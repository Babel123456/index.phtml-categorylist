<?php

namespace Core;

class File
{
    function __construct()
    {
    }

    /**
     * 複製檔案
     * a:1. 檔案陣列  2. 目標目錄  3. 是否覆蓋檔案
     * @回傳陣列格式:
     *  status = 1 / 0
     *  input = (int) 操作筆數
     *  output = (int) 執行筆數
     *  list = Array(str) 執行完成檔案
     *  fail = Array(str) 執行失敗檔案
     */
    static function copy(array $file, $target, $mode = false)
    {
        $return = array();
        //Default status = 0;
        $return['status'] = 0;

        if (!is_dir($target)) {
            $return['message'] = 'Directory not exist';
            return $return;
        }

        if (count($file) > 0) {
            $file_list = array();
            $file_fail = array();
            //enter array => status = 1;
            $return['status'] = 1;
            $return['input'] = count($file);
            foreach ($file as $v0) {
                $basename = end(explode('/', $v0));
                $filename = pathinfo($basename)['filename'];
                $extension = pathinfo($basename)['extension'];

                //不覆蓋
                if ($mode == false) {
                    if (file_exists($target . $basename)) {
                        $file_fail[] = $v0;
                        continue;
                    }
                }

                if (is_file($v0)) {
                    $file_list[] = $target . $basename;
                    copy($v0, end($file_list));
                } else {
                    $file_fail[] = $v0;
                }
            }
            $return['output'] = count($file_list);
            $return['list'] = $file_list;
            $return['fail'] = $file_fail;
        }
        return $return;
    }

    /**
     * 刪除檔案
     * 1. 檔案陣列
     * @將重製尺寸的圖片一併清除 (glob($key))
     * @其餘類型先直接刪除
     * @回傳 ture / false
     */
    static function delete($file)
    {
        $function = function ($param) {
            $return = [];

            if (is_file($param)) {
                $pathinfo = pathinfo($param);

                if (is_image($param)) {
                    $return = glob($pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'] . '*.' . $pathinfo['extension']);
                } else {
                    $return[] = $param;
                }
            }

            return $return;
        };

        $unlink = [];

        if (is_array($file)) {
            foreach ($file as $v_0) {
                $unlink = array_merge($unlink, $function($v_0));
            }
        } else {
            $unlink = array_merge($unlink, $function($file));
        }

        if ($unlink) {
            foreach (array_unique($unlink) as $v_0) {
                if (unlink($v_0)) {
                    \Extension\aws\S3::deleteObject($v_0);
                }
            }
        }
    }

    static function download($file, $basename = null)
    {
        if (!is_file($file)) throw new \Exception("[" . __METHOD__ . "] Parameters error");

        set_time_limit(0);

        $finfo = new \finfo();
        $filesize = filesize($file);
        $handle = fopen($file, 'rb');
        $basename = empty($basename) ? pathinfo($file, PATHINFO_BASENAME) : $basename;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $http_range = explode('=', $_SERVER['HTTP_RANGE'])[1];
            $pos = strpos($http_range, '-');
            $http_range_start = substr($http_range, 0, $pos);
            $http_range_end = substr($http_range, $pos + 1);
            header('HTTP/1.1 206 Partial Content');
            header('Accept-Ranges: bytes');
            header('Content-Range: bytes ' . $http_range_start . '-' . $http_range_end . '/' . $filesize);
            header('Content-Length: ' . ($http_range_end - $http_range_start + 1));
            fseek($handle, $http_range_start);
        } else {
            $http_range_start = 0;
            $http_range_end = $filesize;
            header('HTTP/1.1 200 OK');
            header('Content-Length: ' . $filesize);
        }
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $finfo->file($file, FILEINFO_MIME_TYPE));
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $basename . '";');
        header('Content-Transfer-Encoding: binary');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');
        ob_clean();
        flush();
        echo fread($handle, $http_range_end - $http_range_start + 1);
        fclose($handle);
    }

    /**
     * 重新命名檔案
     * a:1. 檔案陣列  2. 完整路徑+檔名陣列  3. 是否覆蓋檔案
     * b:避免錯誤，重命名路徑需等於原檔案路徑，否則視為搬移檔案而非重命名
     * @回傳陣列格式:
     *  status = 1 / 0
     *  input = (int) 操作筆數
     *  output = (int) 執行筆數
     *  list = Array(str) 執行完成檔案
     *  fail = Array(str) 執行失敗檔案
     */
    static function rename(array $file, array $target, $mode = false)
    {
        $return = array();
        //Default status = 0;
        $return['status'] = 0;

        if (!is_array($target)) return $return;

        if (count($file) == count($target)) {
            $file_list = array();
            $file_fail = array();
            //enter array => status = 1;
            $return['status'] = 1;
            $return['input'] = count($file);
            foreach ($file as $k0 => $v0) {
                $basename = end(explode('/', $v0));
                $filename = pathinfo($basename)['filename'];
                $extension = pathinfo($basename)['extension'];

                //覆蓋
                if ($mode == false && file_exists($target[$k0])) {
                    $file_fail[] = $v0;
                    continue;
                }
                //路徑需相同
                if (dirname($file[$k0]) != dirname($target[$k0])) {
                    $file_fail[] = $v0;
                    continue;
                }

                if (is_file($v0)) {
                    $file_list[] = $target[$k0];
                    rename($v0, $target[$k0]);
                } else {
                    $file_fail[] = $v0;
                }
            }
            $return['output'] = count($file_list);
            $return['list'] = $file_list;
            $return['fail'] = $file_fail;
        }
        return $return;
    }

    /**
     * 搬移檔案
     * a:1. 檔案陣列  2. 目標目錄  3. 是否覆蓋檔案
     * @回傳陣列格式:
     *  status = 1 / 0
     *  input = (int) 操作筆數
     *  output = (int) 執行筆數
     *  list = Array(str) 執行完成檔案
     *  fail = Array(str) 執行失敗檔案
     */
    static function move(array $file, $target, $mode = false)
    {
        $return = array();
        //Default status = 0;
        $return['status'] = 0;

        if (!is_dir($target)) {
            $return['message'] = 'Directory not exist';
            return $return;
        }

        if (count($file) > 0) {
            $file_list = array();
            $file_fail = array();
            //enter array => status = 1;
            $return['status'] = 1;
            $return['input'] = count($file);
            foreach ($file as $v0) {
                $basename = end(explode('/', $v0));
                $filename = pathinfo($basename)['filename'];
                $extension = pathinfo($basename)['extension'];

                //不覆蓋
                if ($mode == false) {
                    if (file_exists($target . $basename)) {
                        $file_fail[] = $v0;
                        continue;
                    }
                }

                if (is_file($v0)) {
                    $file_list[] = $target . $basename;
                    rename($v0, end($file_list));
                } else {
                    $file_fail[] = $v0;
                }
            }
            $return['output'] = count($file_list);
            $return['list'] = $file_list;
            $return['fail'] = $file_fail;
        }
        return $return;
    }
}