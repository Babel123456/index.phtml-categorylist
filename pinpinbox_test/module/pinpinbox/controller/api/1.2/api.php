<?php

namespace Controller\v1_2;

class api extends \frontstageController
{
    function __construct()
    {
    }

    function __checkParamIsSet(array $param)
    {
        foreach ($param as $v0) {
            if (!isset($_POST[$v0])) {
                json_encode_return(0, 'Param error. "' . $v0 . '" is required.');
                break;
            }
        }

        return true;
    }

    function __checkSign(array $param)
    {
        $tmp0 = array();
        foreach ($param as $v0) {
            $tmp0[$v0] = $_POST[$v0];
        }

        return ($_POST['sign'] == encrypt($tmp0)) ? true : false;
    }

    function __checkUser($user_id)
    {
        list ($result, $message) = array_decode_return((new \userModel())->usable($user_id));

        if ($result != 1) json_encode_return(0, $message);

        (new \userModel())->setSession($user_id);
    }

    function albumsettings()
    {
        $result = 1;
        $message = null;
        $this->__checkParamIsSet(['id', 'token', 'albumid', 'settings', 'sign']);
        if (!$this->__checkSign(['id', 'token', 'albumid', 'settings'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $album_id = $_POST['albumid'];
        $settings = json_decode($_POST['settings'], true);

        $this->__checkUser($user_id);

        list ($result1, $message1) = array_decode_return((new \albumModel)->settingsable($album_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        $edit = [];

        //audio
        switch ($settings['audio_mode']) {
            case 'none':
                $edit['audio_mode'] = 'none';
                break;

            case 'plural':
                $edit['audio_mode'] = 'plural';
                break;

            case 'singular':
                $edit['audio_mode'] = 'singular';
                $edit['audio_refer'] = 'system';
                $edit['audio_target'] = $settings['audio'];
                break;
        }

        if (isset($settings['secondpaging'])) $edit['category_id'] = $settings['secondpaging'];
        if (isset($settings['title'])) $edit['name'] = $settings['title'];
        if (isset($settings['description'])) $edit['description'] = $settings['description'];
        if (!empty($settings['preview'])) $edit['preview'] = explode(',', str_replace(' ', '', $settings['preview']));
        if (isset($settings['location'])) $edit['location'] = $settings['location'];
        if (!empty($settings['weather'])) $edit['weather'] = $settings['weather'];
        if (!empty($settings['mood'])) $edit['mood'] = $settings['mood'];
        if (isset($settings['point'])) $edit['point'] = $settings['point'];
        if (!empty($settings['act'])) $edit['act'] = $settings['act'];

        (new \albumModel)->updateSettings($album_id, $edit);

        _return:
        json_encode_return($result, $message);
    }

    function buyalbum()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'platform', 'albumid', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'platform', 'albumid'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $platform = $_POST['platform'];
        $album_id = $_POST['albumid'];

        if (!in_array($platform, ['apple', 'google'])) {
            $result = 0;
            $message = _('Unknown case of platform.');
            goto _return;
        }

        $this->__checkUser($user_id);

        list($result1, $message1, , $m_album) = array_decode_return(Model('album')->buyable($album_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        (new \Model)->beginTransaction();

        list($result2, $message2, , $data2) = array_decode_return(\Core::exchange($user_id, $platform, 'album', $album_id));
        if (!$result2) {
            (new \Model)->rollBack();

            $result = $result2;
            $message = $message2;
            goto _return;
        }

        (new \Model)->commit();

        $Image = new \Core\Image;

        $data = [
            'download_id' => $data2['download_id'],
            'coverurl' => is_image(PATH_UPLOAD . $m_album['cover']) ? fileinfo($Image->set(PATH_UPLOAD . $m_album['cover'])->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function buytemplate()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'template_id', 'platform', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'template_id', 'platform'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $template_id = $_POST['template_id'];
        $platform = $_POST['platform'];

        $this->__checkUser($user_id);

        list($result, $message) = array_decode_return(Model('template')->buyable($template_id, $user_id));
        if ($result != 1) json_encode_return($result, $message);

        (new \Model)->beginTransaction();

        list($result, $message, , $data) = array_decode_return(\Core::exchange($user_id, $platform, 'template', $template_id));
        if (!$result) {
            (new \Model)->rollBack();

            json_encode_return(0, $message);
        }

        (new \Model)->commit();

        json_encode_return(1, null, null, $data['download_id']);
    }

    function changefollowstatus()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'authorid', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'authorid'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $authorid = $_POST['authorid'];

        $this->__checkUser($user_id);

        $m_user1 = Model('user')->where([[[['user_id', '=', $authorid]], 'and']])->fetch();
        if (empty($m_user1)) {
            json_encode_return(0, _('Author does not exist.'));
        } elseif ($m_user1['act'] != 'open') {
            json_encode_return(0, _('Author is not open.'));
        }

        Model('follow');
        Model('followfrom');
        Model('followto');
        Model()->beginTransaction();
        $followstatus = \Core::set_follow($user_id, $m_user1['user_id']);
        Model()->commit();

        json_encode_return(1, null, null, ['followstatus' => $followstatus]);
    }

    function check()
    {
        if (!$this->__checkParamIsSet(['checkcolumn', 'checkvalue', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['checkcolumn', 'checkvalue'])) json_encode_return(0, _('Sign error.'));
        $checkcolumn = $_POST['checkcolumn'];
        $checkvalue = $_POST['checkvalue'];

        switch ($checkcolumn) {
            case 'account':
                list($result, $message) = array_decode_return(Model('user')->check('account', $checkvalue));
                break;

            case 'cellphone':
                list($phone1, $phone2) = explode(',', $checkvalue);
                $checkvalue = '+' . $phone1 . $phone2;
                list($result, $message) = array_decode_return(Model('user')->check('cellphone', $checkvalue));
                break;

            case 'creative_code':
                list($result, $message) = array_decode_return(Model('user')->check('creative_code', $checkvalue));
                break;

            default:
                $result = 0;
                $message = _('Unknown check column.');
                break;
        }

        json_encode_return($result, $message);
    }

    function checkalbumofdiy()
    {
        $result = 1;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_album = Model('album')->process3($user_id);
        if ($m_album) {
            $data = [
                'album' => [
                    'album_id' => $m_album[0]['album_id']
                ],
                'template' => [
                    'template_id' => $m_album[0]['template_id']
                ],
            ];
        } else {
            $result = 0;
        }

        _return:
        json_encode_return($result, null, null, $data);
    }

    function checkalbumzip()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'album_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        $path = PATH_STORAGE . storagefile(SITE_LANG . '/album/' . $album_id . '.zip');

        if (!is_file($path)) {
            $result = 0;
            $message = _('Album\'s file does not exist.');
            goto _return;
        }

        $data = [
            'size' => filesize($path),
            'modifytime' => filemtime($path),
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function checknoticequeue()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        list(, , , $c_notice) = array_decode_return(Model('notice')->countFollow($user_id));

        json_encode_return(1, null, null, $c_notice);
    }

    function checktoken()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_token = Model('token')->where([[[['user_id', '=', $user_id]], 'and']])->fetch();
        if (empty($m_token)) json_encode_return(0);

        json_encode_return(1);
    }

    function checkupdateversion()
    {
        $result = 1;
        $message = null;
        if (!$this->__checkParamIsSet(['platform', 'version', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['platform', 'version'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $platform = $_POST['platform'];
        $version = $_POST['version'];

        list ($result0, $message0) = array_decode_return(\versioncontrolModel::ableToUpdateVersion($platform, $version));
        if ($result0 != 1) {
            $result = $result0;
            $message = $message0;
            goto _return;
        }

        _return:
        json_encode_return($result, $message);
    }

    function delalbum()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'albumid', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'albumid'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $album_id = $_POST['albumid'];

        $this->__checkUser($user_id);

        list($result, $message) = array_decode_return(Model('album')->deleteAlbum($album_id, $user_id));

        json_encode_return($result, $message);
    }

    function deldownloadlist()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'download_id', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'download_id'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $download_id = $_POST['download_id'];

        $this->__checkUser($user_id);

        $a_download_id0 = empty($download_id) ? array() : explode(',', $download_id);

        $where = array(
            array(array(array('user_id', '=', $user_id), array('type', '=', 'album'), array('state', '=', 'pretreat')), 'and'),
        );
        $m_download = Model('download')->where($where)->fetchAll();
        $a_download_id1 = array();
        foreach ($m_download as $v0) {
            $a_download_id1[] = $v0['download_id'];
        }
        foreach ($a_download_id0 as $v0) {
            if (!in_array($v0, $a_download_id1)) {
                json_encode_return(0, _('Download does not exist.'));
            }
            $where = array(
                array(array(array('download_id', '=', $v0)), 'and'),
            );
            $m_download = Model('download')->where($where)->fetch();
            if ($m_download['point'] > 0) {
                json_encode_return(0, _('Download-point is greater than 0.'));
            }
        }
        $where = array(
            array(array(array('download_id', 'in', $a_download_id0)), 'and'),
        );
        if (!Model('download')->where($where)->edit(array('state' => 'fail'))) {
            json_encode_return(0, _('[Download] occur exception, please contact us.'));
        }
        json_encode_return(1);
    }

    function deleteaudioofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'photo_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'album_id', 'photo_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];

        $this->__checkUser($user_id);

        list($result1, $message1) = array_decode_return(Model('photo')->ableToDeleteAudio($photo_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        Model('photo')->deleteAudio($photo_id);

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function deletecooperation()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'type', 'type_id', 'user_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'type', 'type_id', 'user_id'])) json_encode_return(0, _('Sign error.'));
        $user_id_0 = $_POST['id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];
        $user_id_1 = $_POST['user_id'];

        $this->__checkUser($user_id_0);

        list($result, $message) = array_decode_return(Model('cooperation')->deleteCooperation($type, $type_id, $user_id_1));
        if (!$result) json_encode_return(0, $message);

        json_encode_return(1);
    }

    function deletephotoofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'photo_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        if (!$this->__checkSign(['id', 'token', 'album_id', 'photo_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];

        $this->__checkUser($user_id);

        list($result1, $message1) = array_decode_return((new \photoModel)->ableToDeletePhoto($photo_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        (new \photoModel)->deletePhoto($photo_id);

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function deletevideoofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'photo_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'album_id', 'photo_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];

        $this->__checkUser($user_id);

        list($result1, $message1) = array_decode_return((new \photoModel)->ableToDeleteVideo($photo_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        (new \photoModel)->deleteVideo($photo_id);

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function downloadalbumzip()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'albumid', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'albumid'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $album_id = $_POST['albumid'];

        $this->__checkUser($user_id);

        list($result, $message) = array_decode_return(Model('album')->downloadable($album_id, $user_id));
        if ($result != 1) json_encode_return($result, $message);

        $m_albumqueue = Model('albumqueue')->column(['visible'])->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->fetch();

        if ($m_albumqueue && !$m_albumqueue['visible']) Model('albumqueue')->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->edit(['visible' => 1]);

        \Core\File::download(PATH_STORAGE . storagefile(SITE_LANG . '/album/' . $album_id . '.zip'), $album_id . '.zip');

        die;
    }

    function downloadtemplate()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'template_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'template_id'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $template_id = $_POST['template_id'];

        $this->__checkUser($user_id);

        $template = storagefile(SITE_LANG . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $template_id . '.zip');
        if (!is_file(PATH_STORAGE . $template)) {
            json_encode_return(0, _('Template does not exist.'));
        }

        $m_template = Model('template')->column(['template_id', 'user_id'])->where([[[['template_id', '=', $template_id]], 'and']])->fetch();
        if (empty($m_template)) {
            json_encode_return(0, _('Template does not exist.'));
        }

        //如果不是版型作者本人, 則檢查下載清單是否有紀錄
        if ($user_id != $m_template['user_id']) {
            $m_download = Model('download')->where([[[['user_id', '=', $user_id], ['`type`', '=', 'template'], ['id', '=', $m_template['template_id']], ['state', '=', 'pretreat']], 'and']])->fetch();
            if (empty($m_download)) {
                json_encode_return(0, _('Download does not exist.'));
            }
        }

        \Core\File::download(PATH_STORAGE . $template, $m_template['template_id'] . '.zip');

        die;
    }

    function facebooklogin()
    {
        if (!$this->__checkParamIsSet(['facebookid', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['facebookid'])) json_encode_return(0, _('Sign error.'));
        $facebookid = $_POST['facebookid'];

        $m_user_facebook = Model('user_facebook')->where([[[['facebook_id', '=', $facebookid]], 'and']])->fetch();
        if (empty($m_user_facebook)) json_encode_return(2);

        $m_user = Model('user')->where([[[['user_id', '=', $m_user_facebook['user_id']]], 'and']])->fetch();
        if (empty($m_user)) {
            json_encode_return(0, _('User does not exist.'));
        } elseif ($m_user['act'] != 'open') {
            json_encode_return(0, _('User is not open.'));
        }

        $m_token = Model('token')->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->fetch();

        json_encode_return(1, null, null, ['id' => $m_user['user_id'], 'token' => $m_token['token']]);
    }

    function finishalbum()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'download_id', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'download_id'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $download_id = $_POST['download_id'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('download_id', '=', $download_id)), 'and'),
        );
        $m_download = Model('download')->where($where)->fetch();
        if (empty($m_download)) {
            json_encode_return(0, _('Download does not exist.'));
        }
        if ($m_download['state'] != 'pretreat') {
            json_encode_return(0, _('Download-state is not on the pretreatment.'));
        }
        $where = array(
            array(array(array('download_id', '=', $m_download['download_id'])), 'and'),
        );
        if (!Model('download')->where($where)->edit(array('state' => 'success'))) {
            json_encode_return(0, _('[Download] occur exception, please contact us.'));
        }
        json_encode_return(1);
    }

    function finishpurchased()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'order_id', 'platform', 'dataSignature', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'order_id', 'platform', 'dataSignature'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $order_id = $_POST['order_id'];
        $platform = $_POST['platform'];
        $dataSignature = $_POST['dataSignature'];

        if (empty($order_id)) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!in_array($platform, ['apple', 'google'])) {
            $result = 0;
            $message = _('Unknown case of platform.');
            goto _return;
        }

        $this->__checkUser($user_id);

        list ($result1, $message1) = json_decode_return(curl(\frontstageController::url('cashflow', 'feedback', ['cashflow_id' => $platform]), ['order_id' => $order_id] + json_decode($dataSignature, true)));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        $data = (new \userModel)->getPoint($user_id, $platform);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function insertalbumofdiy()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'template_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'template_id'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $template_id = (int)$_POST['template_id'];

        $this->__checkUser($user_id);

        list($result, $message, ,) = array_decode_return(Model('template')->usable($template_id, $user_id));
        if (!$result) json_encode_return(0, $message);

        list($result, , , $album_id) = array_decode_return(Model('album')->pretreat($user_id, $template_id));

        json_encode_return($result, null, null, $album_id);
    }

    function insertaudioofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'album_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'album_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        list ($result, $message) = array_decode_return((new \photoModel)->ableToInsertAudio($album_id, $user_id));
        if ($result != 1) {
            goto _return;
        }

        if (empty($_FILES['file'])) {
            $result = 0;
            $message = 'Input name must be [file].';
            goto _return;
        } else {
            if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
                    $Audio = new \Core\Audio();

                    $Audio->setFile($path);

                    if ($Audio->getType() !== 'mp3') {
                        $path = $Audio->setType('mp3')->save(null, true, true);
                    }

                    Model()->beginTransaction();

                    (new \photoModel)->insertAudio($album_id, $user_id, 'file', $path);

                    //2016-08-18 Lion: 刪除 album 自行上傳的音頻
                    $m_album = (new \albumModel)->column(['audio_refer', 'audio_target'])->where([[[['album_id', '=', $album_id]], 'and']])->lock('for update')->fetch();

                    if ($m_album['audio_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_album['audio_target']]);

                    (new \albumModel)->where([[[['album_id', '=', $album_id]], 'and']])->edit([
                        'audio_mode' => 'plural',
                        'audio_loop' => 0,
                        'audio_refer' => 'none',
                        'audio_target' => '',
                    ]);

                    Model()->commit();
                } else {
                    $result = 0;
                    $message = _('Upload failed, please try again.');
                    goto _return;
                }
            } else {
                $result = 0;
                $message = \Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']];
                goto _return;
            }
        }

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function insertcooperation()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'type', 'type_id', 'user_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'type', 'type_id', 'user_id'])) json_encode_return(0, _('Sign error.'));
        $user_id_0 = $_POST['id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];
        $user_id_1 = $_POST['user_id'];

        $this->__checkUser($user_id_0);

        list($result, $message) = array_decode_return(Model('cooperation')->insertCooperation($type, $type_id, $user_id_1));
        if (!$result) json_encode_return(0, $message);

        json_encode_return(1);
    }

    function insertphotoofdiy()
    {
        return (new \Controller\v1_1\api)->insertphotoofdiy();
    }

    function insertreport()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'reportintent_id', 'type', 'type_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'reportintent_id', 'type', 'type_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $reportintent_id = $_POST['reportintent_id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];

        $this->__checkUser($user_id);

        list($result, $message) = array_decode_return(Model('report')->report($reportintent_id, $user_id, $type, $type_id));

        _return:
        json_encode_return($result, $message);
    }

    function insertvideoofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'album_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'album_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        list ($result, $message) = array_decode_return((new \photoModel)->ableToInsertVideo($album_id, $user_id));
        if ($result != 1) {
            goto _return;
        }

        (new \Model)->beginTransaction();

        (new \photoModel)->insertVideo($album_id, $user_id, 'file');

        (new \Model)->commit();

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getadlist()
    {
        $result = 1;
        $message = null;
        $data = null;

        if (!$this->__checkParamIsSet(['id', 'token', 'adarea_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        if (!$this->__checkSign(['id', 'token', 'adarea_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $adarea_id = $_POST['adarea_id'];
        $lang_id = isset($_POST['lang_id']) ? $_POST['lang_id'] : null;

        $this->__checkUser($user_id);

        $m_ad = (new \adModel)->getByArea($adarea_id, $lang_id);

        $data = [];
        foreach ($m_ad as $v0) {
            $a_album = null;
            if ($v0['album']) {
                $a_album = [
                    'album_id' => $v0['album']['album_id'],
                ];
            }

            $a_event = null;
            if ($v0['event']) {
                $a_event = [
                    'event_id' => $v0['event']['event_id'],
                ];
            }

            $a_template = null;
            if ($v0['template']) {
                $a_template = [
                    'template_id' => $v0['template']['template_id'],
                ];
            }

            $a_user = null;
            if ($v0['user']) {
                $a_user = [
                    'user_id' => $v0['user']['user_id'],
                ];
            }

            $data[] = [
                'ad' => [
                    'image' => is_image(PATH_UPLOAD . $v0['ad']['image_960x540']) ? URL_UPLOAD . $v0['ad']['image_960x540'] : null,
                    'name' => $v0['ad']['name'],
                    'url' => json_decode($v0['ad']['url'], true)['href'],
                ],
                'album' => $a_album,
                'event' => $a_event,
                'template' => $a_template,
                'user' => $a_user,
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getalbumdataoptions()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token'])) json_encode_return(0, _('Sign error.'));

        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $a_audio = [];

        $m_audio = (new \audioModel)
            ->column(['audio_id', 'name', 'file'])
            ->where([[[['act', '=', 'open']], 'and']])
            ->fetchAll();

        foreach ($m_audio as $v0) {
            $a_audio[] = [
                'id' => $v0['audio_id'],
                'name' => $v0['name'],
                'url' => URL_STATIC_FILE . $v0['file'],
            ];
        }

        $a_categoryarea = [];

        $m_categoryarea = (new \categoryareaModel)
            ->column(['categoryarea_id', 'name'])
            ->where([
                [[['level', '=', 0], ['act', '=', 'open']], 'and'],
            ])
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        foreach ($m_categoryarea as $v0) {
            $tmp0 = [];
            $tmp0['id'] = $v0['categoryarea_id'];
            $tmp0['name'] = $v0['name'];

            //category
            $m_category = (new \categoryModel)
                ->column(['category_id', 'name'])
                ->join([
                    ['left join', 'categoryarea_category', 'using(category_id)'],
                ])
                ->where([
                    [[['categoryarea_category.categoryarea_id', '=', $v0['categoryarea_id']], ['categoryarea_category.act', '=', 'open']], 'and'],
                ])
                ->order(['categoryarea_category.sequence' => 'asc'])
                ->fetchAll();

            $a_category = [];
            foreach ($m_category as $v1) {
                $a_category[] = [
                    'id' => $v1['category_id'],
                    'name' => $v1['name'],
                ];
            }
            $tmp0['secondpaging'] = $a_category;

            $a_categoryarea[] = $tmp0;
        }
        $a_mood = [];
        foreach (json_decode(\Core::settings('MOOD'), true) as $k0 => $v0) {
            $a_mood[] = ['id' => $k0, 'name' => $v0];
        }
        $a_weather = [];
        foreach (json_decode(\Core::settings('WEATHER'), true) as $k0 => $v0) {
            $a_weather[] = ['id' => $k0, 'name' => $v0];
        }
        $data = [
            'act' => [['id' => 'close', 'name' => 'Close'], ['id' => 'open', 'name' => 'Open']],
            'audio' => $a_audio,
            'firstpaging' => $a_categoryarea,
            'mood' => $a_mood,
            'usergrade' => \Core::get_usergrade($user_id),
            'weather' => $a_weather,
        ];

        json_encode_return(1, null, null, $data);
    }

    function getalbumofdiy()
    {
        return (new \Controller\v1_1\api)->getalbumofdiy();
    }

    function getalbumsettings()
    {
        return (new \Controller\v1_1\api)->getalbumsettings();
    }

    function getcalbumlist()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'rank', 'limit', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        if (!$this->__checkSign(['id', 'token', 'rank', 'limit'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $rank = $_POST['rank'];
        $limit = $_POST['limit'];

        $this->__checkUser($user_id);

        $Image = new \Core\Image;

        switch ($rank) {
            case 'mine'://我上傳的相本
                $m_album = (new \albumModel)->mine_v2($user_id, null, ['album.inserttime' => 'desc'], $limit);
                break;

            case 'other'://其他收藏
                $m_album = (new \albumModel)->other_v2($user_id, null, ['album.inserttime' => 'desc'], $limit);
                break;

            case 'cooperation'://共用收藏
                $m_album = (new \albumModel)->cooperation_v2($user_id, null, ['album.inserttime' => 'desc'], $limit);
                break;

            default:
                $result = 0;
                $message = _('Unknown case of rank.');
                goto _return;
                break;
        }

        $data = [];
        $a_picture = [];
        $a_usefor_all = array_diff(array_merge((new \photoModel)->fetchEnum('usefor'), ['audio']), ['none']);

        if ($m_album) {
            $m_photo = (new \photoModel)
                ->column(['DISTINCT(`usefor`)', 'album_id'])
                ->where([[[['album_id', 'IN', array_column($m_album, 'album_id')], ['act', '=', 'open']], 'and']])
                ->fetchAll();

            foreach ($m_photo as $v0) {
                $a_usefor_inused[$v0['album_id']][] = $v0['usefor'];
            }
        }

        foreach ($m_album as $v0) {
            //user - picture
            if (!array_key_exists($v0['user_id'], $a_picture)) {
                $picture = \Core::get_userpicture($v0['user_id']);
                $a_picture[$v0['user_id']] = is_image(PATH_STORAGE . $picture) ? fileinfo($Image->set(PATH_STORAGE . $picture)->setSize(160, 160)->save())['url'] : null;
            }

            //album - cover
            $cover = null;
            $cover_width = null;
            $cover_height = null;

            if (is_image(PATH_UPLOAD . $v0['cover'])) {
                $I_image = $Image->set(PATH_UPLOAD . $v0['cover']);

                $cover = fileinfo($I_image->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'];
                $cover_width = $I_image->getWidthTarget();
                $cover_height = $I_image->getHeightTarget();
            }

            //album - usefor
            $a_usefor = array_fill_keys($a_usefor_all, false);

            foreach ($a_usefor as $k1 => $v1) {
                if (isset($a_usefor_inused[$v0['album_id']]) && in_array($k1, $a_usefor_inused[$v0['album_id']])) $a_usefor[$k1] = true;
            }

            if ($v0['audio_mode'] != 'none') $a_usefor['audio'] = true;

            //event
            $a_event = null;
            if ($rank == 'mine') {
                $m_event = (new \eventModel)->getJoinableByTemplate($v0['template_id']);

                foreach ($m_event as $v1) {
                    list ($result1, $message1, , $data1) = array_decode_return((new \eventModel)->is_contribution($v1['event']['event_id'], $v0['album_id']));
                    if ($result1 != 1) {
                        $result = $result1;
                        $message = $message1;
                        goto _return;
                    }

                    $a_event[] = [
                        'event_id' => $v1['event']['event_id'],
                        'name' => $v1['event']['name'],
                        'contributionstatus' => $data1['event']['contributionstatus'],
                    ];
                }
            }

            $data[] = [
                'album' => [
                    'act' => $v0['act'],
                    'album_id' => $v0['album_id'],
                    'count_photo' => empty($v0['photo']) ? 0 : count(json_decode($v0['photo'], true)),
                    'cover' => $cover,
                    'cover_width' => $cover_width,
                    'cover_height' => $cover_height,
                    'description' => strip_tags($v0['description']),
                    'insertdate' => date('Y-m-d', strtotime($v0['inserttime'])),
                    'location' => $v0['location'],
                    'name' => $v0['album_name'],
                    'usefor' => $a_usefor,
                    'zipped' => $v0['zipped'],
                ],
                'cooperation' => [
                    'identity' => empty($v0['identity']) ? null : $v0['identity']
                ],
                'cooperationstatistics' => [
                    'count' => (new \cooperationModel)->column(['count(1)'])->where([[[['`type`', '=', 'album'], ['type_id', '=', $v0['album_id']]], 'and']])->fetchColumn(),
                ],
                'event' => $a_event,
                'template' => [
                    'template_id' => $v0['template_id'],
                ],
                'user' => [
                    'user_id' => $v0['user_id'],
                    'name' => $v0['user_name'],
                    'picture' => $a_picture[$v0['user_id']],
                ],
                'usergrade' => [
                    'photo_limit_of_album' => \usergradeModel::getPhotoLimitOfAlbum($v0['user_id']),
                ]
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getcooperation()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'type', 'type_id', 'user_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'type', 'type_id', 'user_id'])) json_encode_return(0, _('Sign error.'));
        $user_id_0 = $_POST['id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];
        $user_id_1 = $_POST['user_id'];

        if (!in_array($type, ['album', 'template'])) json_encode_return(0, _('Unknown case of type.'));

        $this->__checkUser($user_id_0);

        $m_cooperation = Model('cooperation')->column(['identity'])->where([[[['`type`', '=', $type], ['type_id', '=', $type_id], ['user_id', '=', $user_id_1]], 'and']])->fetch();
        if (empty($m_cooperation)) json_encode_return(0, _('共用關係不存在。'));

        json_encode_return(1, null, null, $m_cooperation['identity']);
    }

    function getcooperationlist()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'type', 'type_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'type', 'type_id'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];

        if (!in_array($type, ['album', 'template'])) json_encode_return(0, _('Unknown case of type.'));

        $this->__checkUser($user_id);

        $Image = new \Core\Image();

        $m_cooperation = Model('cooperation')->menu($type, $type_id)->fetchAll();
        $data = [];
        $a_picture = [];
        foreach ($m_cooperation as $v0) {
            if (!array_key_exists($v0['user_id'], $a_picture)) {
                $picture = \Core::get_userpicture($v0['user_id']);
                $a_picture[$v0['user_id']] = is_image(PATH_STORAGE . $picture) ? fileinfo($Image->set(PATH_STORAGE . $picture)->setSize(160, 160)->save())['url'] : null;
            }

            $data[] = [
                'cooperation' => [
                    'identity' => $v0['identity'],
                ],
                'user' => [
                    'name' => $v0['name'],
                    'picture' => $a_picture[$v0['user_id']],
                    'user_id' => $v0['user_id'],
                ]
            ];
        }

        json_encode_return(1, null, null, $data);
    }

    function getcreative()
    {
        return (new \Controller\v1_1\api)->getcreative();
    }

    function getdownloadlist()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_download = Model('download')->where([[[['user_id', '=', $user_id], ['type', '=', 'album'], ['state', '=', 'pretreat']], 'and']])->fetchAll();
        $data = array();
        if (!empty($m_download)) {
            $Image = new \Core\Image();

            foreach ($m_download as $v0) {
                $column = [
                    'album_id',
                    'user_id',
                    'name',
                    'description',
                    'cover',
                    'location',
                ];
                $m_album = Model('album')->column($column)->where([[[['album_id', '=', $v0['id']]], 'and']])->fetch();

                $m_user = Model('user')->column(['name'])->where([[[['user_id', '=', $m_album['user_id']]], 'and']])->fetch();
                $data[] = [
                    'download_id' => $v0['download_id'],
                    'albumid' => $m_album['album_id'],
                    'author' => $m_user['name'],
                    'title' => $m_album['name'],
                    'prize' => $v0['point'],
                    'buydate' => $v0['inserttime'],
                    'location' => $m_album['location'],
                    'description' => strip_tags($m_album['description']),
                    'coverurl' => is_image(PATH_UPLOAD . $m_album['cover']) ? fileinfo($Image->set(PATH_UPLOAD . $m_album['cover'])->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
                ];
            }
        }

        json_encode_return(1, null, null, $data);
    }

    function getevent()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'event_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'event_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $event_id = $_POST['event_id'];

        $this->__checkUser($user_id);

        list ($result0, $message0, , $data0) = array_decode_return((new \eventModel)->usable($event_id, $refer = 'api'));

        if ($result0 == 0) {
            $result = $result0;
            $message = $message0;
            goto _return;
        }

        $result = $result0;

        $event = $data0['event'];

        $data = [
            'event' => [
                'contribute_endtime' => $event['contribute_endtime'],
                'contribute_starttime' => $event['contribute_starttime'],
                'contribution' => $event['contribution'],
                'endtime' => $event['endtime'],
                'image' => is_image(PATH_UPLOAD . $event['image_960x540']) ? URL_UPLOAD . $event['image_960x540'] : null,
                'name' => $event['name'],
                'popularity' => $event['popularity'],
                'prefix_text' => $event['prefix_text'],
                'starttime' => $event['starttime'],
                'title' => $event['title'],
                'url' => \frontstageController::url('event', 'info', ['event_id' => $event_id]),
                'vote_endtime' => $event['vote_endtime'],
                'vote_starttime' => $event['vote_starttime'],
            ],
            'event_templatejoin' => $data0['event_templatejoin'],
            'special' => [
                'url' => $event['exchange_page'] ? \specialModel::getUrl($event_id) : null,
            ]
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function geteventlist()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_event = Model('event')->menu();

        $a_event = [];
        foreach ($m_event as $v0) {
            $a_event[] = [
                'event_id' => $v0['event_id'],
                'name' => $v0['event_name'],
            ];
        }

        $data = [
            'event' => $a_event,
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getinfoofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'album_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        list($result1, $message1, , $data1) = array_decode_return(Model('album')->diyable($album_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        $data = [
            'photo' => [
                'count' => empty($data1['photo']) ? 0 : count(json_decode($data1['photo'], true)),
            ],
            'usergrade' => [
                'photo_limit_of_album' => \usergradeModel::getPhotoLimitOfAlbum($data1['user_id']),
            ],
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getpayload()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'platform', 'platform_flag', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'platform', 'platform_flag'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $platform = $_POST['platform'];
        $platform_flag = $_POST['platform_flag'];

        if (!in_array($platform, ['apple', 'google'])) {
            $result = 0;
            $message = _('Unknown case of platform.');
            goto _return;
        }

        $this->__checkUser($user_id);

        $m_buy = Model('buy')->column(['assets', 'assets_item', 'total', 'currency', 'obtain', 'act'])->where([[[['platform', '=', $platform], ['platform_flag', '=', $platform_flag]], 'and']])->fetch();
        if (empty($m_buy)) {
            $result = 0;
            $message = _('Buy does not exist.');
            goto _return;
        }
        if ($m_buy['act'] != 'open') {
            $result = 0;
            $message = _('Buy is not open.');
            goto _return;
        }

        switch ($m_buy['assets']) {
            case 'usergrade':
                $assets_info = [
                    'assets_item' => $m_buy['assets_item'],
                    'obtain' => $m_buy['obtain'],
                ];
                break;

            case 'userpoint':
                $assets_info = [
                    'obtain' => $m_buy['obtain'],
                ];
                break;

            default:
                $result = 0;
                $message = _('Unknown case of assets.');
                goto _return;
                break;
        }

        $add = [
            'cashflow_id' => $platform,
            'user_id' => $user_id,
            'platform' => $platform,
            'assets' => $m_buy['assets'],
            'assets_info' => json_encode($assets_info),
            'total' => $m_buy['total'],
            'currency' => $m_buy['currency'],
            'remote_ip' => remote_ip(),
            'state' => 'pretreat',
            'fulfill' => 'pretreat',
            'inserttime' => inserttime(),
        ];
        $order_id = Model('order')->add($add);
        if (!$order_id) {
            $result = 0;
            $message = _('[Order] occurs exception, please contact us.');
            goto _return;
        }

        $data = $order_id;

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getphotoofdiy()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'photo_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'album_id', 'photo_id'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];

        $this->__checkUser($user_id);

        list($result, $message, , $m_photo) = array_decode_return(Model('photo')->diyable($photo_id, $album_id, $user_id));
        if (!$result) json_encode_return(0, $message);

        $Image = new \Core\Image;

        $data = [
            'photo_id' => $m_photo['photo_id'],
            'image_url' => is_image(PATH_UPLOAD . $m_photo['image']) ? URL_UPLOAD . $m_photo['image'] : null,
            'image_url_thumbnail' => is_image(PATH_UPLOAD . $m_photo['image']) ? fileinfo($Image->set(PATH_UPLOAD . $m_photo['image'])->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
        ];

        json_encode_return(1, null, null, $data);
    }

    function getphotousefor_user()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'photo_id', 'identifier', 'sign'])) {
            $result = 0;
            $message = 'Param error.';
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'photo_id', 'identifier'])) {
            $result = 0;
            $message = 'Sign error.';
            goto _return;
        }
        $user_id = $_POST['id'];
        $photo_id = $_POST['photo_id'];
        $identifier = $_POST['identifier'];

        $this->__checkUser($user_id);

        list ($result1, $message1) = array_decode_return((new \albumModel)->usable('photo', $photo_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        $device_id = (new \deviceModel)->getDevice_id($user_id, $identifier);

        list ($result2, $message2, , $data2) = array_decode_return((new \photoModel)->ableToInsertUsefor_User($photo_id, $user_id, $device_id));
        if ($result2 != 1) {
            $result = $result2;
            $message = $message2;

            if ($result2 == 2) {
                $Image = new \Core\Image;

                $a_photousefor = $data2['photousefor'];
                $a_photousefor_user = $data2['photousefor_user'];

                $data = [
                    'photousefor' => [
                        'description' => strip_tags($a_photousefor['description']),
                        'image' => is_image(PATH_UPLOAD . $a_photousefor['image']) ? fileinfo($Image->set(PATH_UPLOAD . $a_photousefor['image'])->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
                        'name' => $a_photousefor['name'],
                        'photousefor_id' => $a_photousefor['photousefor_id'],
                    ],
                    'photousefor_user' => [
                        'photousefor_user_id' => $a_photousefor_user['photousefor_user_id'],
                    ],
                ];
            }

            goto _return;
        }

        Model()->beginTransaction();

        $data3 = (new \photoModel)->insertUsefor_User($photo_id, $user_id, $device_id);

        Model()->commit();

        $Image = new \Core\Image;

        $a_photousefor = $data3['photousefor'];
        $a_photousefor_user = $data3['photousefor_user'];

        $data = [
            'photousefor' => [
                'description' => strip_tags($a_photousefor['description']),
                'image' => is_image(PATH_UPLOAD . $a_photousefor['image']) ? fileinfo($Image->set(PATH_UPLOAD . $a_photousefor['image'])->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
                'name' => $a_photousefor['name'],
                'photousefor_id' => $a_photousefor['photousefor_id'],
            ],
            'photousefor_user' => [
                'photousefor_user_id' => $a_photousefor_user['photousefor_user_id'],
            ],
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getpointstore()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'platform', 'currency', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'platform', 'currency'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $platform = $_POST['platform'];
        $currency = $_POST['currency'];

        if (!in_array($platform, ['apple', 'google'])) {
            $result = 0;
            $message = _('Unknown case of platform.');
            goto _return;
        }
        if (!in_array($currency, ['TWD', 'USD'])) {
            $result = 0;
            $message = _('Unknown case of currency.');
            goto _return;
        }

        $this->__checkUser($user_id);

        $m_buy = Model('buy')->column(['platform_flag', 'total', 'obtain'])->where([[[['platform', '=', $platform], ['assets', '=', 'userpoint'], ['assets_item', '=', 'point'], ['currency', '=', $currency], ['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();
        if (empty($m_buy)) {
            $result = 0;
            $message = _('Buy does not exist.');
            goto _return;
        }

        $data = [];
        foreach ($m_buy as $v0) {
            $data[] = [
                'platform_flag' => $v0['platform_flag'],
                'total' => $v0['total'],
                'obtain' => $v0['obtain'],
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getprofile()
    {
        return (new \Controller\v1_1\api)->getprofile();
    }

    function getpushqueue()
    {
        return (new \Controller\api)->getpushqueue();
    }

    function getqrcode()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'type', 'type_id', 'effect', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'type', 'type_id', 'effect'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];
        $effect = $_POST['effect'];
        $is = isset($_POST['is']) ? json_decode($_POST['is'], true) : null;

        $this->__checkUser($user_id);

        switch ($effect) {
            case 'execute':

                switch ($type) {
                    case 'album':
                        $is_cooperation = (isset($is['is_cooperation']) && $is['is_cooperation']) ? true : false;
                        $is_follow = (isset($is['is_follow']) && $is['is_follow']) ? true : false;

                        $array_0 = ['type' => $type, 'type_id' => $type_id, 'is_cooperation' => $is_cooperation, 'is_follow' => $is_follow];

                        $url = \frontstageController::url('highway', 'index', array_merge($array_0, ['sign' => encrypt($array_0)]));
                        break;

                    default:
                        $result = 0;
                        $message = 'Unknown case of "type".';
                        goto _return;
                        break;
                }

                break;

            case 'guide':

                switch ($type) {
                    case 'album':
                        $url = \frontstageController::url('album', 'content', ['album_id' => $type_id, 'autoplay' => 1, 'categoryarea_id' => \albumModel::getCategoryAreaId($type_id)]);
                        break;

                    case 'template':
                        $url = \frontstageController::url('template', 'content', ['template_id' => $type_id]);
                        break;

                    case 'user':
                        $url = \Core::get_creative_url($type_id);
                        break;
                }

                break;

            default:
                $result = 0;
                $message = 'Unknown case of "effect".';
                goto _return;
                break;
        }

        $QRcode = new \Core\QRcode();

        $data = $QRcode->setTextUrl($url)->setLevel(1)->getBase64();

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getrecommendedauthor()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'rank', 'limit', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'rank', 'limit'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $rank = $_POST['rank'];
        $limit = $_POST['limit'];

        $this->__checkUser($user_id);

        list($searchtype, $searchkey) = explode('=', $rank);

        $Image = new \Core\Image;

        switch ($searchtype) {
            case 'official':
                $m_user = Model('user')->recommended($user_id, $searchtype)->limit($limit)->fetchAll();
                break;

            case 'cellphone':
            case 'facebook':
                $m_user = $searchkey == null ? [] : Model('user')->recommended($user_id, $searchtype, $searchkey)->limit($limit)->fetchAll();
                break;

            default:
                json_encode_return(0, _('Unknown case of rank.'));
                break;
        }
        $data = [];
        $a_picture = [];
        foreach ($m_user as $v0) {
            if (!array_key_exists($v0['user_id'], $a_picture)) {
                $picture = \Core::get_userpicture($v0['user_id']);
                $a_picture[$v0['user_id']] = is_image(PATH_STORAGE . $picture) ? fileinfo($Image->set(PATH_STORAGE . $picture)->setSize(160, 160)->save())['url'] : null;
            }

            $data[] = [
                'follow' => [
                    'count_from' => empty($v0['count_from']) ? 0 : $v0['count_from'],
                ],
                'user' => [
                    'user_id' => $v0['user_id'],
                    'name' => $v0['name'],
                    'description' => strip_tags($v0['description']),
                    'picture' => $a_picture[$v0['user_id']],
                    'inserttime' => $v0['inserttime'],
                ],
            ];
        }

        json_encode_return(1, null, null, $data);
    }

    function getreportintentlist()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_reportintent = Model('reportintent')->column(['reportintent_id', 'name'])->where([[[['act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();
        foreach ($m_reportintent as $v0) {
            $data[] = [
                'reportintent_id' => $v0['reportintent_id'],
                'name' => $v0['name'],
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getsettings()
    {
        if (!$this->__checkParamIsSet(['keyword', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['keyword'])) json_encode_return(0, _('Sign error.'));
        $keyword = $_POST['keyword'];

        json_encode_return(1, null, null, Model('settings')->getByKeyword($keyword));
    }

    function gettemplate()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'template_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'template_id'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $template_id = $_POST['template_id'];

        $this->__checkUser($user_id);

        $Image = new \Core\Image;

        $column = ['template.template_id', 'template.user_id', 'template.name template_name', 'template.description', 'template.point', 'user.name user_name'];
        $join = [['left join', 'user', 'using(user_id)']];
        $where = [
            [[['template.template_id', '=', $template_id], ['template.act', '=', 'open']], 'and'],
            [[['template.user_id', '=', 0], ['user.act', '=', 'open']], 'or']
        ];
        $m_template0 = Model('template')->column($column)->join($join)->where($where)->fetch();
        $a_album = [];
        $a_frame = [];
        $a_other = [];
        $a_template = [];
        $a_user = [];
        if (!empty($m_template0)) {
            $m_album = Model('album')->template([[[['album.template_id', '=', $m_template0['template_id']]], 'and']], ['album.inserttime' => 'desc'], '0,5');
            foreach ($m_album as $v0) {
                $a_album[] = [
                    'album_id' => $v0['album_id'],
                    'cover' => is_image(PATH_UPLOAD . $v0['cover']) ? fileinfo($Image->set(PATH_UPLOAD . $v0['cover'])->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'] : null,
                    'name' => $v0['name'],
                ];
            }

            $m_frame = Model('frame')->column(['user_id', 'url'])->where([[[['template_id', '=', $m_template0['template_id']], ['act', '=', 'open']], 'and']])->fetchAll();
            foreach ($m_frame as $v0) {
                $path_frame = PATH_STORAGE . SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $v0['user_id'] . DIRECTORY_SEPARATOR . $v0['url'];
                $a_frame[] = [
                    'url' => is_image($path_frame) ? fileinfo($Image->set($path_frame)->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'] : null,
                ];
            }

            $m_template1 = Model('template')->column(['template_id', 'name', 'image'])->where([[[['template_id', '!=', $m_template0['template_id']], ['user_id', '=', $m_template0['user_id']], ['act', '=', 'open']], 'and']])->order(['inserttime' => 'desc'])->limit('0,5')->fetchAll();
            foreach ($m_template1 as $v0) {
                $a_other[] = [
                    'image' => is_image(PATH_UPLOAD . M_PACKAGE . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . M_PACKAGE . $v0['image'])->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'] : null,
                    'name' => $v0['name'],
                    'template_id' => $v0['template_id'],
                ];
            }

            $a_template = [
                'description' => strip_tags($m_template0['description']),
                'name' => $m_template0['template_name'],
                'own' => Model('template')->is_own($m_template0['template_id'], $user_id),
                'point' => $m_template0['point'],
            ];

            $a_user = [
                'name' => ($m_template0['user_id'] == 0) ? 'pinpinbox' : $m_template0['user_name']
            ];
        }

        $data = [
            'album' => $a_album,
            'frame' => $a_frame,
            'other' => $a_other,
            'template' => $a_template,
            'user' => $a_user,
        ];

        json_encode_return(1, null, null, $data);
    }

    function gettemplatelist()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'rank', 'limit', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'rank', 'limit'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $rank = $_POST['rank'];
        $limit = $_POST['limit'];
        $event_id = empty($_POST['event_id']) ? null : $_POST['event_id'];
        $style_id = empty($_POST['style_id']) ? null : $_POST['style_id'];

        $this->__checkUser($user_id);

        $where = [];

        if ($event_id !== null) {
            $a_template_id = array_column(Model('event_templatejoin')->column(['template_id'])->where([[[['event_id', '=', $event_id]], 'and']])->fetchAll(), 'template_id');
            if ($a_template_id) {
                $where[] = [[['template.template_id', 'in', $a_template_id]], 'and'];
            }
        }

        if ($style_id !== null) {
            $where[] = [[['template.style_id', '=', $style_id]], 'and'];
        }

        switch ($rank) {
            case 'free':
                $m_template = Model('template')->getFree($where, ['template.inserttime' => 'desc'], $limit);
                break;

            case 'hot':
                $m_template = Model('template')->getHot($where, ['template.inserttime' => 'desc'], $limit);
                break;

            case 'own':
                $m_template = Model('template')->getOwn($user_id, $where, ['template.inserttime' => 'desc'], $limit);
                break;

            case 'sponsored':
                $m_template = Model('template')->getSponsored($where, ['template.inserttime' => 'desc'], $limit);
                break;

            default:
                json_encode_return(0, _('Unknown case of rank.'));
                break;
        }

        $Image = new \Core\Image;

        $data = [];
        foreach ($m_template as $v0) {
            $data[] = [
                'template' => [
                    'description' => strip_tags($v0['description']),
                    'image' => is_image(PATH_UPLOAD . M_PACKAGE . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . M_PACKAGE . $v0['image'])->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'] : null,
                    'name' => $v0['template_name'],
                    'own' => Model('template')->is_own($v0['template_id'], $user_id),
                    'point' => $v0['point'],
                    'template_id' => $v0['template_id'],
                ],
                'templatestatistics' => [
                    'count' => $v0['count']
                ],
                'user' => [
                    'name' => $v0['user_name']
                ]
            ];
        }

        json_encode_return(1, null, null, $data);
    }

    function gettemplatestylelist()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_style = Model('style')->menu()->fetchAll();
        $data = [];
        foreach ($m_style as $v0) {
            $data[] = [
                'style_id' => $v0['style_id'],
                'name' => $v0['name'],
            ];
        }

        json_encode_return(1, null, null, $data);
    }

    function getupdatelist()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'limit', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'limit'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $limit = $_POST['limit'];
        $rank = empty($_POST['rank']) ? null : $_POST['rank'];

        $this->__checkUser($user_id);

        switch ($rank) {
            case 'follow'://關注
                $m_album = (new \albumModel)->getFollow($user_id, null, $limit);
                break;

            case 'free'://免費
                $m_album = \albumModel::getFree(null, null, null, null, $limit);
                break;

            case 'hot'://熱門
                $m_album = \albumModel::getHot(null, null, null, null, $limit);
                break;

            case 'latest'://最新
            default:
                $m_album = \albumModel::getLatest(null, null, null, null, $limit);
                break;

            case 'sponsored'://贊助
                $m_album = \albumModel::getSponsored(null, null, null, null, $limit);
                break;
        }

        $a_picture = [];
        $data = [];
        $Image = new \Core\Image();

        foreach ($m_album as $v0) {
            //album - cover
            $cover = null;
            $cover_height = null;
            $cover_width = null;

            if (is_image(PATH_UPLOAD . $v0['album']['cover'])) {
                $Image->set(PATH_UPLOAD . $v0['album']['cover']);

                $cover = fileinfo($Image->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'];
                $cover_height = $Image->getHeightTarget();
                $cover_width = $Image->getWidthTarget();
            }

            //album - difftime
            $publishtime = new \DateTime($v0['album']['publishtime']);
            $now = new \DateTime();
            $diff = $publishtime->diff($now);

            //user - picture
            if (!array_key_exists($v0['user']['user_id'], $a_picture)) {
                $picture = \userModel::getPicture($v0['user']['user_id']);
                $a_picture[$v0['user']['user_id']] = is_image(PATH_STORAGE . $picture) ? fileinfo($Image->set(PATH_STORAGE . $picture)->setSize(160, 160)->save())['url'] : null;
            }

            $data[] = [
                'album' => [
                    'album_id' => $v0['album']['album_id'],
                    'cover' => $cover,
                    'cover_height' => $cover_height,
                    'cover_hex' => $v0['album']['cover_hex'],
                    'cover_width' => $cover_width,
                    'location' => $v0['album']['location'],
                    'name' => $v0['album']['name'],
                    'usefor' => \albumModel::getUseForInfo($v0['album']['album_id']),
                ],
                'albumstatistics' => [
                    'count' => $v0['albumstatistics']['count'],
                    'likes' => $v0['albumstatistics']['likes'],
                    'viewed' => $v0['albumstatistics']['viewed'],
                ],
                'follow' => [
                    'count_from' => $v0['follow']['count_from'],
                ],
                'notice' => [
                    'difftime' => $diff->y . ',' . $diff->m . ',' . $diff->d . ',' . $diff->h . ',' . $diff->i,
                ],
                'user' => [
                    'user_id' => $v0['user']['user_id'],
                    'name' => $v0['user']['name'],
                    'picture' => $a_picture[$v0['user']['user_id']],
                ],
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function geturpoints()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'platform', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'platform'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $platform = $_POST['platform'];

        if (!in_array($platform, ['apple', 'google'])) {
            $result = 0;
            $message = _('Unknown case of platform.');
            goto _return;
        }

        $this->__checkUser($user_id);

        $data = Model('user')->getPoint($user_id, $platform);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function hidealbumqueue()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'album_id'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        Model('albumqueue')->where([[[['user_id', '=', $user_id], ['album_id', '=', $album_id]], 'and']])->edit(['visible' => 0]);

        json_encode_return(1);
    }

    function login()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['account', 'pwd', 'sign']);

        if (!$this->__checkSign(['account', 'pwd'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $account = $_POST['account'];
        $password = $_POST['pwd'];

        list($result0, $message0) = array_decode_return((new \userModel())->ableToLogin($account, $password));
        if ($result0 != 1) {
            $result = $result0;
            $message = $message0;
            goto _return;
        }

        (new \userModel())->login($account, $password);

        $m_user = (new \userModel())->getSession();

        //cellphone
        $cellphone = str_replace(' ', '', $m_user['cellphone']);
        if (strlen($cellphone) >= 10 && strlen($cellphone) <= 14) {
            if (substr($cellphone, 0, 2) == '09') {
                $cellphone = \Core\I18N::cellphone($cellphone);
                $cellphone = substr($cellphone, 1, 3) . ',' . substr($cellphone, 4);
            } elseif (substr($cellphone, 0, 5) == '+8869') {
                $cellphone = substr($cellphone, 1, 3) . ',' . substr($cellphone, 4);
            } elseif (substr($cellphone, 0, 6) == '+88609') {
                $cellphone = substr($cellphone, 1, 3) . ',' . substr($cellphone, 5);
            }
        }

        switch ($m_user['gender']) {
            default:
            case'none':
                $gender = 2;
                break;
            case'male':
                $gender = 1;
                break;
            case'female':
                $gender = 0;
                break;
        }

        $cover = \Core::get_usercover($m_user['user_id']);
        $picture = \Core::get_userpicture($m_user['user_id']);

        $c_album = (new \albumModel)->column(['sum(albumstatistics.count) as count', 'sum(albumstatistics.viewed) as viewed'])->join([['left join', 'albumstatistics', 'using(album_id)']])->where([[[['album.user_id', '=', $m_user['user_id']], ['album.act', '=', 'open']], 'and']])->fetch();

        $data = [
            'birthday' => $m_user['birthday'] == '0000-00-00' ? '1900-01-01' : $m_user['birthday'],
            'cellphone' => $cellphone,
            'cover' => is_file(PATH_STORAGE . $cover) ? URL_STORAGE . $cover : null,
            'creative' => (boolean)$m_user['creative'],
            'creative_name' => $m_user['creative_name'],
            'email' => $m_user['email'],
            'follow' => (new \followfromModel())->column(['count(1)'])->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->fetchColumn(),
            'gender' => $gender,
            'id' => $m_user['user_id'],
            'nickname' => $m_user['name'],
            'profilepic' => file_exists(PATH_STORAGE . $picture) ? URL_STORAGE . $picture : null,
            'selfdescription' => $m_user['description'],
            'sociallink' => empty($m_user['sociallink']) ? null : json_decode($m_user['sociallink'], true),
            'token' => (new \tokenModel())->column(['token'])->where([[[['user_id', '=', $m_user['user_id']]], 'and']])->fetchColumn(),
            'usergrade' => \Core::get_usergrade($m_user['user_id']),
            'viewed' => empty($c_album['viewed']) ? 0 : (int)$c_album['viewed'],
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function registration()
    {
        $result = 1;
        $message = null;
        $data = null;

        if (!$this->__checkParamIsSet(['account', 'password', 'name', 'cellphone', 'smspassword', 'way', 'way_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        if (!$this->__checkSign(['account', 'password', 'name', 'cellphone', 'smspassword', 'way', 'way_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $account = $_POST['account'];
        $password = $_POST['password'];
        $name = $_POST['name'];
        $cellphone = $_POST['cellphone'];
        $gender = (isset($_POST['gender']) && $_POST['gender'] !== '') ? $_POST['gender'] : null;
        $birthday = (isset($_POST['birthday']) && $_POST['birthday'] !== '') ? $_POST['birthday'] : null;
        $newsletter = isset($_POST['newsletter']) ? $_POST['newsletter'] : false;
        $smspassword = $_POST['smspassword'];
        $way = $_POST['way'];
        $way_id = $_POST['way_id'];
        $coordinate = (isset($_POST['coordinate']) && $_POST['coordinate'] !== '') ? $_POST['coordinate'] : null;

        list ($phone1, $phone2) = explode(',', $cellphone);
        $cellphone = '+' . $phone1 . $phone2;

        $param = [
            'account' => $account,
            'password' => $password,
            'name' => $name,
            'cellphone' => $cellphone,
            'gender' => $gender,
            'birthday' => $birthday,
            'way' => $way,
            'way_id' => $way_id,
            'coordinate' => $coordinate,
            'newsletter' => $newsletter,
            'smspassword' => $smspassword,
        ];

        switch ($way) {
            case 'none':
                (new \Model)->beginTransaction();

                list ($result1, $message1, , $data1) = array_decode_return((new \userModel)->register($param));

                if ($result1 != 1) {
                    (new \Model)->rollBack();

                    $result = $result1;
                    $message = $message1;

                    goto _return;
                }

                (new \Model)->commit();

                $data = $data1;
                break;

            case 'facebook':
                (new \Model)->beginTransaction();

                list ($result1, $message1, , $data1) = array_decode_return((new \userModel)->register($param));

                if ($result1 != 1) {
                    (new \Model)->rollBack();

                    $result = $result1;
                    $message = $message1;

                    goto _return;
                }

                (new \Model)->commit();

                $data = $data1;
                break;

            default:
                $result = 0;
                $message = _('Unknown case of way.');

                goto _return;
                break;
        }

        _return:

        json_encode_return($result, $message, null, $data);
    }

    function requestsmspwd()
    {
        if (!$this->__checkParamIsSet(['account', 'cellphone', 'usefor', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['account', 'cellphone', 'usefor'])) json_encode_return(0, _('Sign error.'));
        $user_account = $_POST['account'];
        $user_cellphone = $_POST['cellphone'];
        $usefor = $_POST['usefor'];

        list($phone1, $phone2) = explode(',', $user_cellphone);
        $user_cellphone = '+' . $phone1 . $phone2;

        switch ($usefor) {
            case 'editcellphone':
            case 'register':
                list($result, $message) = array_decode_return(Model('smspassword')->usefor($usefor, $user_account, $user_cellphone));
                if (!$result) json_encode_return(0, $message);
                break;

            default:
                json_encode_return(0, _('Unknown case of usefor.'));
                break;
        }

        json_encode_return(1);
    }

    function retrievealbump()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'album_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'album_id'])) {
            $result = 0;
            $message = 'Sign error.';
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $viewed = isset($_POST['viewed']) ? (boolean)$_POST['viewed'] : false;

        $this->__checkUser($user_id);

        if ($viewed) (new \albumModel)->increaseViewed($album_id);

        list ($result1, $message1, , $data1) = array_decode_return((new \albumModel)->content($album_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        list ($result2, $message2, , $data2) = array_decode_return((new \albumModel)->getEventInfo($album_id));
        if ($result2 != 1) {
            $result = $result2;
            $message = $message2;
            goto _return;
        }

        //album
        $own = (new \albumModel)->is_own($album_id, $user_id);

        $album_audio_refer = $data1['album']['audio_refer'];
        $album_audio_target = null;
        switch ($data1['album']['audio_refer']) {
            case 'embed':
                $album_audio_target = $data1['album']['audio_target'];
                break;

            case 'file':
                $album_audio_target = URL_UPLOAD . $data1['album']['audio_target'];
                break;

            case 'system':
                $album_audio_target = URL_STATIC_FILE . SITE_ID . DIRECTORY_SEPARATOR . 'zh_TW' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $data1['album']['audio_target'] . '.mp3';
                break;

            case 'none':
                $album_audio_target = null;
                break;

            default:
                throw new \Exception('Unknown case');
                break;
        }

        //event + eventjoin
        $a_event = null;
        $a_eventjoin = null;
        if ($data2['event']) {
            list($result3, $message3, , $data3) = array_decode_return(Model('event')->is_vote($data2['event']['event_id'], $album_id, $user_id));
            if ($result3 != 1) {
                $result = $result3;
                $message = $message3;
                goto _return;
            }

            $a_event = [
                'event_id' => $data2['event']['event_id'],
                'name' => $data2['event']['name'],
                'url' => \frontstageController::url('event', 'content', ['event_id' => $data2['event']['event_id']]),
                'votestatus' => $data3['event']['votestatus'],
            ];

            $count = (new \eventjoinModel)
                ->column(['`count`'])
                ->where([[[['event_id', '=', $data2['event']['event_id']], ['album_id', '=', $album_id]], 'and']])
                ->fetchColumn();

            $a_eventjoin = [
                'count' => empty($count) ? 0 : $count,
            ];
        }

        //photo
        $a_photo = null;

        $where = [[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']];

        if (!$own && !empty($data1['album']['preview'])) $where = array_merge($where, [[[['image', 'in', json_decode($data1['album']['preview'], true)]], 'and']]);

        $m_photo = (new \photoModel)
            ->column([
                'photo_id',
                'name',
                'description',
                'image',
                'location',
                'usefor',
                'hyperlink',
                'audio_loop',
                'audio_refer',
                'audio_target',
                'video_refer',
                'video_target',
                'duration',
            ])
            ->where($where)
            ->order(['sequence' => 'asc'])
            ->fetchAll();

        if (!empty($m_photo)) {
            $Image = new \Core\Image();

            foreach ($m_photo as $k0 => $v0) {
                //audio
                switch ($v0['audio_refer']) {
                    case 'embed':
                        $audio_refer = $v0['audio_refer'];
                        $audio_target = $v0['audio_target'];
                        break;

                    case 'file':
                        $audio_refer = $v0['audio_refer'];
                        $audio_target = URL_UPLOAD . $v0['audio_target'];
                        break;

                    case 'none':
                        $audio_refer = $v0['audio_refer'];
                        $audio_target = null;
                        break;

                    case 'system':
                        $audio_refer = $v0['audio_refer'];
                        $audio_target = URL_STATIC_FILE . SITE_ID . DIRECTORY_SEPARATOR . 'zh_TW' . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $v0['audio_target'] . '.mp3';
                        break;

                    default:
                        throw new \Exception('Unknown case');
                        break;
                }

                //hyperlink
                $a_hyperlink = null;
                if (!empty($v0['hyperlink'])) {
                    foreach (json_decode($v0['hyperlink'], true) as $k1 => $v1) {
                        $icon = $k0 . '-hyperlink-' . $k1 . '.png';
                        $a_hyperlink[] = [
                            'icon' => $icon,
                            'text' => $v1['text'],
                            'url' => $v1['url'],
                        ];
                    }
                }

                //video
                switch ($v0['video_refer']) {
                    case 'embed':
                        $video_refer = $v0['video_refer'];
                        $video_target = $v0['video_target'];
                        break;

                    case 'file':
                        $video_refer = $v0['video_refer'];
                        $video_target = URL_UPLOAD . $v0['video_target'];

                        break;

                    case 'none':
                        $video_refer = $v0['video_refer'];
                        $video_target = null;
                        break;

                    case 'system':
                        //^尚無
                        break;

                    default:
                        throw new \Exception('Unknown case');
                        break;
                }

                //image
                $tmp_image_url = null;
                $tmp_image_url_thumbnail = null;

                if (is_image(PATH_UPLOAD . $v0['image'])) {
                    $Image->set(PATH_UPLOAD . $v0['image']);

                    switch ($Image->getType()) {
                        case IMAGETYPE_GIF:
                            //2017-08-18 Lion: gif 不做 resize 處理
                            $tmp_image_url_thumbnail = $tmp_image_url = fileinfo(PATH_UPLOAD . $v0['image'])['url'];
                            break;

                        default:
                            $tmp_image_url = fileinfo($Image->setSize(\Config\Image::S6, \Config\Image::S6)->save())['url'];
                            $tmp_image_url_thumbnail = fileinfo($Image->setSize(\Config\Image::S3, \Config\Image::S3)->save())['url'];
                            break;
                    }
                }

                $a_photo[] = [
                    'audio_loop' => (boolean)$v0['audio_loop'],
                    'audio_refer' => $audio_refer,
                    'audio_target' => $audio_target,
                    'description' => $v0['description'],
                    'duration' => $v0['duration'],
                    'location' => $v0['location'],
                    'name' => $v0['name'],
                    'photo_id' => $v0['photo_id'],
                    'usefor' => $v0['usefor'],
                    'video_refer' => $video_refer,
                    'video_target' => $video_target,
                    'image_url' => $tmp_image_url,
                    'image_url_thumbnail' => $tmp_image_url_thumbnail,
                    'hyperlink' => $a_hyperlink,
                ];
            }
        }

        //album - usefor
        $a_usefor_all = array_diff(array_merge((new \photoModel)->fetchEnum('usefor'), ['audio']), ['none']);

        $a_usefor = array_fill_keys($a_usefor_all, false);

        $a_usefor_inused = array_column((new \photoModel)->column(['DISTINCT(`usefor`)'])->where([[[['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])->fetchAll(), 'usefor');

        foreach ($a_usefor as $k0 => $v0) {
            if (in_array($k0, $a_usefor_inused)) $a_usefor[$k0] = true;
        }

        if ($data1['album']['audio_mode'] != 'none') $a_usefor['audio'] = true;

        //photousefor + photousefor_user
        $a_photousefor = null;
        $a_photousefor_user = null;

        if (!empty($m_photo) && $own) {
            $Image = new \Core\Image();

            $m_photousefor = (new \photouseforModel)
                ->column([
                    'photousefor.description',
                    'photousefor.image',
                    'photousefor.name',
                    'photousefor.photo_id',
                    'photousefor.photousefor_id',
                    'photousefor_user.photousefor_user_id',
                ])
                ->join([['inner join', 'photousefor_user', 'using(photousefor_id)']])
                ->where([[[['photousefor.photo_id', 'IN', array_column($m_photo, 'photo_id')], ['photousefor_user.user_id', '=', $user_id]], 'and']])
                ->fetchAll();

            foreach ($m_photousefor as $v0) {
                $a_photousefor[] = [
                    'description' => strip_tags($v0['description']),
                    'image' => is_image(PATH_UPLOAD . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
                    'name' => $v0['name'],
                    'photo_id' => $v0['photo_id'],
                    'photousefor_id' => $v0['photousefor_id'],
                ];

                $a_photousefor_user[] = [
                    'photousefor_id' => $v0['photousefor_id'],
                    'photousefor_user_id' => $v0['photousefor_user_id'],
                ];
            }
        }

        $data = [
            'album' => [
                'count_photo' => empty($data1['album']['photo']) ? 0 : count(json_decode($data1['album']['photo'], true)),
                'description' => strip_tags($data1['album']['description']),
                'display_num_of_collect' => $data1['album']['display_num_of_collect'],
                'inserttime' => $data1['album']['inserttime'],
                'is_likes' => (boolean)(new \album2likesModel)->hasLikes($user_id, $album_id),
                'location' => $data1['album']['location'],
                'name' => $data1['album']['name'],
                'own' => $own,
                'audio_mode' => $data1['album']['audio_mode'],
                'audio_loop' => $data1['album']['audio_loop'],
                'audio_refer' => $album_audio_refer,
                'audio_target' => $album_audio_target,
                'point' => $data1['album']['point'],
                'preview_page_num' => $data1['album']['preview_page_num'],
                'reward_after_collect' => $data1['album']['reward_after_collect'],
                'reward_description' => $data1['album']['reward_description'],
                'usefor' => $a_usefor,
            ],
            'albumstatistics' => [
                'count' => $data1['albumstatistics']['count'],
                'exchange' => $data1['albumstatistics']['exchange'],
                'likes' => $data1['albumstatistics']['likes'],
                'messageboard' => $data1['albumstatistics']['messageboard'],
                'viewed' => $data1['albumstatistics']['viewed'],
            ],
            'event' => $a_event,
            'eventjoin' => $a_eventjoin,
            'photo' => $a_photo,
            'photousefor' => $a_photousefor,
            'photousefor_user' => $a_photousefor_user,
            'user' => [
                'name' => $data1['user']['name'],
                'picture' => $data1['user']['picture'],
                'user_id' => $data1['user']['user_id'],
            ],
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function retrievealbumpbypn()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'productn', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'productn'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $productn = $_POST['productn'];

        $this->__checkUser($user_id);

        $m_albumindex = Model('albumindex')->column(['album_id'])->where([[[['`index`', '=', $productn]], 'and']])->fetch();
        if (empty($m_albumindex)) json_encode_return(0, _('Album Index does not exist.'));

        list($result0, $message0, , $data0) = array_decode_return(Model('album')->content($m_albumindex['album_id']));
        if (!$result0) json_encode_return(0, $message0);

        $data = [
            'albumid' => $m_albumindex['album_id'],
            'author' => $data0['user']['name'],
            'description' => strip_tags($data0['album']['description']),
            'location' => $data0['album']['location'],
            'prize' => $data0['album']['point'],
            'title' => $data0['album']['name'],
            'album' => [
                'own' => Model('album')->is_own($m_albumindex['album_id'], $user_id)
            ],
            'albumstatistics' => [
                'count' => $data0['albumstatistics']['count']
            ],
            'user' => [
                'user_id' => $data0['user']['user_id']
            ],
        ];
        if (!empty($data0['album']['preview'])) {
            $Image = new \Core\Image();

            foreach (json_decode($data0['album']['preview'], true) as $k0 => $v0) {
                $data['s' . ($k0 + 1)] = is_image(PATH_UPLOAD . $v0) ? fileinfo($Image->set(PATH_UPLOAD . $v0)->setSize(\Config\Image::S6, \Config\Image::S6)->save())['url'] : null;
            }
        }

        json_encode_return(1, null, null, $data);
    }

    function retrieveauthor()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'albumid', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'albumid'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $albumid = $_POST['albumid'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('album_id', '=', $albumid)), 'and'),
        );
        $m_album = Model('album')->where($where)->fetch();
        if (empty($m_album)) {
            json_encode_return(0, _('Album does not exist.'));
        } elseif ($m_album['act'] != 'open') {
            json_encode_return(0, _('Album is not open.'));
        }
        $where = array(
            array(array(array('user_id', '=', $m_album['user_id'])), 'and'),
        );
        $m_user1 = Model('user')->where($where)->fetch();
        $where = array(
            array(array(array('user_id', '=', $user_id)), 'and'),
        );
        $m_followto = Model('followto')->where($where)->fetchAll();
        $a_to = array();
        foreach ($m_followto as $v0) {
            $a_to[] = $v0['to'];
        }
        $picture = \Core::get_userpicture($m_user1['user_id']);
        $data = array();
        $data['authorid'] = $m_user1['user_id'];
        $data['name'] = $m_user1['name'];
        $data['profilepic'] = file_exists(PATH_STORAGE . $picture) ? URL_STORAGE . $picture : null;
        $data['bio'] = strip_tags($m_user1['description']);
        $data['follow'] = (empty($a_to) || !in_array($m_user1['user_id'], $a_to)) ? 0 : 1;

        json_encode_return(1, null, null, $data);
    }

    function retrievecatgeorylist()
    {
        $this->__checkParamIsSet(['id', 'token', 'sign']);

        if (!$this->__checkSign(['id', 'token'])) json_encode_return(0, _('Sign error.'));

        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_categoryarea = (new \categoryareaModel)
            ->column([
                'categoryarea_id',
                'colorhex',
                'image_360x360',
                '`name`',
            ])
            ->where([[[['level', '=', 0], ['act', '=', 'open']], 'and']])
            ->order(['sequence' => 'ASC'])
            ->fetchAll();

        $data = [];

        foreach ($m_categoryarea as $v0) {
            $data[] = [
                'categoryarea' => [
                    'categoryarea_id' => $v0['categoryarea_id'],
                    'colorhex' => $v0['colorhex'],
                    'image_360x360' => is_file(PATH_UPLOAD . $v0['image_360x360']) ? path2url(PATH_UPLOAD . $v0['image_360x360']) : null,
                    'name' => $v0['name'],
                ],
            ];
        }

        json_encode_return(1, null, null, $data);
    }

    function retrievehotrank()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'categoryarea_id', 'limit', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'categoryarea_id', 'limit'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $categoryarea_id = $_POST['categoryarea_id'];
        $limit = $_POST['limit'];
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $a_picture = [];
        $data = [];
        $Image = new \Core\Image();
        $m_album = \albumModel::getLatest($categoryarea_id, null, null, null, $limit);

        foreach ($m_album as $v0) {
            //album - cover
            $cover = null;
            $cover_height = null;
            $cover_width = null;

            if (is_image(PATH_UPLOAD . $v0['album']['cover'])) {
                $Image->set(PATH_UPLOAD . $v0['album']['cover']);

                $cover = fileinfo($Image->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'];
                $cover_height = $Image->getHeightTarget();
                $cover_width = $Image->getWidthTarget();
            }

            //user - picture
            if (!array_key_exists($v0['user']['user_id'], $a_picture)) {
                $picture = PATH_STORAGE . \userModel::getPicture($v0['user']['user_id']);
                $a_picture[$v0['user']['user_id']] = is_image($picture) ? fileinfo($Image->set($picture)->setSize(160, 160)->save())['url'] : null;
            }

            $data[] = [
                'album' => [
                    'album_id' => $v0['album']['album_id'],
                    'cover' => $cover,
                    'cover_height' => $cover_height,
                    'cover_hex' => $v0['album']['cover_hex'],
                    'cover_width' => $cover_width,
                    'name' => $v0['album']['name'],
                    'usefor' => \albumModel::getUseForInfo($v0['album']['album_id']),
                ],
                'albumstatistics' => [
                    'likes' => $v0['albumstatistics']['likes'],
                    'viewed' => $v0['albumstatistics']['viewed'],
                ],
                'user' => [
                    'user_id' => $v0['user']['user_id'],
                    'name' => $v0['user']['name'],
                    'picture' => $a_picture[$v0['user']['user_id']],
                ],
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function retrievepassword()
    {
        $result = 1;
        $message = null;
        if (!$this->__checkParamIsSet(['account', 'cellphone', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['account', 'cellphone'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $account = $_POST['account'];
        $cellphone = $_POST['cellphone'];

        list($phone1, $phone2) = explode(',', $cellphone);
        $cellphone = '+' . $phone1 . $phone2;

        list($result0, $message0) = array_decode_return(Model('user')->forgotPassword($account, $cellphone));
        if ($result0 != 1) {
            $result = 0;
            $message = $message0;
            goto _return;
        }

        _return:
        json_encode_return($result, $message);
    }

    function search()
    {
        return (new \Controller\v1_1\api)->search();
    }

    function setawssns()
    {
        $result = 1;
        $message = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'devicetoken', 'identifier', 'os', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'devicetoken', 'identifier', 'os'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $devicetoken = $_POST['devicetoken'];
        $identifier = $_POST['identifier'];
        $os = $_POST['os'];

        $this->__checkUser($user_id);

        Model('cronjob');
        Model('device');
        Model('subscription');
        Model()->beginTransaction();

        $param = [
            'user_id' => $user_id,
            'identifier' => $identifier,
            'os' => $os,
            'token' => $devicetoken,
        ];

        list ($result1) = array_decode_return(\deviceModel::ableToDestroy($param));
        if ($result1 == 1) \deviceModel::destroy($param);

        list ($result2, $message2) = array_decode_return(\deviceModel::ableToBuild($param));
        if ($result2 != 1) {
            Model()->rollBack();

            $result = $result2;
            $message = $message2;
            goto _return;
        }

        \deviceModel::build($param);

        Model()->commit();

        _return:
        json_encode_return($result, $message);
    }

    function sortphotoofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'sort', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'album_id', 'sort'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $sort = $_POST['sort'];

        $this->__checkUser($user_id);

        list($result1, $message1) = array_decode_return(Model('photo')->ableToSortPhoto($album_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        Model('photo')->sortPhoto($album_id, explode(',', $sort));

        $m_photo = Model('photo')->column(['photo_id', 'image'])->where([[[['album_id', '=', $album_id]], 'and']])->order(['sequence' => 'asc'])->fetchAll();

        $Image = new \Core\Image();

        foreach ($m_photo as $v0) {
            $data[] = [
                'photo_id' => $v0['photo_id'],
                'image_url' => URL_UPLOAD . $v0['image'],
                'image_url_operate' => is_image(PATH_UPLOAD . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize(\Config\Image::S5, \Config\Image::S5)->save())['url'] : null,
                'image_url_thumbnail' => is_image(PATH_UPLOAD . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function switchstatusofcontribution()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'event_id', 'album_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'event_id', 'album_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $event_id = $_POST['event_id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        Model('eventjoin');
        Model('eventvote');
        Model()->beginTransaction();

        list($result1, $message1, , $data1) = array_decode_return(Model('event')->switchStatusOfContribution($event_id, $album_id));
        if ($result1 != 1) {
            Model()->rollBack();

            $result = $result1;
            $message = $message1;
            goto _return;
        }

        Model()->commit();

        $data = [
            'event' => [
                'contributionstatus' => $data1['event']['contributionstatus'],
            ],
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function switchstatusofvote()
    {
        $result = 1;
        $message = null;
        $data = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'event_id', 'album_id', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'event_id', 'album_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $event_id = $_POST['event_id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        Model('eventjoin');
        Model('eventvote');
        Model()->beginTransaction();

        list($result1, $message1, , $data1) = array_decode_return(Model('event')->switchStatusOfVote($event_id, $album_id, $user_id));
        if ($result1 != 1) {
            Model()->rollBack();

            $result = $result1;
            $message = $message1;
            goto _return;
        }

        Model()->commit();

        $data = [
            'event' => $data1['event'],
            'eventjoin' => $data1['eventjoin'],
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function updatealbumofdiy()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'album_id', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'album_id'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];

        $this->__checkUser($user_id);

        list($result, $message) = array_decode_return(Model('album')->diyable($album_id, $user_id));
        if (!$result) json_encode_return(0, $message);

        Model('album');
        Model('followfrom');
        Model('notice');
        Model('noticequeue');
        Model('photo');
        Model()->beginTransaction();

        list($result, $message) = array_decode_return(Model('album')->save($album_id));
        if (!$result) {
            Model()->rollBack();
            json_encode_return(0, $message);
        }

        Model()->commit();

        json_encode_return(1);
    }

    function updateaudioofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'album_id', 'photo_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'album_id', 'photo_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];

        $this->__checkUser($user_id);

        list ($result, $message) = array_decode_return((new \photoModel)->ableToUpdateAudio($photo_id, $user_id));
        if ($result != 1) {
            goto _return;
        }

        if (empty($_FILES['file'])) {
            $result = 0;
            $message = 'Input name must be [file].';
            goto _return;
        } else {
            if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

                if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
                    $Audio = new \Core\Audio();

                    $Audio->setFile($path);

                    if ($Audio->getType() !== 'mp3') {
                        $path = $Audio->setType('mp3')->save(null, true, true);
                    }

                    Model()->beginTransaction();

                    (new \photoModel)->updateAudio($photo_id, 'file', $path);

                    //2016-08-18 Lion: 刪除 album 自行上傳的音頻
                    $m_album = (new \albumModel)->column(['audio_refer', 'audio_target'])->where([[[['album_id', '=', $album_id]], 'and']])->lock('for update')->fetch();

                    if ($m_album['audio_refer'] == 'file') \Core\File::delete([PATH_UPLOAD . $m_album['audio_target']]);

                    (new \albumModel)->where([[[['album_id', '=', $album_id]], 'and']])->edit([
                        'audio_mode' => 'plural',
                        'audio_loop' => 0,
                        'audio_refer' => 'none',
                        'audio_target' => '',
                    ]);

                    Model()->commit();
                } else {
                    $result = 0;
                    $message = _('Upload failed, please try again.');
                    goto _return;
                }
            } else {
                $result = 0;
                $message = \Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']];
                goto _return;
            }
        }

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function updatecellphone()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'oldcellphone', 'newcellphone', 'smspassword', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'oldcellphone', 'newcellphone', 'smspassword'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $oldcellphone = $_POST['oldcellphone'];
        $newcellphone = $_POST['newcellphone'];
        $smspassword = $_POST['smspassword'];

        $this->__checkUser($user_id);

        $m_user = (new \userModel)->getSession();

        list($phone1, $phone2) = explode(',', $oldcellphone);
        $oldcellphone = '+' . $phone1 . $phone2;
        list($phone1, $phone2) = explode(',', $newcellphone);
        $newcellphone = '+' . $phone1 . $phone2;

        if ($oldcellphone == $newcellphone) json_encode_return(0, _('The old cellphone number and the new cellphone number are the same.'));

        list($result, $message) = array_decode_return((new \smspasswordModel)->verify($m_user['account'], $newcellphone, $smspassword));
        if (!$result) json_encode_return(0, $message);

        (new \Model)->beginTransaction();

        (new \smspasswordModel)->where([[[['user_account', '=', $m_user['account']], ['user_cellphone', '=', $newcellphone]], 'or']])->delete();
        (new \userModel)->where([[[['user_id', '=', $user_id]], 'and']])->edit(['cellphone' => $newcellphone]);

        (new \Model)->commit();

        (new \userModel)->setSession($user_id);

        json_encode_return(1);
    }

    function updatecooperation()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'type', 'type_id', 'user_id', 'identity', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'type', 'type_id', 'user_id', 'identity'])) json_encode_return(0, _('Sign error.'));
        $user_id_0 = $_POST['id'];
        $type = $_POST['type'];
        $type_id = $_POST['type_id'];
        $user_id_1 = $_POST['user_id'];
        $identity = $_POST['identity'];

        $this->__checkUser($user_id_0);

        list($result, $message) = array_decode_return(Model('cooperation')->updateCooperation($type, $type_id, $user_id_1, $identity));
        if (!$result) json_encode_return(0, $message);

        json_encode_return(1);
    }

    function updatephotoofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'album_id', 'photo_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'album_id', 'photo_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];
        $settings = empty($_POST['settings']) ? null : $_POST['settings'];

        $this->__checkUser($user_id);

        list($result1, $message1) = array_decode_return((new \photoModel())->ableToUpdatePhoto($photo_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        $param = null;
        if (isset($settings['description'])) $param['description'] = $settings['description'];
        if (isset($settings['hyperlink'])) $param['hyperlink'] = json_decode($settings['hyperlink'], true);
        if (isset($settings['location'])) $param['location'] = $settings['location'];

        if ($_FILES) {
            if (empty($_FILES['file'])) {
                $result = 0;
                $message = 'Input name must be [file].';
                goto _return;
            } else {
                if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                    if (!in_array(exif_imagetype($_FILES['file']['tmp_name']), [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
                        $result = 0;
                        $message = _('Upload file type only can be JPEG / JPG / PNG.');
                        goto _return;
                    }

                    $path = mkdir_p_v2(PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR) . uniqid() . '.' . strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

                    if (move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
                        $param['image'] = $path;
                    } else {
                        $result = 0;
                        $message = _('Upload failed, please try again.');
                        goto _return;
                    }
                } else {
                    $result = 0;
                    $message = \Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']];
                    goto _return;
                }
            }
        }

        if ($param) {
            (new \Model)->beginTransaction();

            (new \photoModel())->updatePhoto($photo_id, $param);

            (new \Model)->commit();
        }

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }

    /**
     * @deprecated 改用 2.0 gainphotousefor_user
     */
    function updatephotousefor_user()
    {
        $result = 1;
        $message = null;

        $this->__checkParamIsSet(['id', 'token', 'photousefor_user_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'photousefor_user_id'])) {
            $result = 0;
            $message = 'Sign error.';
            goto _return;
        }

        $user_id = $_POST['id'];
        $photousefor_user_id = $_POST['photousefor_user_id'];

        $this->__checkUser($user_id);

        list ($result1, $message1) = array_decode_return((new \albumModel)->usable('photousefor_user', $photousefor_user_id, $user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

        list ($result2, $message2) = array_decode_return((new \photoModel)->ableToGain($photousefor_user_id));
        if ($result2 != 1) {
            $result = $result2;
            $message = $message2;
            goto _return;
        }

        (new \Model)->beginTransaction();

        (new \photoModel)->updateUsefor_User($photousefor_user_id);

        (new \Model)->commit();

        _return:
        json_encode_return($result, $message);
    }

    function updateprofile()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'nickname', 'email', 'gender', 'birthday', 'selfdescription', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'nickname', 'email', 'gender', 'birthday', 'selfdescription'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $nickname = $_POST['nickname'];
        $email = $_POST['email'];
        $gender = $_POST['gender'];
        $birthday = $_POST['birthday'];
        $selfdescription = $_POST['selfdescription'];

        $this->__checkUser($user_id);

        switch ($gender) {
            default:
            case 2:
                $gender = 'none';
                break;
            case 1:
                $gender = 'male';
                break;
            case 0:
                $gender = 'female';
                break;
        }

        $param = [
            'birthday' => date('Y-m-d', strtotime($birthday)),
            'description' => $selfdescription,
            'email' => $email,
            'gender' => $gender,
            'name' => $nickname,
        ];

        if (!(new \userModel)->where([[[['user_id', '=', $user_id]], 'and']])->edit($param)) {
            json_encode_return(0, _('[User] occur exception, please contact us.'));
        }

        (new \userModel)->setSession($user_id);

        json_encode_return(1);
    }

    function updateprofilehobby()
    {
        if (!$this->__checkParamIsSet(['id', 'token', 'hobby', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token', 'hobby'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $hobby = $_POST['hobby'];

        $this->__checkUser($user_id);

        $a_hobby = explode(',', $hobby);

        \hobby_userModel::setHobbyToUser($user_id, $a_hobby);

        json_encode_return(1);
    }

    function updateprofilepic()
    {
        return (new \Controller\v1_1\api)->updateprofilepic();
    }

    function updatepwd()
    {
        $result = 1;
        $message = null;
        if (!$this->__checkParamIsSet(['id', 'token', 'oldpwd', 'newpwd', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }
        if (!$this->__checkSign(['id', 'token', 'oldpwd', 'newpwd'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }
        $user_id = $_POST['id'];
        $oldpwd = $_POST['oldpwd'];
        $newpwd = $_POST['newpwd'];

        $this->__checkUser($user_id);

        list($result0, $message0) = array_decode_return(Model('user')->updatePassword($user_id, $oldpwd, $newpwd));
        if ($result0 != 1) {
            $result = $result0;
            $message = $message0;
            goto _return;
        }

        _return:
        json_encode_return($result, $message);
    }

    function updatevideoofdiy()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'album_id', 'photo_id', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'album_id', 'photo_id'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['album_id'];
        $photo_id = $_POST['photo_id'];

        $this->__checkUser($user_id);

        list ($result, $message) = array_decode_return((new \photoModel)->ableToUpdateVideo($photo_id, $user_id));
        if ($result != 1) {
            goto _return;
        }

        (new \Model)->beginTransaction();

        (new \photoModel)->updateVideo($photo_id, 'file');

        (new \Model)->commit();

        $data = \albumModel::getDataOfDiyForApp($album_id);

        _return:
        json_encode_return($result, $message, null, $data);
    }
}