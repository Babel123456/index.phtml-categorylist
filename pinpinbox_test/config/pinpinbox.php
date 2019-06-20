<?php
//echo '<span style=color:red >pinpinbox.php:</span>'.date('m/d/Y h:i:s a', time());

echo "<script>console.log(".json_encode("\config\pinpinbox.php:start(設各平台, DB的環境變數)".date ("Y-m-d H:i:s" , mktime(date('H')+6, date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";
$CONFIG = [];

/**
 * 基本
 */
date_default_timezone_set('Asia/Taipei');

define('APP_CHARSET', 'UTF8');
define('SITE_SECRET', 'd9$kv3fk(ri3mv#d-kg05[vs)F;f2lg/');//Lion 2015-01-13:後續希望設計為, 如果此值更改後, 有再次驗證的機制. Lion 2014-01-01:一旦使用就不能更改, 避免過去產生的 sign 對不起來
define('SITE_LANG', 'zh_TW');

define('DB_PREFIX', 'pinpinbox_');

/**
 * E-mail
 */
define('EMAIL_ACCOUNT_INTRANET', 'pinpinbox888@gmail.com');//Gmail account
define('EMAIL_PASSWORD_INTRANET', 'pinpinbox123123');//Gmail password
define('EMAIL_NAME_INTRANET', 'pinpinbox'); //Gmail name

/**
 * FTP
 */
//^

/**
 * 環境
 *     database:
 *         <mssql 範例>
 *         $CONFIG['DB'] = [
 *             'site'=>[
 *                 'HOST'=>$host = 'localhost',
 *                 'DSN'=>'sqlsrv:Server='.$host.'\SQLEXPRESS;Database=VMAGE',
 *                 'USER'=>'sa',
 *                 'PASSWORD'=>'25099911',
 *             ],
 *         ];
 */
switch (SITE_EVN) {
    case 'development':
        define('SITE_FOLDER', null);

        //URL
        //define('URL_ROOT', URL_PROTOCOL . 'localhost/');
		define('URL_ROOT', URL_PROTOCOL . 'localhost/pinpinbox_test/'); //B本機測試路徑
		

        //database
        $CONFIG['DB'] = [
            'analysis' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'analysis;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => ''
            ],
            'cashflow' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'cashflow;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => ''
            ],
            'userlog' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'userlog;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => ''
            ],
            'site' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'site;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => ''
            ],
        ];

        //sphinx
        $CONFIG['SPHINX'] = [
            'cashflow' => [
                'HOST' => 'localhost',//同 db 的 host
                'PORT' => 9312,
            ],
            'site' => [
                'HOST' => 'localhost',//同 db 的 host
                'PORT' => 9312,
            ],
        ];

        //memcache
        $CONFIG['MC'] = [
            'analysis' => [
                'SERVER' => 'localhost',
                'PORT' => '11211',
                'EXPIRE' => 10
            ],
            'cashflow' => [
                'SERVER' => 'localhost',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
            'site' => [
                'SERVER' => 'localhost',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
        ];
        break;

    case 'test':
        define('SITE_FOLDER', 'pinpinbox/');

        //URL
        define('URL_ROOT', URL_PROTOCOL . 'platformvmage5.cloudapp.net/' . SITE_FOLDER);

        //database
        $CONFIG['DB'] = [
            'analysis' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'analysis;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => 'd3ree3SDfe2A'
            ],
            'cashflow' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'cashflow;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => 'd3ree3SDfe2A'
            ],
            'userlog' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'userlog;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => 'd3ree3SDfe2A'
            ],
            'site' => [
                'HOST' => $host = 'localhost',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'site;host=' . $host,
                'USER' => 'root',
                'PASSWORD' => 'd3ree3SDfe2A'
            ],
        ];

        //sphinx
        $CONFIG['SPHINX'] = [
            'cashflow' => [
                'HOST' => 'localhost',//同 db 的 host
                'PORT' => 9312,
            ],
            'site' => [
                'HOST' => 'localhost',//同 db 的 host
                'PORT' => 9312,
            ],
        ];

        //memcache
        $CONFIG['MC'] = [
            'analysis' => [
                'SERVER' => 'localhost',
                'PORT' => '11211',
                'EXPIRE' => 10
            ],
            'cashflow' => [
                'SERVER' => 'localhost',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
            'site' => [
                'SERVER' => 'localhost',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
        ];
        break;

    case 'qa':
        define('SITE_FOLDER', null);

        //URL
        define('URL_ROOT', URL_PROTOCOL . 'w3.pinpinbox.com/' . SITE_FOLDER);

        //database
        $CONFIG['DB'] = [
            'analysis' => [
                'HOST' => $host = '127.0.0.1',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'analysis;host=' . $host,
                'USER' => 'ppbusr',
                'PASSWORD' => 'kik9quj2i387sh'
            ],
            'cashflow' => [
                'HOST' => $host = '127.0.0.1',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'cashflow;host=' . $host,
                'USER' => 'ppbusr',
                'PASSWORD' => 'kik9quj2i387sh'
            ],
            'userlog' => [
                'HOST' => $host = '127.0.0.1',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'userlog;host=' . $host,
                'USER' => 'ppbusr',
                'PASSWORD' => 'kik9quj2i387sh'
            ],
            'site' => [
                'HOST' => $host = '127.0.0.1',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'site;host=' . $host,
                'USER' => 'ppbusr',
                'PASSWORD' => 'kik9quj2i387sh'
            ],
        ];

        //sphinx
        $CONFIG['SPHINX'] = [
            'cashflow' => [
                'HOST' => '127.0.0.1',//同 db 的 host
                'PORT' => 9312,
            ],
            'site' => [
                'HOST' => '127.0.0.1',//同 db 的 host
                'PORT' => 9312,
            ],
        ];

        //memcache
        $CONFIG['MC'] = [
            'analysis' => [
                'SERVER' => '127.0.0.1',
                'PORT' => '11211',
                'EXPIRE' => 10
            ],
            'cashflow' => [
                'SERVER' => '127.0.0.1',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
            'site' => [
                'SERVER' => '127.0.0.1',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
        ];
        break;

    case 'production':
        define('SITE_FOLDER', null);

        //URL
        define('URL_ROOT', URL_PROTOCOL . 'www.pinpinbox.com/' . SITE_FOLDER);

        //database
        $CONFIG['DB'] = [
            'analysis' => [
                'HOST' => $host = '172.31.9.187',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'analysis;host=' . $host,
                'USER' => 'ppbuser',
                'PASSWORD' => '1qa2ws3ed'
            ],
            'cashflow' => [
                'HOST' => $host = '172.31.9.187',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'cashflow;host=' . $host,
                'USER' => 'ppbuser',
                'PASSWORD' => '1qa2ws3ed'
            ],
            'userlog' => [
                'HOST' => $host = '172.31.9.187',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'userlog;host=' . $host,
                'USER' => 'ppbuser',
                'PASSWORD' => '1qa2ws3ed'
            ],
            'site' => [
                'HOST' => $host = '172.31.9.187',
                'DSN' => 'mysql:dbname=' . DB_PREFIX . 'site;host=' . $host,
                'USER' => 'ppbuser',
                'PASSWORD' => '1qa2ws3ed'
            ],
        ];

        //sphinx
        $CONFIG['SPHINX'] = [
            'cashflow' => [
                'HOST' => '172.31.9.187',//同 db 的 host
                'PORT' => 9312,
            ],
            'site' => [
                'HOST' => '172.31.9.187',//同 db 的 host
                'PORT' => 9312,
            ],
        ];

        //memcache
        $CONFIG['MC'] = [
            'analysis' => [
                'SERVER' => '127.0.0.1',
                'PORT' => '11211',
                'EXPIRE' => 10
            ],
            'cashflow' => [
                'SERVER' => '127.0.0.1',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
            'site' => [
                'SERVER' => '127.0.0.1',
                'PORT' => '11211',
                'EXPIRE' => 2
            ],
        ];
        break;
}

/**
 * LOG
 */
define('LOGSITE_DELAY', 1);
define('NEWSLOG_DELAY', 1);

/**
 * SITE
 */

include 'global.php';

/**
 * TCPDF
 */
define('K_PATH_IMAGES', PATH_ROOT);
define('PDF_FONT_NAME_MAIN', 'msungstdlight');
define('PDF_FONT_NAME_DATA', 'msungstdlight');
define('PDF_FONT_MONOSPACED', 'msungstdlight');

register_shutdown_function('shutdown');

$_ = [];
$_['CONFIG'] = $CONFIG;

/**
 * route rule
 */
function route_rule($url)
{
    $package = null;
    $class = null;
    $function = null;
    $version = null;
    $a_url = explode('/', strtolower($url));
		
    $level1 = empty($a_url[1]) ? 'index' : $a_url[1];
    $level2 = empty($a_url[2]) ? 'index' : $a_url[2];
    $level3 = empty($a_url[3]) ? 'index' : $a_url[3];
    $level4 = empty($a_url[4]) ? 'index' : $a_url[4];

	//echo '<span style=color:lime>$level1=</span>'.$level1.'</br>';
	//echo '<span style=color:lime>$level2=</span>'.$level2.'</br>';
	//echo '<span style=color:lime>$level3=</span>'.$level3.'</br>';
	//echo '<span style=color:lime>$level4=</span>'.$level4.'</br>';
	
    switch ($level1) {
        case 'index':
            if (in_array($level2, ['admin', 'business'])) {
                $package = $level2;
                $class = $level3;
                $function = $level4;
            } else {
                $package = 'pinpinbox';

                switch ($level2) {
                    case 'api':
                        $class = $level2;
                        $function = $level3;
                        $version = ($level4 == 'index') ? null : $level4;
                        break;

                    default:
                        $class = $level2;
                        $function = $level3;
                        break;
                }
            }
            break;

        default://作者專區
            $package = 'pinpinbox';
            $class = 'index';
            $function = 'index';
            $m_user = (new userModel())->column(['user_id'])->where([[[['creative_code', '=', $level1]], 'and']])->fetch();

            if (!empty($m_user)) {
                $class = 'creative';
                $function = 'content';
                $_GET['user_id'] = $m_user['user_id'];
            }
            break;
    }

    return [$package, $class, $function, $version];
}
echo "<script>console.log(".json_encode("\config\pinpinbox.php:end(設各平台, DB的環境變數)".date('m/d/Y h:i:s', time())).");</script>";