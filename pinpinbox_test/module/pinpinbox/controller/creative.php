<?php

class creativeController extends frontstageController
{
    function __construct()
    {
    }

    function apply()
    {
        $user = parent::user_get();

        if (is_ajax()) {

            if (empty($user)) json_encode_return(0, _('Please login first.'), parent::url('user', 'login', array('redirect' => parent::url('creative', 'apply'))), 'Modal');

            if (!empty(Model('user')->where(array(array(array(array('user_id', '=', $user['user_id']), array('creative', '=', true)), 'and')))->fetch())) json_encode_return(0, _('Application for the account completed.'));

            //驗證拉桿
            $captcha = (isset($_POST['captcha']) && !empty($_POST['captcha'])) ? $_POST['captcha'] : null;
            if (Session::get('captcha') != $captcha) {
                json_encode_return(0, _('Slider validate fail!'));
            }

            //申請身分
            $apply = empty($_POST['apply']) ? null : $_POST['apply'];
            $param = array();


            //國別不同，信件內容不同
            $email_country = '';
            $_return_msg = [];

            switch ($apply) {
                case 'personal':
                    $personal_email = !empty($_POST['personal_email']) ? $_POST['personal_email'] : null;
                    $personal_country = !empty($_POST['personal_country']) ? $_POST['personal_country'] : null;
                    $personal_zipcode = !empty($_POST['personal_zipcode']) ? $_POST['personal_zipcode'] : null;
                    $personal_address = !empty($_POST['personal_address']) ? $_POST['personal_address'] : null;
                    $personal_career = !empty($_POST['personal_career']) ? $_POST['personal_career'] : null;
                    $personal_idcardnumber = !empty($_POST['personal_idcardnumber']) ? $_POST['personal_idcardnumber'] : null;
                    $company_vatnumber = !empty($_POST['company_vatnumber']) ? $_POST['company_vatnumber'] : null;
                    $email_country = $personal_country;

                    if (empty($personal_email)) $_return_msg[] = _('Email');
                    if (empty($personal_country)) $_return_msg[] = _('國籍');
                    if (empty($personal_address)) $_return_msg[] = _('地址');
                    if (empty($personal_zipcode)) $_return_msg[] = _('郵遞區號');
                    if (empty($personal_career)) $_return_msg[] = _('職業');
                    if ($personal_country == 'TW' && empty($personal_idcardnumber)) $_return_msg[] = _('身分證字號');

                    if (count($_return_msg) > 0) json_encode_return(0, _('您有以下的必填欄位尚未填寫：<br>-') . implode('<br>-', $_return_msg));

                    $param['personal_email'] = $email_confirm = $personal_email;
                    $param['personal_country'] = $personal_country;
                    $param['personal_zipcode'] = $personal_zipcode;
                    $param['personal_address'] = $personal_address;
                    $param['personal_career'] = $personal_career;
                    //此資訊於 7/18 棄用,暫填入unuse
                    $param['personal_website'] = 'unuse';
                    $param['personal_idcardnumber'] = $personal_idcardnumber;
                    $param['company_vatnumber'] = $company_vatnumber;
                    break;

                case 'company':
                    $company_email = !empty($_POST['company_email']) ? $_POST['company_email'] : null;
                    $company_country = !empty($_POST['company_country']) ? $_POST['company_country'] : null;
                    $company_name_zh_TW = !empty($_POST['company_name_zh_TW']) ? $_POST['company_name_zh_TW'] : null;
                    $company_name_en_US = !empty($_POST['company_name_en_US']) ? $_POST['company_name_en_US'] : null;
                    $company_vatnumber = !empty($_POST['company_vatnumber']) ? $_POST['company_vatnumber'] : null;
                    $company_telephone = !empty($_POST['company_telephone']) ? $_POST['company_telephone'] : null;
                    $company_zipcode = !empty($_POST['company_zipcode']) ? $_POST['company_zipcode'] : null;
                    $company_address = !empty($_POST['company_address']) ? $_POST['company_address'] : null;
                    $company_website = !empty($_POST['company_website']) ? $_POST['company_website'] : null;
                    $email_country = $company_country;

                    if (empty($company_email)) $_return_msg[] = _('Email');
                    if (empty($company_country)) $_return_msg[] = _('國籍');
                    if (empty($company_address)) $_return_msg[] = _('地址');
                    if (empty($company_zipcode)) $_return_msg[] = _('郵遞區號');
                    if ($company_country == 'TW' && empty($company_vatnumber)) $_return_msg[] = _('統一編號');

                    if (count($_return_msg) > 0) json_encode_return(0, _('您有以下的必填欄位尚未填寫：<br>-') . implode('<br>-', $_return_msg));

                    $param['company_email'] = $email_confirm = $company_email;
                    $param['company_country'] = $company_country;
                    $param['company_name_zh_TW'] = $company_name_zh_TW;
                    $param['company_name_en_US'] = $company_name_en_US;
                    $param['company_vatnumber'] = $company_vatnumber;
                    $param['company_telephone'] = $company_telephone;
                    $param['company_zipcode'] = $company_zipcode;
                    $param['company_address'] = $company_address;
                    $param['company_website'] = $company_website;
                    break;

                default:
                    json_encode_return(0, _('Unknown case, please try again.'));
                    break;
            }

            //#1327 同步修改使用者聯絡信箱
            (new userModel)->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['email' => $email_confirm]);

            //匯款方式
            $remittance = empty($_POST['remittance']) ? null : $_POST['remittance'];
            if ($remittance == null) json_encode_return(0, _('Please select remittance way.'));

            switch ($remittance) {
                case 'paypal':
                    $paypal_account = !empty($_POST['paypal_account']) ? $_POST['paypal_account'] : null;
                    $paypal_currency = !empty($_POST['paypal_currency']) ? $_POST['paypal_currency'] : null;

                    if ($paypal_account == null) $_return_msg[] = _('帳號');
                    if ($paypal_currency == null) $_return_msg[] = _('幣別');
                    if (count($_return_msg) > 0) json_encode_return(0, _('您有以下的必填欄位尚未填寫：<br>-') . implode('<br>-', $_return_msg));

                    $remittance_info = json_encode(array(
                        'paypal_account' => $paypal_account,
                        'paypal_currency' => $paypal_currency
                    ));
                    break;

                case 'other':
                    $name = !empty($_POST['name']) ? $_POST['name'] : null;
                    $bank = !empty($_POST['bank']) ? $_POST['bank'] : null;
                    $branch = !empty($_POST['branch']) ? $_POST['branch'] : null;
                    $account = !empty($_POST['account']) ? $_POST['account'] : null;
                    $remark = !empty($_POST['remark']) ? $_POST['remark'] : null;

                    if ($name == null) $_return_msg[] = _('戶名');
                    if ($bank == null) $_return_msg[] = _('銀行名稱');
                    if ($branch == null) $_return_msg[] = _('分行');
                    if ($account == null) $_return_msg[] = _('帳號');
                    if (count($_return_msg) > 0) json_encode_return(0, _('您有以下的必填欄位尚未填寫：<br>-') . implode('<br>-', $_return_msg));

                    if ($name === null || $bank === null || $branch === null || $account === null) {
                        json_encode_return(0, _('Please enter your bank info.'), null, 'Modal');
                    }

                    $remittance_info = json_encode(array(
                        'name' => $name,
                        'bank' => $bank,
                        'branch' => $branch,
                        'account' => $account,
                        'remark' => $remark
                    ));
                    break;

                default:
                    json_encode_return(0, _('Unknown case, please try again.'));
                    break;
            }

            $param['applyfor'] = $apply;
            $param['user_id'] = $user['user_id'];
            $param['remittance'] = $remittance;
            $param['remittance_info'] = $remittance_info;
            $param['inserttime'] = inserttime();

            (new \creativeModel)->replace($param);

            $sign = encrypt(['user_id' => $user['user_id'], 'user_account' => $email_confirm, 'user_apply' => $apply]);
            $u_param = ['sign' => $sign, 'creator' => $user['user_id'], 'type' => $apply, 'email' => $email_confirm];
            if (!empty($_GET['redirect'])) $u_param = array_merge($u_param, ['redirect' => 'grade']);
            $url = parent::url('creative', 'check', $u_param);

            switch ($email_country) {
                case 'TW':
                    $tmp0 = array(
                        _('Dear pinpinbox creator：'),
                        _('Thanks for applying to join the pinpinbox talent recruitment program，pinpinbox provides you enough space to enhance your talent and enjoy the creative process！'),
                        _('Please complete the security check to verify your identity:'),
                        '<a href="' . $url . '">' . _('Validate') . '</a><br>',
                        _('Click this to verify or please copy the website to the address bar and press Enter:'),
                        $url . '<br>',
                        _('------------ If you didn’t apply the service, please ignore. Thank you! ------------'),
                        _('當您得到贊助收益，結算後我們將會匯款給您，所以請記得在結算收益之前回傳此附件資料，才可以方便您我作業，也維護您的權益喔！'),
                        '<span style="color:red;">' . _('附件為pinpinbox創作人報酬單，請填寫完畢，存成PDF檔後寄至service@pinpinbox.com！') . '</span><br>',
                        _('詳細說明及注意事項都在附件內，有問題請寄信詢問pinpinbox。'),
                        _('(This is a system-generated message. Please don’t reply directly to this message.)'),
                    );
                    $email_title = _('pinpinbox 專案註冊成功，為作品設定贊助價格！');
                    $email_attachment = array(
                        array(
                            'tmp_name' => PATH_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/document/pay.docx',
                            'name' => 'pinpinbox創作人報酬單.docx',
                        ),
                    );
                    break;

                default:
                    $tmp0 = array(
                        _('Dear pinpinbox creator：'),
                        _('Thanks for applying to join the pinpinbox talent recruitment program，pinpinbox provides you enough space to enhance your talent and enjoy the creative process！'),
                        _('Please complete the security check to verify your identity:'),
                        '<a href="' . $url . '">' . _('Validate') . '</a><br>',
                        _('Click this to verify or please copy the website to the address bar and press Enter:'),
                        $url . '<br>',
                        _('------------ If you didn’t apply the service, please ignore. Thank you! ------------'),
                        _('pinpinbox lets you Pin and share wonderful moments.'),
                        _('(This is a system-generated message. Please don’t reply directly to this message.)'),
                    );
                    $email_title = _('pinpinbox Recruitment');
                    $email_attachment = null;
                    break;
            }

            $body = implode('<br>', $tmp0);

            email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $email_confirm, $email_title, $body, $email_attachment);

            $redirect = (!empty($_GET['redirect'])) ? urldecode($_GET['redirect']) : parent::url('index');

            json_encode_return(1, _('Sent off. Please check your email and verify your account.'), $redirect);
        }

        if (empty($user)) redirect(parent::url('user', 'login', ['redirect' => parent::url('creative', 'apply')]), _('Please login first.'));

        parent::$data['user'] = $user;

        //career
        $a_career = array();
        $column = ['career_id', 'name',];
        $where = [[[['act', '=', 'open']], 'and']];
        $order = ['sequence' => 'asc'];
        $m_career = Model('career')->column($column)->where($where)->order($order)->fetchAll();
        foreach ($m_career as $v0) {
            $a_career[] = array(
                'value' => $v0['career_id'],
                'text' => $v0['name'],
            );
        }
        parent::$data['career'] = $a_career;

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Recruitment'),
            array(_('Author’s'), _('Recruitment'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$data['max'] = Session::set('captcha', rand(1, 100));
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui-1.10.4.custom.min.css'), 'href');

        parent::$html->set_js(static_file('js/jquery-ui-1.10.4.custom.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.ui.touch-punch.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.tw-city-selector.min.js'), 'src');
        parent::$html->set_jquery_validation();
        parent::$html->jbox();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function buyalbum()
    {
        if (is_ajax()) {
            $result = 1;
            $message = _('Purchase success!');
            $redirect = null;
            $data = null;

            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            if ($album_id === null) {
                $result = 0;
                $message = _('Abnormal process, please try again.');
                goto _return;
            }

            $user = parent::user_get();
            if (empty($user)) {
                $result = 2;
                $message = _('Please login first.');

                $redirect_param = [
                    'album_id' => $album_id,
                    'autobuy' => true,
                    'categoryarea_id' => \albumModel::getCategoryAreaId($album_id),
                ];
                if (isset($_GET['agent'])) $redirect_param['agent'] = $_GET['agent'];

                $redirect = parent::url('user', 'login', ['redirect' => parent::url('album', 'content', $redirect_param)]);
                goto _return;
            }

            $buy = empty($_POST['buy']) ? null : $_POST['buy'];
            if ($buy) {
                list($result1, $message1) = array_decode_return(Model('album')->buyable($album_id, $user['user_id']));
                switch ($result1) {
                    case 0:
                        $result = $result1;
                        $message = $message1;
                        goto _return;
                        break;

                    case 2:
                        $result = $result1;
                        $message = $message1;
                        $redirect = parent::url('album', 'content', ['album_id' => $album_id, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]);
                        goto _return;
                        break;
                }

                //頻繁操作驗證
                $m_download = Model('download')->where([[[['user_id', '=', $user['user_id']]], 'and']])->order(['inserttime' => 'desc'])->limit('9,1')->fetch();

                if (strtotime('+1 minute', strtotime($m_download['inserttime'])) >= time()) {
                    $result = 0;
                    $message = _('This operation cannot redo within 1 minutes.');
                    goto _return;
                } else {
                    //進行交易(收藏作品)
                    (new Model)->beginTransaction();

                    list($result1, $message1, $redirect1, $data1) = array_decode_return(Core::exchange($user['user_id'], 'web', 'album', $album_id));
                    if (!$result1) {
                        (new Model)->rollBack();

                        $result = 0;
                        $message = $message1;
                        $data = $data1;

                        //P點不足時內容 $result 與 $message 再造
                        if (!$data1['balance']) {
                            $result = 3;
                            $message = '<div class="content"><p>P點不足</p><p class="red">(是否離開作品前往儲值P點?)</p></div>';
                        }
                        goto _return;
                    }

                    /**
                     *  0704 - 執行任務-收藏相本
                     */
                    $m_album_point = Model('album')->column(['point'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();
                    $data = [];
                    $task_for = ($m_album_point == 0) ? 'collect_free_album' : 'collect_pay_album';
                    $user_id = $user['user_id'];
                    $data = model('task')->doTask($task_for, $user_id, 'web', ['type' => 'album', 'type_id' => $album_id]);
                    $data['album_count'] = Model('albumstatistics')->column(['`count`'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();;

                    (new Model)->commit();
                }
            } else {
                $result = 4;//詢問是否收藏
                $message = null;
                $data = $this->content_property($album_id);
            }

            _return:
            json_encode_return($result, $message, $redirect, $data);
        }
        die;
    }

    function check()
    {
        if (empty($_GET['sign']) || empty($_GET['creator']) || empty($_GET['email']) || empty($_GET['type'])) redirect(parent::url(), _('Abnormal process, please try again.'));

        //驗證處理後的字串及取得的 sign
        if (encrypt(['user_id' => $_GET['creator'], 'user_account' => urldecode($_GET['email']), 'user_apply' => $_GET['type']]) != $_GET['sign']) redirect(parent::url(), _('Validate fail.'));

        if (Model('user')->where([[[['user_id', '=', $_GET['creator']]], 'and']])->edit(['creative' => true])) {
            $user_id = Model('user')->column(['user_id'])->where([[[['user_id', '=', $_GET['creator']]], 'and']])->fetchColumn();

            //可用基礎版型編輯作品
            $add = [
                'user_id' => $user_id,
                'exchange_id' => 0,
                'template_id' => 1,
                'inserttime' => inserttime(),
            ];
            Model('templatequeue')->add($add);

            Model('user')->setSession($user_id);

            $redirect = (!empty($_GET['redirect']) && $_GET['redirect'] == 'grade') ? parent::url('user', 'grade') : parent::url('user', 'sale_album');

            redirect($redirect, _('Validate success.'));
        }
    }

    function content() {
		if (empty($_GET['user_id'])) redirect(parent::url('creative', 'index'), _('Abnormal process, please try again.'));

		$rank_id =  ( isset($_GET['rank_id']) && in_array($_GET['rank_id'], [1,2,3,4,5]) ) ? $_GET['rank_id'] : 1 ;
		parent::$data['rank_id'] = $rank_id;

		// 創作人資料
        $m_user = (new userModel)
			->column([
				'follow.count_from + userstatistics.followfrom_manual count_from',
				'user.*',
				'userstatistics.besponsored + userstatistics.besponsored_manual besponsored',
			])
			->join([
				['LEFT JOIN', 'follow', 'on follow.user_id = user.user_id'],
				['INNER JOIN', 'userstatistics', 'on userstatistics.user_id = user.user_id'],
			])
			->where([[[['user.user_id', '=', $_GET['user_id']]], 'and']])
			->fetch();

        if (empty($m_user)) {
			redirect(parent::url('creative', 'index'), _('User does not exist.'));
		} elseif ($m_user['act'] != 'open') {
			redirect(parent::url('creative', 'index'), _('User is not open.'));
		}

		// 登入用戶資料
        $user = parent::user_get();
        parent::$data['user'] = $user;

		 // 未登入 : viewer / 登入: user / 創作人 : creator
		$pageState = null;
		if (empty($user)) {
			$pageState = 'viewer';
		} else {
			$pageState = ($m_user['user_id'] === $user['user_id']) ? 'creator' : 'user';
		}

        // follow
		$follow = false;
		if (!empty($user)) {
			$follow = Core::get_follow($user['user_id'], $m_user['user_id']) ? 1 : 0;
		}

		$a_sociallink = [
			'blog' => '',
			'facebook' => '',
			'google' => '',
			'instagram' => '',
			'line' => '',
			'linkedin' => '',
			'pinterest' => '',
			'twitter' => '',
			'youtube' => '',
			'web' => '',
		];

		//專區連結
		$creative_url = ((strpos(Core::get_creative_url($m_user['user_id']), '?')) == null)
			? Core::get_creative_url($m_user['user_id']) . '?rank_id=' . $rank_id . '&'
			: Core::get_creative_url($m_user['user_id']) . '&rank_id=' . $rank_id . '&';

		/**
		 *    Tablists : tab01(我的作品)   / tab02(收藏/贊助) / tab03(群組作品) / tab04(關於我) / tab05(留言板)
		 */
		$act = true;
		for ($i=1;$i<6;$i++) {
			${'classTab0'.$i} = ${'classTabContent0'.$i} = null ;
		}
		if ((isset($_GET['appview']) && ($_GET['appview']) == true) || isset($_GET['setting_viewed'])) {
			$classTab01 = $classTabContent01 = 'active';
		} else {
			${'classTab0'.$rank_id} = ${'classTabContent0'.$rank_id} = 'active' ;
		}

		$titleChange = ($pageState == 'creator') ? '我的作品' : '作品集' ;
		$tab01 = [
			'act' => $act,
			'href' => '#tab1',
			'name' => 'tabMyAlbum',
			'class' => $classTab01,
			'text' => ($pageState == 'creator') ? '<span>我的作品</span>' : '<span>作品集</span>',
			'onclick' => 'tabSwitch(1); $(\'#mobile_tab #tab_title\').text(\''.$titleChange.'\');',
		];

		if ($pageState != 'creator') $act = false;
		$tab02 = [
			'act' => $act,
			'href' => '#tab2',
			'name' => 'tabMyAlbum',
			'class' => $classTab02,
			'text' => '<span>收藏・贊助</span>',
			'onclick' => 'tabSwitch(2); $(\'#mobile_tab #tab_title\').text(\'收藏．贊助\');',
		];

		if ($pageState != 'creator') $act = false;
		$tab03 = [
			'act' => $act,
			'href' => '#tab3',
			'name' => 'tabMyCollect',
			'class' => $classTab03,
			'text' => '<span>群組作品</span>',
			'onclick' => 'tabSwitch(3); $(\'#mobile_tab #tab_title\').text(\'群組作品\');',
		];

		$tab04 = [
			'act' => true,
			'href' => '#tab4',
			'name' => 'tabCooperate',
			'class' => $classTab04,
			'text' => '<span>關於我</span>',
			'onclick' => '$(\'#mobile_tab #tab_title\').text(\'關於我\');',
		];

		$tab05 = [
			'act' => true,
			'href' => '#tab5',
			'name' => 'tabPinpinboard',
			'class' => $classTab05,
			'text' => ($m_user['discuss'] == 'close') ? '<span>' . _('Message Board') . '&nbsp;<i class="fa fa-lock" aria-hidden="true"></i></span>' : '<span>' . _('Message Board') . '</span>',
			'onclick' => '$(\'#mobile_tab #tab_title\').text(\''._('Message Board').'\');',
		];

		for ($i=1;$i<7;$i++) {
			$tabLists[] = (isset(${'tab0'.$i})) ? ${'tab0'.$i} : null ;
			$tabContentLists[] = (isset(${'classTabContent0'.$i})) ? ${'classTabContent0'.$i} : null ;
		}

		// 下載數
		$s_album = (new albumModel())
			->column(['sum(albumstatistics.count) as count', 'sum(albumstatistics.viewed) as viewed'])
			->join([['left join', 'albumstatistics', 'using(album_id)']])
			->where([[[['album.user_id', '=', $m_user['user_id']], ['album.act', '=', 'open']], 'and']])
			->fetch();

		// cover
		$userCover = (file_exists(PATH_STORAGE . Core::get_usercover($m_user['user_id']))) ? URL_STORAGE . Core::get_usercover($m_user['user_id']) : null ;

		// sociallink
		$sociallinks = (count(json_decode($m_user['sociallink'], true)) > 0) ? array_merge($a_sociallink, json_decode($m_user['sociallink'], true)) : $a_sociallink;
		foreach ($sociallinks as $key => $value) {
			// Remove all illegal characters from a url
			$value = filter_var($value, FILTER_SANITIZE_URL);
			// Validate url
			$sociallinks[$key] = (filter_var($value, FILTER_VALIDATE_URL)) ? (filter_var($value, FILTER_VALIDATE_URL)) : null ;
		}

		// creative 此專區的創作人資料
		$a_creative = [
			'album' => [
				'sum' => $s_album['count'],
				'formatSum' => custom_number_format($s_album['count']),
				'viewed' => (new userModel)->getUserViewed($m_user['user_id']),
				'formatViewed' => (new userModel)->getUserViewed($m_user['user_id']),
			],
			'creative' => [
				'sociallink' => empty($m_user['sociallink']) ? array() : json_decode($m_user['sociallink'], true)
			],
			'followfrom' => [
				'count' => $m_user['count_from'],
				'formatCount' => custom_number_format($m_user['count_from']),
			],
			'user' => [
				'user_id' => $m_user['user_id'],
				'name' => $m_user['name'],
				'description0' => ($m_user['description']),
				'description1' => htmlspecialchars($m_user['description']),
				'level' => Core::get_userlevel($m_user['user_id']),
				'creative_name0' => $m_user['creative_name'],
				'creative_name1' => htmlspecialchars($m_user['creative_name']),
				'creative' => htmlspecialchars($m_user['creative']),
				'picture' => URL_STORAGE . (new userModel())->getPicture($m_user['user_id']),
				'cover' =>  $userCover,
				'collectedbyusers' => ($m_user['besponsored'] == 0) ? 0 : $m_user['besponsored'],
				'formatCollectedbyusers' => ($m_user['besponsored'] == 0) ? 0 : custom_number_format($m_user['besponsored']),
				'discuss' => $m_user['discuss'],
			],
			'sociallink' => $sociallinks,

		];

		/**
		 * 專區連結QRcode
		 */
		$subpathname_storage = SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $m_user['user_id'];
		$QRcode = new \Core\QRcode();
		$QRcode->setTextUrl(Core::get_creative_url($m_user['user_id']))->setLevel(1)->setSize(5)->save(PATH_STORAGE . storagefile($subpathname_storage . DIRECTORY_SEPARATOR . 'CreativeQRcode.jpg'));
		$qrcode = str_replace('\\', '/', URL_STORAGE . storagefile($subpathname_storage . DIRECTORY_SEPARATOR . 'CreativeQRcode.jpg'));


		//組合專區頁面內容參數
		$creative = [
			//專區內資料
			'creative' => [
				'creative_url' => $creative_url,
				'pageState' => $pageState,
				'follow' => $follow,
				'tabLists' => $tabLists,
				'tabContentLists' => $tabContentLists,
				'tabInitail' => $rank_id,
				'qrcode' => $qrcode,
			],
			//專區作者
			'creator' => $a_creative,
		];
		parent::$data['creative'] = $creative;

		//pinpinboard
		$a_pinpinboard = (new pinpinboardModel())->getComment('user', $_GET['user_id'], $user['user_id']);
		parent::$data['pinpinboard'] = $a_pinpinboard;

		$pinpinboardParam = [
			'type' => 'creative',
			'type_id' => $_GET['user_id'],
			'redirectParam' => 'user_id',
		];
		parent::$data['pinpinboardParam'] = $pinpinboardParam;

		//resize Image for facebook minimum size 200*200
		$userPic = PATH_STORAGE . (new userModel())->getPicture($m_user['user_id']);
		if (is_image($userPic)) {
			$image = fileinfo((new \Core\Image())->set($userPic)->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'];
		} else {
			$image = static_file('images/face_sample.svg');
		}

		// reportintent
		$reportintent = parent::alertData();
        parent::$data['reportintent'] = $reportintent;

		//seo
		$this->seo(
			$a_creative['user']['creative_name0'] . ' | ' . Core::settings('SITE_TITLE'),
			[$m_user['creative_code'], $m_user['creative_name'], $m_user['name'], _('Author’s'), _('Recruitment')],
			$m_user['description'],
			$image,
			$creative_url
		);

		parent::head_v2();
		parent::headbar_v2();
		parent::foot_v2();
		parent::footbar_v2();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);

		parent::$html->set_css(static_file('css/style_v2.css'), 'href');

		parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');

		parent::$html->set_js(static_file('js/counterjs/js/jquery.counterup.min.js'), 'src');
		parent::$html->set_js(static_file('js/waypoint/js/waypoint.js'), 'src');
		parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-timeago-master/js/jquery.timeago.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-timeago-master/js/jquery.timeago.zh-TW.js'), 'src');
		parent::$html->set_js(static_file('js/masonry-4.2.2/js/masonry.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/infinite-scroll-3.0.5/js/infinite-scroll.pkgd.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');

		//jquery-textcomplete
		parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.textcomplete.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.overlay.js'), 'src');
		parent::$html->set_css(static_file('js/jquery-textcomplete/media/stylesheets/textcomplete.css'), 'href');

		//lightgallery
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
		parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
		parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');
		parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/pinpinbox/report.js', 'src');

		parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
    }

	/**
	 * 準備棄用改用新版 Mars - 181130
	 */
    function content_old()
    {
        if (empty($_GET['user_id'])) redirect(parent::url('creative', 'index'), _('Abnormal process, please try again.'));

        $m_user = (new userModel)
            ->column([
                'follow.count_from + userstatistics.followfrom_manual count_from',
                'user.*',
                'userstatistics.besponsored + userstatistics.besponsored_manual besponsored',
            ])
            ->join([
                ['LEFT JOIN', 'follow', 'on follow.user_id = user.user_id'],
                ['INNER JOIN', 'userstatistics', 'on userstatistics.user_id = user.user_id'],
            ])
            ->where([[[['user.user_id', '=', $_GET['user_id']]], 'and']])
            ->fetch();

        if (empty($m_user)) {
            redirect(parent::url('creative', 'index'), _('User does not exist.'));
        } elseif ($m_user['act'] != 'open') {
            redirect(parent::url('creative', 'index'), _('User is not open.'));
        }

        $user = parent::user_get();
        parent::$data['user'] = $user;

        $rank_id = (!empty($_GET['rank_id']) && in_array($_GET['rank_id'], [1, 3, 4])) ? $_GET['rank_id'] : 1;
        parent::$data['rank_id'] = $rank_id;

        $c_searchkey = (isset($_GET['c_searchkey']) && $_GET['c_searchkey'] !== 'creative') ? urldecode($_GET['c_searchkey']) : null;
        parent::$data['c_searchkey'] = htmlspecialchars($c_searchkey);

        //專區連結
        $creative_url = ((strpos(Core::get_creative_url($m_user['user_id']), '?')) == null)
            ? Core::get_creative_url($m_user['user_id']) . '?rank_id=' . $rank_id . '&'
            : Core::get_creative_url($m_user['user_id']) . '&rank_id=' . $rank_id . '&';

        /**
         * 專區連結QRcode
         */
        $subpathname_storage = SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $m_user['user_id'];
        $QRcode = new \Core\QRcode();
        $QRcode->setTextUrl(Core::get_creative_url($m_user['user_id']))->setLevel(1)->setSize(5)->save(PATH_STORAGE . storagefile($subpathname_storage . DIRECTORY_SEPARATOR . 'CreativeQRcode.jpg'));
        $qrcode = str_replace('\\', '/', URL_STORAGE . storagefile($subpathname_storage . DIRECTORY_SEPARATOR . 'CreativeQRcode.jpg'));

        /**
         * 未登入 : viewer / 登入: user / 創作人 : creator
         */
        $pageState = null;
        if (empty($user)) {
            $pageState = 'viewer';
        } else {
            $pageState = ($m_user['user_id'] === $user['user_id']) ? 'creator' : 'user';
        }

        /**
         * 訪問頁面時關閉右上方驚嘆號提示圖
         */
        if ($pageState == 'creator' && isset($_GET['setting_viewed'])) {
            if (!$m_user['setting_viewed']) {
                Model('user')->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['setting_viewed' => 1]);
                Model('user')->setSession($m_user['user_id']);
            }
        }

        /**
         * follow
         */
        $follow = false;
        if (!empty($user)) {
            $follow = Core::get_follow($user['user_id'], $m_user['user_id']) ? 1 : 0;
        }

        /**
         * buttons
         */
        $buttonFollow = [
            'name' => 'buttonFollow',
            'onclick' => 'onclick="follow();"',
            'href' => 'javascript:void(0)',
            'class' => 'pin_button attention',
            'text' => ($follow) ? _('關注中') : _('Follow'),
            'style' => null,
        ];

        $buttonSave = [
            'name' => 'buttonSave',
            'onclick' => 'onclick="save();"',
            'href' => 'javascript:void(0)',
            'class' => 'edit-finish',
            'text' => _('編輯完成'),
            'style' => 'display:none;',
        ];

        $buttonEdit = [
            'name' => 'buttonEdit',
            'onclick' => 'onclick="edit();"',
            'href' => 'javascript:void(0)',
            'class' => 'pin_button',
            'text' => _('Edit'),
            'style' => null,
        ];

        $buttonSettings = [
            'name' => 'buttonSettings',
            'onclick' => null,
            'href' => parent::url('user', 'settings'),
            'class' => 'share_button hidden-xs',
            'text' => _('進階管理'),
            'style' => null,
        ];

        $buttonShare = [
            'name' => 'buttonShare',
            'onclick' => 'onclick="share(\'creative\', \'' . $m_user['user_id'] . '\', \'' . $qrcode . '\' )"',
            'href' => 'javascript:void(0)',
            'class' => 'share_button',
            'text' => '<img src="' . static_file('images/assets-v6/ic-back.svg') . '"><span class="hidden-xs">' . _('分享') . '</span>',
            'style' => null,
        ];

        $buttonMore = [
            'name' => 'buttonMore',
            'onclick' => 'onclick="$(\'#mobileOptions\').trigger(\'click\');"',
            'href' => 'javascript:void(0)',
            'class' => 'more-btn',
            'text' => '<img src="' . static_file('images/assets-v6/ic-more.svg') . '">',
            'style' => null,
        ];

        $buttons = [$buttonFollow, $buttonShare];
        if (empty($user)) {
            $buttonFollow['onclick'] = 'onclick="var r = {result: 0, message: \'' . _('Please login first.') . '\', redirect: \'' . self::url('user', 'login', ['redirect' => Core::get_creative_url($m_user['user_id'], 'r_follow')]) . '\'}; _jBox(r, \'success_notext\');"';
            $buttons = [$buttonFollow, $buttonShare];
        } else {
            if ($m_user['user_id'] === $user['user_id']) {
                $buttons = [$buttonSave, $buttonEdit, $buttonSettings, $buttonShare,];
            } elseif ($follow) {
                $buttonFollow['class'] = 'pin_button attention active';
                $buttons = [$buttonFollow, $buttonShare];
            }
        }

        /**
         *    Tablists : tab01(關於我)   / tab02(我的作品) / tab03(我的收藏) / tab04(群組作品) / tab05(留言板) / tab06(編輯關於我頁籤, 隱藏)
         */
        $rank_id =  ( isset($_GET['rank_id']) ) ? $_GET['rank_id'] : 2 ;

        $act = true;

		for ($i=1;$i<6;$i++) {
			${'classTab0'.$i} = ${'classTabContent0'.$i} = null ;
		}

        if ((isset($_GET['appview']) && ($_GET['appview']) == true) || isset($_GET['setting_viewed'])) {
            $classTab01 = $classTabContent01 = 'active';
        } else {
			${'classTab0'.$rank_id} = ${'classTabContent0'.$rank_id} = 'active' ;
		}

        $tab01 = [
            'act' => $act,
            'href' => '#tab01',
            'name' => 'tabAbout',
            'class' => $classTab01,
            'text' => '<span>關於我</span>',
            'onclick' => '',
        ];

        $tab02 = [
            'act' => $act,
            'href' => '#tab02',
            'name' => 'tabMyAlbum',
            'class' => $classTab02,
            'text' => '<span>我的作品</span>',
            'onclick' => 'tabSwitch(\'tabMyAlbum\');',
        ];

        if ($pageState != 'creator') $act = false;
        $tab03 = [
            'act' => $act,
            'href' => '#tab03',
            'name' => 'tabMyCollect',
            'class' => $classTab03,
            'text' => '<span>我的收藏</span>',
            'onclick' => 'tabSwitch(\'tabMyCollect\');',
        ];

        if ($pageState != 'creator') $act = false;
        $tab04 = [
            'act' => $act,
            'href' => '#tab04',
            'name' => 'tabCooperate',
            'class' => $classTab04,
            'text' => '<span>群組作品</span>',
            'onclick' => 'tabSwitch(\'tabCooperate\');',
        ];

        $tab05 = [
            'act' => true,
            'href' => '#tab05',
            'name' => 'tabPinpinboard',
            'class' => 'pinpinboard_btn',
            'text' => ($m_user['discuss'] == 'close') ? '<span>' . _('Message Board') . '&nbsp;<i class="fa fa-lock" aria-hidden="true"></i></span>' : '<span>' . _('Message Board') . '</span>',
            'onclick' => '',
        ];

        $tab06 = [
            'act' => true,
            'href' => '#tab06',
            'name' => 'tabAboutEdit',
            'class' => '',
            'text' => '',
            'onclick' => '',
        ];

        switch ($rank_id) {
			case 3: $tabInitial = 'tabMyCollect' ; break;
			case 4: $tabInitial = 'tabCooperate' ; break;
			default: $tabInitial = 'tabMyAlbum' ; break;
		}

		for ($i=1;$i<7;$i++) {
			$tabLists[] = (isset(${'tab0'.$i})) ? ${'tab0'.$i} : null ;
			$tabContentLists[] = (isset(${'classTabContent0'.$i})) ? ${'classTabContent0'.$i} : null ;
		}

        /**
         * creative
         */
        //下載數
        $s_album = Model('album')
            ->column(array('sum(albumstatistics.count) as count', 'sum(albumstatistics.viewed) as viewed'))
            ->join(array(array('left join', 'albumstatistics', 'using(album_id)')))
            ->where(array(array(array(array('album.user_id', '=', $m_user['user_id']), array('album.act', '=', 'open')), 'and')))
            ->fetch();

        $a_creative = array(
            'album' => array(
                'sum' => $s_album['count'],
                'formatSum' => custom_number_format($s_album['count']),
                'viewed' => (new userModel)->getUserViewed($m_user['user_id']),
                'formatViewed' => custom_number_format((new userModel)->getUserViewed($m_user['user_id'])),
            ),
            'creative' => array(
                'sociallink' => empty($m_user['sociallink']) ? array() : json_decode($m_user['sociallink'], true)
            ),
            'followfrom' => array(
                'count' => $m_user['count_from'],
                'formatCount' => custom_number_format($m_user['count_from']),
            ),
            'user' => array(
                'user_id' => $m_user['user_id'],
                'name' => $m_user['name'],
                'description0' => ($m_user['description']),
                'description1' => htmlspecialchars($m_user['description']),
                'level' => Core::get_userlevel($m_user['user_id']),
                'creative_name0' => $m_user['creative_name'],
                'creative_name1' => htmlspecialchars($m_user['creative_name']),
                'creative' => htmlspecialchars($m_user['creative']),
                'picture' => URL_STORAGE . Core::get_userpicture($m_user['user_id']),
                'cover' => URL_STORAGE . Core::get_usercover($m_user['user_id']),
                'collectedbyusers' => ($m_user['besponsored'] == 0) ? 0 : $m_user['besponsored'],
                'formatCollectedbyusers' => ($m_user['besponsored'] == 0) ? 0 : custom_number_format($m_user['besponsored']),
            ),
            'discuss' => $m_user['discuss'],
        );

        /**
         *  可填寫的sociallink 清單
         */

        $a_sociallink = [
            'blog' => '',
            'facebook' => '',
            'google' => '',
            'instagram' => '',
            'line' => '',
            'linkedin' => '',
            'pinterest' => '',
            'twitter' => '',
            'youtube' => '',
            'web' => '',
        ];

        //組合專區頁面內容參數
        $creative = [
            //專區內資料
            'creative' => [
                'creative_url' => $creative_url,
                'qrcode' => $qrcode,
                'pageState' => $pageState,
                'follow' => $follow,
                'buttons' => $buttons,
                'tabLists' => $tabLists,
                'tabContentLists' => $tabContentLists,
				'tabInitail' => $tabInitial,
            ],
            //專區作者
            'creator' => $a_creative,
            //登入的使用者
            'user' => $user,
            'sociallink' => (count(json_decode($m_user['sociallink'], true)) > 0) ? array_merge($a_sociallink, json_decode($m_user['sociallink'], true)) : $a_sociallink,
        ];

        parent::$data['creative'] = $creative;

        /**
         * redirect follow
         */
        $r_follow = null;
        if (isset($_GET['click'])) {
            if ($_GET['click'] == 'r_follow' && !$follow) {
                $r_follow = 'follow()';
            }
        }
        parent::$data['r_follow'] = $r_follow;

        /**
         * userstatistics
         */
        $tmp0 = md5(parent::disqus_creative($m_user['user_id']));
        if (!isset($_COOKIE[$tmp0])) {
            setcookie($tmp0, true, time() + 86400);
            $viewed = Model('userstatistics')->column(array('viewed'))->where(array(array(array(array('user_id', '=', $m_user['user_id'])), 'and')))->fetchColumn();
            Model('userstatistics')->where(array(array(array(array('user_id', '=', $m_user['user_id'])), 'and')))->edit(array('viewed' => $viewed + 1));
        }

        //pinpinboard
        $a_pinpinboard = (new pinpinboardModel())->getComment('user', $_GET['user_id'], $user['user_id']);
        parent::$data['pinpinboard'] = $a_pinpinboard;

        $pinpinboardParam = [
            'type' => 'user',
            'type_id' => $_GET['user_id'],
            'redirectParam' => 'user_id',
        ];
        parent::$data['pinpinboardParam'] = $pinpinboardParam;

        //若有取得autoplay則自動撥放
        $autoplay = null;
        if (!empty($_GET['autoplay'])) {
            $autoplay = 'browseKit_album(\'' . self::url('album', 'show_photo') . '\', {album_id: \'' . $_GET['autoplay'] . '\'});';
        }
        parent::$data['autoplay'] = $autoplay;

        //resize Image for facebook minimum size 200*200
        if (is_image(PATH_STORAGE . Core::get_userpicture($m_user['user_id']))) {
            $image = fileinfo((new \Core\Image())->set(PATH_STORAGE . Core::get_userpicture($m_user['user_id']))->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'];
        } else {
            $image = static_file('images/face_sample.svg');
        }

        //seo
        $this->seo(
            $a_creative['user']['creative_name0'] . ' | ' . Core::settings('SITE_TITLE'),
            [$m_user['creative_code'], $m_user['creative_name'], $m_user['name'], _('Author’s'), _('Recruitment')],
            $m_user['description'],
            $image,
            $creative_url
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();

        //lightgallery
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
        parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');

        //jquery-textcomplete
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.textcomplete.js'), 'src');
        parent::$html->set_js(static_file('js/jquery-textcomplete/jquery.overlay.js'), 'src');
        parent::$html->set_css(static_file('js/jquery-textcomplete/media/stylesheets/textcomplete.css'), 'href');

        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/social-likes/css/social-likes_flat.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.magnific-popup/css/magnific-popup.css'), 'href');
        parent::$html->set_css(static_file('js/croppie/css/croppie.css'), 'href');
        parent::$html->set_css(static_file('js/slick/slick.css'), 'href');

        parent::$html->set_js(static_file('js/ckeditor_4.5.10_full/ckeditor.js'), 'src');
        parent::$html->set_js(static_file('js/croppie/js/croppie.min.js'), 'src');
        parent::$html->set_js(static_file('js/croppie/js/exif.js'), 'src');
        parent::$html->set_js(static_file('js/slick/slick.min.js'), 'src');
        parent::$html->set_js(static_file('js/autolink-min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.show-more.js'), 'src');
        parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');
        parent::$html->set_js(static_file('js/social-likes/js/social-likes.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.magnific-popup/js/jquery.magnific-popup.js'), 'src');
        parent::$html->set_js(URL_ROOT . 'js/php.js', 'src');
        parent::$html->set_jquery_validation();
        parent::$html->jbox();

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

	/**
	 * 準備棄用改用新版 Mars - 181130
	 */
    function content_edit()
    {
        if (is_ajax()) {
            $user_id = empty($_POST['user_id']) ? null : $_POST['user_id'];
            $username = empty($_POST['username']) ? null : $_POST['username'];
            $creative_name = empty($_POST['creative_name']) ? null : $_POST['creative_name'];
            $sociallink = empty($_POST['user_sociallink']) ? null : $_POST['user_sociallink'];

            if ($user_id == null) {
                json_encode_return(0, _('Abnormal process, please try again.'));
            } elseif ($username == null) {
                json_encode_return(0, _('Please enter your creative-name.'));
            }

            $user = parent::user_get();

            if ($user == null) json_encode_return(2, _('Please login first.'), parent::url('user', 'login', array('redirect' => Core::get_creative_url($user_id))));

            if ($user_id != $user['user_id']) json_encode_return(0, _('Abnormal process, please try again.'));

            $edit = [
                'name' => $username,
                'creative_name' => $creative_name,
                'sociallink' => $sociallink,
            ];

            (new userModel())->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit($edit);

            $ajax_result = array(
                'creative_name' => $creative_name,
            );

            json_encode_return(1, $ajax_result);
        }
        die;
    }

    function content_property($album_id)
    {
        $user = parent::user_get();
        list($result, $message, , $data0) = array_decode_return(Model('album')->content($album_id));
        $userpoint = (isset($user)) ? userModel::newly()->getPoint($user['user_id'], 'web') : '0';
        $balance = $userpoint - $data0['album']['point'];

        /**
         *  device 裝置  1 =行動裝置  0=傳統電腦裝置
         */
        $mobile = SDK('Mobile_Detect')->isMobile();

        //登入
        $login = empty($user) ? 0 : 1;

        //favorited 收藏
        $favorited = false;
        if (!empty($user)) {
            $m_albumqueue = (new albumqueueModel)->column(['visible'])->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->fetch();
            if ($m_albumqueue && !$m_albumqueue['visible']) {
                Model('albumqueue')->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->edit(['visible' => 1]);
            }
            if ($m_albumqueue || $data0['user']['user_id'] == $user['user_id']) {
                $favorited = true;
            }
        }

        //photo 讀取相片
        $where = $favorited ? [[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']] : [[[['album_id', '=', $album_id], ['act', '=', 'open'], ['image', 'in', json_decode($data0['album']['preview'], true)]], 'and']];
        $column = ['`name`', 'description', 'image', 'usefor', 'hyperlink', 'audio_loop', 'audio_refer', 'audio_target', 'video_refer', 'video_target'];
        $photo = (new photoModel)->column($column)->where($where)->order(['sequence' => 'asc'])->fetchAll();

        //albumPhotos 總頁數
        $albumPhotos = count(json_decode($data0['album']['photo'], true));

        //previewPhotos 預覽頁數
        $previewPhotos = count($photo);

        /**
         *  releaseMode 閱覽模式  1 =全部內容  0 =部分內容
         */
        $releaseMode = ($albumPhotos === $previewPhotos) ? 1 : 0;

        /**
         *  buyAlbumBox 以下處理購買相本時彈出視窗內容
         */
        //Btn 彈出視窗的按鈕型態
        if ($login) {
            $buyAlbumBoxBtn = ($balance < 0) ? _('前往儲值') : _('Yes');
        } else {
            $buyAlbumBoxBtn = _('前往登入');
        }

        //Content 文字內容
        $buyAlbumBoxContent = '<div class="content">';
        //content0
        if (!$releaseMode) {
            $Content0 = '<p class="keypoint" style="font-size:2em;">' . _('馬上收藏 看全部內容') . '</p><br>';
        } else {
            $Content0 = ($data0['album']['point']) ? '<p class="keypoint" style="font-size:2em;">' . _('喜歡作品就給個鼓勵吧!') . '</p><br>' : '<p class="keypoint" style="font-size:2em;">' . _('馬上收藏 接收更新通知') . '</p><br>';
        }

        //content1
        if ($mobile) {
            $content1 = '<p>';
            $content1 .= '<a data-uri="' . Core::settings('ANDROID_DATA_URI') . '" onclick="clickHandler(this.dataset.uri)"><img class="app" src="' . static_file('images/jbox_and.png') . '"></a>';
            $content1 .= '<a data-uri="' . Core::settings('IOS_DATA_URI') . '" onclick="clickHandler(this.dataset.uri); ios_notice();" href="javascript:void(0)"><img class="app" src="' . static_file('images/jbox_ios.png') . '"></a>';
            $content1 .= '</p>';
        } else {
            $content1 = '<p>';
            $content1 .= '<a href ="' . Core::settings('GOOGLEPLAY_APK_URL') . '"><img class="app" src="' . static_file('images/jbox_and.png') . '"></a>';
            $content1 .= '<a href="javascript:void(0)" onclick="ios_notice()"><img class="app" src="' . static_file('images/jbox_ios.png') . '"></a>';
            $content1 .= '</p>';
        }

        //content2
        if ($login) {
            $content2 = (!$data0['album']['point']) ? '<p class="keypoint" style="font-size:1.4em;margin:6px 0px;">' . _('免費收藏') . '</p>' :
                ($releaseMode) ? '<p class="keypoint" style="font-size:1.4em;margin:6px 0px;">' . _('確定贊助此作品？') . '</p>' : '<p class="keypoint" style="font-size:1.4em;margin:6px 0px;">' . _('Are you sure you want to save this photo album?') . '</p>';

            $content3 = (!$data0['album']['point']) ? '<p>' . _('花費P點') . '&nbsp;：<span class="red">' . $data0['album']['point'] . 'P</span></p>' : '<p>' . _('現有P點') . '&nbsp;：<span class="red">' . $userpoint . 'P</span></p>
				  <p>' . _('花費P點') . '&nbsp;：<span class="red">' . $data0['album']['point'] . 'P</span></p><hr>
				  <p class="red">' . _('剩餘P點') . '&nbsp;：' . $balance . 'P</p>';
        } else {
            $content2 = '<p class="keypoint" style="font-size:1.4em;margin:6px 0px;">' . _('Are you sure you want to save this photo album?') . '</p>';
            $content3 = '<p>' . _('花費P點') . '&nbsp;：<span class="red">' . $data0['album']['point'] . 'P</span></p>';
        }

        $ContentEnd = '</div>';
        $buyAlbumBoxContent .= $Content0 . $content1 . $content2 . $content3 . $ContentEnd;
        // --buyAlbumBox文字區塊結尾

        //collected 是否收藏
        if ($login) {
            //檢查是否已經有購買
            $m_albumqueue = albumqueueModel::newly()->where([[[['user_id', '=', $user['user_id']], ['album_id', '=', $album_id]], 'and']])->fetch();
            //檢查是否為職人
            $album_owner = ($user['user_id'] == $data0['user']['user_id']) ? true : false;
        }
        $collected = (!empty($m_albumqueue) || !empty($album_owner)) ? true : false;

        //eventjoin 相本是否參加活動
        $m_eventjoin = eventjoinModel::newly()->join([['left join', 'event', 'using(event_id)']])->column(['event.event_id', 'event.name'])->where([[[['eventjoin.album_id', '=', $album_id], ['event.act', '=', 'open'], ['event.endtime', '>', date('Y-m-d H:i:s', time())]], 'and']])->fetch();

        $eventjoin = !empty($m_eventjoin) ? $m_eventjoin['event_id'] : false;

        //pc_button mobile_button 電腦版及手機板投票樣式
        $vote_btn = $btn_style = $m_vote_btn = null;
        if ($collected) {
            if ($eventjoin) {
                $btn_style = 'width:48%; display: inline-block;';
                $vote_btn = '<a id="vote" href="' . parent::url('event', 'content', ['event_id' => $eventjoin]) . '#my_' . $album_id . '" class="used big white" style="' . $btn_style . '">
						<img style="width: 12%;" src="' . static_file('images/icon_vote.svg') . '" >' . _('前往投票') . '
					</a>';
                $m_vote_btn = '<a href="' . parent::url('event', 'content', ['event_id' => $eventjoin]) . '#my_' . $album_id . '" id="vote_mobile" ><img src="' . static_file('images/icon_vote.svg') . '"></a>';
            }

            $pc_button = '<li class="mobilehide02">
				' . $vote_btn . '
				<a  id="read" href="javascript:void(0);" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})" id="trip_collect" style="' . $btn_style . '" class="used big">
					<img src="' . static_file('images/album_icon03.png') . '">' . _('Read') . '
				</a>
			</li>';
            $mobile_button = $m_vote_btn . '<a href="javascript:void(0);" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})"><img src="' . static_file('images/icon_collection_click.svg') . '"></a>';
        } else {
            $btn_style = 'width:48%; display: inline-block;';
            if ($eventjoin) {
                $btn_style = 'width:32%; display: inline-block;';
                $vote_btn = '<a id="vote" href="' . parent::url('event', 'content', ['event_id' => $eventjoin]) . '#my_' . $album_id . '" class="used big white" style="' . $btn_style . '">
						<img style="width: 16%;" src="' . static_file('images/icon_vote.svg') . '" >' . _('前往投票') . '
					</a>';

                $m_vote_btn = '<a href="' . parent::url('event', 'content', ['event_id' => $eventjoin]) . '#my_' . $album_id . '" id="vote_mobile" ><img src="' . static_file('images/icon_vote.svg') . '"></a>';
            }

            $heart_text = ($data0['album']['point'] > 0) ? _('Sponsored') : _('Collection');
            $pc_button = '<li class="mobilehide02" id="preview">
					' . $vote_btn . '
					<a href="javascript:void(0);" id="read" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})" class="used big white" style="margin-bottom:5px; ' . $btn_style . '">
						<img src="' . static_file('images/album_icon02.png') . '" >' . _('閱讀作品') . '
					</a>
					<a href="javascript:void(0);" onclick="buyalbum()" id="trip_collect" class="used big" style="' . $btn_style . '">
						<img width="24" height="auto" src="' . static_file('images/icon_collection.svg') . '">' . $heart_text . '
					</a>
				</li>';

            $mobile_button = $m_vote_btn . '<a href="javascript:void(0);" id="preview_mobile" onclick="browseKit_album(\'' . parent::url('album', 'show_photo') . '\', {album_id: \'' . $album_id . '\'})"><img src="' . static_file('images/icon_Preview.svg') . '" onerror="this.onerror=null; this.src=\'' . static_file('images/album_icon02_big.png') . '\'"></a>
				<a href="javascript:void(0);" id="heart_mobile" onclick="buyalbum()"><img src="' . static_file('images/icon_collection.svg') . '" onerror="this.onerror=null; this.src=\'' . static_file('images/album_icon01_big.png') . '\'"></a>';
        }

        $return = [
            'album' => [
                'data' => $data0,
                'photo' => $photo,
                'releaseMode' => $releaseMode,
                'albumPhotos' => $albumPhotos,
                'previewPhotos' => $previewPhotos,
                'eventjoin' => $eventjoin,
            ],

            'user' => [
                'mobileDevice' => $mobile,
                'userpoint' => $userpoint,
                'collected' => $collected,
            ],

            'property' => [
                'login' => $login,
                'balance' => $balance,
                'favorited' => $favorited,
                'buyAlbumBoxContent' => $buyAlbumBoxContent,
                'buyAlbumBoxBtn' => $buyAlbumBoxBtn,
                'pc_button' => $pc_button,
                'mobile_button' => $mobile_button,
            ],
        ];

        return $return;
    }

    function croppie()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $data = (!empty($_POST['data'])) ? $_POST['data'] : null;  //取得base64的code
            $uploadType = (!empty($_POST['uploadType'])) ? $_POST['uploadType'] : null;  //取得base64的code

            $upload_folder = '/user/' . date('Ymd') . '/';
            mkdir_p(PATH_UPLOAD, M_PACKAGE . $upload_folder);
            $data = explode(',', $data);

            switch ($uploadType) {
                case 'avatar':
                    if (\userModel::setPicture($user['user_id'], base64_decode($data[1]))) {
                        json_encode_return(1, _('Croppie success.'));
                    }
                    break;

                case 'CreatorCover':
                    if (\Core::set_usercover($user['user_id'], base64_decode($data[1]))) {
                        json_encode_return(1, _('Croppie success.'));
                    }
                    break;
            }

            json_encode_return(0, _('Croppie fail.'));
        }
    }

    function edit() {
		$login_user = parent::user_get();
    	if (empty($_GET['user_id']) || ($login_user['user_id'] != $_GET['user_id'])) redirect(parent::url('creative', 'index'), _('Abnormal process, please try again.'));

    	// 取得 user 資料
		$userModel = (new userModel());
		$m_user = $userModel->where([[[['user_id', '=', $login_user['user_id']]], 'and']])->fetch();

		switch ($m_user['gender']) {
			case 'male' : $gender_text = '男性' ; break;
			case 'female' : $gender_text = '女性' ; break;
			default : $gender_text = '不透露' ; break;
		}

		switch ($m_user['relationship']) {
			case 'single' : $relationship_text = '未婚' ; break;
			case 'married' : $relationship_text = '已婚' ; break;
			default : $relationship_text = '不透露' ; break;
		}

		// user的hobby清單
		$m_hobby_user = (new hobby_userModel())->column(['hobby_id', 'hobby.name'])->join([['LEFT JOIN', 'hobby', 'USING(hobby_id)']])->where([[[['user_id', '=', $login_user['user_id']]], 'and']])->fetchAll();
		$a_hobby_user = $hide_hobby = [];
		foreach ($m_hobby_user as $v0) {
			$a_hobby_user[] = [
				'id' => $v0['hobby_id'],
				'name' => $v0['name'],
			];

			// 初始頁面時要隱藏的興趣 id
			$hide_hobby[] = $v0['hobby_id'];
		}

		parent::$data['hobby_user'] = $a_hobby_user;
		parent::$data['hide_hobby'] = $hide_hobby;

		// 取得地址清單
		$addressModel = (new addressModel());
		$m_address_1st = $addressModel->where([[[['level', '=', 0]], 'and']])->fetchAll();
		foreach ($m_address_1st as $v0) {
			$cities = $addressModel->where([[[['level', '=', 1], ['parent', '=', $v0['address_id']]], 'and']])->fetchAll();
			$addresses[] = [
				'name' => $v0['name'],
				'address_id' => $v0['address_id'],
				'cities' => $cities,
			];
		}
		parent::$data['addresses'] = $addresses;

		// user 地址資料
		$address_1st = $addressModel->column(['name', 'address_id'])->where([[[['address_id', '=', $m_user['address_id_1st']]] ,'and']])->fetch();
		$address_2nd = $addressModel->column(['name', 'address_id'])->where([[[['address_id', '=', $m_user['address_id_2nd']]] ,'and']])->fetch();

		// 取得所有興趣清單
		$hobbies = [];
		$m_hobby = (new hobbyModel())->column(['hobby_id', 'name'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
		foreach ($m_hobby as $k => $v) {
			$hobbies[$k]['hobby_id'] = $v['hobby_id'];
			$hobbies[$k]['name'] = \Core\Lang::i18n($v['name']);
		}
		parent::$data['hobbies'] = $hobbies;

		$user = [
			'account' => $m_user['account'],
			'birthday' => $m_user['birthday'],
			'cellphone' => $m_user['cellphone'],
			'address_1st' => $address_1st['name'],
			'address_1st_id' => $address_1st['address_id'],
			'address_2nd' => (!empty($address_2nd['name'])) ? $address_2nd['name'] : _('--請選擇--'),
			'address_2nd_id' => $address_2nd['address_id'],
			'creative_name' => $m_user['creative_name'],
			'creative_code' => $m_user['creative_code'],
			'cover' => URL_STORAGE . Core::get_usercover($m_user['user_id']),
			'description0' => $m_user['description'],
			'discuss' => $m_user['discuss'],
			'email' => $m_user['email'],
			'gender' => $m_user['gender'],
			'gender_text' => $gender_text,
			'newsletter' => $m_user['newsletter'],
			'relationship' => $m_user['relationship'],
			'relationship_text' => $relationship_text,
			'social_link' => empty($m_user['sociallink']) ? [] : json_decode($m_user['sociallink'], true),
			'name' => $m_user['name'],
			'picture' => URL_STORAGE. $userModel->getPicture($m_user['user_id']),
			'user_id' => $m_user['user_id'],
		];

		parent::$data['user'] = $user;

		parent::head_v2();
		parent::headbar_v2();
		parent::foot_v2();
		parent::footbar_v2();
		parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);

		parent::$html->set_css(static_file('css/style_v2.css'), 'href');

		parent::$html->set_css(static_file('js/datepicker/css/bootstrap-datepicker.min.css'), 'href');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.js'), 'src');
		parent::$html->set_js(static_file('js/datepicker/js/bootstrap-datepicker.zh-TW.js'), 'src');

		parent::$html->set_css(static_file('js/croppie/css/croppie.css'), 'href');
		parent::$html->set_js(static_file('js/croppie/js/croppie.min.js'), 'src');

		parent::$html->set_css(static_file('js/intl-tel-input-master/css/intlTelInput.css'), 'href');
		parent::$html->set_js(static_file('js/intl-tel-input-master/js/intlTelInput.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery.countdown.js'), 'src');

		parent::$html->set_js(static_file('js/jquery-validation-1.19.0/lib/jquery.mockjax.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-validation-1.19.0/lib/jquery.form.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-validation-1.19.0/dist/jquery.validate.min.js'), 'src');
		parent::$html->set_js(static_file('js/jquery-validation-1.19.0/src/localization/messages_zh_TW.js'), 'src');

		parent::$html->set_js(static_file('js/ckeditor_4.5.10_full/ckeditor.js'), 'src');

	}

	function exting_cooperation()
	{
		if(is_ajax()) {
			$album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
			$user = parent::user_get();
			if ($album_id == null) {
				json_encode_return(0, _('Abnormal process, please try again.'));
			}

			$cooperation = (new albumModel())->cooperation($user['user_id'])->fetch();
			if($cooperation['identity'] != 'none') {
				$result = (new albumModel())->exting_cooperation($album_id, $user['user_id']);
			}

			json_encode_return($result, '退出群組成功', null, null);
		}
    }
    
    function follow()
    {
        if (is_ajax()) {
            $user_id = empty($_POST['user_id']) ? null : $_POST['user_id'];
            if ($user_id == null) {
                json_encode_return(0, _('Abnormal process, please try again.'));
            }

            $user = userModel::newly()->getSession();

            if ($user == null) json_encode_return(0, _('Please login first.'), parent::url('user', 'login', ['redirect' => Core::get_creative_url($user_id)]));

            if ($user_id == $user['user_id']) json_encode_return(0, _('Abnormal process, please try again.'));

            //計算follow人數是否超過上限，僅動作於"關注"對方
            $m_followto = followtoModel::newly()->where([[[['user_id', '=', $user['user_id']], ['`to`', '=', $user_id]], 'and']])->fetch();
            if (empty($m_followto)) {
                $m_follow_count = followModel::newly()->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetch();
                if ($m_follow_count['count_to'] >= Core::settings('FOLLOWTO_MAX')) json_encode_return(0, _('Your followed up issues has reached the maximum limit.'));
            }

            //避免頻繁操作
            $m_followto = followtoModel::newly()->where([[[['user_id', '=', $user['user_id']], ['`to`', '=', $user_id]], 'and']])->order(['inserttime' => 'desc'])->fetch();
            if (!empty($m_followto) && strtotime('+10 second', strtotime($m_followto['inserttime'])) >= time()) json_encode_return(0, _('This operation cannot redo within 10 seconds.'));

            Model('follow');
            Model('followfrom');
            Model('followto');
            Model()->beginTransaction();
            $followstatus = Core::set_follow($user['user_id'], $user_id);
            $c_followfrom = followfromModel::newly()->column(['count(1)'])->where([[[['user_id', '=', $user_id]], 'and']])->fetchColumn();
            Model()->commit();

            /**
             *  0125 - 執行任務 - 關注作者
             */
            if (empty($m_followto)) {
                $data = Model('task')->doTask('follow_user', $user['user_id'], 'web', ['type' => 'user', 'type_id' => $user_id]);
            }

            $data['followstatus'] = $followstatus;
            $data['count'] = $c_followfrom;

            json_encode_return(1, null, null, $data);
        }
        die;
    }

    function getItem()
    {
        if (is_ajax()) {
			$message = null;
			/**
			 * rank_id : 1(所有相本)  / 2(版型風格) / 3(我的收藏) / 4(共用收藏)
			 */
			$rank_id = (!empty($_GET['rank_id'])) ? $_GET['rank_id'] : 1;
			$initial = (!empty($_GET['initial'])) ? $_GET['initial'] : 0;
			$user_id = (!empty($_GET['user_id'])) ? $_GET['user_id'] : null;
			$login_user = parent::user_get();
			$page = (!empty($_GET['page'])) ? $_GET['page'] : 1;
			$c_searchkey = (isset($_POST['c_searchkey']) && replaceSpace($_POST['c_searchkey']) !== '' && $_POST['c_searchkey'] !== 'creative') ? urldecode($_POST['c_searchkey']) : null;

			function search_type($type, $searchkey)
			{
				$s_type_id = [];

				if ($searchkey !== null) {
					${'s_' . $type} = Solr($type)
						->column([$type . '_id'])
						->where([[[['_text_', '=', $searchkey]], 'and']])
						->fetchAll();

					if (!empty(${'s_' . $type})) {
						$s_type_id = array_column(${'s_' . $type}, $type . '_id');
					}
				}

				return $s_type_id;
			}

			//一頁幾個項目
			$num_of_per_page = 10;
			$m_data = [];
			$c_album = 0;
			$c_data = 0;

			switch ($rank_id) {
				//專區所有相本
				case 1 :
					$type = 'album';
					$filter_act = ($login_user['user_id'] == $user_id) ? [$type . '.act', 'in', ['open', 'close']] : [$type . '.act', '=', 'open'];
					$filter_state = ($login_user['user_id'] == $user_id) ? [$type . '.state', 'in', ['process', 'success']] : [$type . '.state', '=', 'success'];

					$where = [[[['user_id', '=', $user_id], $filter_state, $filter_act], 'and']];

					if ($c_searchkey !== null) {
						$where = array_merge($where, [[[[$type . '_id', 'in', search_type($type, $c_searchkey)]], 'and']]);
					}

					$m_album = (new albumModel())->mine_v2($user_id, $where, ['inserttime' => 'desc'], $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
					$m_data = $m_album;

					$c_album = (new albumModel())->column(array('count(1)'))->where($where)->fetchColumn();
					$c_data = $c_album;

					if ($c_data === 0) $message = _('尚未建立作品');
					break;

				//專區我的收藏 *登入自己專區
				case 2 :
					$type = 'album';

					$where = [[[['albumqueue.user_id', '=', $user_id]], 'and']];

					if ($c_searchkey !== null) {
						$where = array_merge($where, [[[[$type . '_id', 'in', search_type($type, $c_searchkey)]], 'and']]);
					}

					$m_album = (new albumqueueModel())->myCollect($user_id, $where, ['albumqueue.inserttime' => 'desc'], $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
					$m_data = $m_album;

					$c_album = (new albumqueueModel())->column(array('count(1)'))->where($where)->fetchColumn();
					$c_data = $c_album;

					if ($c_data === 0) $message = _('尚未收藏的作品');
					break;

				//專區共用收藏 *登入自己專區
				case 3 :
					$type = 'album';

					$where = [];

					if ($c_searchkey !== null) {
						$where = array_merge($where, [[[['cooperation.type_id', 'in', search_type($type, $c_searchkey)]], 'and']]);
					}

					$m_album = (new albumModel())->cooperation_v2($login_user['user_id'], $where, ['album.inserttime' => 'desc'], $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
					$m_data = $m_album;

					$c_album = (new albumModel())->cooperation_v2($login_user['user_id'], $where, ['album.inserttime' => 'desc']);
					$c_data = count($c_album);

					if ($c_data === 0) $message = _('尚未有群組作品');
					break;

				//專區所有版型風格
				case 4 :
					$type = 'template';
					$filter_act = ($login_user['user_id'] == $user_id) ? [$type . '.act', 'in', ['open', 'close']] : [$type . '.act', '=', 'open'];
					$filter_state = ($login_user['user_id'] == $user_id) ? [$type . '.state', 'in', ['process', 'success']] : [$type . '.state', '=', 'success'];

					$where = [[[['user_id', '=', $user_id], $filter_state, $filter_act], 'and']];

					if ($c_searchkey !== null) {
						$where = array_merge($where, [[[[$type . '_id', 'in', search_type($type, $c_searchkey)]], 'and']]);
					}

					$m_template = (new templateModel())->mine_v2($user_id, $where, ['inserttime' => 'desc'], $num_of_per_page * ($page - 1) . ',' . $num_of_per_page);
					$m_data = $m_template;

					$c_template = (new templateModel())->column(['count(1)'])->where($where)->fetchColumn();
					$c_data = $c_template;

					if ($c_data === 0) $message = _('這位創作人目前沒有製作版型');
					break;

				default:
					break;
			}

			$data = [];
			$Image = new \Core\Image;

			foreach ($m_data as $v0) {
				$_description = (mb_strlen(strip_tags(nl2br($v0['description']), 'UTF-8')) > 36) ? mb_substr(strip_tags(nl2br($v0['description'])), 0, 35, 'UTF-8') . '...' : strip_tags(nl2br($v0['description']));

				if ($v0['cover'] && is_image(PATH_UPLOAD . $v0['cover'])) {
					if ($type == 'album') {
						$Image->set(PATH_UPLOAD . $v0['cover']);

						switch ($Image->getType()) {
							case 1:
								//2017-08-21 Lion: 不做 resize 處理
								break;

							default:
								$Image->setSize(\Config\Image::S4, \Config\Image::S4);
								break;
						}

						$cover = fileinfo($Image->save())['url'];
					} else {
						$cover = URL_UPLOAD . M_PACKAGE . $v0['cover'];
					}
				} else {
					$cover = null;
				}

				$data[] = [
					'categoryarea_id' => isset($v0['categoryarea_id']) ? $v0['categoryarea_id'] : null,
					'collect' => '',
					'cover' => $cover,
					'cover_url' => parent::url($type . '', 'content_v2', [$type . '_id' => $v0[$type . '_id'], 'click' => 'cover']),
					'description' => $_description,
					'lock' => ($v0['act'] == 'close') ? '<div class="lock_box"><span><i class="fa fa-lock"></i></span></div>' : '',
					'name' => $v0[$type . '_name'],
					'name_all' => $v0[$type . '_name'],
					'name_url' => parent::url($type . '', 'content', [$type . '_id' => $v0[$type . '_id'], 'click' => 'name']),
					'picture' => URL_STORAGE . Core::get_userpicture($v0['user_id']),
					'point' => $v0['point'],
					'qrcodeUrl' => str_replace('\\', '/', URL_STORAGE . storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $v0['user_id'] . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $v0[$type . '_id'] . DIRECTORY_SEPARATOR . 'qrcode.jpg')),
					'showPhotoUrl' => parent::url($type, 'show_photo'),
					'type' => $type,
					'type_id' => $v0[$type . '_id'],
					'user_id' => $v0['user_id'],
					'user_url' => Core::get_creative_url($v0['user_id']),
					'user_name' => $v0['user_name'],
					'viewed' => (new albumModel())->getAlbumViewed( $v0[$type . '_id']),
					'gift_tag' => ((new albumModel())->hasGiftTags( $v0[$type . '_id'])) ? '<i class="fa fa-gift"></i>' : '' ,
					'audio_tag' => ((new albumModel())->hasAudio( $v0[$type . '_id'])) ? '<i class="fa fa-volume-down"></i>' : '' ,
					'video_tag' => ((new albumModel())->hasVideoPhoto( $v0[$type . '_id'])) ? '<i class="fa fa-play-circle"></i>' : '' ,
				];
			}

			/**
			 * 下拉選單
			 */
			foreach ($data as $k0 => $v0) {
				$_share = '<li><a href="javascript:void(0)" onclick="share(\'' . $type . '\',' . $v0['type_id'] . ', \'' . $v0['qrcodeUrl'] . '\' , \'' . $v0['cover'] . '\', \'' . parent::url('album', 'content', ['album_id' => $v0['type_id'], 'autoplay' => 1, 'categoryarea_id' => $v0['categoryarea_id']]) . '\')">' . _('分享') . '</a></li>';
				$_collect = '';		//收藏相本
				$_edit = '';		//修改相本
				$_report = '';		//檢舉相本
				$_exiting  = '';	//檢舉相本

				if (!empty($login_user)) {

					if ($v0['user_id'] != $login_user['user_id']) {

						if (!Model("$type")->is_own($v0['type_id'], $login_user['user_id'])) {
							$_collect = ($type == 'album')
								? '<li data-' . $type . 'Id="' . $v0['type_id'] . '"><a onclick="buyalbum(' . $v0['type_id'] . ');" href="javascript:void(0)">' . _('Collection') . '</a></li>'
								: '<li data-' . $type . 'Id="' . $v0['type_id'] . '"><a onclick="taketemplate(' . $v0['type_id'] . ');" href="javascript:void(0)">' . _('Collection') . '</a></li>';
						}

						$cooperation =  Model('cooperation')->getCooeration('album', $v0['type_id'], $login_user['user_id']);

						if (in_array($cooperation['identity'], ['approver', 'editor']) && $rank_id == 4) {
							// 為 副管理員 or 共用 身份時，可直接進入編輯器上傳檔案
							$_edit = ($type == 'album') ? '<li><a href="'. parent::url('diy', 'index', ['album_id' => $v0['type_id']]) .'">' . _('編輯') . '</a></li>' : '';
						}

						if (in_array($cooperation['identity'], ['approver', 'editor', 'viewer']) && $rank_id == 4) {
							// 為 副管理員 or 共用 or 瀏覽 身份時，須提供退出群組按鍵
							$_exiting = '<li><a onclick="exting_cooperation(' . $v0['type_id'] . ');" href="javascript:void(0)" >' . _('退出群組') . '</a></li>';
						}

						$_report = '<li><a class="alert_btn" href="javascript:void(0)" data-type="' . $v0['type'] . '" data-type_id="' . $v0['type_id'] . '">' . _('Report') . '</a></li>';

					} else {
						$_edit = ($type == 'album') ? '<li><a href="' . parent::url('user', 'albumcontent_setting', [$type . '_id' => $v0['type_id']]) . '">' . _('編輯') . '</a></li>' : '';
					}

				} else {
					$_collect = '<li data-' . $type . 'Id="' . $v0['type_id'] . '"><a href="javascript:void(0)" onclick="buyalbum(' . $v0['type_id'] . ');">' . _('Collection') . '</a></li>';
					$_report = '<li><a class="alert_btn" href="' . parent::url('user', 'login', ['redirect' => parent::url('album', 'content', ['album_id' => $v0['type_id'], 'categoryarea_id' => $v0['categoryarea_id'], 'report' => true])]) . '">' . _('Report') . '</a></li>';
				}

				// 分享標籤先停用
				$_share = '';

				$data[$k0]['menulist'] = array_values(array_filter([$_share, $_collect,  $_edit, $_report, $_exiting], function ($value) {
					return $value !== '';
				}));
			}
			//是否需填入收藏的icon
			if (!empty($login_user)) {
				foreach ($data as $k0 => $v0) {
					$splitNum = 17;
					switch ($rank_id) {
						case 1:
						case 2:
							if ($v0['user_id'] != $login_user['user_id']) {
								$data[$k0]['collect'] = Model("$type" . 'queue')->where([[[['user_id', '=', $login_user['user_id']], [$type . '_id', '=', $v0['type_id']]], 'and']])->fetch() ? '<img src="' . static_file('images/assets-v5/icon-collection-h.svg') . '">' : '';
								$splitNum = 16;
							}
							if ($v0['lock'] != '') $splitNum = 16;
							break;

						case 3 :
							$splitNum = 16;
							$data[$k0]['collect'] = '<img src="' . static_file('images/assets-v5/icon-collection-h.svg') . '">';
							break;

						default:
							break;
					}

					$data[$k0]['name'] = (mb_strlen(strip_tags(nl2br($data[$k0]['name']), 'UTF-8')) > 45) ? mb_substr(strip_tags(nl2br($data[$k0]['name'])), 0, $splitNum, 'UTF-8') . '...' : strip_tags(nl2br($data[$k0]['name']));
				}
			}
			/**
			 * 初始化請求不須組出html, 但在ias請求中需組(印)出ias需要的html內容
			 */
			$iasData = '';
			foreach ($data as $v0) {
				$iasData .= '<div class="content_box">
					'.$v0['lock'].'
					<div class="content_box_img">
						<a href="javascript:void(0);" onclick="popview(\''.$v0['cover_url'].'\')">
							<img src="'.$v0['cover'].'" onerror="this.src=\'' . static_file('images/origin.jpg') . '\'">
						</a>
					</div>
					<div class="content_box_info">
						<div>
							<div class="content_box_icon">'.$v0['audio_tag'].$v0['video_tag'].$v0['gift_tag'].'</div>
							<div class="content_box_name" data-album_id="'.$v0['type_id'].'" title="'.$v0['name'].'">
								<a href="'.$v0['name_url'].'">'.$v0['name'].'</a>
							</div>
						</div>
						<div class="content_box_menu_btn">
                            <div class="dropdown-sign dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </div>
							<div class="dropdown-menu dropdown_menu" role="menu">
								<ul>' . implode('', $v0['menulist']) . '</ul>
							</div>
						</div>
					</div>
				</div>';
			}

			//more
			$tmp0 = array();
			$num_of_item = $c_data;
			$num_of_max_page = ceil($num_of_item / $num_of_per_page);
			$num_of_now_page = (1 <= $page && $page <= $num_of_max_page) ? $page : 1;
			if ($rank_id !== null) $tmp0['rank_id'] = $rank_id;
			if ($c_searchkey !== null) $tmp0['c_searchkey'] = $c_searchkey;
			if ($page >= $num_of_max_page) {
				$tmp0['user_id'] = $user_id;
				$more = null;
			} else {
				$tmp0['user_id'] = $user_id;
				$more = parent::url('creative', 'getItem', $tmp0);
			}

			($initial) ? json_encode_return(1, $message, null, ['more' => $more, 'item' => $data]) : print_r($iasData);

		}
    }

    function index()
    {
        $user = parent::user_get();
        $page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
        $num_of_per_page = 10;//一頁幾個
        $a_creative = array();
        $tmp = array();

        /**
         * banner 區 八大類
         */
        $creative_group = Model('creative')->creative_group();
        parent::$data['creative_group'] = $creative_group;

        /**
         * 分類清單
         */
        $categoryarea = Model('categoryarea')->column(['categoryarea_id', 'name'])->where([[[['act', '=', 'open']], 'and']])->fetchAll();
        parent::$data['categoryarea'] = $categoryarea;

        /**
         * 搜尋
         */
        $searchtype = isset($_GET['searchtype']) ? urldecode($_GET['searchtype']) : null;
        parent::$data['searchtype'] = $searchtype;
        $searchkey = (isset($_GET['searchkey']) && $_GET['searchkey'] !== '') ? urldecode($_GET['searchkey']) : null;
        parent::$data['searchkey'] = htmlspecialchars($searchkey);

        /**
         * 0:最新  1:熱門
         */
        $rank_id = ((!empty($_GET['rank_id'])) && in_array($_GET['rank_id'], [0, 1])) ? $_GET['rank_id'] : 0;
        parent::$data['rank_id'] = $rank_id;
        $rank_name = [_('Latest'), _('Hot')];
        parent::$data['rank_name'] = $rank_name;
        /**
         * 搜尋資料
         */
        if ($rank_id !== null) $tmp['rank_id'] = $rank_id;
        if ($searchkey !== null) {
            $tmp['searchtype'] = $searchtype;
            $tmp['searchkey'] = $searchkey;
        }

        $rank0 = self::url('creative', 'index', array_merge($tmp, ['rank_id' => 0]));
        $rank1 = self::url('creative', 'index', array_merge($tmp, ['rank_id' => 1]));

        parent::$data['rank0'] = $rank0;
        parent::$data['rank1'] = $rank1;

        if ($searchkey !== null) {
            if (!empty($_GET['area']) && $_GET['area'] == 'true') {
                //取得分類內的user_id
                $creative_group = Model('creative')->creative_group([$searchkey]);
                $where = array();
                foreach ($creative_group[0]['sort'] as $k0 => $v0) {
                    $tmp = ['_area_', '=', $v0['user_id']];
                    $where[] = $tmp;
                }
                $s_user = Solr('user')->column(['user_id'])->where([[$where, 'or']])->fetchAll();
            } else {
                $s_user = Solr('user')->column(['user_id'])->where([[[['_text_', '=', $searchkey]], 'and']])->fetchAll();
            }
            if (empty($s_user)) {
                $a_user = [];
                $c_user = 0;
                goto _relay0;
            }
        }

        switch ($rank_id) {
            default:
                $column = [
                    'user.user_id',
                    'user.description',
                    'user.name user_name',
                    'follow.count_from + userstatistics.followfrom_manual count_from',
                    'creative.inserttime creative_inserttime'
                ];

                $where = [[[['user.act', '=', 'open']], 'and']];
                if (!empty($s_user)) $where = array_merge($where, [[[['user.user_id', 'in', array_column($s_user, 'user_id')]], 'and']]);

                $join = [
                    ['left join', 'follow', 'using(`user_id`)'],
                    ['left join', 'creative', 'using(`user_id`)'],
                    ['INNER JOIN', 'userstatistics', 'on userstatistics.user_id = user.user_id'],
                ];
                $m_user = (new userModel())->column($column)->where($where)->join($join)->order(['creative_inserttime' => 'desc'])->limit($num_of_per_page * ($page - 1) . ',' . $num_of_per_page)->fetchAll();
                $c_user = (new userModel())->column(['user.user_id'])->join($join)->group(['user.user_id'])->where($where)->fetchAll();

                foreach ($m_user as $k0 => $v0) {
                    $viewed = (new userModel)->getUserViewed($v0['user_id']);
                    $a_user[] = [
                        'user_id' => $v0['user_id'],
                        'description' => $v0['description'],
                        'user_name' => $v0['user_name'],
                        'count_from' => $v0['count_from'],
                        'creative_inserttime' => $v0['creative_inserttime'],
                        'viewed' => $viewed,
                    ];
                }

                break;
        }

        _relay0:

        $user_id_pool = array();
        foreach ($a_user as $k => $v0) {
            $user_id_pool[] = $v0['user_id'];
        }  //$user_id_pool = collect this page album_id
        $data = [];
        $s_param = [];
        foreach ($a_user as $k0 => $v0) {
            $a_param = $s_param;
            $data[$k0] = array(
                'user_id' => $v0['user_id'],
                'cover_url' => Core::get_creative_url($v0['user_id']),
                'name_url' => Core::get_creative_url($v0['user_id']),
                'name' => htmlspecialchars($v0['user_name']),
                'description' => $v0['description'],
                'picture' => URL_STORAGE . Core::get_userpicture($v0['user_id']),
                'follow' => (is_null($v0['count_from'])) ? 0 : $v0['count_from'],
                'viewed' => (is_null($v0['viewed'])) ? 0 : $v0['viewed'],
                'creative_belong' => Model('creative')->creative_belong($v0['user_id']),
            );
        }
        parent::$data['creator'] = $data;

        //more
        $num_of_item = count($c_user);
        $num_of_max_page = ceil($num_of_item / $num_of_per_page);
        $more = ($page >= $num_of_max_page) ? null : parent::url('creative', 'index', array_merge($tmp, ['rank_id' => $rank_id], $s_param));
        parent::$data['more'] = $more;

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Author’s'),
            array(_('Author’s'), _('Recruitment'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        //owl
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.carousel.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.theme.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.transitions.css'), 'href');
        parent::$html->set_js(static_file('js/owl.carousel/js/owl.carousel.min.js'), 'src');
        parent::$html->set_js(static_file('js/imagesloaded.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/masonry/js/masonry.pkgd.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.infinitescroll.min.js'), 'src');

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }

    function info()
    {
        $user_id = empty($_GET['user_id']) ? redirect(parent::url('creative', 'index'), _('User does not exist.')) : $_GET['user_id'];

        $Model_user = (new \userModel)
            ->column(['description'])
            ->where([[[['user_id', '=', $user_id], ['act', '=', 'open']], 'and']])
            ->fetch();

        if (empty($Model_user)) redirect(parent::url('creative', 'index'), _('User does not exist.'));

        //event
        parent::$data['user'] = [
            'description' => $Model_user['description'],
        ];

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function pinpinboard($type, $type_id)
    {
        $user = parent::user_get();
        $column = [
            'user.name user_name',
            'pinpinboard.*',
        ];
        $m_pinpinboard = (new pinpinboardModel)->column($column)->join([['left join', 'user', 'using(user_id)']])->where([[[['type', '=', $type], ['type_id', '=', $type_id], ['pinpinboard.act', '=', 'open']], 'and']])->order(['pinpinboard.inserttime' => 'desc'])->fetchAll();

        $a_pinpinboard = [];
        if (!empty($m_pinpinboard)) {
            foreach ($m_pinpinboard as $k0 => $v0) {
                $a_pinpinboard[] = [
                    'pinpinboard_id' => $v0['pinpinboard_id'],
                    'authorName' => $v0['user_name'],
                    'authorUrl' => Core::get_creative_url($v0['user_id']),
                    'time' => date('Y/m/d h:m:s', strtotime($v0['inserttime'])),
                    'text' => $v0['text'],
                    'picture' => URL_STORAGE . Core::get_userpicture($v0['user_id']),
                    'act' => ($user['user_id'] == $v0['user_id']) ? '<span onclick="delComment(' . $v0['pinpinboard_id'] . ')" aria-hidden="true">&times;</span><span class="sr-only"></span>' : null,
                ];
            }
        }
        return $a_pinpinboard;
    }

    function recruit()
    {
        //#1387 此頁暫不接受訪問
        redirect(parent::url('about'));

        $m_creative = Model('creative')->column(array('count(1)'))->fetchColumn();

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Recruitment'),
            array(_('Author’s'), _('Recruitment'))
        );

        parent::$data['count'] = $m_creative;
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->jbox();
        //photoswipe
        parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/photoswipe.css'), 'href');
        parent::$html->set_css(static_file('js/PhotoSwipe-master/dist/default-skin/default-skin.css'), 'href');
        parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe.min.js'), 'src');
        parent::$html->set_js(static_file('js/PhotoSwipe-master/dist/photoswipe-ui-default.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fittext.js'), 'src');
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function report()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $url = !empty($_POST['url']) ? $_POST['url'] : null;

            if (empty($user)) {
                json_encode_return(0, _('Please login first.'), parent::url('user', 'login', ['redirect' => $url]));
            }

            $value = !empty($_POST['value']) ? $_POST['value'] : null;
            $text = !empty($_POST['text']) ? $_POST['text'] : null;
            $type = !empty($_POST['type']) ? $_POST['type'] : null;
            $type_id = !empty($_POST['type_id']) ? $_POST['type_id'] : null;

            if ($type_id == null) {
                json_encode_return(0, _('Abnormal process, please try again.'));
            }

            $where = array(
                array(array(array($type . '_id', '=', $type_id)), 'and'),
            );
            $m_type = Model($type)->where($where)->fetch();
            if (empty($m_type) || $m_type['act'] != 'open') {
                json_encode_return(0, _('Album does not exist.'));
            }

            /**
             * 同作品重複檢舉且未處理數量超過三筆 , 十分鐘內檢舉過
             */
            $where = array(
                array(array(array('user_id', '=', $user['user_id']), array('id', '=', $type_id), array('type', '=', $type), array('state', '=', 'pretreat')), 'and'),
            );
            $m_report = Model('report')->where($where)->order(array('inserttime' => 'desc'))->fetchAll();
            if (!empty($m_report[0]['inserttime'])) {
                if (strtotime('+10 minute', strtotime($m_report[0]['inserttime'])) >= time()) json_encode_return(0, _('This operation cannot redo within 10 minutes.'));
            }

            if (count($m_report) > 3) json_encode_return(0, _('You have been report this album, we will deal with as soon as possible.'));

            $add = array(
                'reportintent_id' => $value,
                'user_id' => $user['user_id'],
                'type' => $type,
                'id' => $type_id,
                'description' => $text,
                'state' => 'pretreat',
                'inserttime' => inserttime(),
            );

            Model('report')->add($add);

            json_encode_return(1, _('Your report has been sent, we will deal with as soon as possible, thanks.'));
        }
        die;
    }

	/**
	 * 準備棄用改用新版 Mars - 181204
	 */
    function saveAboutEdit()
    {
        if (is_ajax()) {
            $user_id = (!empty($_POST['user_id'])) ? $_POST['user_id'] : null;
            $text = (!empty($_POST['text'])) ? $_POST['text'] : null;

            $result = (new userModel())->where([[[['user_id', '=', $user_id]], '=']])->edit(['description' => $text]);

            if ($result) {
                json_encode_return(1);
            } else {
                json_encode_return(0);
            }

        }
    }

	function settings_phone()
	{
		if (is_ajax()) {
			$usefor = (!empty($_POST['usefor'])) ? $_POST['usefor'] : null;

			if ($usefor == null || !in_array($usefor, ['sms_send', 'editcellphone'])) json_encode_return(0, _('[Request] occur exception, please contact us.'), null, 'Modal');

			$user = (new \userModel)->getSession();

			$cellphone = (isset($_POST['cellphone']) && trim($_POST['cellphone']) !== '') ? trim($_POST['cellphone']) : null;

			switch ($usefor) {
				//發送驗證簡訊
				case 'sms_send' :
					list ($result, $message) = array_decode_return(\smspasswordModel::ableToRequestSMSPasswordForUpdateCellphone($user['user_id'], $cellphone));
					if ($result != \Lib\Result::SYSTEM_OK) {
						json_encode_return(0, $message);
					}

					\smspasswordModel::requestSMSPasswordForUpdateCellphone($user['user_id'], $cellphone);

					json_encode_return(1, _('Validation code has been sent.'));
					break;

				case 'editcellphone':
					$smspassword = (isset($_POST['smspassword']) && trim($_POST['smspassword']) !== '') ? trim($_POST['smspassword']) : null;

					list ($result, $message) = array_decode_return(\userModel::ableToUpdateCellphone($user['user_id'], $cellphone, $smspassword));
					if ($result != \Lib\Result::SYSTEM_OK) {
						json_encode_return(0, $message);
					}

					(new Model)->beginTransaction();

					\userModel::updateCellphone($user['user_id'], $cellphone);

					(new Model)->commit();

					json_encode_return(1, _('驗證成功，手機號碼更新完成'), parent::url('creative', 'edit', ['user_id' => $user['user_id'] ]));
					break;
			}

			json_encode_return(0, _('[Request] occur exception, please contact us.'), null, 'Modal');
		}
	}

    function update() {
		if (is_ajax()) {
			$data = $_POST['data'];
			$user = parent::user_get();
			if(empty($data['name'])) json_encode_return(0, _('請填寫暱稱'));
			if(empty($data['email'])) json_encode_return(0, _('請填寫信箱'));

			$user_hobby[0] = !empty($data['hobby_0']) ? $data['hobby_0'] : null;
			$user_hobby[1] = !empty($data['hobby_1']) ? $data['hobby_1'] : null;
			$user_hobby[2] = !empty($data['hobby_2']) ? $data['hobby_2'] : null;
			//檢查未選擇興趣
			if (count(array_filter($user_hobby)) == 0) json_encode_return(0, _('Select at least one interest.'));
			//檢查重複選取興趣
			foreach ($user_hobby as $k0 => $v0) { if (is_null($v0)) unset($user_hobby[$k0]); }
			if (count($user_hobby) != count(array_unique($user_hobby))) json_encode_return(0, _('Can\'t select same hobby, please select again.'));

			if ($data['address_id_1st'] != 1) $data['address_id_2nd'] = 0;

			$edit = [
				'name' => $data['name'],
				'email' => $data['email'],
				'creative_code' => $data['creative_code'],
				'birthday' => $data['birthday'],
				'gender' => $data['gender'],
				'relationship' => $data['relationship'],
				'address_id_1st' => $data['address_id_1st'],
				'address_id_2nd' => $data['address_id_2nd'],
				'discuss' => $data['discuss'],
				'newsletter' => $data['newsletter'],
			];

			(new userModel())->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit($edit);

			(new hobby_userModel())->setHobbyToUser($user['user_id'], $user_hobby);

			json_encode_return(1, _('資料更新完成。'));
		}
	}

	function updatePassword() {
		if (is_ajax()) {
			$data = $_POST['data'];
			$user = parent::user_get();
			$userModel = (new userModel());
			$c_userPassword = $userModel->column(['password'])->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetchColumn();

			// 驗證舊密碼
			if (!password_verify($data['old_pass'], $c_userPassword)) json_encode_return(0, _('舊密碼輸入錯誤。'));

			$renewPassword = $userModel->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['password' => password_hash($data['new_pass'], PASSWORD_DEFAULT)]);

			if (!$renewPassword) {
				json_encode_return(0, _('occur exception, please contact us.'));
			} else {
				$userModel->logout();
				json_encode_return(1, _('密碼修改完成，請重新登入。'), parent::url('user', 'login', ['redirect' => parent::url('creative', 'edit', ['user_id' => $user['user_id']])]));
			}
		}
	}

	function updateCreative() {
		if (is_ajax()) {
			$data = empty($_POST['data']) ? null : $_POST['data'];
			$creative_name = empty($_POST['creative_name']) ? null : $_POST['creative_name'];
			$user = parent::user_get();

			$updateSociallink = (new userModel())->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['sociallink' => $data, 'creative_name' => $creative_name]);

			if (!$updateSociallink) {
				json_encode_return(0, _('occur exception, please contact us.'));
			} else {
				json_encode_return(1, _('專區資料修改完成。'));
			}
		}
	}

	function updateDescription () {
		if (is_ajax()) {
			$user_description = empty($_POST['user_description']) ? null : $_POST['user_description'];
			$user = parent::user_get();

			$updateDescription = (new userModel())->where([[[['user_id', '=', $user['user_id']]], 'and']])->edit(['description' => $user_description]);

			if (!$updateDescription) {
				json_encode_return(0, _('occur exception, please contact us.'));
			} else {
				json_encode_return(1, _('"關於我"內容修改完成。'));
			}
		}
	}
} 