<?php

class uploadController extends backstageController
{
    function __construct()
    {
    }

    function ckeditor()
    {
        if (!empty($_FILES['upload'])) {

            //初始目錄
            if (isset($_GET['class']) && !empty($_GET['class'])) {
                $subfolder = M_PACKAGE . '/' . $_GET['class'] . '/' . date('Ymd') . '/';
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

                    if (move_uploaded_file($_FILES['upload']['tmp_name'], $dir_filename)) {
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

}