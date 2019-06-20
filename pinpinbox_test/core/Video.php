<?php
/**
 * Video 處理器
 * <p>v1.0 2016-01-20</p>
 * @author lion
 */

namespace Core;
class Video
{
    private $codec;
    private $codec_target;
    private $duration;
    private $filesize;
    private $framerate;
    private $framerate_target;
    private $height;
    private $height_target;
    private $file;
    private $file_target;
    private $outScreenShotWidth;
    private $outScreenShotHeight;
    private $rotate;
    private $screenshot_time = 0;
    private $screenshot_type = 'jpg';
    private $type;
    private $type_target;
    private $width;
    private $width_target;

    function __construct()
    {
        if (!trim(shell_exec('ffmpeg -version'))) throw new \Exception('Not found ffmpeg');

        if (!trim(shell_exec('ffprobe -version'))) throw new \Exception('Not found ffprobe');
    }

    /**
     * 取得編解碼器
     * @return string
     */
    function getCodec()
    {
        return $this->codec;
    }

    /**
     * 取得持續時間
     * @return integer(秒)
     */
    function getDuration()
    {
        return $this->duration;
    }

    /**
     * 取得輸出路徑
     * @return mixed
     */
    function getOutPath()
    {
        return $this->file_target;
    }

    /**
     * 取得截圖時間
     * @return integer(秒)/string
     */
    function getScreenshotTime()
    {
        return $this->screenshot_time;
    }

    /**
     * 取得截圖類型
     * @return string
     */
    function getScreenshotType()
    {
        return $this->screenshot_type;
    }

    /**
     * 取得原檔完整路徑
     * @param string $file
     * @return string
     */
    function getSource($file)
    {
        $pathinfo = pathinfo($file);
        $tmp0 = explode('_', $pathinfo['filename']);
        if (isset($tmp0[1])) $file = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . preg_replace('/(_[0-9]+x[0-9]+)$/i', '', $pathinfo['filename']) . '.' . $pathinfo['extension'];

        return $file;
    }

    /**
     * 取得類型
     * @return string
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * 儲存視頻
     * @param string $file_target : 目標檔案絕對路徑
     * @param boolean $overwrite : 是否覆寫目標
     * @param boolean $delete : 是否刪除原檔(包含所有尺寸)
     * @param boolean $suffix : 是否添加後綴
     * @return string
     */
    function save($file_target = null, $overwrite = false, $delete = false, $suffix = true)
    {
        if ($file_target === null) {
            $pathinfo = pathinfo($this->file);

            $filename = ($suffix && ($this->width_target != $this->width || $this->height_target != $this->height)) ? $pathinfo['filename'] . '_' . $this->width_target . 'x' . $this->height_target : $pathinfo['filename'];

            $this->file_target = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $filename . '.' . $this->type_target;
        } else {
            $this->file_target = $file_target;
        }

        if (!is_file($this->file_target) || $overwrite) {
            //處理翻轉
            switch ($this->rotate) {
                case 90:
                    $a_vf = [
                        'scale=' . $this->height_target . ':' . $this->width_target,
                        'transpose=1'
                    ];
                    break;

                case 180:
                    $a_vf = [
                        'scale=' . $this->width_target . ':' . $this->height_target,
                        'transpose=1,transpose=1'
                    ];
                    break;

                case 270:
                    $a_vf = [
                        'scale=' . $this->height_target . ':' . $this->width_target,
                        'transpose=2',
                    ];
                    break;

                default:
                    $a_vf = ['scale=' . $this->width_target . ':' . $this->height_target];
                    break;
            }

            /**
             * 2017-02-23 Lion: -strict -2, 參考 http://stackoverflow.com/questions/32931685/the-encoder-aac-is-experimental-but-experimental-codecs-are-not-enabled, 但會有處理很久的情況
             */
            $exec = 'ffmpeg -i ' . escapeshellarg($this->file) . ' -vf ' . escapeshellarg(implode(',', $a_vf)) . ' -metadata:s:v:0 rotate=0 ' . escapeshellarg($this->file_target) . ' -loglevel error 2>&1';

            unset($output);

            exec($exec, $output);

            if ($output) (new \userlogModel)->setException(new \Exception(implode("\r\n", array_merge([$exec], $output))));
        }

        if ($delete && $this->getSource($this->file_target) != $this->getSource($this->file)) {
            $pathinfo = pathinfo($this->file);

            \Core\File::delete(glob($pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'] . '*.' . $pathinfo['extension']));
        }

        return $this->file_target;
    }

    /**
     * 儲存視頻截圖
     * @param string $file_target : 目標檔案絕對路徑
     * @param boolean $overwrite : 是否覆寫目標
     * @param boolean $suffix : 是否添加後綴
     * @return string
     */
    function saveScreenShot($file_target = null, $overwrite = false, $suffix = true)
    {
        $return = false;

        if ($file_target === null) {
            $pathinfo = pathinfo($this->file);

            $filename = ($suffix && ($this->outScreenShotWidth != $this->width || $this->outScreenShotHeight != $this->height)) ? $pathinfo['filename'] . '_' . $this->outScreenShotWidth . 'x' . $this->outScreenShotHeight : $pathinfo['filename'];

            $this->file_target = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $filename . '.' . $this->getScreenshotType();
        } else {
            $this->file_target = $file_target;
        }

        if (!is_image($this->file_target) || $overwrite) {
            if (is_file($this->file_target)) unlink($this->file_target);

            //處理翻轉
            switch ($this->rotate) {
                case 90:
                    $vf = '-vf transpose=1';
                    break;

                case 180:
                    $vf = '-vf transpose=1,transpose=1';
                    break;

                case 270:
                    $vf = '-vf transpose=2';
                    break;

                default:
                    $vf = null;
                    break;
            }

            shell_exec('ffmpeg -i ' . escapeshellarg($this->file) . ' -an -s ' . escapeshellarg($this->outScreenShotWidth . 'x' . $this->outScreenShotHeight) . ' -ss ' . escapeshellarg($this->getScreenshotTime()) . ' ' . $vf . ' -vframes 1 ' . escapeshellarg($this->file_target));

            if (!file_exists($this->file_target)) copy(static_file('images/video_origin.jpg'), $this->file_target);//若檔案有毀損造成無法正確截圖，使用 videoorigin 圖檔作為該相片預設圖
        }

        if (is_file($this->file_target)) $return = true;

        return $return;
    }

    /**
     * 設置視頻, 取得資訊指令參考 https://trac.ffmpeg.org/wiki/FFprobeTips
     * @param string $file : 檔案絕對路徑
     * @param boolean $source : 是否尋回原檔案進行處理
     * @return \Core\Video
     */
    function setFile($file, $source = true)
    {
        $pathinfo = pathinfo($file);

        if ($source) {
            $tmp0 = explode('_', $pathinfo['filename']);
            if (isset($tmp0[1])) {
                $file = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . preg_replace('/(_[0-9]+x[0-9]+)$/i', '', $pathinfo['filename']) . '.' . $pathinfo['extension'];
            }
        }

        if (!is_video($file)) throw new \Exception('File\'s type is incorrect.');

        $this->file_target = $this->file = $file;
        $this->type_target = $this->type = $pathinfo['extension'];//2017-02-18 Lion: ffprobe 沒有參數可取得 video 的副檔名, 因此以 pathinfo 處理

        $entries = json_decode(shell_exec('ffprobe -print_format json -select_streams v:0 -show_entries format=duration,size:stream=avg_frame_rate,codec_name,height,width:stream_tags=rotate ' . escapeshellarg($file)), true);

        //stream
        $this->codec_target = $this->codec = $entries['streams'][0]['codec_name'];
        $this->framerate = $entries['streams'][0]['avg_frame_rate'];
        $this->height = $entries['streams'][0]['height'];
        $this->rotate = isset($entries['streams'][0]['tags']['rotate']) ? $entries['streams'][0]['tags']['rotate'] : null;
        $this->width = $entries['streams'][0]['width'];

        //format
        $this->duration = $entries['format']['duration'];
        $this->filesize = $entries['format']['size'];

        //處理翻轉
        switch ($this->rotate) {
            case 90:
            case 270:
                $tmp0 = $this->height;
                $this->height = $this->width;
                $this->width = $tmp0;
                break;
        }
        $this->outScreenShotHeight = $this->height_target = $this->height;
        $this->outScreenShotWidth = $this->width_target = $this->width;

        return $this;
    }

    /**
     * 設置截圖時間
     * @param integer (秒)/string $screenshot_time: 截圖時間
     * @return \Core\Video
     */
    function setScreenshotTime($screenshot_time)
    {
        $this->screenshot_time = $screenshot_time;

        return $this;
    }

    /**
     * 設置截圖類型
     * @param string $screenshot_type : 截圖類型
     * @return \Core\Video
     */
    function setScreenshotType($screenshot_type)
    {
        $this->screenshot_type = $screenshot_type;

        return $this;
    }

    /**
     * 設置視頻寬、高
     * @param number $width : 寬
     * @param number $height : 高
     * @param boolean $forced : 是否強制縮放為指定寬高
     * @return \Core\Video
     */
    function setSize($width = 100, $height = 100, $forced = false)
    {
        if ($this->width != $width || $this->height != $height) {
            if (!$forced) {
                $w_rate = $this->width / $width;
                $h_rate = $this->height / $height;

                $rate = ($w_rate > $h_rate) ? $w_rate : $h_rate;

                $width = round($this->width / $rate);
                $height = round($this->height / $rate);
            }

            $this->width_target = $width;
            $this->height_target = $height;
        }

        return $this;
    }

    function setScreenShotScaleSize($size)
    {
        if (!isset($this->file)) goto _return;

        if ($this->width != $size || $this->height != $size) {
            $widthRate = (float)$this->width / $size;
            $heightRate = (float)$this->height / $size;

            $rate = ($widthRate > $heightRate) ? $widthRate : $heightRate;

            $this->outScreenShotWidth = round($this->width / $rate);
            $this->outScreenShotHeight = round($this->height / $rate);
        }

        _return:
        return $this;
    }

    /**
     * 設置檔案類型
     * @param string $type
     * @throws Exception
     * @return \Core\Video
     */
    function setType($type)
    {
        $this->type_target = strtolower(trim($type));

        return $this;
    }
}