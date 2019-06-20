<?php

class userlogModel extends Model
{
    public $database = 'userlog';
    public $table;
    protected $memcache = 'userlog';

    function __construct()
    {
		
		echo "<script>console.log(".json_encode("\model\userlog.php:start()".date ("Y-m-d H:i:s" , mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";

        parent::__construct_child();

        $this->table = date('Ymd');
		
		echo "<script>console.log(".json_encode($this->table).");</script>";
    }

    private function ableToWrite()
    {
        $tables = parent::$database_instance[$this->database]->fetchColumn("SHOW TABLES FROM " . DB_PREFIX . $this->database . " LIKE " . parent::$database_instance[$this->database]->quote($this->table));

        //echo "<script>console.log(".json_encode($tables).");</script>";
		
		return $tables ? true : false;
    }

    function crontabForCreateTable()
    {
        $string0 = date('Ymd', strtotime('+1 day'));

        $tables = parent::$database_instance[$this->database]->fetchColumn("SHOW TABLES FROM " . DB_PREFIX . $this->database . " LIKE " . parent::$database_instance[$this->database]->quote($string0));

        if (!$tables) {
            $sql = "CREATE TABLE " . DB_PREFIX . $this->database . "." . $string0 . " (
                        `userlog_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                        `user_id` int(10) unsigned DEFAULT NULL,
                        `session_id` varchar(32) DEFAULT NULL,
                        `server` text,
                        `get` text,
                        `post` text,
                        `input` text,
                        `session` text,
                        `cookie` text,
                        `return` text,
                        `exception` text,
                        `error` text,
                        `headers` text,
                        `ip` varbinary(16) NOT NULL,
                        `latitude` decimal(10,7) DEFAULT NULL,
                        `longitude` decimal(10,7) DEFAULT NULL,
                        `coordinate_return` text,
                        `runtime` float(6,3) unsigned NOT NULL DEFAULT '0.000',
                        `inserttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        PRIMARY KEY (`userlog_id`),
                        KEY `user_id` (`user_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8";

            parent::$database_instance[$this->database]->exec($sql);
        }
    }

    function viewed()
    {
        $userlog_id = 0;

        if (\Session::get('userlog') === null) {
            if ((new \userlogModel)->ableToWrite()) {
                $add = [];

                //user_id
                $user_id = \Session::get('user')['user_id'];
                if ($user_id) $add += ['user_id' => $user_id];

                //server
                $server = [];
                if (isset($_SERVER['HTTP_REFERER'])) $server['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
                if (isset($_SERVER['HTTP_USER_AGENT'])) $server['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
                if (isset($_SERVER['REQUEST_URI'])) $server['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

                //session
                $session = \Session::get();

                $input = trim(file_get_contents('php://input'));

                if (defined('USERLOG_ID')) {

                } else {
                    $add += [
                        'headers' => json_encode(getallheaders()),
                        'input' => ($input === '') ? null : $input,
                        'session_id' => \Session::getID(),
                        '`server`' => ($server) ? json_encode($server) : null,
                        '`get`' => ($_GET) ? json_encode($_GET) : null,
                        'post' => ($_POST) ? json_encode($_POST) : null,
                        '`session`' => ($session) ? json_encode($session) : null,
                        'cookie' => ($_COOKIE) ? json_encode($_COOKIE) : null,
                        'ip' => inet_pton(remote_ip()),
                        'runtime' => runtime(),
                    ];

                    $userlog_id = (new \userlogModel)->add($add);

                    define('USERLOG_ID', $userlog_id);
                }
            }
        }
        
		//echo "<script>console.log(".json_encode("userlog_id=".$userlog_id).");</script>";
        
        return $userlog_id;
    }

    static function setError()
    {
        $errorArray = error_get_last();

        if ($errorArray) {
            self::setUserLog([
                'error' => $errorArray['message'] . ' in ' . $errorArray['file'] . ' on line ' . $errorArray['line'],
            ]);
        }
    }

    //2017-12-05 Lion: 這個準備棄用，改用 self::setExceptionV2
    function setException($e)
    {
        if (defined('M_PACKAGE') && M_PACKAGE != 'admin' && defined('USERLOG_ID')) {
            $e_string = exceptioninfostring($e);

            $m_userlog = (new userlogModel)->column(['exception', 'runtime'])->where([[[['userlog_id', '=', USERLOG_ID]], 'and']])->fetch();

            $exception = empty(trim($m_userlog['exception'])) ? $e_string : $m_userlog['exception'] . "\r\n" . $e_string;

            (new userlogModel)->where([[[['userlog_id', '=', USERLOG_ID]], 'and']])->edit(['exception' => $exception, 'runtime' => ($m_userlog['runtime'] + runtime())]);

            //if (SITE_EVN === 'development') echo $e_string;
        }
    }

    static function setExceptionV2($level, $message)
    {
        $Exception = (new \Lib\Exception)
            ->setLevel($level)
            ->setMessage($message);

        self::setUserLog([
            'exception' => $Exception->getTraceString(),
        ]);

        $Exception->output();
    }

    function setReturn($r)
    {
        if (defined('M_PACKAGE') && M_PACKAGE != 'admin' && defined('USERLOG_ID')) {
            $r_string = json_encode($r);

            $m_userlog = (new userlogModel)->column(['`return`', 'runtime'])->where([[[['userlog_id', '=', USERLOG_ID]], 'and']])->fetch();

            $return = empty(trim($m_userlog['return'])) ? $r_string : $m_userlog['return'] . "\r\n" . $r_string;

            (new userlogModel)->where([[[['userlog_id', '=', USERLOG_ID]], 'and']])->edit(['`return`' => $return, 'runtime' => ($m_userlog['runtime'] + runtime())]);
        }
    }

    static function setUserLog(array $param)
    {
        if ((new \userlogModel)->ableToWrite() && (defined('M_PACKAGE') && M_PACKAGE != 'admin')) {
            if (defined('USERLOG_ID')) {
                $userlogModel = (new \userlogModel)
                    ->column(
                        [
                            'error',
                            'exception',
                            '`return`',
                            'runtime',
                        ]
                    )
                    ->where([[[['userlog_id', '=', USERLOG_ID]], 'and']])
                    ->fetch();

                $update = [];

                if (isset($param['error'])) {
                    $update['error'] = (replaceSpace($userlogModel['error']) === '') ? $param['error'] : $userlogModel['error'] . "\r\n" . $param['error'];
                }

                if (isset($param['exception'])) {
                    $update['exception'] = (replaceSpace($userlogModel['exception']) === '') ? $param['exception'] : $userlogModel['exception'] . "\r\n" . $param['exception'];
                }

                if (isset($param['return'])) {
                    $update['`return`'] = $param['return'];
                }

                (new \userlogModel)
                    ->where([[[['userlog_id', '=', USERLOG_ID]], 'and']])
                    ->edit(array_merge(
                            [
                                'runtime' => ($userlogModel['runtime'] + runtime())
                            ],
                            $update
                        )
                    );
            } else {
                //input
                $input = trim(file_get_contents('php://input'));
				
				
                //server
                $server = [];
                if (isset($_SERVER['HTTP_REFERER'])) $server['HTTP_REFERER'] = $_SERVER['HTTP_REFERER'];
                if (isset($_SERVER['HTTP_USER_AGENT'])) $server['HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
                if (isset($_SERVER['REQUEST_URI'])) $server['REQUEST_URI'] = $_SERVER['REQUEST_URI'];

                //session
                $session = \Session::get();

                //user_id
                $user_id = (new \userModel)->getSession()['user_id'];

                $userlog_id = (new \userlogModel)
                    ->add(array_merge(
                        [
                            'cookie' => ($_COOKIE) ? json_encode($_COOKIE) : null,
                            '`get`' => ($_GET) ? json_encode($_GET) : null,
                            'headers' => json_encode(getallheaders()),
                            'input' => ($input === '') ? null : $input,
                            'ip' => inet_pton(remote_ip()),
                            'post' => ($_POST) ? json_encode($_POST) : null,
                            'runtime' => runtime(),
                            '`server`' => ($server) ? json_encode($server) : null,
                            '`session`' => ($session) ? json_encode($session) : null,
                            'session_id' => \Session::getID(),
                            'user_id' => ($user_id) ? $user_id : null,
                        ],
                        $param
                    ));

                define('USERLOG_ID', $userlog_id);
            }
        }
    }
}