<?php

class userModel extends Model
{
    protected $database = 'site';
    protected $table = 'user';
    protected $memcache = 'site';
    protected $join_table = ['hobby_user', 'userstatistics', 'albumstatistics', 'user_facebook', 'follow', 'album', 'albumqueue', 'creative', 'indexcreative', 'pinpinboard', 'reward'];

    protected static $key = 'file';

    function __construct()
    {
        parent::__construct_child();
		
		
		echo "<script>console.log(".json_encode("\model\user.php:start(使用者DB與動作變數)".date('m/d/Y h:i:s', time())).");</script>";
    }

    

    function ableToLogin($account, $password)
    {
        $result = 1;
        $message = null;

        $account = (trim($account) === '') ? null : trim($account);
        $password = (trim($password) === '') ? null : trim($password);

        if ($account === null || $password === null) {
            $result = 0;
            $message = _('Please enter Account No. and password.');
            goto _return;
        }

        $m_user = Model('user')->column(['user_id', '`password`', 'act'])->where([[[['account', '=', $account], ['way', '=', 'none']], 'and']])->fetch();

        if (empty($m_user)) {
            $result = 0;
            $message = _('This Account is not exist.');
            goto _return;
        } else {
            if (!password_verify($password, $m_user['password'])) {
                $result = 0;
                $message = _('User\'s password is incorrect.');
                goto _return;
            } elseif ($m_user['act'] == 'close') {
                $result = 0;
                $message = _('This account has been locked.');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToRegister(array $param)
    {
        $result = 1;
        $message = null;
        $redirect = null;

        $account = (isset($param['account']) && trim($param['account']) !== '') ? trim($param['account']) : null;
        $birthday = (isset($param['birthday']) && trim($param['birthday']) !== '') ? trim($param['birthday']) : \Schema\user::$birthday_Default;
        $businessuser_id = isset($param['businessuser_id']) ? $param['businessuser_id'] : null;
        $cellphone = (isset($param['cellphone']) && trim($param['cellphone']) !== '') ? \Core\I18N::cellphone($param['cellphone']) : null;
        $coordinate = (isset($param['coordinate']) && trim($param['coordinate']) !== '') ? str_replace(' ', '', $param['coordinate']) : null;
        $gender = isset($param['gender']) ? $param['gender'] : \Schema\user::$gender_Default;
        $name = (isset($param['name']) && trim($param['name']) !== '') ? trim($param['name']) : null;
        $password = isset($param['password']) ? $param['password'] : null;
        $smspassword = (isset($param['smspassword']) && trim($param['smspassword']) !== '') ? trim($param['smspassword']) : null;
        $way = isset($param['way']) ? $param['way'] : null;
        $way_id = isset($param['way_id']) ? $param['way_id'] : null;

        if (isset($param['act'])) {
            if (!array_key_exists($param['act'], \Schema\user::$act)) {
                $result = 0;
                $message = 'Param error. "act" is an invalid value.';
                goto _return;
            }
        }

        if ($way === null) {
            $result = 0;
            $message = 'Param error. "way" is required.';
            goto _return;
        } else {
            if (!in_array($way, \Schema\user::$way)) {
                $result = 0;
                $message = 'Param error. "way" is an invalid value.';
                goto _return;
            }
        }

        switch ($way) {
            case 'none':
                if ($account === null) {
                    $result = 0;
                    $message = 'Param error. "account" is required.';
                    goto _return;
                }

                if ($cellphone === null) {
                    $result = 0;
                    $message = 'Param error. "cellphone" is required.';
                    goto _return;
                }

                if ($name === null) {
                    $result = 0;
                    $message = 'Param error. "name" is required.';
                    goto _return;
                }

                if ($password === null) {
                    $result = 0;
                    $message = 'Param error. "password" is required.';
                    goto _return;
                }

                if ($smspassword === null) {
                    $result = 0;
                    $message = 'Param error. "smspassword" is required.';
                    goto _return;
                }

                list ($result, $message) = array_decode_return((new smspasswordModel)->verify($account, $cellphone, $smspassword));
                if ($result != 1) {
                    goto _return;
                }

                list ($result, $message) = array_decode_return((new userModel)->check('account', $account));
                if ($result != 1) {
                    goto _return;
                }

                list ($result, $message) = array_decode_return((new userModel)->check('cellphone', $cellphone));
                if ($result != 1) {
                    goto _return;
                }
                break;

            case 'facebook':
                if ($way_id === null) {
                    $result = 0;
                    $message = 'Param error. "way_id" is required.';
                    goto _return;
                }

                $user_id = (new user_facebookModel)
                    ->column(['user_id'])
                    ->where([[[['facebook_id', '=', $way_id]], 'and']])
                    ->fetchColumn();

                //有找到 user, 視為登入
                if ($user_id) {
                    list ($result, $message) = array_decode_return((new userModel)->usable($user_id));
                    if ($result != 1) {
                        goto _return;
                    }

                    (new userModel)
                        ->where([[[['user_id', '=', $user_id]], 'and']])
                        ->edit([
                            'lastloginip' => remote_ip(),
                            'lastlogintime' => inserttime()
                        ]);

                    (new userModel)->setSession($user_id);

                    $result = \Lib\Result::SYSTEM_OK;
                    $redirect = empty(query_string_parse()['redirect']) ? frontstageController::url() : query_string_parse()['redirect'];
                    goto _return;
                } else {
                    if ($name === null) {
                        $result = 0;
                        $message = 'Param error. "name" is required.';
                        goto _return;
                    }
                }
                break;
        }

        $result = 1;
        $message = _('Your account has been registered successfully! Please enter your personal information. Thank you!');
        $redirect = frontstageController::url('user', 'verify', query_string_parse());

        _return:
        return array_encode_return($result, $message, $redirect);
    }

    static function ableToSetUserCover($user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return((new \userModel())->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        //
        if (empty($_FILES)) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "file" is required.';
            goto _return;
        } else {
            if (!isset($_FILES[self::$key])) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. Key name must be "' . self::$key . '".';
                goto _return;
            }

            if (!is_writable(mkdir_p_v2(PATH_STORAGE . SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR))) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = '檔案資料夾不可寫入。';
                goto _return;
            }

            $a_file = $_FILES[self::$key];

            if ($a_file['error'] != UPLOAD_ERR_OK) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$a_file['error']];
                goto _return;
            }

            if (!is_uploaded_file($a_file['tmp_name'])) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = '檔案未經由正常途徑上傳。';
                goto _return;
            }

            if (!is_image($a_file['tmp_name'], [IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('上傳檔案類型僅JPEG／JPG／PNG。');
                goto _return;
            }

            if ($a_file['size'] > (2 * 1024 * 1024)) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('超過上傳單一檔案大小限制：2MB。');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToUpdate($user_id, array $param)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return((new \userModel())->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        if (isset($param['creative_name'])) {
            if (charlen($param['creative_name']) > \Schema\user::$creative_name_Length) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. The string of "creative_name" is longer than ' . \Schema\user::$creative_name_Length . '.';
                goto _return;
            }
        }

        if (isset($param['gender'])) {
            if (!in_array($param['gender'], \Schema\user::$gender)) {
                $result = \Lib\Result::SYSTEM_ERROR;
                $message = 'Param error. "gender" is an invalid value.';
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function ableToUpdateCellphone($user_id, $cellphone, $smspassword)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        //
        list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        //
        $cellphone = (trim($cellphone) === '') ? null : \Core\I18N::cellphone($cellphone);

        if ($cellphone === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "cellphone" is required.';
            goto _return;
        } else {
            $Model_user = (new \userModel)
                ->column(['cellphone'])
                ->where([[[['user_id', '=', $user_id]], 'and']])
                ->fetch();

            if ($cellphone == $Model_user['cellphone']) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('舊的手機號碼與新的手機號碼相同。');
                goto _return;
            }

            $count = (new \userModel)
                ->column(['COUNT(1)'])
                ->where([[[['cellphone', '=', $cellphone]], 'and']])
                ->fetchColumn();

            if ($count) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('The cellphone number already exists, please use another.');
                goto _return;
            }
        }

        //
        $smspassword = (trim($smspassword) === '') ? null : trim($smspassword);

        if ($smspassword === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "smspassword" is required.';
            goto _return;
        } else {
            $Model_smspassword = (new \smspasswordModel)
                ->column(['smspassword'])
                ->where([[[['user_id', '=', $user_id], ['user_cellphone', '=', $cellphone]], 'and']])
                ->fetch();

            if (empty($Model_smspassword)) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('SMS-password does not exist.');
                goto _return;
            } elseif ($smspassword != $Model_smspassword['smspassword']) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('SMS-password is incorrect.');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'albumModel'],
            ['class' => 'albumstatisticsModel'],
            ['class' => 'cooperationModel'],
            ['class' => 'followfromModel'],
            ['class' => 'incomeModel'],
            ['class' => 'photoModel'],
            ['class' => 'photouseforModel'],
            ['class' => 'photousefor_userModel'],
            ['class' => 'templateModel'],
            ['class' => 'creativeModel'],
            ['class' => 'indexcreativeModel'],
            ['class' => 'albumqueueModel'],
            ['class' => 'pinpinboardModel'],
        ];
    }

    function check($checkcolumn, $checkvalue)
    {
        $result = 1;
        $message = null;

        $checkcolumn = (trim($checkcolumn) === '') ? null : trim($checkcolumn);

        if ($checkcolumn === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        switch ($checkcolumn) {
            case 'account':
                $checkvalue = (trim($checkvalue) === '') ? null : trim($checkvalue);

                if ($checkvalue === null) {
                    $result = 0;
                    $message = _('Param error.');
                    goto _return;
                }

                $m_user = Model('user')->column(['count(1)'])->where([[[['account', '=', $checkvalue], ['way', '=', 'none']], 'and']])->fetchColumn();
                if (!empty($m_user)) {
                    $result = 0;
                    $message = _('The account already exists, please use another.');
                    goto _return;
                }
                break;

            case 'cellphone':
                $checkvalue = (trim($checkvalue) === '') ? null : \Core\I18N::cellphone($checkvalue);

                if ($checkvalue === null) {
                    $result = 0;
                    $message = _('Param error.');
                    goto _return;
                }

                $m_user = Model('user')->column(['count(1)'])->where([[[['cellphone', '=', $checkvalue]], 'and']])->fetchColumn();
                if (!empty($m_user)) {
                    $result = 0;
                    $message = _('The cellphone number already exists, please use another.');
                    goto _return;
                }
                break;

            case 'creative_code':
                $checkvalue = (trim($checkvalue) === '') ? null : trim($checkvalue);

                if ($checkvalue === null) {
                    $result = 0;
                    $message = _('Param error.');
                    goto _return;
                }

                if (!preg_match('/^[a-zA-Z0-9]+$/', $checkvalue)) {
                    $result = 0;
                    $message = _('The creative-code enter only letters and numbers.');
                    goto _return;
                } elseif (preg_match('/^index$/i', $checkvalue)) {
                    $result = 0;
                    $message = _('The creative-code can not be named [index].');
                    goto _return;
                } else {
                    $m_user = Model('user')->column(['count(1)'])->where([[[['creative_code', '=', $checkvalue]], 'and']])->fetchColumn();
                    if (!empty($m_user)) {
                        $result = 0;
                        $message = _('The creative-code already exists, please use another.');
                        goto _return;
                    }
                }
                break;

            case 'user':
                $checkvalue = empty($checkvalue) ? null : $checkvalue;

                if ($checkvalue === null) {
                    $result = 0;
                    $message = _('Param error.');
                    goto _return;
                }

                $m_user = Model('user')->column(['act'])->where([[[['user_id', '=', $checkvalue]], 'and']])->fetch();
                if (empty($m_user)) {
                    $result = 0;
                    $message = _('User does not exist.');
                    goto _return;
                } elseif ($m_user['act'] != 'open') {
                    $result = 0;
                    $message = _('User is not open.');
                    goto _return;
                }
                break;

            default:
                throw new Exception('Unknown case');
                break;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function forgotPassword($account, $cellphone)
    {
        $result = 1;
        $message = _('Success.');
        $redirect = frontstageController::url('user', 'login');

        $account = (trim($account) === '') ? null : trim($account);
        $cellphone = (trim($cellphone) === '') ? null : \Core\I18N::cellphone($cellphone);

        if ($account === null || $cellphone === null) {
            $result = 0;
            $message = _('Please enter Account No. and cellphone.');
            $redirect = frontstageController::url('user', 'forgot');
            goto _return;
        }

        $m_user = Model('user')->column(['user_id', 'cellphone', 'act'])->where([[[['account', '=', $account], ['way', '=', 'none']], 'and']])->fetch();

        if (empty($m_user)) {
            $result = 0;
            $message = _('This Account is not exist.');
            $redirect = frontstageController::url('user', 'forgot');
            goto _return;
        } else {
            if ($m_user['cellphone'] != $cellphone) {
                $result = 0;
                $message = _('User\'s account and cellphone does not match.');
                $redirect = frontstageController::url('user', 'forgot');
                goto _return;
            } elseif ($m_user['act'] == 'close') {
                $result = 0;
                $message = _('This account has been locked.');
                $redirect = frontstageController::url('user', 'forgot');
                goto _return;
            }
        }

        $password = random_password(8, 's');

        Model('user')->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->edit(['`password`' => password_hash($password, PASSWORD_DEFAULT)]);

        //sms
        $message = _('Your password') . ':［' . $password . '］，' . _('We suggest you change your security password to keep your account safe.');
        list($result0, $message0) = array_decode_return(Core::extension('sms', 'every8d')->send($m_user['cellphone'], $message));
        if ($result0 != 1) {
            $result = $result0;
            $message = _('[SMS] occur exception, please contact us.');
            $redirect = frontstageController::url('user', 'forgot');
            goto _return;
        }

        _return:
        return array_encode_return($result, $message, $redirect);
    }

    function getCreative($user_id)
    {
        $return = [];

        $m = (new userModel)
            ->column([
                'user.creative_name',
                'user.discuss',
                'user.user_id',
                'user.name',
                'user.sociallink',
                'user.description',
                'userstatistics.besponsored + userstatistics.besponsored_manual besponsored',
                'follow.count_from + userstatistics.followfrom_manual count_from',
            ])
            ->join([
                ['LEFT JOIN', 'follow', 'USING(user_id)'],
                ['INNER JOIN', 'userstatistics', 'USING(user_id)'],
            ])
            ->where([[[['user.user_id', '=', $user_id], ['user.act', '=', 'open']], 'and']])
            ->fetch();

        if ($m) {
            $return = [
                'follow' => [
                    'count_from' => $m['count_from'],
                ],
                'user' => [
                    'creative_name' => $m['creative_name'],
                    'discuss' => $m['discuss'] === 'open' ? true : false,
                    'user_id' => $m['user_id'],
                    'name' => $m['name'],
                    'sociallink' => $m['sociallink'],
                    'description' => $m['description'],
                ],
                'userstatistics' => [
                    'besponsored' => $m['besponsored'],
                ],
            ];
        }

        return $return;
    }

    function getFollowto(array $where = null, array $order = null, $limit = null)
    {
        $column = [
            'user.user_id',
            'user.name',
            'user.description',
            'followto.inserttime',
        ];

        $join = [
            ['inner join', 'followto', 'on followto.to = user.user_id'],
        ];

        $where = array_merge([[[['user.act', '=', 'open']], 'and']], (array)$where);

        return Model('user')->column($column)->join($join)->where($where)->order($order)->limit($limit)->fetchAll();
    }

    function getHobby($user_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($user_id)) $user_id = null;

        if ($user_id === null) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        $m_hobby = Model('hobby')->column(['hobby.hobby_id'])->join([['inner join', 'hobby_user', 'using(hobby_id)']])->where([[[['hobby_user.user_id', '=', $user_id]], 'and']])->fetchAll();

        $a_hobby = array_column($m_hobby, 'hobby_id');

        $data = [
            'hobby' => $a_hobby,
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    static function getHotList($limit = null)
    {
        $a_exhibit = json_decode(
            (new \indexpopularityModel)
                ->column(['exhibit'])
                ->where([[[['indexpopularity_id', '=', 1]], 'and']])
                ->fetchColumn(),
            true
        );

        $data = [];

        if ($a_exhibit) {
            $m_user = (new \userModel)
                ->column([
                    'follow.count_from + userstatistics.followfrom_manual count_from',
                    'user.creative_name',
                    'user.description',
                    'user.user_id',
                    'user.name',
                    'user.inserttime',
                ])
                ->join([
                    ['inner join', 'follow', 'using(user_id)'],
                    ['inner join', 'userstatistics', 'on userstatistics.user_id = user.user_id'],
                ])
                ->where([[[['user.user_id', 'in', $a_exhibit], ['user.act', '=', 'open']], 'and']])
                ->limit($limit)
                ->fetchAll();

            foreach ($m_user as $v_0) {
                $data[] = [
                    'follow' => [
                        'count_from' => $v_0['count_from'],
                    ],
                    'user' => [
                        'creative_name' => $v_0['creative_name'],
                        'description' => $v_0['description'],
                        'user_id' => $v_0['user_id'],
                        'name' => $v_0['name'],
                        'inserttime' => $v_0['inserttime'],
                    ],
                ];
            }
        }

        return $data;
    }

    static function getNewJoinList($limit = null)
    {
        $m_user = (new \userModel)
            ->column([
                'follow.count_from + userstatistics.followfrom_manual count_from',
                'user.creative_name',
                'user.description',
                'user.user_id',
                'user.name',
                'user.inserttime',
            ])
            ->join([
                ['inner join', 'indexcreative', 'on indexcreative.user_id = user.user_id and indexcreative.act = \'open\''],
                ['inner join', 'follow', 'on follow.user_id = user.user_id'],
                ['inner join', 'userstatistics', 'on userstatistics.user_id = user.user_id'],
            ])
            ->where([[[['user.act', '=', 'open']], 'and']])
            ->order(['indexcreative.sequence' => 'asc'])
            ->limit($limit)
            ->fetchAll();

        $data = [];

        foreach ($m_user as $v_0) {
            $data[] = [
                'follow' => [
                    'count_from' => $v_0['count_from'],
                ],
                'user' => [
                    'creative_name' => $v_0['creative_name'],
                    'description' => $v_0['description'],
                    'user_id' => $v_0['user_id'],
                    'name' => $v_0['name'],
                    'inserttime' => $v_0['inserttime'],
                ],
            ];
        }

        return $data;
    }

    static function getPicture($user_id)
    {
        //讀取時間戳
        $file_timestamp = self::getStoragePath($user_id) . 'timestamp.txt';
        $a_timestamp = file_exists($file_timestamp) ? json_decode(file_get_contents($file_timestamp), true) : [];
        $timestamp = (empty($a_timestamp['picture.jpg'])) ? null : $a_timestamp['picture.jpg'];

        $path_sub = storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . 'picture.jpg', $timestamp);

        $path = PATH_STORAGE . $path_sub;

        if (!is_file($path)) {
            (new \Core\Image)
                ->set(PATH_STATIC_FILE . M_PACKAGE . DIRECTORY_SEPARATOR . SITE_LANG . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'face_sample.png')
                ->setType('jpg')
                ->save($path);
        }

        return $path_sub;
    }

    /**
     * 取得 P 點
     * @param int $user_id
     * @param string $platform
     * @return int
     */
    //2016-07-14 Lion: \Core::get_userpoint 準備棄用, 改使用這個
    function getPoint($user_id, $platform)
    {
        $point = (new \userpointModel)
            ->column(['(`point` + point_free) `point`'])
            ->where([[[['user_id', '=', (int)$user_id], ['platform', '=', trim($platform)]], 'and']])
            ->fetchColumn();

        return $point ? $point : 0;
    }

    function getRecommended(array $where = null, array $order = null, $limit = null)
    {
        $a_user_id = [];

        /**
         * order 1st
         */
        //置頂
        /* 2019-01-02 Lion: mantis 2295
        $a_user_id_1st = array_column(
            (new indexcreativeModel)
                ->column(['user_id'])
                ->where([[[['act', '=', 'open']], 'and']])
                ->order(['sequence' => 'DESC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                ->fetchAll(),
            'user_id'
        );
        */

        $a_user_id_1st = json_decode(
            (new \indexpopularityModel)
                ->column(['exhibit'])
                ->where([[[['indexpopularity_id', '=', 2]], 'and']])
                ->fetchColumn(),
            true
        );

        $order_1st = $a_user_id_1st ? ['FIELD(' . implode(',', array_merge(['user.user_id'], $a_user_id_1st)) . ')' => 'DESC'] : [];

        $a_user_id = array_merge($a_user_id, $a_user_id_1st);

        /**
         * order 2nd
         */
        //權重
        $order_2nd = [];

        /* 2019-01-02 Lion: mantis 2295
        $s_user = (new userModel)->getSession();

        if ($s_user) {
            //2017-01-18 Lion: 因為 user2weight 沒有所有的關聯資料, 不能以 join 方式處理
            $a_user_id_2nd = array_column(
                (new user2weightModel)
                    ->column(['user_id4weight'])
                    ->where([[[['user_id', '=', $s_user['user_id']]], 'and']])
                    ->order(['weight' => 'ASC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                    ->fetchAll(),
                'user_id4weight'
            );

            $order_2nd = $a_user_id_2nd ? ['FIELD(' . implode(',', array_merge(['user.user_id'], $a_user_id_2nd)) . ')' => 'DESC'] : [];

            $a_user_id = array_merge($a_user_id, $a_user_id_2nd);
        }
        */

        /**
         * order 3th
         */
        //一個月內關注數量由高至低
        $order_3th = [];

        /* 2019-01-02 Lion: mantis 2295
        $a_user_id_3th = array_column(
            (new follow2monthModel)
                ->column(['user_id'])
                ->order(['count_from' => 'ASC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                ->fetchAll(),
            'user_id'
        );

        $order_3th = $a_user_id_3th ? ['FIELD(' . implode(',', array_merge(['user.user_id'], $a_user_id_3th)) . ')' => 'DESC'] : [];

        $a_user_id = array_merge($a_user_id, $a_user_id_3th);
        */

        /**
         * order 4th
         */
        //一年內關注數量由高至低
        $order_4th = [];

        /* 2019-01-02 Lion: mantis 2295
        $a_user_id_4th = array_column(
            (new follow2yearModel)
                ->column(['user_id'])
                ->order(['count_from' => 'ASC'])//2017-01-18 Lion: 由於 FIELD() 並不一定有所有項目, 故項目需反過來再以 DESC 排序
                ->fetchAll(),
            'user_id'
        );

        $order_4th = $a_user_id_4th ? ['FIELD(' . implode(',', array_merge(['user.user_id'], $a_user_id_4th)) . ')' => 'DESC'] : [];

        $a_user_id = array_merge($a_user_id, $a_user_id_4th);
        */

        $data = [];

        if ($a_user_id) {
            $m_user = (new userModel)
                ->column([
                    'follow.count_from + userstatistics.followfrom_manual count_from',
                    'user.creative_name',
                    'user.description',
                    'user.user_id',
                    'user.name',
                    'user.inserttime',
                ])
                ->join([
                    ['inner join', 'follow', 'using(user_id)'],
                    ['inner join', 'userstatistics', 'on userstatistics.user_id = user.user_id'],
                ])
                ->where(array_merge([[[['user.user_id', 'in', $a_user_id], ['user.act', '=', 'open']], 'and']], (array)$where))
                ->order(array_merge($order_1st, $order_2nd, $order_3th, $order_4th, (array)$order))
                ->limit($limit)
                ->fetchAll();

            foreach ($m_user as $v0) {
                $data[] = [
                    'follow' => [
                        'count_from' => $v0['count_from'],
                    ],
                    'user' => [
                        'creative_name' => $v0['creative_name'],
                        'description' => $v0['description'],
                        'user_id' => $v0['user_id'],
                        'name' => $v0['name'],
                        'inserttime' => $v0['inserttime'],
                    ],
                ];
            }

            usort($data, function ($a, $b) {
                if ($a['user']['inserttime'] == $b['user']['inserttime']) {
                    return 0;
                }

                return ($a['user']['inserttime'] < $b['user']['inserttime']) ? 1 : -1;
            });
        }

        return $data;
    }

    function getSession()
    {
        return Session::get('user');
    }

    static function getStoragePath($user_id)
    {
        return mkdir_p_v2(PATH_STORAGE . SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR);
    }

    // 取得 user 專區連結
    public function getCreativeUrl($user_id, $click = null)
    {
        $param = $click != null ? '?click=' . $click : null;
        $param2 = $click != null ? ['user_id' => $user_id, 'click' => $click] : ['user_id' => $user_id];

        $m_user = (new \userModel)
            ->column(['creative_code'])
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->fetch();

        return empty($m_user['creative_code']) ? frontstageController::url('creative', 'content', $param2) : URL_ROOT . $m_user['creative_code'] . $param;
    }

    static function getUserCoverPath($user_id)
    {
        //讀取時間戳
        $file_timestamp = self::getStoragePath($user_id) . 'timestamp.txt';
        $a_timestamp = file_exists($file_timestamp) ? json_decode(file_get_contents($file_timestamp), true) : [];
        $timestamp = (empty($a_timestamp['cover.jpg'])) ? null : $a_timestamp['cover.jpg'];

        return PATH_STORAGE . storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . 'cover.jpg', $timestamp);
    }

    static function getUserCoverUrl($user_id)
    {
        return path2url(self::getUserCoverPath($user_id));
    }

    function getUserViewed($user_id)
    {
        $return = 0;

        $m_album = (new albumModel())
            ->column(['album_id'])
            ->where([[[['user_id', '=', $user_id, ['act', '!=', 'delete']]], 'and']])
            ->fetchAll();

        if (!empty($m_album)) {
            foreach ($m_album as $k1 => $v1) {
                $a_album_id[] = $v1['album_id'];
            }

            $return = (new albumstatisticsModel())
                ->column(['SUM(`viewed`)'])
                ->where([[[['album_id', 'in', $a_album_id]], 'and']])
                ->fetchColumn();
        }

        $return += (new \userstatisticsModel)
            ->column(['viewed_manual'])
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->fetchColumn();

        return $return;
    }

    /**
     * 2018-10-11 Lion: 此函式由 crontab 運行
     * @return array
     */
    static function importToIndexCreative()
    {
        $array_user_id = (new \creativeModel)
            ->getActiveCreator();

        if ($array_user_id) {
            $array_user_id = array_column(
                (new \userModel)
                    ->column(['user.user_id'])
                    ->where([[[
                        ['user.user_id', 'in', $array_user_id],
                    ], 'and']])
                    ->order(['user.inserttime' => 'desc'])
                    ->limit('0,3')
                    ->fetchAll(),
                'user_id'
            );

            $array_indexcreative_id = [1, 2, 3];

            $editByCase = [];
            $array = [];
            foreach ($array_indexcreative_id as $k_0 => $v_0) {
                $array['when'][] = ['indexcreative_id', '=', $v_0, $array_user_id[$k_0]];
            }
            $array['else'] = 'user_id';
            $editByCase['user_id'] = $array;

            (new \indexcreativeModel)
                ->where([[[['indexcreative_id', 'in', $array_indexcreative_id]], 'and']])
                ->editByCase($editByCase);
        }

        return array_encode_return(1);
    }

    /**
     * 2018-10-11 Lion: 此函式由 crontab 運行
     * @return array
     */
    static function importToIndexPopularity()
    {
        $array_user_id = (new \creativeModel)
            ->getActiveCreator();

        $num = 6;
        $past = 7;

        if ($array_user_id) {
            $array_user_id = array_column(
                (new \userModel)
                    ->column(['user.user_id'])
                    ->join([
                        ['inner join', 'album', 'on album.user_id = user.user_id'],
                        ['inner join', 'albumstatistics2viewed', 'on albumstatistics2viewed.album_id = album.album_id']
                    ])
                    ->where([[[
                        ['user.user_id', 'in', $array_user_id],
                        ['albumstatistics2viewed.datatime', '>=', date('Y-m-d 00:00:00', strtotime('-' . $past . ' days'))]
                    ], 'and']])
                    ->group(['user.user_id'])
                    ->order(['SUM(albumstatistics2viewed.viewed)' => 'desc'])
                    ->limit('0,' . $num)
                    ->fetchAll(),
                'user_id'
            );
        }

        $count = count($array_user_id);

        while ($num > $count) {
            $last = $num - $count;
            ++$past;

            $array_user_id = array_merge(
                $array_user_id,
                array_column(
                    (new \userModel)
                        ->column(['user.user_id'])
                        ->join([
                            ['inner join', 'album', 'on album.user_id = user.user_id'],
                            ['inner join', 'albumstatistics2viewed', 'on albumstatistics2viewed.album_id = album.album_id']
                        ])
                        ->where([[[
                            ['user.user_id', 'not in', $array_user_id],
                            ['albumstatistics2viewed.datatime', '>=', date('Y-m-d 00:00:00', strtotime('-' . $past . ' days'))]
                        ], 'and']])
                        ->group(['user.user_id'])
                        ->order(['SUM(albumstatistics2viewed.viewed)' => 'desc'])
                        ->limit('0,' . $last)
                        ->fetchAll(),
                    'user_id'
                )
            );

            $count = count($array_user_id);
        }

        (new \indexpopularityModel)
            ->where([[[['indexpopularity_id', '=', 1]], 'and']])
            ->edit([
                'exhibit' => json_encode($array_user_id)
            ]);

        return array_encode_return(1);
    }

    static function isDownlineOfBusinessUserOfCompany($user_id)
    {
        $count = (new \userModel())
            ->column(['COUNT(1)'])
            ->join([
                ['INNER JOIN', 'businessuser', 'ON businessuser.businessuser_id = user.businessuser_id AND businessuser.mode = \'company\'']
            ])
            ->where([[[['user.user_id', '=', $user_id]], 'and']])
            ->fetchColumn();

        return $count ? true : false;
    }

    function login($account, $password)
    {
        $account = trim($account);
        $password = trim($password);

        $m_user = Model('user')->column(['user_id', '`password`'])->where([[[['account', '=', $account], ['way', '=', 'none']], 'and']])->fetch();

        if ($m_user) {
            Model('user')->setSession($m_user['user_id']);

            Model('user')->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->edit(['lastloginip' => remote_ip(), 'lastlogintime' => inserttime()]);
        }

        return;
    }

    function logout()
    {
        Session::delete(['user', 'userlog']);
    }

    function menu()
    {
        $column = [
            'follow.count_from',
            'user.user_id',
            'user.name',
            'user.inserttime',
        ];
        $this->column($column);

        $join = [
            ['left join', 'follow', 'using(user_id)'],
        ];
        $this->join($join);

        $where = [
            [[['user.act', '=', 'open']], 'and'],
        ];
        $this->where($where);

        return $this;
    }

    function recommended($user_id, $searchtype, $searchkey = null)
    {
        //排除已關注的
        $m_followto = Model('followto')->column(['`to`'])->where([[[['user_id', '=', $user_id]], 'and']])->fetchAll();
        $a_followto_user_id = [(int)$user_id];//也排除自己
        foreach ($m_followto as $v0) {
            $a_followto_user_id[] = (int)$v0['to'];
        }

        $this->column([
            'user.user_id',
            'user.name',
            'user.description',
            'user.inserttime',
            'follow.count_from',
        ]);

        $join = [];
        $join[] = ['left join', 'follow', 'using(user_id)'];
        $where = [];
        $where[] = [[['user.user_id', 'not in', $a_followto_user_id]], 'and'];
        $order = [];
        switch ($searchtype) {
            case 'official':
                $order = [
                    'follow.count_from' => 'desc',
                    'user.name' => 'asc',
                ];
                break;

            case 'cellphone':
                $a_cellphone = $searchkey == null ? [0] : \Core\I18N::cellphone(explode(',', $searchkey));

                $where[] = [[['user.cellphone', 'in', $a_cellphone]], 'and'];

                $order = [
                    'user.name' => 'asc',
                ];
                break;

            case 'facebook':
                $join[] = ['inner join', 'user_facebook', 'using(user_id)'];

                $a_facebook_id = explode(',', $searchkey);
                if ($a_facebook_id == null) $a_facebook_id = [0];
                $where[] = [[['user_facebook.facebook_id', 'in', $a_facebook_id]], 'and'];

                $order = [
                    'user.name' => 'asc',
                ];
                break;
        }
        $this->join($join);
        $this->where($where);
        $this->order($order);

        return $this;
    }

    function register(array $param)
    {
        $result = 1;
        $message = null;
        $redirect = frontstageController::url('user', 'verify', query_string_parse());
        $data = null;

        //user
        $account = (isset($param['account']) && trim($param['account']) !== '') ? trim($param['account']) : null;
        $password = (isset($param['password']) && trim($param['password']) !== '') ? trim($param['password']) : null;
        $name = (isset($param['name']) && trim($param['name']) !== '') ? trim($param['name']) : null;
        $cellphone = (isset($param['cellphone']) && trim($param['cellphone']) !== '') ? \Core\I18N::cellphone($param['cellphone']) : null;
        $gender = (isset($param['gender']) && trim($param['gender']) !== '') ? trim($param['gender']) : 'none';
        $birthday = (isset($param['birthday']) && trim($param['birthday']) !== '') ? trim($param['birthday']) : '1900-01-01';
        $newsletter = isset($param['newsletter']) ? $param['newsletter'] : false;
        $way = (isset($param['way']) && trim($param['way']) !== '') ? trim($param['way']) : null;
        $way_id = (isset($param['way_id']) && trim($param['way_id']) !== '') ? trim($param['way_id']) : null;

        //userlog
        $coordinate = (isset($param['coordinate']) && trim($param['coordinate']) !== '') ? str_replace(' ', '', $param['coordinate']) : null;

        //smspassword
        $smspassword = (isset($param['smspassword']) && trim($param['smspassword']) !== '') ? trim($param['smspassword']) : null;

        switch ($way) {
            case 'none':
                if ($account === null || $password === null || $name === null || $cellphone === null || $smspassword === null) {
                    $result = 0;
                    $message = _('Param error.');
                    $redirect = null;
                    goto _return;
                }

                list ($result1, $message1) = array_decode_return((new \smspasswordModel)->verify($account, $cellphone, $smspassword));
                if ($result1 != 1) {
                    $result = $result1;
                    $message = $message1;
                    $redirect = null;
                    goto _return;
                }

                list ($result1, $message1) = array_decode_return((new \userModel)->check('account', $account));
                if ($result1 != 1) {
                    $result = $result1;
                    $message = $message1;
                    $redirect = null;
                    goto _return;
                }

                list ($result1, $message1) = array_decode_return((new \userModel)->check('cellphone', $cellphone));
                if ($result1 != 1) {
                    $result = $result1;
                    $message = $message1;
                    $redirect = null;
                    goto _return;
                }

                $add = [
                    'account' => $account,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'name' => $name,
                    'cellphone' => $cellphone,
                    'email' => $account,
                    'discuss' => 'open',
                    'act' => 'open',
                    'lastloginip' => remote_ip(),
                    'lastlogintime' => inserttime(),
                    'inserttime' => inserttime(),
                    'newsletter' => $newsletter,
                ];

                if ($coordinate !== null) {
                    $address_id_1st = 0;
                    $address_id_2nd = 0;

                    list ($result1, $message1, , $data1) = array_decode_return((new \addressModel)->getByCoordinate($coordinate));
                    if ($result1 == 1) list ($address_id_1st, $address_id_2nd) = $data1;

                    if (defined('USERLOG_ID')) {
                        list ($lat, $lng) = explode(',', $coordinate);

                        $m_userlog = new \userlogModel;

                        $sql = 'UPDATE ' . DB_PREFIX . $m_userlog->database . '.' . $m_userlog->table . ' SET
								latitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lat) . ',
								longitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lng) . ',
								coordinate_return = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($message1) . '
								WHERE userlog_id = ' . $m_userlog::$database_instance[$m_userlog->database]->quote(USERLOG_ID);

                        $m_userlog::$database_instance[$m_userlog->database]->exec($sql);
                    }

                    $add['address_id_1st'] = $address_id_1st;
                    $add['address_id_2nd'] = $address_id_2nd;
                }

                $user_id = (new \userModel)->add($add);

                if (!$user_id) {
                    $result = 0;
                    $message = _('[User] occur exception, please contact us.');
                    $redirect = null;
                    goto _return;
                }

                if (!Model('smspassword')->where([[[['user_account', '=', $account], ['user_cellphone', '=', $cellphone]], 'or']])->delete()) {
                    $result = 0;
                    $message = _('[SMSpassword] occur exception, please contact us.');
                    $redirect = null;
                    goto _return;
                }
                break;

            case 'facebook':
                $user_id = (new \user_facebookModel)
                    ->column(['user_id'])
                    ->where([[[['facebook_id', '=', $way_id]], 'and']])
                    ->fetchColumn();

                //有找到 user, 視為登入
                if (!empty($user_id)) {
                    list ($result0, $message0) = array_decode_return((new \userModel)->usable($user_id));
                    if ($result0 != 1) {
                        $result = $result0;
                        $message = $message0;
                        $redirect = null;
                        goto _return;
                    }

                    (new \userModel)
                        ->where([[[['user_id', '=', $user_id]], 'and']])
                        ->edit(['lastloginip' => remote_ip(), 'lastlogintime' => inserttime()]);

                    $token = (new \tokenModel)
                        ->column(['token'])
                        ->where([[[['user_id', '=', $user_id]], 'and']])
                        ->fetchColumn();

                    $redirect = empty(query_string_parse()['redirect']) ? frontstageController::url() : query_string_parse()['redirect'];
                    goto _relay0;
                }

                //2016-04-26 Lion: facebook 不一定會取得 account
                if ($name === null) {
                    $result = 0;
                    $message = _('Param error.');
                    $redirect = null;
                    goto _return;
                }

                $add = [
                    'password' => password_hash(uniqid(null, true), PASSWORD_DEFAULT),
                    'name' => $name,
                    'gender' => $gender,
                    'birthday' => $birthday,
                    'way' => 'facebook',
                    'act' => 'open',
                    'lastloginip' => remote_ip(),
                    'lastlogintime' => inserttime(),
                    'inserttime' => inserttime(),
                    'newsletter' => $newsletter,
                ];
                if ($account !== null) {
                    $add['account'] = $account;
                    $add['email'] = $account;
                }
                if ($coordinate !== null) {
                    $address_id_1st = 0;
                    $address_id_2nd = 0;

                    list ($result1, $message1, , $data1) = array_decode_return((new \addressModel)->getByCoordinate($coordinate));
                    if ($result1 == 1) list($address_id_1st, $address_id_2nd) = $data1;

                    if (defined('USERLOG_ID')) {
                        list ($lat, $lng) = explode(',', $coordinate);

                        $m_userlog = new \userlogModel;

                        $sql = 'UPDATE ' . DB_PREFIX . $m_userlog->database . '.' . $m_userlog->table . ' SET
							latitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lat) . ',
							longitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lng) . ',
							coordinate_return = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($message1) . '
							WHERE userlog_id = ' . $m_userlog::$database_instance[$m_userlog->database]->quote(USERLOG_ID);

                        $m_userlog::$database_instance[$m_userlog->database]->exec($sql);
                    }

                    $add['address_id_1st'] = $address_id_1st;
                    $add['address_id_2nd'] = $address_id_2nd;
                }

                $user_id = (new \userModel)->add($add);

                if (!$user_id) {
                    $result = 0;
                    $message = _('[User] occur exception, please contact us.');
                    $redirect = null;
                    goto _return;
                }

                Model('user_facebook')->add(['user_id' => $user_id, 'facebook_id' => $way_id]);

                //取用 fb 大頭貼
                if (SDK('Mobile_Detect')->isMobile()) {

                } else {
                    $picture = 'http://graph.facebook.com/' . $way_id . '/picture?type=large';
                    if (substr(get_headers($picture)[0], 9, 3) != '404') {
                        Core::set_userpicture($user_id, file_get_contents($picture));
                    }
                }
                break;

            default:
                $result = 0;
                $message = _('Unknown case of way.');
                $redirect = null;
                goto _return;
                break;
        }

        $add = null;
        foreach (['apple', 'google', 'web'] as $v0) {
            $add[] = [
                'user_id' => $user_id,
                'platform' => $v0,
            ];
        }
        if ($add) Model('userpoint')->add($add);

        Model('userstatistics')->add(['user_id' => $user_id]);

        Model('follow')->add(['user_id' => $user_id]);

        Model('token')->add([
            'user_id' => $user_id,
            'token' => $token = encrypt(['user_id' => $user_id, 'time' => time()])
        ]);

        Model('topic')->build('user', $user_id);
        Model('topic')->build('follow', $user_id);

        subscriptionModel::build($user_id, 'user', $user_id);

        (new weightqueueModel)->add([
            'user_id' => $user_id,
            'state' => 'pretreat',
        ]);

        _relay0:
        $data = ['id' => $user_id, 'token' => $token];

        _return:
        return array_encode_return($result, $message, $redirect, $data);
    }

    function register_v2(array $param)
    {
        $data = null;

        switch ($param['way']) {
            case 'none':
                $act = isset($param['act']) ? $param['act'] : \Schema\user::$act_Default;
                $account = trim($param['account']);
                $birthday = (isset($param['birthday']) && trim($param['birthday']) !== '') ? trim($param['birthday']) : \Schema\user::$birthday_Default;
                $businessuser_id = isset($param['businessuser_id']) ? $param['businessuser_id'] : null;
                $gender = isset($param['gender']) ? $param['gender'] : \Schema\user::$gender_Default;
                $password = $param['password'];
                $name = trim($param['name']);
                $cellphone = \Core\I18N::cellphone($param['cellphone']);
                $newsletter = isset($param['newsletter']) ? $param['newsletter'] : false;

                $add = [
                    'act' => $act,
                    'account' => $account,
                    'birthday' => $birthday,
                    'businessuser_id' => $businessuser_id,
                    'gender' => $gender,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'name' => $name,
                    'cellphone' => $cellphone,
                    'email' => $account,
                    'lastloginip' => remote_ip(),
                    'lastlogintime' => inserttime(),
                    'inserttime' => inserttime(),
                    'newsletter' => $newsletter,
                ];

                if (isset($param['coordinate']) && trim($param['coordinate']) !== '') {
                    $coordinate = str_replace(' ', '', $param['coordinate']);

                    $address_id_1st = 0;
                    $address_id_2nd = 0;

                    list ($result1, $message1, , $data1) = array_decode_return((new addressModel)->getByCoordinate($coordinate));
                    if ($result1 == 1) list ($address_id_1st, $address_id_2nd) = $data1;

                    if (defined('USERLOG_ID')) {
                        list ($lat, $lng) = explode(',', $coordinate);

                        $m_userlog = new userlogModel;

                        $sql = 'UPDATE ' . DB_PREFIX . $m_userlog->database . '.' . $m_userlog->table . ' SET
								latitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lat) . ',
							    longitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lng) . ',
								coordinate_return = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($message1) . '
								WHERE userlog_id = ' . $m_userlog::$database_instance[$m_userlog->database]->quote(USERLOG_ID);

                        $m_userlog::$database_instance[$m_userlog->database]->exec($sql);
                    }

                    $add['address_id_1st'] = $address_id_1st;
                    $add['address_id_2nd'] = $address_id_2nd;
                }

                $user_id = (new userModel)->add($add);

                (new smspasswordModel)->where([[[['user_account', '=', $account], ['user_cellphone', '=', $cellphone]], 'or']])->delete();

                if ($businessuser_id !== null) {
                    \businessuser\Model::setUserGrade($businessuser_id, $user_id, 'By BusinessUser.');
                }
                break;

            case 'facebook':
                $act = isset($param['act']) ? $param['act'] : \Schema\user::$act_Default;
                $birthday = (isset($param['birthday']) && trim($param['birthday']) !== '') ? $param['birthday'] : \Schema\user::$birthday_Default;
                $businessuser_id = isset($param['businessuser_id']) ? $param['businessuser_id'] : null;
                $gender = (isset($param['gender']) && trim($param['gender']) !== '') ? $param['gender'] : \Schema\user::$gender_Default;;
                $name = trim($param['name']);
                $newsletter = isset($param['newsletter']) ? $param['newsletter'] : false;
                $way_id = $param['way_id'];

                $add = [
                    'businessuser_id' => $businessuser_id,
                    'password' => password_hash(uniqid(null, true), PASSWORD_DEFAULT),
                    'name' => $name,
                    'gender' => $gender,
                    'birthday' => $birthday,
                    'way' => 'facebook',
                    'act' => $act,
                    'lastloginip' => remote_ip(),
                    'lastlogintime' => inserttime(),
                    'inserttime' => inserttime(),
                    'newsletter' => $newsletter,
                ];

                if (isset($param['account']) && trim($param['account']) !== '') {
                    $account = trim($param['account']);

                    $add['account'] = $account;
                    $add['email'] = $account;
                }

                if (isset($param['coordinate']) && trim($param['coordinate']) !== '') {
                    $coordinate = str_replace(' ', '', $param['coordinate']);

                    $address_id_1st = 0;
                    $address_id_2nd = 0;

                    list ($result1, $message1, , $data1) = array_decode_return((new addressModel)->getByCoordinate($coordinate));
                    if ($result1 == 1) list ($address_id_1st, $address_id_2nd) = $data1;

                    if (defined('USERLOG_ID')) {
                        list ($lat, $lng) = explode(',', $coordinate);

                        $m_userlog = (new userlogModel);

                        $sql = 'UPDATE ' . DB_PREFIX . $m_userlog->database . '.' . $m_userlog->table . ' SET
							latitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lat) . ',
							longitude = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($lng) . ',
							coordinate_return = ' . $m_userlog::$database_instance[$m_userlog->database]->quote($message1) . '
							WHERE userlog_id = ' . $m_userlog::$database_instance[$m_userlog->database]->quote(USERLOG_ID);

                        $m_userlog::$database_instance[$m_userlog->database]->exec($sql);
                    }

                    $add['address_id_1st'] = $address_id_1st;
                    $add['address_id_2nd'] = $address_id_2nd;
                }

                $user_id = (new userModel)->add($add);

                (new user_facebookModel)->add(['user_id' => $user_id, 'facebook_id' => $way_id]);

                if ($businessuser_id !== null) {
                    \businessuser\Model::setUserGrade($businessuser_id, $user_id, 'By BusinessUser.');
                }

                //取用 fb 大頭貼
                if (SDK('Mobile_Detect')->isMobile()) {

                } else {
                    $picture = 'http://graph.facebook.com/' . $way_id . '/picture?type=large';

                    if (substr(get_headers($picture)[0], 9, 3) != '404') {
                        Core::set_userpicture($user_id, file_get_contents($picture));
                    }
                }
                break;
        }

        $add = null;

        foreach (array_diff(\Schema\userpoint::$platform, ['none']) as $v0) {
            $add[] = [
                'platform' => $v0,
                'user_id' => $user_id,
            ];
        }

        if ($add) (new userpointModel)->add($add);

        (new userstatisticsModel)->add(['user_id' => $user_id]);

        (new followModel)->add(['user_id' => $user_id]);

        (new tokenModel)->add([
            'user_id' => $user_id,
            'token' => $token = encrypt(['user_id' => $user_id, 'time' => time()])
        ]);

        (new topicModel)->build('user', $user_id);
        (new topicModel)->build('follow', $user_id);

        subscriptionModel::build($user_id, 'user', $user_id);

        (new weightqueueModel)->add([
            'user_id' => $user_id,
            'state' => 'pretreat',
        ]);

        //設置預設大頭貼
        $file_timestamp = mkdir_p_v2(PATH_STORAGE . SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR) . 'timestamp.txt';
        $a_timestamp = file_exists($file_timestamp) ? json_decode(file_get_contents($file_timestamp), true) : [];
        $a_timestamp = array_replace($a_timestamp, ['picture.jpg' => time()]);
        file_put_contents($file_timestamp, json_encode($a_timestamp));

        (new userModel)->setSession($user_id);

        return [
            'id' => $user_id,
            'token' => $token,
        ];
    }

    static function setPicture($user_id, $picture = null)
    {
        $return = false;

        $ex = \userModel::getPicture($user_id);
        if ($picture != null) file_put_contents(PATH_STORAGE . $ex, $picture);

        //覆寫時間戳
        $file_timestamp = self::getStoragePath($user_id) . 'timestamp.txt';
        $a_timestamp = file_exists($file_timestamp) ? json_decode(file_get_contents($file_timestamp), true) : [];
        $a_timestamp = array_replace($a_timestamp, ['picture.jpg' => time()]);

        if (file_put_contents($file_timestamp, json_encode($a_timestamp)) !== false) {
            \Extension\aws\S3::upload($file_timestamp);
        }

        $path = PATH_STORAGE . \userModel::getPicture($user_id);

        if (rename(PATH_STORAGE . $ex, $path)) {
            $return = true;

            if (exif_imagetype($path) !== IMAGETYPE_JPEG) {
                (new \Core\Image)
                    ->set($path)
                    ->setType('jpg')
                    ->save(null, true, true);
            }

            \Extension\aws\S3::upload($path);
        }

        //
        foreach (glob(self::getStoragePath($user_id) . 'picture*.jpg') as $v_0) {
            if (basename($v_0) == basename(\userModel::getPicture($user_id))) {
                continue;
            }

            if (unlink($v_0)) {
                \Extension\aws\S3::deleteObject($v_0);
            }
        }

        return $return;
    }

    function setSession($user_id)
    {
        $m_user = (new userModel)
            ->column([
                'account',
                'birthday',
                'cellphone',
                'creative',
                'creative_name',
                'description',
                'email',
                'gender',
                'inserttime',
                'level',
                'name',
                'tutorial_viewed',
                'setting_viewed',
                'sociallink',
                'user_id',
                'way',
            ])
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->fetch();

        Session::set('user', $m_user);

        return;
    }

    static function setUserCover($user_id)
    {
        foreach (glob(self::getStoragePath($user_id) . 'cover*.jpg') as $v_0) {
            if (unlink($v_0)) {
                \Extension\aws\S3::deleteObject($v_0);
            }
        }

        //
        $path = self::getStoragePath($user_id) . 'cover.' . pathinfo($_FILES[self::$key]['name'], PATHINFO_EXTENSION);

        if (move_uploaded_file($_FILES[self::$key]['tmp_name'], $path)) {
            $Image = new \Core\Image();

            $Image->set($path);

            if ($Image->getType() != IMAGETYPE_JPEG) {
                $path = $Image->setType('jpg')->save(null, true, true);
            }

            //覆寫時間戳
            $file_timestamp = self::getStoragePath($user_id) . 'timestamp.txt';
            $a_timestamp = file_exists($file_timestamp) ? json_decode(file_get_contents($file_timestamp), true) : [];
            $a_timestamp = array_replace($a_timestamp, ['cover.jpg' => time()]);

            if (file_put_contents($file_timestamp, json_encode($a_timestamp)) !== false) {
                \Extension\aws\S3::upload($file_timestamp);
            }

            //
            if (rename($path, self::getUserCoverPath($user_id))) {
                \Extension\aws\S3::upload(self::getUserCoverPath($user_id));
            }
        }
    }

    static function updateCellphone($user_id, $cellphone)
    {
        $cellphone = \Core\I18N::cellphone($cellphone);

        (new \smspasswordModel)
            ->where([[[['user_id', '=', $user_id], ['user_cellphone', '=', $cellphone]], 'or']])
            ->delete();

        (new \userModel)
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->edit([
                'cellphone' => $cellphone
            ]);

        (new \userModel)->setSession($user_id);
    }

    function updatePassword($user_id, $old_password, $new_password)
    {
        $result = 1;
        $message = _('Your passwords modify completed.') . '<br>' . _('You may use your new password to log in next time.');
        $redirect = frontstageController::url('user', 'login');

        $user_id = empty($user_id) ? null : $user_id;
        $old_password = (trim($old_password) === '') ? null : trim($old_password);
        $new_password = (trim($new_password) === '') ? null : trim($new_password);

        if ($user_id === null || $old_password === null || $new_password === null) {
            $result = 0;
            $message = _('Param error.');
            $redirect = null;
            goto _return;
        } elseif ($old_password == $new_password) {
            $result = 0;
            $message = _('The old and new password must be different.');
            $redirect = null;
            goto _return;
        }

        list($result0, $message0, , $data0) = array_decode_return(Model('user')->usable($user_id));
        if ($result0 != 1) {
            $result = $result0;
            $message = $message0;
            $redirect = null;
            goto _return;
        }

        if (!password_verify($old_password, $data0['user']['password'])) {
            $result = 0;
            $message = _('User\'s password is incorrect.');
            $redirect = null;
            goto _return;
        }

        if (Model('user')->where([[[['user_id', '=', $user_id]], 'and']])->edit(['password' => password_hash($new_password, PASSWORD_DEFAULT)])) {
            //完成後寄信通知
            $body = implode('<br>', [
                _('This is a notification from pinpinbox system that you have changed your password on') . '[' . date("Y-m-d H:i") . ']' . _('You may use your new password to log in next time.'),
            ]);
            email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $data0['user']['email'], _('pinpinbox- password change notification'), $body);

            Model('user')->logout();
        }

        _return:
        return array_encode_return($result, $message);
    }

    function updateUser($user_id, array $param)
    {
        $update = [];

        if (isset($param['birthday'])) {
            $update['birthday'] = $param['birthday'];
        }

        if (isset($param['creative_name'])) {
            $update['creative_name'] = $param['creative_name'];
        }

        if (isset($param['email'])) {
            $update['email'] = $param['email'];
        }

        if (isset($param['gender'])) {
            $update['gender'] = $param['gender'];
        }

        if (isset($param['name'])) {
            $update['name'] = $param['name'];
        }

        if (isset($param['newsletter'])) {
            $update['newsletter'] = $param['newsletter'];
        }

        if (isset($param['sociallink'])) {
            $update['sociallink'] = is_json($param['sociallink']) ? $param['sociallink'] : json_encode($param['sociallink']);
        }

        (new userModel)
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->edit($update);
    }

    function usable($user_id)
    {
        $result = 1;
        $message = null;
        $data = null;

        if (empty($user_id)) {
            $result = 0;
            $message = _('User ID is empty.');
            $data = null;
            goto _return;
        }

        $m_user = Model('user')->column(['`password`', 'email', 'act'])->where([[[['user_id', '=', $user_id]], 'and']])->fetch();
        if (empty($m_user)) {
            $result = 0;
            $message = _('User does not exist.');
            $data = null;
            goto _return;
        }

        if ($m_user['act'] != 'open') {
            $result = 0;
            $message = _('User is not open.');
            $data = null;
            goto _return;
        }

        $data = [
            'user' => [
                'password' => $m_user['password'],
                'email' => $m_user['email'],
            ],
        ];

        _return:
        return array_encode_return($result, $message, null, $data);
    }

    //2017-07-06 Lion: 預計取代 userModel::usable
    function usable_v2($user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        if (empty($user_id)) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "user_id" is required.';
            goto _return;
        }

        $m_user = (new \userModel)->column(['act'])->where([[[['user_id', '=', $user_id]], 'and']])->fetch();

        if (empty($m_user)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('"用戶"資料不存在。');
            goto _return;
        } else {
            if ($m_user['act'] == 'close') {
                $result = \Lib\Result::USER_ERROR;
                $message = _('用戶帳號已關閉。');
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    function cerateNewsLetterExcel()
    {
        $column = [
            'user_id',
            'name',
            'cellphone',
            'email',
            'gender',
            'lastlogintime',
            'way',
            'act',
            'modifytime',
        ];

        $where = [[[['act', '=', 'open']], 'and']];

        $m_user = (new userModel())->column($column)->where($where)->fetchAll();

        $time = time();

        $array_0 = [
            ['user_id', 'name', 'cellphone', 'email', 'gender', 'lastlogintime', 'way', 'act', 'modifytime',],
        ];

        foreach ($m_user as $v_0) {
            //內容
            $array_0[] = array(
                $v_0['user_id'],
                $v_0['name'],
                $v_0['cellphone'],
                $v_0['email'],
                $v_0['gender'],
                $v_0['lastlogintime'],
                $v_0['way'],
                $v_0['act'],
                $v_0['modifytime'],
            );
        }

        $excel = [$array_0];

        $sn_0 = 0;

        $PHPExcel = new \PHPExcel;

        $PHPExcel->getProperties()->setCreator('pinpinbox');

        foreach ($excel as $k_0 => $v_0) {
            if ($k_0 > 0) {
                $PHPExcel->createSheet($k_0);
            }

            $PHPExcel->getActiveSheet()->getStyle('A1:I1')
                ->applyFromArray([
                    'alignment' => [
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'wrap' => true//實現 cell 內跳行
                    ]
                ]);

            //設定要操作的Sheet
            $PHPExcel->setActiveSheetIndex($k_0);

            $PHPExcel->getActiveSheet()->setTitle('訂閱電子報會員清單');

            //儲存格內容
            foreach ($v_0 as $k_1 => $v_1) {
                foreach ($v_1 as $k_2 => $v_2) {
                    $cell_coordinate = toAlpha($k_2) . (int)($k_1 + 1);

                    if (toAlpha($k_2) === 'K' && (int)($k_1 + 1) >= 3) {
                        $PHPExcel
                            ->getActiveSheet()
                            ->getcell($cell_coordinate)
                            ->setValueExplicit($v_2, PHPExcel_Cell_DataType::TYPE_STRING);
                    } else {
                        $PHPExcel->getActiveSheet()->setCellValue($cell_coordinate, $v_2);
                    }
                }
            }

            //自動欄寬
            for ($i = 0; $i <= 12; ++$i) {
                $PHPExcel->getActiveSheet()->getColumnDimension(toAlpha($i))->setAutoSize(true);
            }
        };

        $PHPExcel->setActiveSheetIndex(0);

        $filename = 'User NewsLetter report.xlsx';
        $file = PATH_ROOT . $filename;

        \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007')->save($file);

        return $file;
    }

}