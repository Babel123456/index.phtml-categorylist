<?php

class uploadController extends backstageController
{
    function __construct()
    {
    }

    function init_folder($class)
    {
        $m_adminmenu = Model('adminmenu')->where(array(array(array(array('class', '=', $class)), 'and')))->fetch();
        if (empty($m_adminmenu)) throw new Exception("[" . __METHOD__ . "] Unknown class");

        return M_PACKAGE . '/' . $class . '/' . date('Ymd') . '/';
    }

    function aviary()
    {
        if (is_ajax()) {
            $href = empty($_REQUEST['href']) ? null : $_REQUEST['href'];
            $url = empty($_REQUEST['url']) ? null : $_REQUEST['url'];
            if ($href == null || $url == null) {
                json_encode_return(0, _('Param error.'));
            }

            $fileinfo = fileinfo(strtok($href, '?'));

            file_put_contents($fileinfo['path'], file_get_contents($url));

            //href
            $href = $fileinfo['url'] . '?=' . time();

            //src
            $image = new \Core\Image;
            $src = fileinfo($image->set($fileinfo['path'])->setSize()->save(null, true))['url'] . '?=' . time();

            json_encode_return(1, _('Image edit success.'), null, array('href' => $href, 'src' => $src));
        }
        die;
    }

    function ckeditor()
    {
        if (!empty($_FILES['upload'])) {
            //初始目錄
            if (isset($_GET['class']) && !empty($_GET['class'])) {
                $subfolder = $this->init_folder($_GET['class']);
            } else {
                throw new Exception("[" . __METHOD__ . "] Parameters error");
            }

            //副檔名
            $extname = strtolower(pathinfo($_FILES['upload']['name'], PATHINFO_EXTENSION));

            $url_filename = null;

            //檢查副檔名
            if (Core::settings('UPLOAD_ALLOW_EXTENSION_ADMIN') == null || !in_array($extname, json_decode(Core::settings('UPLOAD_ALLOW_EXTENSION_ADMIN'), true))) {
                $message = _('The extension of file is not allowed.');
            } else {
                if ($_FILES['upload']['error'] == UPLOAD_ERR_OK) {
                    //路徑
                    $dir = mkdir_p(PATH_UPLOAD, $subfolder);
                    $url = URL_UPLOAD . $subfolder;

                    //賦予新檔名
                    $filename = uniqid() . '.' . $extname;

                    //檔案完整路徑
                    $dir_filename = $dir . $filename;
                    $url_filename = $url . $filename;

                    if (move_uploaded_file($_FILES['upload']['tmp_name'], $dir_filename)){
                        chmod($dir_filename, 0644);

                        \Extension\aws\S3::upload($dir_filename);

                        $message = _('Upload success.');
                    } else {
                        $message = _('Upload failed, please try again.');
                    }
                } else {
                    $message = Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['upload']['error']];
                }

                //ftp
                /*
                 if (FtpWrapper::put(Yii::app()->params['cdn']['local'].$filename.$extname,$filename.$extname)) {
                 return $filename.$extname;
                 }
                 */
            }

            //Required: anonymous function reference number as explained above.
            $funcNum = $_GET['CKEditorFuncNum'];
            //Optional: instance name (might be used to load a specific configuration file or anything else).
            //$CKEditor = $_GET['CKEditor'];
            //Optional: might be used to provide localized messages.
            //$langCode = $_GET['langCode'];

            /**
             * CKEDITOR.tools.callFunction 參數說明
             * 1. ckeditor 經由 GET 所帶入的 function number
             * 2. 將要植入回 CKEditor 的值
             * 3. alert 的值
             */
            die('<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction(' . $funcNum . ', "' . $url_filename . '", "' . $message . '");</script>');
        }
        die;
    }

    function upload()
    {
        if (!empty($_FILES['file'])) {
            $filetype = isset($_POST['filetype']) ? $_POST['filetype'] : null;

            //初始目錄
            if (isset($_GET['class']) && !empty($_GET['class'])) {
                $subfolder = $this->init_folder($_GET['class']);
            } else {
                throw new Exception('Parameters error');
            }

            //副檔名
            $extname = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            //檢查副檔名
            if (Core::settings('UPLOAD_ALLOW_EXTENSION_ADMIN') == null || !in_array($extname, json_decode(Core::settings('UPLOAD_ALLOW_EXTENSION_ADMIN'), true))) json_encode_return(0, _('The extension of file is not allowed.'));

            //路徑
            $dir = mkdir_p(PATH_UPLOAD, $subfolder);
            $url = URL_UPLOAD . $subfolder;

            //賦予新檔名
            $uniqid = uniqid();
            $filename = $uniqid . '.' . $extname;

            //檔案完整路徑
            $file_folder = $subfolder . $filename;
            $dir_filename = $dir . $filename;
            $url_filename = $url . $filename;

            //搬移檔案
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_OK:
                    switch ($filetype) {
                        case 'image':
							if($_FILES['file']['type'] != 'image/svg+xml') {
								if (is_image($_FILES['file']['tmp_name'])) {
									$width_assign = empty($_POST['width']) ? null : $_POST['width'];
									$height_assign = empty($_POST['height']) ? null : $_POST['height'];

									list($width, $height) = getimagesize($_FILES['file']['tmp_name']);

									if (($width_assign !== null && $width_assign != $width) || ($height_assign !== null && $height_assign != $height)) {
										json_encode_return(0, _('Image\'s dimensions does not match, should be') . ' ' . $width_assign . ' x ' . $height_assign . '.');
									}
								} else {
									json_encode_return(0, _('Abnormal process, please try again.'));
								}
							}
                            break;
                    }

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $dir_filename)) {
                        chmod($dir_filename, 0644);

                        $Image = new \Core\Image();

                        $return = [];

                        if (is_image($dir_filename)) {
                            \Extension\aws\S3::upload($dir_filename);

                            $return['file_name'] = $filename;
                            $return['file_folder'] = $file_folder;
                            $return['file_url'] = $url_filename;
                            $return['file_thumbnail_url'] = fileinfo($Image->set($dir_filename)->setSize()->save())['url'];
                        } else {
                            switch ($extname) {
                                case 'pdf':
                                    if (class_exists('imagick')) {
                                        $im = new imagick();
                                        $im->setResolution(108, 108);//max 300, 300
                                        $im->readimage($dir_filename);
                                        $im->setImageFormat('jpeg');

                                        if ($im->writeImage($dir . $uniqid . '.jpeg')) {
                                            \Extension\aws\S3::upload($dir . $uniqid . '.jpeg');
                                        }

                                        $im->clear();

                                        $return['file_name'] = $filename;
                                        $return['file_folder'] = $file_folder;
                                        $return['file_url'] = $url . $uniqid . '.jpeg';
                                        $return['file_thumbnail_url'] = URL_UPLOAD . getimageresize($subfolder . $uniqid . '.jpeg');
                                    } else {
                                        $return['file_name'] = $filename;
                                        $return['file_folder'] = $file_folder;
                                        $return['file_url'] = null;
                                        $return['file_thumbnail_url'] = null;
                                    }
                                    break;

								case 'svg' :
									\Extension\aws\S3::upload($dir_filename);

									$return['file_name'] = $filename;
									$return['file_folder'] = $file_folder;
									$return['file_url'] = $url_filename;
									$return['file_thumbnail_url'] = $url_filename;
									break;
                                default:
                                    $return['file_name'] = $filename;
                                    $return['file_folder'] = $file_folder;
                                    $return['file_url'] = null;
                                    $return['file_thumbnail_url'] = null;
                                    break;
                            }
                        }

                        json_encode_return(1, _('Success'), null, $return);
                    } else {
                        json_encode_return(0, _('Abnormal process, please try again.'));
                    }
                    break;

                default:
                    json_encode_return(0, Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']]);
                    break;
            }
        } else {
            json_encode_return(0, 'Error: Input name must be [file]');
        }
    }

    function image_combine()
    {
        //初始目錄
        if (isset($_GET['class']) && !empty($_GET['class'])) {
            $subfolder = $this->init_folder($_GET['class']);
        } else {
            throw new Exception("[" . __METHOD__ . "] Parameters error");
        }

        $background = $_POST['background'];
        $foreground_left = $_POST['foreground_left'];
        $foreground_top = $_POST['foreground_top'];
        $foreground = $_POST['foreground'];

        $fileinfo_background = fileinfo($background);
        $fileinfo_foreground = fileinfo($foreground);

        $im_background = imagecreatefromX($fileinfo_background['path']);
        $im_foreground = imagecreatefromX($fileinfo_foreground['path']);

        if (!$im_background || !$im_foreground) {
            json_encode_return(0, _('File type error'));
        } else {
            list($width, $height) = getimagesize($fileinfo_foreground['path']);

            imagecopymerge($im_background, $im_foreground, (int)$foreground_left, (int)$foreground_top, 0, 0, $width, $height, 100);

            //路徑
            $dir = mkdir_p(PATH_UPLOAD, $subfolder);
            $url = URL_UPLOAD . $subfolder;

            //賦予新檔名
            $extname = $fileinfo_background['extension'];
            $uniqid = uniqid();
            $filename = $uniqid . '.' . $extname;

            //檔案完整路徑
            $file_folder = $subfolder . $filename;
            $dir_filename = $dir . $filename;
            $url_filename = $url . $filename;

            //Save the image to a file
            imageX($im_background, $dir_filename);

            imagedestroy($im_background);
            imagedestroy($im_foreground);

            $return = array(
                'file_name' => $filename,
                'file_folder' => $file_folder,
                'file_url' => $url_filename,
                'file_thumbnail_url' => URL_UPLOAD . getimageresize($file_folder),
            );

            json_encode_return(1, _('Success'), null, $return);
        }
    }

    function image_combine2()
    {
        //初始目錄
        if (isset($_GET['class']) && !empty($_GET['class'])) {
            $subfolder = $this->init_folder($_GET['class']);
        } else {
            throw new Exception("[" . __METHOD__ . "] Parameters error");
        }

        $a_locate = $_POST['locate'];
        $a_background = $a_locate['background'];
        $background = $a_background['src'];
        $a_foreground = $a_locate['foreground'];

        $im_background = imagecreatefromX($background);
        if (!$im_background) {
            json_encode_return(0, _('File type error'));
        }

        foreach ($a_foreground as $k1 => $v1) {
            $foreground = $v1['src'];
            $foreground_left = $v1['left'];
            $foreground_top = $v1['top'];

            $im_foreground = imagecreatefromX($foreground);
            if (!$im_foreground) {
                json_encode_return(0, _('File type error'));
            }

            list($foreground_width, $foreground_height) = getimagesize($foreground);

            /**
             * 序號戳印
             */
            //使用 imagecreatetruecolor 產生的圖檔, 在 imagecopy(merge) 到目標圖樣上時, 會有覆蓋目標圖樣的情形
            $stamp_width = 30;
            $stamp_height = 30;
            $stamp = imagecreate($stamp_width + 1, $stamp_height + 1);

            //保存透明通道
            imagesavealpha($stamp, true);

            //关闭混合模式，以便透明颜色能覆盖原画布
            imagealphablending($stamp, false);

            //拾取一个完全透明的颜色然後填充
            imagefill($stamp, 0, 0, imagecolorallocatealpha($stamp, 0, 0, 0, 127));

            //draw the ellipse
            imagefilledellipse($stamp, $stamp_width / 2, $stamp_height / 2, $stamp_width, $stamp_height, imagecolorallocate($stamp, 255, 0, 0));

            //開啟混合模式，以便文字貼上不會蓋到多餘的區域
            imagealphablending($stamp, true);

            //寫字
            imagettftext($stamp, 15, 0, 9, 23, imagecolorallocate($stamp, 255, 255, 255), PATH_TTF . 'verdana.ttf', $k1 + 1);

            imagecopy($im_foreground, $stamp, ((int)$foreground_width - (int)$stamp_width) / 2, ((int)$foreground_height - (int)$stamp_height) / 2, 0, 0, $stamp_width, $stamp_height);

            //imagecopy: 當使用 imagecreatetruecolor 創建的圖像資源覆蓋目標圖樣時, 即便是透明色, 覆蓋上去也不會透明, 而是沒有顏色的一塊
            //imagecopymerge: 當使用 imagecreatetruecolor 創建的圖像資源覆蓋目標圖樣時, 即便是透明色, 覆蓋上去並不會透明, 而是黑色的一塊
            imagecopy($im_background, $im_foreground, (int)$foreground_left, (int)$foreground_top, 0, 0, $foreground_width, $foreground_height);

            imagedestroy($stamp);
            imagedestroy($im_foreground);
        }

        //路徑
        $dir = mkdir_p(PATH_UPLOAD, $subfolder);
        $url = URL_UPLOAD . $subfolder;

        //賦予新檔名
        $extname = strtolower(pathinfo(PATH_UPLOAD . $background, PATHINFO_EXTENSION));
        $uniqid = uniqid();
        $filename = $uniqid . '.' . $extname;

        //檔案完整路徑
        $file_folder = $subfolder . $filename;
        $dir_filename = $dir . $filename;
        $url_filename = $url . $filename;

        //Save the image to a file
        imageX($im_background, $dir_filename);

        imagedestroy($im_background);

        $return = array(
            'file_name' => $filename,
            'file_folder' => $file_folder,
            'file_url' => $url_filename,
            'file_thumbnail_url' => URL_UPLOAD . getimageresize($file_folder),
        );

        json_encode_return(1, _('Success'), null, $return);
    }
}