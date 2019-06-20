<?php
/**
 * Core
 */
 
echo "<script>console.log(".json_encode("\core\core.php:start(設前後台路徑檔名等變數)".date ("Y-m-d H:i:s" , mktime(date('H')+6, date('i'), date('s'), date('m'), date('d'), date('Y')))).");</script>";

 
class Core
{
    public static $_config;
    public static $_controller;
    public static $_extension;
    public static $_settings;
	
    function __construct()
    {
        try {
            /**
             * i18n
             */
            //i18n support information here
            $lang = Core\Lang::get();
			
            putenv('LANG=' . $lang);
            setlocale(LC_ALL, $lang, explode('_', $lang)[0]);//即使 windows 下 setlocale return true, 貌似仍無法 work

            //set the text domain
            bindtextdomain(M_PACKAGE, PATH_LANG);
            textdomain(M_PACKAGE);
            bind_textdomain_codeset(M_PACKAGE, 'UTF-8');
			
            //Maintain
            if (Core::settings('SITE_MAINTAIN_SWITCH') && M_PACKAGE != 'admin' && M_METHOD != '_::maintain' && empty(adminModel::getSession())) redirect(frontstageController::url('_', 'maintain'));

            /**
             * Model
             */
            Model::$switch_memcache = Core::settings('MEMCACHE');

            /**
             * Controller
             */
            switch (M_PACKAGE) {
                case 'admin':
                    $string0 = 'backstageController';
                    $string1 = M_CLASS . 'Controller';
                    break;

                case 'pinpinbox':
                    $string0 = 'frontstageController';

                    switch (M_CLASS) {
                        case 'api'://2018-04-09 Lion: 過渡邏輯
                            if (M_VERSION == null) {
                                $string1 = '\Controller\api';
                            } else {
                                $string1 = '\Controller\v' . str_replace('.', '_', M_VERSION) . '\api';
                            }
                            break;

                        default:
                            $string1 = M_CLASS . 'Controller';
                            break;
                    }
                    break;

                case 'business':
                    $string0 = '\business\basisController';
                    $string1 = '\business\\' . M_CLASS . 'Controller';
                    break;
            }

			
            $string2 = constant('M_FUNCTION');

            $obj0 = new $string0;

            if (!class_exists($string1)) redirect(frontstageController::url('_', '_404'));

            $obj1 = new $string1;

            $obj1->$string2();
            $obj0->display();
        } catch (\Exception $e) {
            (new \userlogModel)->setException($e);

            $info = exceptioninfostring($e);

            $encode_return = false;

            switch ($e->getCode()) {
                case 2000://(json)error
                    $encode_return = true;
                    break;
            }

            switch (SITE_EVN) {
                case 'development':
                case 'test':
                    $encode_return ? json_encode_return(0, $info) : die($info);
                    break;

                default:
                    $encode_return ? json_encode_return(0, 'Occur exception, please contact us.') : die('Occur exception, please contact us.<br><br>Back to <a href="' . URL_ROOT . '">' . URL_ROOT . '</a>');
                    break;
            }
        }
    }

    public static function controller($name = null)
    {
        $controller = 'Controller';
        if (!isset(self::$_controller[$controller])) {
            self::$_controller[$controller] = new Controller();
        }

        if ($name != null) {
            $controller = $name;
			
            if (!isset(self::$_controller[$controller])) {
                if (!class_exists($controller)) {
                    $filename = PATH_MODULE . M_PACKAGE . '/controller/' . $controller . '.php';
					
                    if (!file_exists($filename)) {
                        //^http_response_code(404);
                        redirect(frontstageController::url('_', '_404'));
                    }
                    include $filename;
					
                }
                self::$_controller[$controller] = new $controller();
            }
        }

        return self::$_controller[$controller];
    }

    /**
     * 引用 extension
     * <p>v1.1 2015-04-01: 移至 Core</p>
     * <p>v1.0 2014-02-20</p>
     * @param unknown $folder
     * @param unknown $name
     * @throws Exception
     */
    public static function extension($folder, $name)
    {
        $extension = $name;
        if (!isset(self::$_extension[$folder][$extension])) {
            if (!class_exists($extension)) {
                $filename = PATH_ROOT . 'extension/' . $folder . '/' . $extension . '.php';
                if (!file_exists($filename)) {
                    throw new Exception('Extension:' . $extension . ' not found');
                }
                include $filename;
            }
            self::$_extension[$folder][$extension] = new $extension();
        }

        return self::$_extension[$folder][$extension];
    }

    public function settings($keyword, $reload = false)
    {
        if (!isset(self::$_settings[$keyword]) || $reload == true) {
            $m_settings_lang = Model('settings_lang')->where([[[['keyword', '=', $keyword], ['lang_id', 'in', [Core\Lang::$default, Core\Lang::get()]]], 'and']])->fetchAll();
            if (empty($m_settings_lang)) {
                $settings = null;
            } else {
                $a_settings = [];
                foreach ($m_settings_lang as $v0) {
                    $a_settings[$v0['lang_id']] = $v0['value'];
                }
                $settings = isset($a_settings[Core\Lang::get()]) ? $a_settings[Core\Lang::get()] : $a_settings[Core\Lang::$default];
            }
            self::$_settings[$keyword] = $settings;
			
        }

        return self::$_settings[$keyword];
    }

	/**
	 * 2018-11-02 Mars: 準備棄用, 改使用 userModel::get_creative_url
	 */
	public function get_creative_url($user_id, $click = null)
	{
		
		
		$param = $click != null ? '?click=' . $click : null;
		$param2 = $click != null ? ['user_id' => $user_id, 'click' => $click] : ['user_id' => $user_id];


		$m_user = (new \userModel)
			->column(['creative_code'])
			->where([[[['user_id', '=', $user_id]], 'and']])
			->fetch();
			
		return empty($m_user['creative_code']) ? frontstageController::url('creative', 'content', $param2) : URL_ROOT . $m_user['creative_code'] . $param;
	}

	public function exchange($user_id, $platform, $type, $id, $payPoint = null)
    {
		
        $m_type = Model($type)->column([$type . '_id', 'user_id', 'point'])->where([[[[$type . '_id', '=', $id]], 'and']])->fetch();

        if ($payPoint === null) $payPoint = $m_type['point'];

        /**
         * 160713 - user點數 = 付費點數 + 免費點數
         */
        $_point = Core::get_userpoint($user_id, $platform);
        $_point_free = Core::get_userpoint($user_id, $platform, 'point_free');
        $point = $_point + $_point_free;
        if ($point < $payPoint) {
            return array_encode_return(0, _('User\'s point is not enough to get the exchange.'), null, ['balance' => false]);
        }

        $reduce_point = 0;
        $reduce_free_point = 0;

        /**
         * 先扣付費點
         */
        if ($_point > $payPoint) {
            $reduce_point = $payPoint;
        } else {
            $reduce_point = $_point;
            $reduce_free_point = $payPoint - $_point;
        }

        $exchange_id = (new exchangeModel)
            ->add([
                'user_id' => $user_id,
                'platform' => $platform,
                'type' => $type,
                'id' => $id,
                'point_before' => $_point,
                'point' => $reduce_point,
                'point_free_before' => $_point_free,
                'point_free' => $reduce_free_point,
                'inserttime' => inserttime(),
            ]);

        $businessuserModel = (new \businessuser\Model())
            ->column([
                'businessuser.mode',
                'businessuser.user_id',
            ])
            ->join([
                ['INNER JOIN', 'user', 'ON user.businessuser_id = businessuser.businessuser_id']
            ])
            ->where([[[['user.user_id', '=', $m_type['user_id']]], 'and']])
            ->fetch();

        //split
        $splitInsert = [];

        switch ($businessuserModel['mode']) {
            case 'company':
                $point = intval(floor($payPoint * \Model\split::getRatioForBusinessuserOfCompany() + 0.5));//2018-08-15 Lion: 浮點數 floor 後可能產生誤差, 因此處理

                if ($point > 0) {
                    $splitInsert = [
                        'exchange_id' => $exchange_id,
                        'user_id' => $businessuserModel['user_id'],
                        'point' => $point,
                    ];
                }
                break;

            case 'personal':
                $point4BusinessuserOfPersonalOfBroker = intval(floor($payPoint * \Model\split::getRatioForBusinessuserOfPersonalOfBroker() + 0.5));//2018-08-15 Lion: 浮點數 floor 後可能產生誤差, 因此處理

                if ($point4BusinessuserOfPersonalOfBroker > 0) {
                    $splitInsert[] = [
                        'exchange_id' => $exchange_id,
                        'user_id' => $businessuserModel['user_id'],
                        'point' => $point4BusinessuserOfPersonalOfBroker,
                    ];
                }

                $point4BusinessuserOfPersonalOfHimself = intval(floor($payPoint * \Model\split::getRatioForBusinessuserOfPersonalOfHimself($m_type['user_id'], $type) + 0.5));//2018-08-15 Lion: 浮點數 floor 後可能產生誤差, 因此處理

                if ($point4BusinessuserOfPersonalOfHimself > 0) {
                    $splitInsert[] = [
                        'exchange_id' => $exchange_id,
                        'user_id' => $m_type['user_id'],
                        'point' => $point4BusinessuserOfPersonalOfHimself,
                    ];
                }
                break;

            default:
                $point = intval(floor($payPoint * \Model\split::getRatioForUser($m_type['user_id'], $type) + 0.5));//2018-08-15 Lion: 浮點數 floor 後可能產生誤差, 因此處理

                if ($point > 0) {
                    $splitInsert = [
                        'exchange_id' => $exchange_id,
                        'user_id' => $m_type['user_id'],
                        'point' => $point,
                    ];
                }
                break;
        }

        (new \Model\split())->add($splitInsert);

        $tmp0 = [
            'user_id' => $user_id,
            'trade' => 'exchange',
            'trade_id' => $exchange_id,
            'platform' => $platform,
            'point' => -(int)$reduce_point,
            'point_free' => -(int)$reduce_free_point,
        ];
        if (!Core::set_userpoint($tmp0)) {
            return array_encode_return(0, _('[UserPoint] occur exception, please contact us.'));
        }

        Model($type . 'queue')->add([
            'user_id' => $user_id,
            'exchange_id' => $exchange_id,
            $type . '_id' => $id,
        ]);

        switch ($type) {
            case 'template':
                $state = 'success';//由於 app 沒有做下載的動作，故購買時就將 state = success
                break;

            default:
                $state = 'pretreat';
                break;
        }

        $download_id = (new downloadModel)->add([
            'user_id' => $user_id,
            'type' => $type,
            'id' => $m_type[$type . '_id'],
            'point' => $payPoint,
            'state' => $state,
            'inserttime' => inserttime(),
        ]);
        if (!$download_id) {
            return array_encode_return(0, _('[Download] occur exception, please contact us.'));
        }

        $count = Model($type . 'statistics')->column(['`count`'])->where([[[[$type . '_id', '=', $id]], 'and']])->lock('for update')->fetchColumn();

        Model($type . 'statistics')->replace([
            $type . '_id' => $id,
            'count' => empty($count) ? 1 : ++$count
        ]);

        //被贊助次數(P 點需大於 0)
        if ($payPoint > 0) {
            (new userstatisticsModel)->where([[[['user_id', '=', $m_type['user_id']]], 'and']])->edit([
                'besponsored' => ['besponsored + 1', false]
            ]);
        }

        //用戶訂閱該作品\版型
        subscriptionModel::build($user_id, $type . 'queue', $id);

        $SNSparam = Core::getSNSParams([
            'trigger' => [
                'user_id' => $user_id,
                'type' => $type,
                'typeId' => $id,
                'refer' => 'UserCollect',
            ],
            'targetId' => $m_type['user_id'],
            'typeOfSNS' => $type . 'queue',
        ]);

		(new \topicModel)->publish($user_id, 'user', $m_type['user_id'], $SNSparam['message'], 'albumqueue', $id, $SNSparam);

		// 付費作品才需要推播贊助完成的訊息給自己
		if($payPoint) {
			$SNSparam = Core::getSNSParams([
				'trigger' => [
					'user_id' => $user_id,
					'type' => $type,
					'typeId' => $id,
					'refer' => 'userNotifyMySelf',
					'exchange_id' => ($exchange_id) ? $exchange_id : null,
				],
				'targetId' => $user_id,
				'typeOfSNS' => $type . 'queue',
			]);

			(new \topicModel)->publish($user_id, 'user', $user_id, $SNSparam['message'], null, null, $SNSparam);
		}
        return array_encode_return(1, null, null, ['exchange_id' => $exchange_id, 'download_id' => $download_id, 'point' => $point]);
    }

	protected function get_follow($user_id_myself, $user_id_others)
    {
        $m_followto = Model('followto')->where([[[['user_id', '=', $user_id_myself]], 'and']])->fetchAll();
        $a_to = [];
        foreach ($m_followto as $v0) {
            $a_to[] = $v0['to'];
        }

        return (empty($a_to) || !in_array($user_id_others, $a_to)) ? false : true;
    }

    protected function set_follow($user_id_myself, $user_id_others)
    {
        if ($this->get_follow($user_id_myself, $user_id_others)) {
            (new subscriptionModel)->destroy($user_id_myself, 'follow', $user_id_others);

            (new followfromModel)->where([[[['user_id', '=', $user_id_others], ['`from`', '=', $user_id_myself]], 'and']])->delete();
            (new followtoModel)->where([[[['user_id', '=', $user_id_myself], ['`to`', '=', $user_id_others]], 'and']])->delete();

            $followstatus = 0;
        } else {
            subscriptionModel::build($user_id_myself, 'follow', $user_id_others);

            $SNSparam = Core::getSNSParams([
                'trigger' => [
                    'user_id' => $user_id_myself,
                    'type' => 'user',
                    'typeId' => $user_id_myself,
                    'refer' => 'userFollow',
                ],
                'targetId' => $user_id_others,
                'typeOfSNS' => 'follow',
            ]);
            (new topicModel)->publish($user_id_myself, 'user', $user_id_others, $SNSparam['message'], 'user', $user_id_myself, $SNSparam);

            (new followfromModel)->add([
                'user_id' => $user_id_others,
                '`from`' => $user_id_myself
            ]);

            (new followtoModel)->add([
                'user_id' => $user_id_myself,
                '`to`' => $user_id_others,
            ]);

            $followstatus = 1;
        }

        (new followModel)->replace([
            'user_id' => $user_id_myself,
            'count_to' => (new followtoModel)->column(['count(1)'])->where([[[['user_id', '=', $user_id_myself]], 'and']])->fetchColumn(),
            'count_from' => (new followfromModel)->column(['count(1)'])->where([[[['user_id', '=', $user_id_myself]], 'and']])->fetchColumn(),
        ]);

        (new followModel)->replace([
            'user_id' => $user_id_others,
            'count_to' => (new followtoModel)->column(['count(1)'])->where([[[['user_id', '=', $user_id_others]], 'and']])->fetchColumn(),
            'count_from' => (new followfromModel)->column(['count(1)'])->where([[[['user_id', '=', $user_id_others]], 'and']])->fetchColumn(),
        ]);

        return $followstatus;
    }

    public function get_usergrade($user_id)
    {
        $checktime = date('Y-m-d 12:00:00');
        $m_usergrade = Model('usergrade')->where(array(array(array(array('user_id', '=', $user_id), array('starttime', '<=', $checktime), array('endtime', '>=', $checktime)), 'and')))->lock('for update')->fetch();
        if (!empty($m_usergrade)) {
            $grade = $m_usergrade['grade'];
        } else {
            //處理 usergradequeue, 取得 user 的新 grade
            $m_usergradequeue = Model('usergradequeue')->where(array(array(array(array('user_id', '=', $user_id), array('starttime', '<=', $checktime), array('endtime', '>=', $checktime)), 'and')))->fetch();
            if (!empty($m_usergradequeue)) {
                $grade = $m_usergradequeue['grade'];
                $starttime = $m_usergradequeue['starttime'];
                $endtime = $m_usergradequeue['endtime'];

                //同樣的 grade, 如果時間連續則寫入最後的 endtime
                $column = array(
                    'starttime',
                    'endtime',
                );
                $where = array(
                    array(array(array('user_id', '=', $user_id), array('grade', '=', $grade), array('starttime', '>=', $starttime)), 'and'),
                );
                $m_usergradequeue = Model('usergradequeue')->column($column)->where($where)->order(array('starttime' => 'asc'))->fetchAll();
                foreach ($m_usergradequeue as $k0 => $v0) {
                    if (empty($m_usergradequeue[$k0 + 1]) || $m_usergradequeue[$k0 + 1]['starttime'] != date('Y-m-d H:i:s', strtotime($v0['endtime'] . ' +1 second'))) {
                        $endtime = $v0['endtime'];
                        break;
                    }
                }

                $replace = array(
                    'user_id' => $user_id,
                    'grade' => $grade,
                    'starttime' => $starttime,
                    'endtime' => $endtime,
                );
                Model('usergrade')->replace($replace);
            } else {
                $grade = 'free';
                $tmp0 = array(
                    'user_id' => $user_id,
                    'grade' => $grade,
                    'starttime' => date('Y-m-d 00:00:00'),
                    'endtime' => date('Y-m-d 23:59:59'),
                );
                Model('usergrade')->replace($tmp0);
            }
        }

        return $grade;
    }

    public function set_usergrade(array $param)
    {
        $result = true;

        if (empty($param['user_id']) || empty($param['grade']) || (empty($param['starttime']) || $param['starttime'] == '0000-00-00 00:00:00') || (empty($param['endtime']) || $param['endtime'] == '0000-00-00 00:00:00')) {
            $result = false;
            goto _return;
        }

        $checktime = date('Y-m-d 12:00:00');

        //先做 add, 再做 get_usergrade
        $tmp0 = [
            'user_id' => $param['user_id'],
            'order_id' => empty($param['order_id']) ? 0 : $param['order_id'],
            'grade' => $param['grade'],
            'starttime' => $param['starttime'],
            'endtime' => $param['endtime'],
            'remark' => isset($param['remark']) ? $param['remark'] : null,
        ];

        (new \usergradequeueModel)->add($tmp0);

        if ($param['starttime'] <= $checktime && $param['endtime'] >= $checktime) {
            switch (self::get_usergrade($param['user_id'])) {
                case 'free':
                    (new \usergradeModel)->replace([
                        'user_id' => $param['user_id'],
                        'grade' => $param['grade'],
                        'starttime' => $param['starttime'],
                        'endtime' => $param['endtime'],
                    ]);
                    break;

                case $param['grade']:
                    (new \usergradeModel)->replace([
                        'user_id' => $param['user_id'],
                        'endtime' => $param['endtime'],
                    ]);
                    break;
            }
        }

        _return:
        return $result;
    }

    public function get_userlevel($user_id)
    {
        //關注數
        $m_follow = (new \followModel)->where(array(array(array(array('user_id', '=', $user_id)), 'and')))->fetch();
        if ($m_follow['count_from'] < 60) {
            $level_followfrom = 0;
        } elseif (60 <= $m_follow['count_from'] && $m_follow['count_from'] < 600) {
            $level_followfrom = 1;
        } elseif (600 <= $m_follow['count_from'] && $m_follow['count_from'] < 9000) {
            $level_followfrom = 2;
        } elseif (9000 <= $m_follow['count_from'] && $m_follow['count_from'] < 72000) {
            $level_followfrom = 3;
        } elseif (72000 <= $m_follow['count_from'] && $m_follow['count_from'] < 504000) {
            $level_followfrom = 4;
        } elseif (504000 <= $m_follow['count_from']) {
            $level_followfrom = 5;
        }

        //相本總下載數
        $where = array(
            array(array(array('album.user_id', '=', $user_id), array('album.act', '!=', 'delete')), 'and')
        );
        $c_album = (new \albumModel)->column(array('sum(albumstatistics.count)'))->join(array(array('left join', 'albumstatistics', 'using(album_id)')))->where($where)->fetchColumn();
        if ($c_album < 100) {
            $level_album = 0;
        } elseif (100 <= $c_album && $c_album < 1440) {
            $level_album = 1;
        } elseif (1440 <= $c_album && $c_album < 29700) {
            $level_album = 2;
        } elseif (29700 <= $c_album && $c_album < 288000) {
            $level_album = 3;
        } elseif (288000 <= $c_album && $c_album < 2520000) {
            $level_album = 4;
        } elseif (2520000 <= $c_album) {
            $level_album = 5;
        }

        $level = min($level_followfrom, $level_album);

        $m_user = (new \userModel)->where(array(array(array(array('user_id', '=', $user_id)), 'and')))->fetch();

        if ($level != $m_user['level']) {
            $tmp0 = array(
                'user_id' => $m_user['user_id'],
                'level' => $level,
            );
            (new \userModel)->replace($tmp0);
        }

        return $level;
    }

    /**
     * 取得用戶大頭照
     * @param $user_id
     * @return string
     * @deprecated 請改用 \userModel::getPicture
     */
    public function get_userpicture($user_id)
    {
        return \userModel::getPicture($user_id);
    }

    public static function get_usercover($user_id)
    {
        //讀取時間戳
        $file_timestamp = PATH_STORAGE . SITE_LANG . '/user/' . $user_id . '/timestamp.txt';
		$a_timestamp = file_exists($file_timestamp) ? json_decode(file_get_contents($file_timestamp), true) : array();
        $timestamp = (empty($a_timestamp['cover.jpg'])) ? null : $a_timestamp['cover.jpg'];

        return storagefile(SITE_LANG . '/user/' . $user_id . '/cover.jpg', $timestamp);
    }

    /**
     * 設置用戶大頭照
     * @param $user_id
     * @param null $picture
     * @return bool
     * @deprecated 請改用 \userModel::setPicture
     */
    public function set_userpicture($user_id, $picture = null)
    {
        return \userModel::setPicture($user_id, $picture);
    }

    /**
     * 170822 - 專區背景圖
     */
    public static function set_usercover($user_id, $cover = null)
    {
        $return = false;

        $ex = \Core::get_usercover($user_id);
        if ($cover != null) file_put_contents(PATH_STORAGE . $ex, $cover);

        //覆寫時間戳
        $file_timestamp = PATH_STORAGE . SITE_LANG . '/user/' . $user_id . '/timestamp.txt';
        $a_timestamp = file_exists($file_timestamp) ? json_decode(file_get_contents($file_timestamp), true) : array();
        $a_timestamp = array_replace($a_timestamp, array('cover.jpg' => time()));

        if (file_put_contents($file_timestamp, json_encode($a_timestamp)) !== false) {
            \Extension\aws\S3::upload($file_timestamp);
        }

        if (rename(PATH_STORAGE . $ex, PATH_STORAGE . \Core::get_usercover($user_id))) {
            $return = true;

            \Extension\aws\S3::upload(PATH_STORAGE . \Core::get_usercover($user_id));
        }

        return $return;
    }

    //2016-07-14 Lion: 這個準備棄用, 改使用 userModel::getPoint
    protected function get_userpoint($user_id, $platform, $point_free = false)
    {
        $m_userpoint = Model('userpoint')->where(array(array(array(array('user_id', '=', $user_id), array('platform', '=', $platform)), 'and')))->lock('for update')->fetch();

        if (!empty($m_userpoint)) {
            $point = ($point_free) ? $m_userpoint['point_free'] : $m_userpoint['point'];
        } else {
            $point = 0;
            $tmp0 = array(
                'user_id' => $user_id,
                'platform' => $platform,
                'point' => $point,
                'point_free' => $point,
            );
            Model('userpoint')->replace($tmp0);
        }

        return $point;
    }

    public function set_userpoint(array $param)
    {
        $result = true;

        if (empty($param['user_id']) || empty($param['platform'])) {
            $result = false;
            goto _return;
        }

        $_point = (empty($param['point'])) ? 0 : $param['point'];
        $_point_free = (empty($param['point_free'])) ? 0 : $param['point_free'];

        $_before = self::get_userpoint($param['user_id'], $param['platform']);
        $_free_before = self::get_userpoint($param['user_id'], $param['platform'], true);

        $tmp0 = [
            'user_id' => $param['user_id'],
            'trade' => $param['trade'],
            'trade_id' => empty($param['trade_id']) ? 0 : $param['trade_id'],
            'platform' => $param['platform'],
            'point_before' => $_before,
            'point_free_before' => $_free_before,
            'point' => $_point,
            'point_free' => $_point_free,
        ];

        Model('userpointqueue')->add($tmp0);

        $tmp0 = [
            'user_id' => $param['user_id'],
            'platform' => $param['platform'],
            'point' => $_before + $_point,
            'point_free' => $_free_before + $_point_free,
        ];
        Model('userpoint')->replace($tmp0);

        _return:
        return $result;
    }

    protected function notice_switch(array $param)
    {
        $return = false;
        if (is_array($param)) {
            switch ($param['type']) {
                case 'user':
                    /**
                     * 取得 user所屬的所有album_id
                     */
                    $m_album_id = Model('album')->column(array('album_id', 'act'))->where(array(array(array(array('user_id', '=', $param['id'])), 'and')))->fetchAll();
                    $all_id = array();
                    $all_open_id = array();
                    foreach ($m_album_id as $k => $v) {
                        $all_id[$k] = $v['album_id'];
                        if ($v['act'] == 'open') {
                            $all_open_id[$k] = $v['album_id'];
                        }
                    }
                    if ($param['act'] == 'open') {
                        $return = Model('notice')->where(array(array(array(array('type', '=', 'album'), array('id', 'in', $all_open_id)), 'and')))->edit(array('act' => 'open'));
                    } else {
                        $return = Model('notice')->where(array(array(array(array('type', '=', 'album'), array('id', 'in', $all_id)), 'and')))->edit(array('act' => 'close'));
                    }
                    break;

                case 'album':
                    switch ($param['act']) {
                        case 'close':
                        case 'delete':
                            $return = Model('notice')->where(array(array(array(array('type', '=', 'album'), array('id', '=', $param['id'])), 'and')))->edit(array('act' => 'close'));
                            break;

                        case 'open':
                            $column = array(
                                'user.act',
                            );
                            $join = array(
                                array('left join', 'user', 'using(user_id)'),
                            );
                            $where = array(
                                array(array(array('album.album_id', '=', $param['id'])), 'and')
                            );
                            $album_user_act = Model('album')->column($column)->join($join)->where($where)->fetchColumn();
                            $return = Model('notice')->where(array(array(array(array('type', '=', 'album'), array('id', '=', $param['id'])), 'and')))->edit(array('act' => $album_user_act));
                            break;
                    }
                    break;
            }
        }
        return $return;
    }

    /**
     *   取得要推播的文字及圖示
     * @param Array $param
     *    $param = [
     *        trigger = [
     *                int    user_id :    觸發者的 id
     *            string    type      :    觸發類型
     *                int    typeID  :    觸發類型id
     *            string    refer     :   動作類型
     *        ],
     *             int   targetID  :   推送對象的目標id (可為空)
     *        string   typeOfSNS  :   typeOfSNS : 送出SNS的對應type名稱
     *    ]
     *
     *  typeOfSNS 請參照: https://docs.google.com/spreadsheets/d/1ikmSaWgOoh0eE3pU97yvaos0KRRbyCHFnOrtMEGmzYk/edit#gid=0
     *
     * @return Array @return
     **/
    public function getSNSParams(array $param)
    {
        $title = 'pinpinbox';
        $message = '';
        $icon = static_file('images/logo.png');
        $pinpinboard = false;
        switch ($param['typeOfSNS']) {
            case 'albumqueue':
                switch ($param['trigger']['refer']) {
                    case 'UserCollect':
                        //收藏者的名稱
                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $param['trigger']['user_id']]], 'and']])->fetchColumn();
                        $trigger_name = (empty($m_userName) || $m_userName == '') ? '有人' : $m_userName;

                        $message = $trigger_name . '在pinpinbox上收藏你的作品!';

                        $icon = frontstageController::type2image_url('user', $param['trigger']['user_id']);
                        break;

                    case 'UserUpdateAlbum':
                        $_thisUserId = $param['trigger']['user_id'];

                        //作者名稱
                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $_thisUserId]], 'and']])->fetchColumn();
                        $trigger_name = (empty($m_userName) || $m_userName == '') ? '創作職人' : $m_userName;

                        //作品
                        $m_album = (new \albumModel)->column(['name', 'cover'])->where([[[['album_id', '=', $param['trigger']['typeId']]], 'and']])->fetch();

                        switch (Core::get_usergrade($_thisUserId)) {
                            case 'plus':
                            case 'profession' :
                                $message = $trigger_name . '更新了作品[' . $m_album['name'] . ']!';
                                break;

                            default:
                                $message = $trigger_name . '更新了作品! 快來看看';
                                break;
                        }
                        $icon = frontstageController::type2image_url('user', $_thisUserId);
                        break;

                    case 'UserLikeAlbum':
                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $param['trigger']['user_id']]], 'and']])->fetchColumn();
                        $trigger_name = (empty($m_userName) || $m_userName == '') ? '有人' : $m_userName;

                        $m_album = (new \albumModel)->column(['name', 'cover'])->where([[[['album_id', '=', $param['trigger']['typeId']]], 'and']])->fetch();

                        $message = $trigger_name . '釘了[' . $m_album['name'] . '] 一下!';
                        $icon = frontstageController::type2image_url('user', $param['trigger']['user_id']);

                        break;

					case 'userNotifyMySelf' :
						// 取得訂單內容
						$type = $param['trigger']['type'];
						$type_id = $param['trigger']['typeId'];
						$m_exchange = (new exchangeModel)->where([[[['exchange_id', '=', $param['trigger']['exchange_id']]] ,'and']])->fetch();
						$column = [$type.'.'.$type.'_id', $type.'.user_id', $type.'.name '.$type.'_name', 'user.name user_name'];
						$modelClass = $type.'Model';
						$m_type = (new $modelClass)
							->column($column)
							->join([['LEFT JOIN', 'user', 'USING(user_id)']])
							->where([[[[$type.'.'.$type.'_id', '=', $m_exchange['id']]], 'and']])
							->fetch();

						$album_author = $m_type['user_name'];
						$album_name = $m_type['album_name'];
						$payPoint = (int)$m_exchange['point'] + (int)$m_exchange['point_free'];
						$balance = (new userModel())->getPoint($param['trigger']['user_id'], $m_exchange['platform']);

						$message = '感謝您贊助「'.$album_author.'」'.'，目前剩餘點數為'.$balance.'p';
						break;

                    default:
                        break;
                }
                break;

            case 'follow':
                switch ($param['trigger']['refer']) {
                    case 'userFollow':
                        /**
                         * 應用: 若 user_id(triggerTypeId) 為付費會員, 則取得該會員自行設定的文字作為推播訊息
                         */

                        //追隨者名稱
                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $param['trigger']['user_id']]], 'and']])->fetchColumn();
                        $trigger_name = (empty($m_userName) || $m_userName == '') ? '創作職人' : $m_userName;
                        //被追隨者名稱
                        $m_targetName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $param['targetId']]], 'and']])->fetchColumn();
                        $target_name = (empty($m_targetName) || $m_targetName == '') ? '創作職人' : $m_targetName;

                        $title = $trigger_name . '在pinpinbox上追蹤你!';
                        $message = 'Hi ' . $trigger_name . ' 已關注你的創作！';
                        $icon = frontstageController::type2image_url('user', $param['trigger']['user_id']);
                        break;

                    case 'UserCreateAlbum' :
                        $_thisUserId = $param['trigger']['user_id'];

                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $_thisUserId]], 'and']])->fetchColumn();
                        $trigger_user_name = (empty($m_userName) || $m_userName == '') ? '創作職人' : $m_userName;
                        $m_albumName = (new \albumModel)->column(['name'])->where([[[['album_id', '=', $param['trigger']['typeId']]], 'and']])->fetchColumn();
                        $trigger_album_name = (empty($m_albumName) || $m_albumName == '') ? null : '[' . $m_albumName . ']';

                        switch (Core::get_usergrade($_thisUserId)) {
                            case 'plus':
                            case 'profession' :
                                $title = M_PACKAGE;
                                $message = $trigger_user_name . '發布了新作品' . $trigger_album_name . '!';
                                break;

                            default:
                                $title = M_PACKAGE;
                                $message = $trigger_user_name . '發布了新作品!';
                                break;
                        }
                        $icon = frontstageController::type2image_url('user', $_thisUserId);

                        break;

                    default:
                        break;
                }

                break;

            case 'albumqueue@messageboard' :
                switch ($param['trigger']['refer']) {
                    case 'addComment':
                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $param['trigger']['user_id']]], 'and']])->fetchColumn();
                        $trigger_name = (empty($m_userName) || $m_userName == '') ? '有人' : $m_userName;

                        $m_album = (new \albumModel)->column(['name', 'cover'])->where([[[['album_id', '=', $param['trigger']['typeId']]], 'and']])->fetch();

                        $message = $trigger_name . '在[' . $m_album['name'] . ']留言!';
                        $icon = frontstageController::type2image_url('user', $param['trigger']['user_id']);
                        $pinpinboard = true;

                        break;

                    default:
                        break;
                }
                break;

            case 'user@messageboard' :
                switch ($param['trigger']['refer']) {
                    case 'addComment':
                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $param['trigger']['user_id']]], 'and']])->fetchColumn();
                        $trigger_name = (empty($m_userName) || $m_userName == '') ? '有人' : $m_userName;

                        $message = $trigger_name . '在你的專區內留言!';
                        $icon = frontstageController::type2image_url('user', $param['trigger']['user_id']);
                        $pinpinboard = true;
                        break;

                    case 'mention':
                        $m_userName = (new \userModel)->column(['name'])->where([[[['user_id', '=', $param['trigger']['user_id']]], 'and']])->fetchColumn();
                        $trigger_name = (empty($m_userName) || $m_userName == '') ? '有人' : $m_userName;
                        $message = $trigger_name . '標注了你!';
                        $icon = frontstageController::type2image_url('user', $param['trigger']['user_id']);
                        $pinpinboard = true;
                        break;
                    default:
                        break;
                }
                break;

            default:
                # code...
                break;
        }

        return [
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'pinpinboard' => $pinpinboard,
        ];
    }
}
echo "<script>console.log(".json_encode("\core\core.php:end(設前後台路徑檔名等變數)".date('m/d/Y h:i:s a', time())).");</script>";
