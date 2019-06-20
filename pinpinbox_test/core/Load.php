<?php
//echo '<span style=color:red>load.php:</span>'.date('m/d/Y h:i:s a', time());
echo "<script>console.log(".json_encode("\core\load.php:start(配路徑)").");</script>";
spl_autoload_register(function ($class) {
	
	//echo ''.$class.'</br>';
	echo "<script>console.log('  class=".json_encode($class)."');</script>";
	
    //Controller
    if ('Controller' == substr($class, -10)) {
		
		//echo '<span style=color:lime>($class, -10)=</span>'.$class.'</br>';
		echo "<script>console.log('Controller=".json_encode($class)."');</script>";
		
        if (strpos(112, '\\') !== false) {//2017-09-05 Lion: 相容
            $array = explode('\\', $class);

            $filename = PATH_MODULE . $array[0] . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . $array[1] . '.php';
        } else {
            switch ($class) {
                case 'backstageController':
                    $filename = PATH_MODULE . 'admin' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'backstageController.php';
                    break;

                case 'frontstageController':
                    $filename = PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'frontstageController.php';
                    break;

                default:
                    $filename = PATH_MODULE . M_PACKAGE . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . substr($class, 0, -10) . '.php';
                    break;
            }
        }
		//echo '    $filename==>'.$filename.'</br>';
		echo "<script>console.log('    Controller>filename=".json_encode($filename)."');</script>";
    }

    //Extension
    if ('Extension' == substr($class, 0, 9) && strlen($class) > 9) {
        $tmp0 = [];
        foreach (explode('\\', substr($class, 9)) as $v0) {
            if ($v0 != null) $tmp0[] = $v0;
        }
        $filename = PATH_ROOT . 'extension' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $tmp0) . '.php';
      //echo '    $filename==>'.$filename.'</br>';
	  echo "<script>console.log('    Extension>filename=".json_encode($filename)."');</script>";
	}

    //Lib
    if ('Lib' == substr($class, 0, 3) && strlen($class) > 3) {
        $tmp0 = [];

        foreach (explode('\\', substr($class, 3)) as $v0) {
            if ($v0 != null) $tmp0[] = $v0;
        }

        $filename = PATH_MODULE . M_PACKAGE . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $tmp0) . '.php';

        if (!is_file($filename)) {
            $filename = PATH_LIB . preg_replace('/^Lib\\\/i', '', $class) . '.php';
        }
		//echo '    $filename==>'.$filename.'</br>';
		echo "<script>console.log('    Lib>filename=".json_encode($filename)."');</script>";
    }

    //Model
    if ('Model' == substr($class, -5)) {
        if (strpos($class, '\\') !== false) {//2017-09-17 Lion: 相容
            $filename = PATH_MODEL . explode('\\', $class)[0] . DIRECTORY_SEPARATOR . 'Model.php';
        } else {
            switch ($class) {
                case 'Model':
                    $filename = PATH_ROOT . 'core' . DIRECTORY_SEPARATOR . 'Model.php';
                    break;

                default:
                    $filename = PATH_MODEL . substr($class, 0, -5) . '.php';
                    break;
            }
        }
		//echo '    $filename==>'.$filename.'</br>';
		echo "<script>console.log('    Model>filename=".json_encode($filename)."');</script>";
    }

    switch ($class) {
        case 'Core\Lang':
            $filename = PATH_ROOT . 'core' . DIRECTORY_SEPARATOR . preg_replace('/^Core\\\/i', '', $class) . '.php';
            break;

        case 'Core':
        case 'Core\AppStore':
        case 'Core\Audio':
        case 'Core\File':
        case 'Core\GooglePlay':
        case 'Core\I18N':
        case 'Core\Image':
        case 'Core\Log':
        case 'Core\Memcache':
        case 'Core\QRcode':
        case 'Core\Solr':
        case 'Core\SphinxClient':
        case 'Core\Video':
        case 'Core\WebPush':
            $filename = PATH_ROOT . 'core' . DIRECTORY_SEPARATOR . preg_replace('/^Core\\\/i', '', $class) . '.php';
            break;

        case 'Controller':
            $filename = PATH_MODULE . M_PACKAGE . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'controller.php';
            break;

        case 'db':
            $filename = PATH_LIB . 'db.php';
            break;

        case 'Facebook\Facebook':
            $filename = PATH_SDK . 'facebook-5.5' . DIRECTORY_SEPARATOR . 'autoload.php';
            break;

        case 'PclZip':
            $filename = PATH_LIB . 'pclzip-2-8-2/pclzip.lib.php';
            break;

        case 'Session':
            $filename = PATH_ROOT . 'core' . DIRECTORY_SEPARATOR . $class . '.php';
            break;

        //SDK
        case 'BrowserDetection':
            $filename = PATH_SDK . 'BrowserDetection.php';
            break;
        case 'PHPExcel':
            $filename = PATH_SDK . 'PHPExcel.php';
            break;
        case 'PHPMailer':
            require PATH_SDK . 'PHPMailer-5.2.21' . DIRECTORY_SEPARATOR . 'class.phpmailer.php';
            require PATH_SDK . 'PHPMailer-5.2.21' . DIRECTORY_SEPARATOR . 'class.smtp.php';
            break;
        case 'QRcode':
            $filename = PATH_SDK . 'phpqrcode_1.1.4' . DIRECTORY_SEPARATOR . 'phpqrcode.php';
            break;
    }
    //echo '    $filename==>'.$filename.'</br>';
	echo "<script>console.log('    switch>$class>filename=".json_encode($filename)."');</script>";
    $mapping = [
        'Aws\S3\S3Client' => PATH_SDK . 'aws' . DIRECTORY_SEPARATOR . 'aws-autoloader.php',
        'Aws\Sns\SnsClient' => PATH_SDK . 'aws' . DIRECTORY_SEPARATOR . 'aws-autoloader.php',

        //Config
        'Config\Image' => PATH_CONFIG . 'Image.php',

        //Controller
        'Controller\api' => PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'api.php',
        'Controller\v1_0\api' => PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . '1.0' . DIRECTORY_SEPARATOR . 'api.php',
        'Controller\v1_1\api' => PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . '1.1' . DIRECTORY_SEPARATOR . 'api.php',
        'Controller\v1_2\api' => PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . '1.2' . DIRECTORY_SEPARATOR . 'api.php',
        'Controller\v1_3\api' => PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . '1.3' . DIRECTORY_SEPARATOR . 'api.php',
        'Controller\v2_0\api' => PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . '2.0' . DIRECTORY_SEPARATOR . 'api.php',
        'Controller\v2_1\api' => PATH_MODULE . 'pinpinbox' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . '2.1' . DIRECTORY_SEPARATOR . 'api.php',

        //Lib
        'Lib\Backstage\Html\Input' => PATH_MODULE . 'admin' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Html_Input.php',

        //Model
        'Model\bookmark' => PATH_MODEL . 'bookmark' . DIRECTORY_SEPARATOR . 'Model.php',
        'Model\point' => PATH_MODEL . 'point' . DIRECTORY_SEPARATOR . 'Model.php',
        'Model\revision' => PATH_MODEL . 'revision' . DIRECTORY_SEPARATOR . 'Model.php',
        'Model\split' => PATH_MODEL . 'split' . DIRECTORY_SEPARATOR . 'Model.php',
        'Model\userpointsplit' => PATH_MODEL . 'userpointsplit' . DIRECTORY_SEPARATOR . 'Model.php',
        'Model\userpointsplitqueue' => PATH_MODEL . 'userpointsplitqueue' . DIRECTORY_SEPARATOR . 'Model.php',

        //Schema
        'Schema\album' => PATH_MODEL . 'album' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\businessuser' => PATH_MODEL . 'businessuser' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\creative' => PATH_MODEL . 'creative' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\photo' => PATH_MODEL . 'photo' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\photousefor_user' => PATH_MODEL . 'photousefor_user' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\push' => PATH_MODEL . 'push' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\split' => PATH_MODEL . 'split' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\user' => PATH_MODEL . 'user' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\userpoint' => PATH_MODEL . 'userpoint' . DIRECTORY_SEPARATOR . 'Schema.php',
        'Schema\userpointqueue' => PATH_MODEL . 'userpointqueue' . DIRECTORY_SEPARATOR . 'Schema.php',

        //SDK
        'Mobile_Detect' => PATH_SDK . 'Mobile-Detect-2.8.28' . DIRECTORY_SEPARATOR . 'Mobile_Detect.php',
    ];

    if (isset($mapping[$class])) $filename = $mapping[$class];

    if (isset($filename) && is_file($filename)) require_once $filename;
	
	echo "<script>console.log('    mapping>filename=".json_encode($filename)."');</script>";
});

define('SITE_ID', 'pinpinbox');
define('SITE_EVN', isset($_SERVER['APPLICATION_ENV']) ? $_SERVER['APPLICATION_ENV'] : 'development');
/**
 * PATH
 */
define('PATH_ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('PATH_CONFIG', PATH_ROOT . 'config' . DIRECTORY_SEPARATOR);
define('PATH_CACHE', PATH_ROOT . 'cache' . DIRECTORY_SEPARATOR);
define('PATH_LANG', PATH_ROOT . 'lang' . DIRECTORY_SEPARATOR);
define('PATH_LIB', PATH_ROOT . 'lib' . DIRECTORY_SEPARATOR);
define('PATH_LOG', PATH_ROOT . 'log' . DIRECTORY_SEPARATOR);
define('PATH_MODEL', PATH_ROOT . 'model' . DIRECTORY_SEPARATOR);
define('PATH_MODULE', PATH_ROOT . 'module' . DIRECTORY_SEPARATOR);
define('PATH_SDK', PATH_ROOT . 'sdk' . DIRECTORY_SEPARATOR);
define('PATH_STATIC_FILE', PATH_ROOT . 'static_file' . DIRECTORY_SEPARATOR);
define('PATH_STORAGE', PATH_ROOT . 'storage' . DIRECTORY_SEPARATOR);
define('PATH_TTF', PATH_ROOT . 'ttf' . DIRECTORY_SEPARATOR);
define('PATH_UPLOAD', PATH_ROOT . 'upload' . DIRECTORY_SEPARATOR);

include PATH_ROOT . 'core/Function.php';
echo "<script>console.log('  include>filename=".json_encode("core/Function.php")."');</script>";

define('URL_PROTOCOL', is_https() ? 'https://' : 'http://');

include PATH_CONFIG . SITE_ID . '.php';
echo "<script>console.log('  include>filename=".json_encode("core/Function.php")."');</script>";
/**
 * Config
 */
Core::$_config = $_;

/**
 * Module
 */
if (isset($_SERVER['REQUEST_URI'])) {
    $SITE_FOLDER = constant('SITE_FOLDER');
    $pos = strpos($_SERVER['REQUEST_URI'], $SITE_FOLDER);
    list ($url) = explode('?', ($pos !== false) ? substr_replace($_SERVER['REQUEST_URI'], '', $pos, strlen($SITE_FOLDER)) : $_SERVER['REQUEST_URI']);
    list ($package, $class, $function, $version) = route_rule($url);
    define('M_PACKAGE', $package);
    define('M_CLASS', $class);
    define('M_VERSION', $version);
    define('M_FUNCTION', $function);
    define('M_METHOD', M_CLASS . '::' . M_FUNCTION);
}

/**
 * URL
 */
switch (SITE_EVN) {
    case 'development':
    case 'test':
        define('URL_CDN_ROOT', URL_ROOT);
        break;

    case 'qa':
        define('URL_CDN_ROOT', URL_PROTOCOL . 'ppb.sharemomo.com/');
        break;

    case 'production':
        define('URL_CDN_ROOT', URL_PROTOCOL . 'cdn.pinpinbox.com/');
        break;
}

define('URL_STATIC_FILE', URL_CDN_ROOT . 'static_file/');
define('URL_STORAGE', URL_CDN_ROOT . 'storage/');
define('URL_UPLOAD', URL_CDN_ROOT . 'upload/');

//
//\Core\Image::$usableOfImagick = class_exists('Imagick') ? true : false;
echo "<script>console.log(".json_encode("\core\load.php:end(配路徑)").");</script>";