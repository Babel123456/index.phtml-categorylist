<?php

class templateController extends backstageController
{
    function __construct()
    {
    }

    function index()
    {
        list($html0, $js0) = parent::$html->grid();
        list($html1, $js1) = parent::$html->browseKit(array('selector' => '.grid-img'));
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
            $style_id = $_POST['style_id'];
            $event_id = (!empty($_POST['event_id'])) ? $_POST['event_id'] : 0;
            $name = $_POST['name'];
            $a_instruction = $_POST['instruction'];
            $description = $_POST['description'];
            $width = $_POST['width'];
            $height = $_POST['height'];
            $point = $_POST['point'];
            $state = (!empty($_POST['state'])) ? $_POST['state'] : null;
            $sequence = $_POST['sequence'];
            $act = $_POST['act'];
            $image1 = $_POST['image1'];
            $image2 = $_POST['image2'];
            $image3 = $_POST['image3'];

            if (!empty($a_instruction)) {
                foreach ($a_instruction as $k => $v) {
                    if (empty($v['remark'])) $a_instruction[$k]['remark'] = date('y/m/d H:i');
                }
            }
            switch ($_GET['act']) {
                //新增
                case 'add':

                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    if (Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    $m_template = Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetch();
                    $template_dir = dirname($m_template['image']);

                    //promote
                    foreach ([$image1, $image2, $image3] as $k0 => $v0) {
                        if (is_image(PATH_UPLOAD . $v0)) {
                            if (strpos(basename($v0), 'promote') !== 0) {
                                $new_file = PATH_UPLOAD . 'pinpinbox' . $template_dir . '/promote_' . basename($v0);
                                copy(PATH_UPLOAD . $v0, $new_file);
                                $file_name = image_reformat($new_file, 'jpeg');
                                unlink($new_file);
                                $tmp_promote_img[] = str_replace('pinpinbox', '', $file_name);
                            } else {
                                $tmp_promote_img[] = str_replace('pinpinbox', '', $v0);
                            }
                        }
                    }

                    if (isset($tmp_promote_img) && $tmp_promote_img) {
                        foreach ($tmp_promote_img as $v_0) {
                            if (is_file(PATH_UPLOAD . 'pinpinbox' . $v_0)) {
                                \Extension\aws\S3::upload(PATH_UPLOAD . 'pinpinbox' . $v_0);
                            }
                        }
                    }

                    //form
                    $tmp0 = [
                        'style_id' => $style_id,
                        'name' => $name,
                        'instruction' => json_encode($a_instruction),
                        'description' => $description,
                        'width' => $width,
                        'height' => $height,
                        'point' => $point,
                        'state' => $state,
                        'image_promote' => json_encode($tmp_promote_img),
                        'image' => $tmp_promote_img[0],
                        'sequence' => $sequence,
                        'act' => $act,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    ];

                    /**
                     * 審核完成=>將template.frame_upload內的src存到storage下
                     * 驗證原本的state狀態，避免因更改其他資訊造成state = success 重複執行
                     */
                    if ($state == 'success' && $m_template['state'] != 'success') {
                        $column = ['blank', 'frame_id'];
                        $m_frame_resource = Model('frame_resource')->column($column)->where(array(array(array(array('act', '=', 'open')), 'and')))->fetchAll();

                        $upload_path = PATH_UPLOAD . 'pinpinbox';
                        $frame_path = SITE_LANG . '/user/' . $m_template['user_id'] . '/template/' . $m_template['template_id'] . '/';
                        mkdir_p(PATH_STORAGE, $frame_path);

                        $template_frame_id = array();
                        foreach (json_decode($m_template['frame_upload'], true) as $k => $v) {
                            //封面
                            $file_name = uniqid() . '.png';

                            //將upload內的樣板放到storage下
                            if (!copy($upload_path . $v['src'], PATH_STORAGE . $frame_path . $file_name)) {
                                json_encode_return(0, _('File copy fail'));
                            }
                            $param = array();
                            $param['name'] = $m_template['name'];
                            $param['user_id'] = $m_template['user_id'];
                            $param['template_id'] = $m_template['template_id'];
                            $param['url'] = 'template/' . $m_template['template_id'] . '/' . $file_name;
                            $param['resource'] = $v['resource'];
                            foreach ($m_frame_resource as $k2 => $v2) {
                                if ($v2['frame_id'] == $v['resource']) {
                                    $param['blank'] = $v2['blank'];
                                }
                            }
                            $param['inserttime'] = inserttime();
                            $param['modifyadmin_id'] = adminModel::getSession()['admin_id'];
                            $template_frame_id[] = Model('frame')->add($param);

                        }
                        //此template包含的frame_id
                        $tmp0['frame'] = json_encode($template_frame_id);
                    }
                    $tmp0['kind'] = ($m_template['kind'] == 'basic') ? 'basic' : 'advanced';

                    /**
                     * 審核失敗=>填寫失敗原因
                     * 驗證原本的state狀態，避免因更改其他資訊造成state = success 重複執行
                     */
                    if ($state == 'fail') {
                        //目前無動作，未來可加入mail提示創作者等等
                    }

                    Model(M_CLASS)->zip($M_CLASS_id);
                    Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->edit($tmp0);

                    /**
                     * 配合活動資料 event_templatejoin
                     */
                    $m_event_templatejoin = Model('event_templatejoin')->where(array(array(array(array('template_id', '=', $M_CLASS_id)), 'and')))->fetchAll();
                    if ($event_id == 0) {
                        Model('event_templatejoin')->where([[[['template_id', '=', $M_CLASS_id]], 'and']])->delete();
                    } else {
                        $tmp0 = array();
                        $tmp0 = [
                            'event_id' => $event_id,
                            'template_id' => $M_CLASS_id,
                        ];
                        if (!empty($m_event_templatejoin)) {
                            Model('event_templatejoin')->where([[[['template_id', '=', $M_CLASS_id]], 'and']])->edit($tmp0);
                        } else {
                            Model('event_templatejoin')->add($tmp0);
                        }
                    }
                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始值-form
        $style_id = null;
        $event_id = null;
        $name = null;
        $image = null;
        $image_promote = null;
        $a_instruction = array(
            array('id' => 0, 'key' => 'AAA', 'value' => '', 'remark' => '僅說明'),
            array('id' => 1, 'key' => 'BBB', 'value' => '', 'remark' => '僅說明'),
            array('id' => 2, 'key' => 'CCC', 'value' => '', 'remark' => '僅說明'),
        );
        $description = null;
        $point = 0;
        $sequence = 255;
        $state = 'pretreat';
        $act = 'close';
        $width = 1024;
        $height = 1024;
        $viewed = 0;
        $inserttime = null;
        $modifytime = null;
        $modifyadmin_name = null;

        //form
        $column = array();
        $extra = null;

        //tabs
        $a_tabs = array();

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'add'));
                break;

            //修改
            case 'edit':
                $M_CLASS_id = $_GET[M_CLASS . '_id'];
                $join = array(array('left join', 'templatestatistics', 'using(template_id)'));

                $m_template = Model(M_CLASS)->column(array('template.*', 'templatestatistics.*'))->join($join)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetch();

                $event_id = Model('event_templatejoin')->column(['event_id'])->where([[[['template_id', '=', $M_CLASS_id]], 'and']])->fetchColumn();

                //form
                $template_id = $m_template['template_id'];
                $user_id = $m_template['user_id'];
                $style_id = $m_template['style_id'];
                $name = $m_template['name'];
                $a_instruction = json_decode($m_template['instruction'], true);
                $image = 'pinpinbox' . $m_template['image'];
                $image_promote = json_decode($m_template['image_promote'], true);
                $description = $m_template['description'];
                $width = $m_template['width'];
                $height = $m_template['height'];
                $frame = $m_template['frame'];
                $frame_upload = $m_template['frame_upload'];
                $state = $m_template['state'];
                $point = $m_template['point'];
                $sequence = $m_template['sequence'];
                $viewed = $m_template['viewed'];
                $act = $m_template['act'];
                $inserttime = $m_template['inserttime'];
                $modifytime = $m_template['modifytime'];
                $modifyadmin_name = adminModel::getOne($m_template['modifyadmin_id'])['name'];

                $a_image_promote = array();
                if (is_array($image_promote)) {
                    foreach ($image_promote as $v) {
                        $a_image_promote[] = 'pinpinbox' . $v;
                    }
                }

                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));

                parent::$data['frame_upload'] = $frame_upload;
                parent::$data['template_id'] = $template_id;

                break;
        }

        list($html, $js) = parent::$html->selectKit(['id' => 'style_id', 'name' => 'style_id'], parent::get_form_select('style'), $style_id);
        $column[] = ['key' => parent::get_adminmenu_name_by_class('style'), 'value' => $html];
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->text('id="name" name="name" value="' . $name . '" size="32" maxlength="32" required');
        $column[] = array('key' => _('Name'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="width" name="width" value="' . $width . '" min="0" max="65535" required');
        $column[] = array('key' => _('width'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="height" name="height" value="' . $height . '" min="0" max="65535" required');
        $column[] = array('key' => _('height'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->dynamictable('keyvalueremark', 'name="instruction[]"', $a_instruction);
        $column[] = array('key' => _('Instruction'), 'value' => $html, 'key_remark' => 'Key填寫編號1.2..., remark可留空');
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->ckeditor('id="description" name="description"', $description);
        $column[] = array('key' => _('Description'), 'value' => $html);
        parent::$html->set_js($js);

        for ($i = 1; $i <= 3; $i++) {
            $img = (empty($a_image_promote[$i - 1])) ? null : $a_image_promote[$i - 1];
            $img_str = ($i == 1) ? '宣傳圖-封面圖片' : '宣傳圖-' . ($i - 1);
            list($html, $js) = parent::$html->image('id="image' . $i . '" name="image" value="' . $img . '" required');
            $column[] = array('key' => $img_str, 'value' => $html);
            parent::$html->set_js($js);
        }


        list($html, $js) = parent::$html->number('id="point" name="point" value="' . $point . '" min="0" max="65535" required');
        $column[] = array('key' => _('Point'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->number('id="sequence" name="sequence" value="' . $sequence . '" min="0" max="255" required');
        $column[] = array('key' => _('Sequence'), 'value' => $html);
        parent::$html->set_js($js);

        //**//
        list($html, $js) = parent::$html->selectKit(['id' => 'event_id', 'name' => 'event_id'], parent::get_form_select('event'), $event_id);
        $column[] = ['key' => _('Event'), 'value' => $html];
        parent::$html->set_js($js);

        $column[] = array('key' => _('Viewed'), 'value' => $viewed);
        parent::$html->set_js($js);

        //act
        $a_act = array();
        foreach (json_decode(Core::settings('TEMPLATE_ACT'), true) as $k0 => $v0) {
            $a_act[] = array(
                'name' => 'act',
                'value' => $k0,
                'text' => $v0,
            );
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


        /*Template  start*/
        $column = array();
        $extra = null;

        $tmp0 = json_decode($frame_upload, true);
        $a_state = array();
        switch ($state) {
            //未審核
            case 'pretreat':
                foreach (json_decode(Core::settings('TEMPLATE_STATE'), true) as $k0 => $v0) {
                    $a_state[] = array(
                        'name' => 'state',
                        'value' => $k0,
                        'text' => $v0,
                    );
                }
                break;

            //審核已通過
            case 'success':
                $a_state[] = array(
                    'name' => 'state',
                    'value' => 'success',
                    'text' => 'Success',
                );
                break;

            //審核未通過
            case 'fail':
                $a_state[] = array(
                    'name' => 'state',
                    'value' => 'fail',
                    'text' => 'Fail',
                );
                break;

            default:
                break;

        }

        list($html, $js) = parent::$html->radiotable('150px', '30px', 5, $a_state, $state);
        $column[] = array('key' => _('State'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->button('id="name" class="dynamictable-button-add" style="width:10%;" onclick="preview();" value="產生預覽圖"');
        $column[] = array('key' => _('Preview img'), 'value' => $html);
        parent::$html->set_js($js);


        $where = array();
        $where[] = array(array(array('act', '=', 'open')), 'and');
        $m_frame_resource = Model('frame_resource')->column(array('frame_id', 'url'))->where($where)->fetchAll();
        $str = '';
        $upload_path = URL_UPLOAD . 'pinpinbox';
        $frame_path = str_replace('admin', 'pinpinbox', static_file('images/'));
        foreach ($tmp0 as $k => $v) {
            foreach ($m_frame_resource as $k2 => $v2) {
                if ($v['resource'] == $v2['frame_id']) {
                    $img_info = getimagesize($upload_path . $v['src']);
                    $str = '<span class="image-a">上傳：<a data-size="' . $img_info[0] . 'x' . $img_info[1] . '" class="img-a" title="寬度：' . $img_info[0] . '/ 高度:' . $img_info[1] . '" href="' . $upload_path . $v['src'] . '" ><img style="margin:0 20px;" width="89" height="134" src="' . $upload_path . $v['src'] . '"></a></span>
						<span class="image-a">對應框架：<img style="background-color:#9D9D9D;" width="89" height="134" src="' . $frame_path . $v2['url'] . '"></span>	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

                    $preview_path = dirname($upload_path . $v['src']) . DIRECTORY_SEPARATOR;
                    if (is_image($preview_path . 'preview_' . $k . '.jpeg')) {
                        $str .= '<span class="image-a">合併圖預覽：<a data-size="' . $img_info[0] . 'x' . $img_info[1] . '" class="img-a" href="' . $preview_path . 'preview_' . $k . '.jpeg" ><img style="margin:0 20px;" width="89" height="134" src="' . $preview_path . 'preview_' . $k . '.jpeg"></a></span>';
                    }
                }
            }
            $num = ($k == 0) ? ($k + 1) . '-(Cover)' : ($k + 1);
            $column[] = array('key' => _('Frame' . $num . ''), 'value' => $str);
        }

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[1] = array('href' => '#tabs-1', 'name' => _('Template'), 'value' => $html);
        parent::$html->set_js($js);


        /*Template  end*/

        list($html, $js) = parent::$html->tabs($a_tabs);
        $formcontent = $html;
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->form('id="form" action="" method="post" onsubmit="false"', $formcontent);
        parent::$data['form'] = $html;
        parent::$html->set_js($js);


        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }

    function delete()
    {
        if (!empty($_POST)) {
            Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $_POST[M_CLASS . '_id'])), 'and')))->delete();
            json_encode_return(1, _('Success'));
        }
        die;
    }

    function json()
    {
        $response = array();

        //column
        $column = array(
            M_CLASS . '_id',
            'user_id',
            'style_id',
            'name',
            'image',
            'point',
            'sequence',
            'state',
            'viewed',
            'act',
            'modifytime',
            'event_id',
        );

        list($where, $group, $order, $limit) = parent::grid_request_encode();
        $join = array(
            array('left join', 'templatestatistics', 'using(template_id)'),
            array('left join', 'event_templatejoin', 'using(template_id)'),
        );

        //data
        $fetchAll = Model(M_CLASS)->column($column)->join($join)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();

        foreach ($fetchAll as &$v0) {
            $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
            $v0['styleX'] = parent::get_grid_display('style', $v0['style_id']);
            if (!empty($v0['image'])) {
                $v0['cover'] = parent::get_gird_img(array('alt' => $v0['name'], 'src' => 'pinpinbox' . $v0['image']));
            }

        }
        $response['data'] = $fetchAll;

        //total
        $response['total'] = Model(M_CLASS)->column(array('count(1)'))->join($join)->where($where)->group($group)->fetchColumn();

        die(json_encode($response));
    }

    function grid_edit()
    {
        if (!empty($_REQUEST['models'])) {
            foreach ($_REQUEST['models'] as $v1) {
                Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', (int)$v1[M_CLASS . '_id'])), 'and')))->edit(array('sequence' => $v1['sequence'], 'modifyadmin_id' => adminModel::getSession()['admin_id']));
            }
            json_encode_return(1, 'Edit success.');
        }
        die;
    }

    function extra0()
    {
        if (is_ajax()) {
            $frame_upload = empty($_POST['frame_upload']) ? null : $_POST['frame_upload'];
            $template_id = empty($_POST['template_id']) ? null : $_POST['template_id'];

            //製作審核用圖片
            foreach (json_decode($frame_upload, true) as $k => $v) {
                $background_path = PATH_UPLOAD . 'pinpinbox' . DIRECTORY_SEPARATOR . $v['src'];
                $dir = dirname($background_path) . DIRECTORY_SEPARATOR;

                /**
                 *  檔案已存在時是否能產生預覽圖
                 */
                // if(file_exists($dir.'preview_'.$k.'.jpeg')) continue;

                $num = $k;
                $background = $v['src'];
                $resource = $v['resource'];

                $where = array();
                $where[] = array(array(array('act', '=', 'open')), 'and');
                $m_frame_resource = Model('frame_resource')->column(array('frame_id', 'blank'))->where($where)->fetchAll();

                foreach ($m_frame_resource as $k0 => $v0) {
                    if ($v0['frame_id'] == $resource) {
                        $background_path = PATH_UPLOAD . 'pinpinbox' . DIRECTORY_SEPARATOR . $background;
                        $im_background = new Imagick($background_path);

                        $foreground = PATH_STATIC_FILE . 'pinpinbox' . DIRECTORY_SEPARATOR . SITE_LANG . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'intro.jpg';
                        $foreground_info = getimagesize($foreground);

                        $dir = dirname($background_path) . DIRECTORY_SEPARATOR;
                        foreach (json_decode($v0['blank'], true) as $k1 => $v1) {

                            //裁切
                            $imgUrl = $foreground;
                            $imgInitW = $foreground_info[0];
                            $imgInitH = $foreground_info[1];
                            $imgW = (int)$v1['W'];
                            $imgH = (int)$v1['H'];
                            $imgY1 = 0;
                            $imgX1 = 0;
                            $cropW = (int)$v1['W'];
                            $cropH = (int)$v1['H'];
                            $angle = 0;

                            $jpeg_quality = 100;

                            $output_filename = 'corp' . $k1;
                            $output_url = $dir . $output_filename;

                            $what = getimagesize($imgUrl);
                            switch (strtolower($what['mime'])) {
                                case 'image/png':
                                    $img_r = imagecreatefrompng($imgUrl);
                                    $source_image = imagecreatefrompng($imgUrl);
                                    $type = '.png';
                                    break;
                                case 'image/jpeg':
                                    $img_r = imagecreatefromjpeg($imgUrl);
                                    $source_image = imagecreatefromjpeg($imgUrl);
                                    $type = '.jpeg';
                                    break;
                                case 'image/gif':
                                    $img_r = imagecreatefromgif($imgUrl);
                                    $source_image = imagecreatefromgif($imgUrl);
                                    $type = '.gif';
                                    break;
                                default:
                                    die('image type not supported');
                            }

                            $resizedImage = imagecreatetruecolor($imgW, $imgH);
                            imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
                            $rotated_image = imagerotate($resizedImage, -$angle, 0);

                            $rotated_width = imagesx($rotated_image);
                            $rotated_height = imagesy($rotated_image);

                            $dx = $rotated_width - $imgW;
                            $dy = $rotated_height - $imgH;

                            $cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
                            imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
                            imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
                            $final_image = imagecreatetruecolor($cropW, $cropH);
                            imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
                            imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);

                            imagejpeg($final_image, $output_url . $type, $jpeg_quality);


                            $im_foreground = new Imagick($output_url . $type);
                            $im_background->compositeImage($im_foreground, Imagick::COMPOSITE_DSTOVER, (int)$v1['L'], (int)$v1['T']);
                            $im_foreground->clear();

                        }

                        //合併
                        $filename = 'preview_' . $num . '.jpeg';
                        $im_background->setImageFormat('jpeg');
                        $im_background->writeImage($dir . $filename);
                        $im_background->clear();

                        image_reformat($dir . $filename, 'jpeg', '668', '1002');
                        unlink($dir . $filename);
                        rename($dir . 'preview_' . $num . '_668x1002.jpeg', $dir . $filename);

                        if (is_file($dir . $filename)) {
                            \Extension\aws\S3::upload($dir . $filename);
                        }
                    }
                }
            }

            for ($i = 0; $i <= 4; $i++) {
                if (file_exists($dir . 'corp' . $i . '.jpeg')) unlink($dir . 'corp' . $i . '.jpeg');;
            }

            json_encode_return(1, _('Success, refresh page?'), parent::url(M_CLASS, 'form', array('act' => 'edit', 'template_id' => $template_id)));
        }

        json_encode_return(0, _('Error'), parent::url(M_PACKAGE, 'index'));
    }
}