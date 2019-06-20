<?php

class albumController extends backstageController
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
            $recommend = $_POST['recommend'];
            $act = $_POST['act'];

            switch ($_GET['act']) {
                //新增
                case 'add':
                    break;

                //修改
                case 'edit':
                    $M_CLASS_id = $_POST[M_CLASS . '_id'];

                    $m_album_act = Model(M_CLASS)->column(array('act'))->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetchColumn();

                    if ($m_album_act != $act) {
                        if (!Core::notice_switch(array('type' => 'album', 'id' => $M_CLASS_id, 'act' => $act))) json_encode_return(0, _('Unknown case, please try again.'), parent::url(M_CLASS, 'index'));
                    }

                    $edit = array(
                        'recommend' => $recommend,
                        'act' => $act,
                        'modifyadmin_id' => adminModel::getSession()['admin_id'],
                    );
                    Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->edit($edit);

                    json_encode_return(1, _('Success, back to previous page?'), parent::url(M_CLASS, 'index'));
                    break;
            }
        }

        //初始
        $extra = null;

        //form for add or edit
        switch ($_GET['act']) {
            //新增
            case 'add':
                break;

            //修改
            case 'edit':
                if (!empty($_GET)) {
                    $M_CLASS_id = $_GET[M_CLASS . '_id'];
                    $join = array(array('left join', 'albumstatistics', 'using(album_id)'));

                    $m_album = Model(M_CLASS)->join($join)->column(array('album.*', 'albumstatistics.*'))->where(array(array(array(array(M_CLASS . '_id', '=', $M_CLASS_id)), 'and')))->fetch();

                    //user
                    $html_user = parent::get_grid_display('user', $m_album['user_id']);

                    //category
                    $html_category = parent::get_grid_display('category', $m_album['category_id']);

                    //template
                    $html_template = parent::get_grid_display('template', $m_album['template_id']);

                    //audio
                    $html_audio = parent::get_grid_display('audio', $m_album['audio_id']);

                    $name = $m_album['name'];
                    $title = $m_album['title'];
                    $description = $m_album['description'];
                    $cover = $m_album['cover'];
                    $a_photo = json_decode($m_album['photo'], true);
                    $location = $m_album['location'];
                    $weather = $m_album['weather'];
                    $mood = $m_album['mood'];
                    $rating = $m_album['rating'];
                    $point = $m_album['point'];
                    $viewed = $m_album['viewed'];
                    $recommend = $m_album['recommend'];
                    $act = $m_album['act'];
                    $inserttime = $m_album['inserttime'];
                    $modifytime = $m_album['modifytime'];
                    $modifyadmin_name = adminModel::getOne($m_album['modifyadmin_id'])['name'];
                }

                list($html, $js) = parent::$html->hidden('id="' . M_CLASS . '_id" name="' . M_CLASS . '_id" value="' . $M_CLASS_id . '"');
                $extra .= $html;
                parent::$html->set_js($js);

                parent::$data['action'] = parent::url(M_CLASS, 'form', array('act' => 'edit'));
                break;
        }

        //form
        $column = array();

        $column[] = array('key' => parent::get_adminmenu_name_by_class('user'), 'value' => $html_user);

        $column[] = array('key' => parent::get_adminmenu_name_by_class('category'), 'value' => $html_category);

        $column[] = array('key' => parent::get_adminmenu_name_by_class('template'), 'value' => $html_template);

        $column[] = array('key' => parent::get_adminmenu_name_by_class('audio'), 'value' => $html_audio);

        $column[] = array('key' => _('Name'), 'value' => $name);

        $column[] = array('key' => _('Title'), 'value' => $title);

        $column[] = array('key' => _('Description'), 'value' => nl2br(htmlspecialchars($description)));

        list($html, $js) = parent::$html->img('name="cover" value="' . $cover . '"');
        $column[] = array('key' => _('Cover'), 'value' => $html);
        parent::$html->set_js($js);

        if (is_array($a_photo)) {
            list($html, $js) = parent::$html->listtable(array('width' => 120, 'height' => 120, 'col' => 5), 'img', $a_photo);
            $column[] = array('key' => _('Photo'), 'value' => $html);
            parent::$html->set_js($js);
        }

        $column[] = array('key' => _('Location'), 'value' => $location);

        $column[] = array('key' => _('Weather'), 'value' => $weather);

        $column[] = array('key' => _('Mood'), 'value' => $mood);

        $column[] = array('key' => _('Rating'), 'value' => $rating);

        $column[] = array('key' => _('Point'), 'value' => $point);

        $column[] = array('key' => _('Viewed'), 'value' => $viewed);

        list($html, $js) = parent::$html->number('id="recommend" name="recommend" value="' . $recommend . '" min="0" max="255" required');
        $column[] = array('key' => _('Recommend'), 'value' => $html);
        parent::$html->set_js($js);

        $a_act = array();
        foreach (json_decode(Core::settings('ALBUM_ACT'), true) as $k0 => $v0) {
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

        //albumindex
        $column = array();
        $extra = null;

        list($html, $js) = parent::$html->grid('albumindex-grid');
        $column[] = array('key' => _('Album Index'), 'value' => $html);
        parent::$html->set_js($js);
        parent::$data[M_CLASS . '_id'] = empty($M_CLASS_id) ? '[]' : $M_CLASS_id;

        list($html, $js) = parent::$html->table('class="table"', $column, $extra);
        $a_tabs[1] = array('href' => '#tabs-1', 'name' => _('Album Index'), 'value' => $html);
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
        $response = array();

        $case = isset($_POST['case']) ? $_POST['case'] : null;

        switch ($case) {
            default:
                //column
                $column = array(
                    M_CLASS . '_id',
                    'user_id',
                    'category_id',
                    'template_id',
                    'audio_id',
                    'name',
                    'cover',
                    'weather',
                    'mood',
                    'rating',
                    'point',
                    'recommend',
                    'act',
                    'inserttime',
                    'modifytime',
                    'viewed',
                );

                $join = array(
                    array('left join', 'albumstatistics', 'using(album_id)'),
                );

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $fetchAll = Model(M_CLASS)->column($column)->join($join)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();
                foreach ($fetchAll as &$v0) {
                    $v0['userX'] = parent::get_grid_display('user', $v0['user_id']);
                    $v0['categoryX'] = parent::get_grid_display('category', $v0['category_id']);
                    $v0['templateX'] = parent::get_grid_display('template', $v0['template_id']);
                    $v0['audioX'] = parent::get_grid_display('audio', $v0['audio_id']);
                    $v0['cover'] = parent::get_gird_img(array('alt' => $v0['name'], 'src' => $v0['cover']));
                }
                $response['data'] = $fetchAll;

                //total
                $response['total'] = Model(M_CLASS)->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
                break;

            case 'albumindex':
                //column
                $column = array(
                    '`index`',
                    'inserttime',
                );

                list($where, $group, $order, $limit) = parent::grid_request_encode();

                //data
                $response['data'] = Model('albumindex')->column($column)->where($where)->group($group)->order($order)->limit($limit)->fetchAll();

                //total
                $response['total'] = Model('albumindex')->column(array('count(1)'))->where($where)->group($group)->fetchColumn();
                break;
        }

        die(json_encode($response));
    }

    function download()
    {
        $album_id = empty($_GET['album_id']) ? null : $_GET['album_id'];
        if ($album_id == null) {
            json_encode_return(0, _('Param error.'));
        }

        $path = PATH_STORAGE . storagefile(SITE_LANG . '/album/' . $album_id . '.zip');

        if (isset($_POST['ready']) && $_POST['ready']) {
            is_file($path) ? json_encode_return(1) : json_encode_return(0, _('File is not exist.'));
        }

        check_remote_file($path, true);
        die;
    }

    function grid_edit()
    {
        if (!empty($_REQUEST['models'])) {
            foreach ($_REQUEST['models'] as $v1) {
                Model(M_CLASS)->where(array(array(array(array(M_CLASS . '_id', '=', (int)$v1[M_CLASS . '_id'])), 'and')))->edit(array('recommend' => $v1['recommend'], 'modifyadmin_id' => adminModel::getSession()['admin_id']));
            }
            json_encode_return(1, 'Edit success.');
        }
        die;
    }

    /**
     * 由於考慮 pdf 會有客製化的可能, 所以拉到各 class 底下實現
     */
    function pdf()
    {
        $album_id = null;
        if (isset($_GET['album_id'])) $album_id = $_GET['album_id'];
        if (empty($album_id)) die;

        if (!class_exists('db')) include PATH_ROOT . 'lib/db.php';
        $db = new db(Core::$_config['CONFIG']['DB']['site']);

        //防止注入處理
        $tmp1 = array();
        $tmp1 = explode(',', $album_id);
        $a_album_id = array();
        foreach ($tmp1 as $v1) {
            $a_album_id[] = $db->quote($v1);
        }

        $sql = "Select album.album_id, album.name album_name, album.title album_title, album.modifytime album_modifytime, ap.albumphoto_id, ap.name albumphoto_name, ap.image albumphoto_image, a.name admin_name
				from album album
				left join albumphoto ap on ap.album_id = album.album_id
				left join admin a on a.admin_id = album.modifyadmin_id
				where album.album_id in (" . implode(",", $a_album_id) . ")
				order by album.sequence, album.album_id, ap.sequence, ap.albumphoto_id";
        $tmp1 = array();
        $tmp1 = $db->fetchAll($sql);
        $tmp2 = array();
        foreach ($tmp1 as $v1) {
            $tmp2[$v1['album_id']]['album_id'] = $v1['album_id'];
            $tmp2[$v1['album_id']]['album_name'] = $v1['album_name'];
            $tmp2[$v1['album_id']]['album_title'] = $v1['album_title'];
            $tmp2[$v1['album_id']]['album_modifytime'] = substr($v1['album_modifytime'], 0, 10);
            $tmp2[$v1['album_id']]['admin_name'] = $v1['admin_name'];
            $tmp2[$v1['album_id']]['albumphoto'][] = array('albumphoto_id' => $v1['albumphoto_id'], 'albumphoto_name' => $v1['albumphoto_name'], 'albumphoto_image' => $v1['albumphoto_image']);
        }

        if (!class_exists('TCPDF')) include PATH_ROOT . 'lib/tcpdf/tcpdf.php';

        //一本相簿一個 pdf 檔
        $tmp3 = array();
        $tmp4 = null;
        foreach ($tmp2 as $v1) {
            //create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            //set document information
            $pdf->SetFont('stsongstdlight', '', 10);
            $pdf->SetCreator(Core::settings('SITE_TITLE'));
            $pdf->SetAuthor($v1['admin_name']);
            $pdf->SetTitle($v1['album_name']);
            $pdf->SetSubject($v1['album_title']);
            //^$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

            //set header and footer fonts
            $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            //set default header data
            $pdf->SetHeaderData('favicon.ico', 30, Core::settings('SITE_TITLE'), Core::settings('SITE_DESCRIPTION'));

            //set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            //set JPEG quality
            $pdf->setJPEGQuality(100);

            foreach ($v1['albumphoto'] as $v2) {
                //add a page
                $pdf->AddPage();

                //輸入 image
                if (!empty($v2['albumphoto_image'])) {
                    $pdf->Image(PATH_UPLOAD . $v2['albumphoto_image'], null, 50, null, null, null, URL_ROOT, '', false, null, 'C', false, false, 1, false, false, true);
                }
            }

            //Close and output PDF document, 檔名必須要轉為 Big5(含有中文名的話)
            $tmp3[] = $tmp4 = iconv('UTF-8', 'Big5', PATH_TMP_FILE . $v1['album_modifytime'] . $v1['album_name'] . '.pdf');
            $pdf->Output($tmp4, 'F');
        }

        //製作 zip
        if (!class_exists('zip')) include PATH_LIB . 'zip.php';
        $tmp5 = PATH_TMP_FILE . date("Y-m-d His") . ' ' . M_CLASS . '.zip';
        $zip = new zip($tmp5);
        $zip->add($tmp3);

        //刪除製作出來的 pdf(unlink 時檔名編碼也要對得上)
        foreach ($tmp3 as $v1) {
            unlink($v1);
        }

        //下載 zip
        check_remote_file($tmp5, true, true);

        die;
    }
}