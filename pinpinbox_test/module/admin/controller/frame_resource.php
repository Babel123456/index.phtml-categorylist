<?php

class frame_resourceController extends backstageController
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

    function extra0()
    {
        if (is_ajax()) {
            $frame_id = empty($_POST['frame_id']) ? null : $_POST['frame_id'];
            $blank_data = empty($_POST['blank_data']) ? null : $_POST['blank_data'];
            $img_str = empty($_POST['img_str']) ? null : $_POST['img_str'];

            $blank = array();
            foreach (json_decode($blank_data, true) as $k0 => $v0) {
                if (substr_count($v0, ',') !== 3) json_encode_return(0, _('縷空數值[Blank]格式錯誤，請重新輸入。'));
                $after_explode = explode(',', $v0);
                $blank[] = ['W' => $after_explode[0], 'H' => $after_explode[1], 'T' => $after_explode[2], 'L' => $after_explode[3],];
            }

            $origin_name = basename($img_str, '.' . pathinfo($img_str)['extension']);
            $background_path = str_replace(URL_ROOT, PATH_ROOT, $img_str);
            $dir = dirname($background_path) . DIRECTORY_SEPARATOR;
            $origin_dir = str_replace(PATH_ROOT, URL_ROOT, $dir);

            $im_background = new Imagick($background_path);
            $foreground = PATH_STATIC_FILE . 'pinpinbox' . DIRECTORY_SEPARATOR . SITE_LANG . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'intro.jpg';
            $foreground_info = getimagesize($foreground);

            foreach ($blank as $k1 => $v1) {
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
            $filename = $origin_name . '_preview' . '.jpeg';
            $im_background->setImageFormat('jpeg');
            $im_background->writeImage($dir . $filename);
            $im_background->clear();

            image_reformat($dir . $filename, 'jpeg', '668', '1002');
            unlink($dir . $filename);
            rename($dir . $origin_name . '_preview_668x1002.jpeg', $dir . $filename);

            if (is_file($dir . $filename)) {
                \Extension\aws\S3::upload($dir . $filename);
            }

            for ($i = 0; $i <= 4; $i++) {
                if (file_exists($dir . 'corp' . $i . '.jpeg')) unlink($dir . 'corp' . $i . '.jpeg');;
            }

            json_encode_return(1, _('Success'), null, $origin_dir . $filename);
        }
    }

    function form()
    {
        if (is_ajax()) {

            function blank_param($blank)
            {
                return (substr_count($blank, ',') !== 3) ? json_encode_return(0, _('縷空數值[Blank]格式錯誤，請重新輸入。')) : $blank;
            }

            //form
            $name = $_POST['name'];
            $image = $_POST['image'];
            $blank = array();

            //最多讀入八個空格的數值
            for ($i = 1; $i <= 8; $i++) {
                $blank[] = empty($_POST['blank' . $i]) ? null : blank_param($_POST['blank' . $i]);
            }

            $act = $_POST['act'];

            $blank_tmp = array();
            foreach ($blank as $v) {
                if ($v == null) continue;
                $blank_split = explode(',', $v);

                $blank_tmp[] = array(
                    "W" => (int)$blank_split[0],
                    "H" => (int)$blank_split[1],
                    "T" => (int)$blank_split[2],
                    "L" => (int)$blank_split[3],
                );
            }

            switch ($_GET['act']) {
                //新增
                case 'add':
                    if (Model(M_CLASS)->where(array(array(array(array('name', '=', $name)), 'and')))->fetch()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    //form
                    $tmp0 = array(
                        'name' => $name,
                        'url' => '',
                        'blank' => json_encode($blank_tmp),
                        'act' => $act,
                        'inserttime' => inserttime(),
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    );
                    if ($frame_id = Model(M_CLASS)->add($tmp0)) {
                        //copy frame to static_file
                        $id = strlen($frame_id) == 1 ? '0' . $frame_id : $frame_id;
                        if (!copy(PATH_UPLOAD . $image, PATH_STATIC_FILE . 'pinpinbox/zh_TW/images/template/' . $id . '.png')) {
                            json_encode_return(1, _('Copy file error.'), parent::url(M_CLASS, 'index'));
                        }

                        $tmp0 = array(
                            'url' => 'template/' . $id . '.png',
                            'modifyadmin_id' => adminModel::getSession()['admin_id'],
                        );
                        Model(M_CLASS)->where(array(array(array(array('frame_id', '=', $frame_id)), 'and')))->edit($tmp0);
                    }

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST['frame_id'];

                    if (Model(M_CLASS)->where(array(array(array(array('frame_id', '!=', $M_CLASS_id), array('name', '=', $name)), 'and')))->fetch()) {
                        json_encode_return(0, _('Data already exists by : ') . 'Name');
                    }

                    /** 0715 Mars
                     *  修改若未包含模板圖片則不做圖片覆蓋,
                     *  原則上修改此頁應以名稱或開關act為主，動到圖片的機率非常低，但還是保留操作可能
                     */
                    if (strpos($image, 'template') === false) {
                        //copy frame to static_file
                        $id = strlen($M_CLASS_id) == 1 ? '0' . $M_CLASS_id : $M_CLASS_id;
                        if (!copy(PATH_UPLOAD . $image, PATH_STATIC_FILE . 'pinpinbox/zh_TW/images/template/' . $id . '.png')) {
                            json_encode_return(1, _('Copy file error'), parent::url(M_CLASS, 'index'));
                        }
                        unlink(PATH_STATIC_FILE . 'pinpinbox/zh_TW/images/template/' . $id . '_67x100.png');
                    }

                    //form
                    $tmp0 = array(
                        'name' => $name,
                        'blank' => json_encode($blank_tmp),
                        'act' => $act,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    );
                    Model(M_CLASS)->where(array(array(array(array('frame_id', '=', $M_CLASS_id)), 'and')))->edit($tmp0);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始值-form
        $name = null;
        $image = null;
        $url = null;

        $act = 'close';
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
                $M_CLASS_id = $_GET['frame_id'];

                $m_frame_resource = Model(M_CLASS)->where(array(array(array(array('frame_id', '=', $M_CLASS_id)), 'and')))->fetch();

                //form
                $frame_id = $m_frame_resource['frame_id'];
                $name = $m_frame_resource['name'];
                $url = str_replace('admin', 'pinpinbox', static_file('images/')) . $m_frame_resource['url'];
                $blank = json_decode($m_frame_resource['blank'], true);
                $act = $m_frame_resource['act'];
                $inserttime = $m_frame_resource['inserttime'];
                $modifytime = $m_frame_resource['modifytime'];
                $modifyadmin_name = adminModel::getOne($m_frame_resource['modifyadmin_id'])['name'];

                $blanklist = array();
                foreach ($blank as $k => $v) {
                    $blanklist[$k + 1] = implode(',', $v);
                }

                list($html, $js) = parent::$html->hidden('id="frame_id" name="frame_id_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
                break;
        }

        list($html, $js) = parent::$html->text('id="name" name="name" value="' . $name . '" size="32" maxlength="32" required');
        $column[] = array('key' => _('Name'), 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->image('id="image" name="image" value="' . $url . '" required');
        $column[] = array('key' => _('Image'), 'value' => $html);
        parent::$html->set_js($js);

        /**
         * frame_resource 用的text list 一縷空格有四個參數: W , H , T , L
         * 單張模板目前最多五縷空格，先多留兩格
         */
        $a_html = '';
        for ($i = 1; $i <= 8; $i++) {
            $blanklist[$i] = empty($blanklist[$i]) ? null : $blanklist[$i];
            list($html, $js) = parent::$html->text('id="blank' . $i . '" name="blank' . $i . '" value="' . $blanklist[$i] . '" size="28" maxlength="32" placeholder="W, H, T, L"');
            $a_html .= $i . '&nbsp;:&nbsp;' . $html . '<br><br>';
        }

        $column[] = array('key' => _('Blank'), 'value' => $a_html, 'key_remark' => '請使用逗號(,)分隔每一個縷空格的 W、H、T、L數值');

        /*preview*/
        $tmp_image = '<input type="button" style="width:50px" value="預覽" id="set_preview">&nbsp;<a href="javascript:void(0)" id="zoomin_preview"><img id="show_preview" width="80" height="120"></a>';
        $column[] = array('key' => _('Preview'), 'value' => $tmp_image, 'key_remark' => '<span style="color:#108199;">修改Blank欄位的數值後，點擊"預覽"會立即產生新的圖。</span>');
        /*preview*/

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
            'frame_id',
            'name',
            'url',
            'blank',
            'act',
            'modifytime',
        );

        list($where, $group, $order, $limit) = parent::grid_request_encode();

        //data
        $fetchAll = Model(M_CLASS)->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();

        $url = str_replace(M_PACKAGE, 'pinpinbox', static_file('images/'));
        foreach ($fetchAll as &$v0) {
            $tmp = array();
            $v0['image'] = '<a class="grid-img" title="' . $v0['name'] . '" href="' . $url . $v0['url'] . '" data-size="1336x2004"><img alt="' . $v0['name'] . '" src="' . $url . $v0['url'] . '" style="background-color:#AFD1D8;" width="60" height="90"></a>';

            foreach (json_decode($v0['blank'], true) as $k1 => $v1) {
                $tmp[] = ($k1 + 1) . '：{W:' . $v1['W'] . ', H:' . $v1['H'] . ', T:' . $v1['T'] . ', L:' . $v1['L'] . '}';
            }
            $v0['blank'] = implode('<br>', $tmp);

        }
        $response['data'] = $fetchAll;

        //total
        $response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
        die(json_encode($response));
    }

    function grid_edit()
    {
        if (!empty($_REQUEST['models'])) {
            foreach ($_REQUEST['models'] as $v1) {
                Model(M_CLASS)->where(array(array(array(array('frame_id', '=', (int)$v1['frame_id'])), 'and')))->edit(array('sequence' => $v1['sequence'], 'modifyadmin_id' => adminModel::getSession()['admin_id']));
            }
            json_encode_return(1, 'Edit success.');
        }
        die;
    }
}