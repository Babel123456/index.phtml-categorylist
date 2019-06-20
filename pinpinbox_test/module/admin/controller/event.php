<?php

class eventController extends backstageController
{
    function __construct()
    {
    }

    function index()
    {
        list($html0, $js0) = parent::$html->grid();
        list($html1, $js1) = parent::$html->browseKit(['selector' => '.grid-img']);
        parent::$data['index'] = $html0 . $html1;
        parent::$html->set_js($js0 . $js1);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function form()
    {
        if (is_ajax()) {
            //form
            $name = $_POST['name'];
            $title = $_POST['title'];
            $image = $_POST['image'];
            $image_promote = $_POST['image_promote'];
            $image_960x540 = $_POST['image_960x540'];
            $description = $_POST['description'];
            $a_award = $_POST['award'];
            $vote = $_POST['vote'];
            $company = $_POST['company'];
            $contribution = $_POST['contribution'];
            $show_rank_num = $_POST['show_rank_num'];
            $starttime = $_POST['starttime'];
            $endtime = $_POST['endtime'];
            $contribute_starttime = $_POST['contribute_starttime'];
            $contribute_endtime = $_POST['contribute_endtime'];
            $vote_starttime = $_POST['vote_starttime'];
            $vote_endtime = $_POST['vote_endtime'];
            $index_display = $_POST['index_display'];
            $exchange_page = $_POST['exchange_page'];
            $prefix_text = $_POST['prefix_text'];
            $act = $_POST['act'];

            $add_company = [];
            if (!empty($company)) {
                foreach ($company as $v) {
                    $add_company[] = [
                        'company_id' => $v,
                        'inserttime' => inserttime(),
                    ];
                }
            }

            switch ($_GET['act']) {
                //新增
                case 'add':
                    if (Model(M_CLASS)->column(['count(1)'])->where([[[['name', '=', $name]], 'and']])->fetchColumn()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //event
                    $add = [
                        'name' => $name,
                        'title' => $title,
                        'image' => $image,
                        'image_promote' => $image_promote,
                        'image_960x540' => $image_960x540,
                        'description' => $description,
                        'award' => json_encode($a_award),
                        'vote' => $vote,
                        'contribution' => $contribution,
                        'show_rank_num' => $show_rank_num,
                        'starttime' => $starttime,
                        'endtime' => $endtime,
						'contribute_starttime' => $contribute_starttime,
                        'contribute_endtime' => $contribute_endtime,
						'vote_starttime' => $vote_starttime,
                        'vote_endtime' => $vote_endtime,
                        'index_display' => $index_display,
                        'exchange_page' => $exchange_page,
                        'prefix_text' => $prefix_text,
                        'act' => $act,
                        'inserttime' => inserttime(),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ];
                    $M_CLASS_id = Model(M_CLASS)->add($add);

                    //eventstatistics
                    Model('eventstatistics')->add(['event_id' => $M_CLASS_id]);

                    //event_companyjoin
                    if (!empty($add_company)) {
                        foreach ($add_company as $k0 => $v0) {
                            $add_company[$k0]['event_id'] = $M_CLASS_id;
                        }
                        Model('event_companyjoin')->add($add_company);
                    }

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    if (Model(M_CLASS)->column(['count(1)'])->where([[[[M_CLASS . '_id', '!=', $M_CLASS_id], ['name', '=', $name]], 'and']])->fetchColumn()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //form
                    $edit = [
                        'name' => $name,
                        'title' => $title,
                        'image' => $image,
                        'image_promote' => $image_promote,
                        'image_960x540' => $image_960x540,
                        'description' => $description,
                        'award' => json_encode($a_award),
                        'vote' => $vote,
                        'contribution' => $contribution,
                        'show_rank_num' => $show_rank_num,
                        'starttime' => $starttime,
                        'endtime' => $endtime,
						'contribute_starttime' => $contribute_starttime,
						'contribute_endtime' => $contribute_endtime,
						'vote_starttime' => $vote_starttime,
						'vote_endtime' => $vote_endtime,
                        'index_display' => $index_display,
                        'exchange_page' => $exchange_page,
                        'prefix_text' => $prefix_text,
                        'act' => $act,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ];
                    Model(M_CLASS)->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])->edit($edit);

                    //event_companyjoin
                    if (!empty($add_company)) {
                        Model('event_companyjoin')->where([[[['event_id', '=', $M_CLASS_id]], 'and']])->delete();
                        Model('event_companyjoin')->add($add_company);
                    }

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始值-from
        $name = null;
        $title = null;
        $image = null;
        $image_promote = null;
        $image_960x540 = null;
        $description = null;
        $a_award = [
            ['id' => 0, 'key' => '第一名', 'value' => '', 'remark' => '僅說明'],
            ['id' => 1, 'key' => '第二名', 'value' => '', 'remark' => '僅說明'],
            ['id' => 2, 'key' => '第三名', 'value' => '', 'remark' => '僅說明'],
        ];
        $vote = 0;
        $contribution = 0;
        $show_rank_num = 0;
        $starttime = null;
        $endtime = null;
        $contribute_starttime = null;
        $contribute_endtime = null;
        $vote_starttime = null;
        $vote_endtime = null;
        $viewed = null;
        $prefix_text = null;
        $act = 'close';
        $a_company = [];
        $index_display = 0;
        $exchange_page = 0;
        $index_display_chekcbox = null;
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;

        //form
        $column = [];
        $extra = null;

        //tabs
        $a_tabs = [];

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'add']);
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];

                    $m_event = Model(M_CLASS)->where([[[[M_CLASS . '_id', '=', $M_CLASS_id]], 'and']])->fetch();

                    $m_event_companyjoin = (new \event_companyjoinModel)
                        ->join([['left join', 'company', 'using(company_id)']])
                        ->where([[[['event_companyjoin.event_id', '=', $M_CLASS_id], ['company.act', '=', 'open']], 'and']])
                        ->fetchAll();

                    foreach ($m_event_companyjoin as $v) {
                        $a_company[] = $v['company_id'];
                    }

                    //form
                    $name = $m_event['name'];
                    $title = $m_event['title'];
                    $image = $m_event['image'];
                    $image_promote = $m_event['image_promote'];
                    $image_960x540 = $m_event['image_960x540'];
                    $description = $m_event['description'];
                    $a_award = json_decode($m_event['award'], true);
                    $vote = $m_event['vote'];
                    $show_rank_num = $m_event['show_rank_num'];
                    $contribution = $m_event['contribution'];
                    $starttime = $m_event['starttime'];
                    $endtime = $m_event['endtime'];
                    $contribute_starttime = $m_event['contribute_starttime'];
                    $contribute_endtime = $m_event['contribute_endtime'];
                    $vote_starttime = $m_event['vote_starttime'];
                    $vote_endtime = $m_event['vote_endtime'];
                    $index_display = $m_event['index_display'];
                    $exchange_page = $m_event['exchange_page'];
                    $prefix_text = $m_event['prefix_text'];
                    $act = $m_event['act'];
                    $inserttime = $m_event['inserttime'];
                    $modifytime = $m_event['modifytime'];
                    $modifyadmin_name = adminModel::getOne($m_event['modifyadmin_id'])['name'];
                    $viewed = (new \eventstatisticsModel)
                        ->column(['viewed'])
                        ->where([[[['event_id', '=', $M_CLASS_id]], 'and']])
                        ->fetchColumn();
                }

                $index_display_chekcbox = ($index_display) ? 'checked="checked"' : null;

                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', ['act' => 'edit']);
                break;
        }

        list($html, $js) = parent::$html->text('id="name" name="name" value="' . $name . '" size="64" maxlength="64" required');
        $column[] = array('key' => _('Name'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->text('id="title" name="title" value="' . $title . '" size="128" maxlength="128"');
        $column[] = array('key' => _('Title'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image" name="image" value="' . $image . '" required');
        $column[] = array('key' => _('Image'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image_promote" name="image_promote" value="' . $image_promote . '" required');
        $column[] = array('key' => _('Image_promote'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image_960x540" name="image_960x540" value="' . $image_960x540 . '" required', null, 960, 540);
        $column[] = ['key' => _('Image') . ' (960 x 540)', 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->ckeditor('id="description" name="description" required', $description);
        $column[] = array('key' => _('Description'), 'value' => $html);
        parent::$html->set_js($js);

        $m_company = (new \companyModel)
            ->where([[[['act', '=', 'open']], 'and']])
            ->fetchAll();
        $s_company = array();
        foreach ($m_company as $v0) {
            $s_company[] = array(
                'value' => $v0['company_id'],
                'text' => $v0['company_id'] . '-' . $v0['name'],
            );
        }

        list($html, $js) = parent::$html->selectKit(['id' => 'company', 'name' => 'company', 'multiple' => true], $s_company, $a_company);
        $column[] = array('key' => _('Company'), 'value' => $html, 'key_remark' => '合作廠商');
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->dynamictable('keyvalueremark', 'name="award[]"', $a_award);
        $column[] = array('key' => _('Award'), 'value' => $html, 'key_remark' => '(由於這裡僅供顯示並不是做設定，說明要跟活動實際內容符合哦，Ex：獎項)');
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="vote" name="vote" value="' . $vote . '" min="0" max="9" required');
        $column[] = array('key' => _('Vote'), 'value' => $html, 'key_remark' => '(此活動可以讓用戶投多少票)');
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="contribution" name="contribution" value="' . $contribution . '" min="0" max="9" required');
        $column[] = array('key' => _('Contribution'), 'value' => $html, 'key_remark' => '(此活動可以讓用戶投稿多少相本)');
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="show_rank_num" name="show_rank_num" value="' . $show_rank_num . '" min="0" max="99" required');
        $column[] = array('key' => _('Show_rank_num'), 'value' => $html, 'key_remark' => '(參賽相本頁面顯示名次的數量)');
        parent::$html->set_js($js);

        $column[] = array('key' => _('Viewed'), 'value' => $viewed);

        list($html, $js) = parent::$html->datetime('id="starttime" name="starttime" value="' . $starttime . '" required');
        $column[] = array('key' => _('Start Time'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->datetime('id="endtime" name="endtime" value="' . $endtime . '" required');
        $column[] = array('key' => _('End Time'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->datetime('id="contribute_starttime" name="contribute_starttime" value="' . $contribute_starttime . '" ');
        $column[] = array('key' => _('Contribute Start Time'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->datetime('id="contribute_endtime" name="contribute_endtime" value="' . $contribute_endtime . '" ');
        $column[] = array('key' => _('Contribute End Time'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->datetime('id="vote_starttime" name="vote_starttime" value="' . $vote_starttime . '" ');
        $column[] = array('key' => _('Vote Start Time'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->datetime('id="vote_endtime" name="vote_endtime" value="' . $vote_endtime . '" ');
        $column[] = array('key' => _('Vote End Time'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->checkbox('id="index_display" ' . $index_display_chekcbox);
        $column[] = array('key' => _('首頁展示'), 'value' => $html, 'key_remark' => '(活動時間到期前台將會自動關閉)');
        parent::$html->set_js($js);

		list($html, $js) = parent::$html->text('id="prefix_text" name="prefix_text" value="' . $prefix_text . '" size="64" maxlength="64"');
		$column[] = array('key' => _('前綴提示文字'), 'value' => $html);
		parent::$html->set_js($js);

        /*****/
        $a_exchange_page = [
            [
                'name' => 'exchange_page',
                'value' => 0,
                'text' => 'False',
            ], [
                'name' => 'exchange_page',
                'value' => 1,
                'text' => 'True',
            ],
        ];
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_exchange_page, $exchange_page);
        $column[] = array('key' => _('Exchange Page'), 'value' => $html);
        parent::$html->set_js($js);
        /*****/

        $a_act = array();
        foreach (json_decode(Core::settings('EVENT_ACT'), true) as $k0 => $v0) {
            $a_act[] = [
                'name' => 'act',
                'value' => $k0,
                'text' => $v0,
            ];
        }
        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_act, $act);
        $column[] = array('key' => _('Act'), 'value' => $html);
        parent::$html->set_js($js);

        $column[] = array('key' => _('Insert Time'), 'value' => $inserttime);

        $column[] = array('key' => _('Modify Time'), 'value' => $modifytime);

        $column[] = array('key' => _('Modify Admin Name'), 'value' => $modifyadmin_name);

        list($html0, $js0) = parent::$html->submit('value="' . _('Submit') . '"');
        list($html1, $js1) = parent::$html->back('value="' . _('Back') . '"');
        $column[] = array('key' => '&nbsp;', 'value' => $html0 . '&emsp;' . $html1);
        parent::$html->set_js($js0 . $js1);

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[0] = array('href' => '#tabs-0', 'name' => _('Form'), 'value' => $html);
        parent::$html->set_js($js);

        //eventjoin
        $column = array();
        $extra = null;

        list($html, $js) = parent::$html->grid('eventjoin-grid');
        $column[] = array('key' => _('Event Join'), 'value' => $html);
        parent::$html->set_js($js);
        parent::$data[M_CLASS . '_id'] = empty($M_CLASS_id) ? '[]' : $M_CLASS_id;

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[1] = array('href' => '#tabs-1', 'name' => _('Event Join'), 'value' => $html);
        parent::$html->set_js($js);

        //eventvote
        $column = array();
        $extra = null;

        list($html, $js) = parent::$html->grid('eventvote-grid');
        $column[] = array('key' => _('Event Vote'), 'value' => $html);
        parent::$html->set_js($js);
        parent::$data[M_CLASS . '_id'] = empty($M_CLASS_id) ? '[]' : $M_CLASS_id;

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[2] = array('href' => '#tabs-2', 'name' => _('Event Vote'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->tabs($a_tabs);
        $formcontent = $html;
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->form('id="form"', $formcontent);
        parent::$data['form'] = $html;
        parent::$html->set_js($js);

        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function delete()
    {
        die;
    }

    function json()
    {
        $response = [];

        $case = isset($_POST['case']) ? $_POST['case'] : null;

        switch ($case) {
            default:
                //column
                $column = [
                    M_CLASS . '_id',
                    'name',
                    'image',
                    'image_promote',
                    'vote',
                    'contribution',
                    'starttime',
                    'endtime',
                    'act',
                    'modifytime',
                ];

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    if (!empty($v0['image'])) {
                        $v0['image'] = parent::get_gird_img(array('alt' => $v0['name'], 'src' => $v0['image']));
                    }
                    if (!empty($v0['image_promote'])) {
                        $v0['image_promote'] = parent::get_gird_img(array('alt' => $v0['name'], 'src' => $v0['image_promote']));
                    }
                    $v0['eventjoinX'] = Model('eventjoin')->column(['count(1)'])->where([[[['event_id', '=', $v0['event_id']]], 'and']])->fetchColumn();
                    $v0['eventvoteX'] = Model('eventvote')->column(['count(1)'])->where([[[['event_id', '=', $v0['event_id']]], 'and']])->fetchColumn();
                    $v0['viewed'] = Model('eventstatistics')->column(['viewed'])->where([[[['event_id', '=', $v0['event_id']]], 'and']])->fetchColumn();
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = Model(M_CLASS)->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;

            case 'eventjoin':
                //column
                $column = [
                    'album_id',
                    'inserttime',
                ];

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = Model('eventjoin')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['albumX'] = parent::get_grid_display('album', $v0['album_id']);
                    $v0['features'] = '<a class="list" href="javascript:void(0)" onclick="removeAlbum(' . $v0['album_id'] . ')">撤下投稿相本</a>';
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = Model('eventjoin')->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;

            case 'eventvote':
                //column
                $column = [
                    'user_id',
                    'album_id',
                    'inserttime',
                ];

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = Model('eventvote')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
                    $v0['albumX'] = parent::get_grid_display('album', $v0['album_id']);
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = Model('eventvote')->column(['count(1)'])->where($where)->group($group)->fetchColumn();
                break;
        }

        die(json_encode($response));
    }

    function remove_album()
    {
        $album_id = !empty($_POST['album_id']) ? $_POST['album_id'] : null;
        $event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : null;
        if ($album_id == null || $event_id == null) json_encode_return(0, _('資料不完整, 請重新操作'), null, null);

        $where = array();
        $where[] = array(array(array('album_id', '=', $album_id), array('event_id', '=', $event_id)), 'and');
        Model('eventjoin')->where($where)->delete();
        Model('eventvote')->where($where)->delete();
        json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'), null);
    }
}