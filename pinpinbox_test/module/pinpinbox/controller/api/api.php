<?php

namespace Controller;

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
        if (!$this->__checkParamIsSet(array('id', 'token', 'albumid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'albumid'))) json_encode_return(0, _('Sign error.'));
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
        (new \Model)->beginTransaction();
        list($result, $message, $redirect, $data) = array_decode_return(\Core::exchange($user_id, 'google', 'album', $m_album['album_id']));
        if (!$result) {
            (new \Model)->rollBack();
            json_encode_return(0, $message);
        }
        (new \Model)->commit();
        log_file(array('exchange'), array('exchange_id' => $data['exchange_id'], 'user_id' => $user_id, 'point_before' => $data['point'], 'point' => $m_album['point']));
        $data = array(
            'download_id' => $data['download_id'],
            'coverurl' => empty($m_album['cover']) ? null : URL_UPLOAD . getimageresize($m_album['cover'], 501, 501),
        );
        json_encode_return(1, null, null, $data);
    }

    function changefollowstatus()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'authorid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'authorid'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $authorid = $_POST['authorid'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('user_id', '=', $authorid)), 'and'),
        );
        $m_user1 = Model('user')->where($where)->fetch();
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
        json_encode_return(1, null, null, array('followstatus' => $followstatus));
    }

    function checktoken()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('user_id', '=', $user_id)), 'and'),
        );
        $m_token = Model('token')->where($where)->fetch();
        if (empty($m_token)) {
            json_encode_return(0);
        }
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
        if (!$this->__checkParamIsSet(array('id', 'token', 'albumid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'albumid'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $albumid = $_POST['albumid'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('album_id', '=', $albumid)), 'and'),
        );
        $m_album = Model('album')->where($where)->fetch();
        if (empty($m_album)) {
            json_encode_return(0, _('Album does not exist.'));
        }
        $where = array(
            array(array(array('album_id', 'in', explode(',', $albumid))), 'and'),
        );
        if (!Model('album')->where($where)->edit(array('act' => 'delete'))) {
            json_encode_return(0, _('[Album] occur exception, please contact us.'));
        }
        json_encode_return(1);
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

    function facebooklogin()
    {
        if (!$this->__checkParamIsSet(array('facebookid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('facebookid'))) json_encode_return(0, _('Sign error.'));
        $facebookid = $_POST['facebookid'];

        $m_user_facebook = Model('user_facebook')->where(array(array(array(array('facebook_id', '=', $facebookid)), 'and')))->fetch();
        if (empty($m_user_facebook)) {
            json_encode_return(0, _('User does not exist.'));
        }

        $where = array(
            array(array(array('user_id', '=', $m_user_facebook['user_id'])), 'and'),
        );
        $m_user = Model('user')->where($where)->fetch();
        if (empty($m_user)) {
            json_encode_return(0, _('User does not exist.'));
        } elseif ($m_user['act'] != 'open') {
            json_encode_return(0, _('User is not open.'));
        }

        $m_token = Model('token')->where(array(array(array(array('user_id', '=', $m_user['user_id'])), 'and')))->fetch();

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
        if (!$this->__checkParamIsSet(array('id', 'token', 'dataSignature', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'dataSignature'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $dataSignature = $_POST['dataSignature'];

        $this->__checkUser($user_id);

        curl(\frontstageController::url('cashflow', 'feedback', array('cashflow_id' => 'google')), json_decode($dataSignature, true));
        json_encode_return(1, null, null, array('points' => \Core::get_userpoint($user_id, 'google')));
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

    function getalbumsettings()
    {
        $result = 1;
        $message = null;
        $data = null;

        $this->__checkParamIsSet(['id', 'token', 'albumid', 'sign']);

        if (!$this->__checkSign(['id', 'token', 'albumid'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $album_id = $_POST['albumid'];

        $this->__checkUser($user_id);

        $column = [
            'album.user_id',
            'album.name',
            'album.description',
            'album.audio_mode',
            'album.audio_refer',
            'album.audio_target',
            'album.location',
            'album.weather',
            'album.mood',
            'album.point',
            'album.act',
            'categoryarea_category.categoryarea_id',
            'categoryarea_category.category_id',
        ];
        $join = [
            ['left join', 'categoryarea_category', 'using(category_id)']
        ];
        $where = [
            [[['album.album_id', '=', $album_id]], 'and'],
        ];
        $m_album = (new \albumModel())->column($column)->join($join)->where($where)->fetch();

        if (empty($m_album)) {
            $result = 0;
            $message = _('Album does not exist.');
            goto _return;
        }

        $data = [
            'title' => $m_album['name'],
            'description' => strip_tags($m_album['description']),
            'location' => $m_album['location'],
            'firstpaging' => $m_album['categoryarea_id'],
            'secondpaging' => $m_album['category_id'],
            'audio' => ($m_album['audio_mode'] == 'singular' && $m_album['audio_refer'] == 'system') ? $m_album['audio_target'] : null,
            'weather' => $m_album['weather'],
            'mood' => $m_album['mood'],
            'point' => $m_album['point'],
            'act' => $m_album['act'],
            'albumindex' => array_column((new \albumindexModel)->column(['`index`'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchAll(), 'index'),
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getcalbumlist()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('user_id', '=', $user_id), array('act', 'in', array('close', 'open'))), 'and'),
        );
        $m_album = Model('album')->where($where)->fetchAll();
        $data = array();
        foreach ($m_album as $v0) {
            $data[] = array(
                'albumid' => $v0['album_id'],
                'title' => $v0['name'],
                'description' => strip_tags($v0['description']),
                'coverurl' => empty($v0['cover']) ? null : URL_UPLOAD . getimageresize($v0['cover'], 334, 501),
                'address' => $v0['location'],
                'privacy' => $v0['act'] == 'open' ? 0 : 1,
                'createdate' => date('Y-m-d', strtotime($v0['inserttime']))
            );
        }
        json_encode_return(1, null, null, $data);
    }

    function getdownloadlist()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('user_id', '=', $user_id), array('type', '=', 'album'), array('state', '=', 'pretreat')), 'and'),
        );
        $m_download = Model('download')->where($where)->fetchAll();
        $data = array();
        if (!empty($m_download)) {
            foreach ($m_download as $v0) {
                $column = array(
                    'album_id',
                    'user_id',
                    'name',
                    'description',
                    'cover',
                    'location',
                );
                $where = array(
                    array(array(array('album_id', '=', $v0['id'])), 'and'),
                );
                $m_album = Model('album')->column($column)->where($where)->fetch();
                $column = array(
                    'name',
                );
                $where = array(
                    array(array(array('user_id', '=', $m_album['user_id'])), 'and'),
                );
                $m_user = Model('user')->column($column)->where($where)->fetch();
                $data[] = array(
                    'download_id' => $v0['download_id'],
                    'albumid' => $m_album['album_id'],
                    'author' => $m_user['name'],
                    'title' => $m_album['name'],
                    'prize' => $v0['point'],
                    'buydate' => $v0['inserttime'],
                    'location' => $m_album['location'],
                    'description' => strip_tags($m_album['description']),
                    'coverurl' => empty($m_album['cover']) ? null : URL_UPLOAD . getimageresize($m_album['cover'], 334, 501),
                );
            }
        }
        json_encode_return(1, null, null, $data);
    }

    function getpayload()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'productid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'productid'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $productid = $_POST['productid'];

        $this->__checkUser($user_id);

        $m_buy = Model('buy')->where(array(array(array(array('google', '=', $productid)), 'and')))->fetch();
        if (empty($m_buy)) {
            json_encode_return(0, _('Buy does not exist.'));
        } elseif ($m_buy['act'] != 'open') {
            json_encode_return(0, _('Buy is not open.'));
        }
        switch ($m_buy['assets']) {
            case 'usergrade':
                $assets_info = array(
                    'assets_item' => $m_buy['assets_item'],
                    'obtain' => $m_buy['obtain'],
                );
                break;

            case 'userpoint':
                $assets_info = array(
                    'obtain' => $m_buy['obtain'],
                );
                break;

            default:
                json_encode_return(0, _('Unknown case of assets.'));
                break;
        }
        $tmp0 = array(
            'cashflow_id' => 'google',
            'user_id' => $user_id,
            'platform' => 'google',
            'assets' => $m_buy['assets'],
            'assets_info' => json_encode($assets_info),
            'total' => $m_buy['total'],
            'currency' => $m_buy['currency'],
            'remote_ip' => remote_ip(),
            'state' => 'pretreat',
            'inserttime' => inserttime(),
        );
        $order_id = Model('order')->add($tmp0);
        if (!$order_id) {
            json_encode_return(0, _('[Order] occurs exception, please contact us.'));
        }
        json_encode_return(1, null, null, array('payload' => $order_id));
    }

    function getpointstore()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('platform', '=', 'google'), array('assets', '=', 'userpoint'), array('assets_item', '=', 'point'), array('currency', '=', 'TWD'), array('act', '=', 'open')), 'and'),//^注意 TWD 是寫死的
        );
        $m_buy = Model('buy')->where($where)->order(array('sequence' => 'asc'))->fetchAll();
        $a_buy = array();
        foreach ($m_buy as $v0) {
            $a_buy[$v0['obtain']] = array(
                $v0['total'],
                $v0['google'],
            );
        }
        if (empty($a_buy)) {
            json_encode_return(0, _('Buy does not exist.'));
        }
        json_encode_return(1, null, null, array('pcurrency' => $a_buy));
    }

    function getprofile()
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

        $m_user = (new \userModel)->getSession();

        $picture = \Core::get_userpicture($user_id);

        list ($result1, $message1, , $data1) = array_decode_return((new \userModel())->getHobby($user_id));
        if ($result1 != 1) {
            $result = $result1;
            $message = $message1;
            goto _return;
        }

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

        //gender
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

        //viewed
        $c_album = (new \albumModel())->column(['sum(albumstatistics.count) as count', 'sum(albumstatistics.viewed) as viewed'])->join([['left join', 'albumstatistics', 'using(album_id)']])->where([[[['album.user_id', '=', $user_id], ['album.act', '=', 'open']], 'and']])->fetch();

        $data = [
            'birthday' => $m_user['birthday'] == '0000-00-00' ? '1900-01-01' : $m_user['birthday'],
            'cellphone' => $cellphone,
            'creative' => (boolean)$m_user['creative'],
            'creative_name' => $m_user['creative_name'],
            'email' => $m_user['email'],
            'follow' => (new \followfromModel())->column(['count(1)'])->where([[[['user_id', '=', $user_id]], 'and']])->fetchColumn(),
            'gender' => $gender,
            'hobby' => $data1['hobby'],
            'nickname' => $m_user['name'],
            'profilepic' => file_exists(PATH_STORAGE . $picture) ? URL_STORAGE . $picture : null,
            'selfdescription' => strip_tags($m_user['description']),
            'sociallink' => empty($m_user['sociallink']) ? null : json_decode($m_user['sociallink'], true),
            'usergrade' => \Core::get_usergrade($user_id),
            'viewed' => empty($c_album['viewed']) ? 0 : $c_album['viewed'],
        ];

        _return:
        json_encode_return($result, $message, null, $data);
    }

    function getpushqueue()
    {
        $result = 1;
        $message = null;
        $data = null;

        if (!$this->__checkParamIsSet(['id', 'token', 'limit', 'sign'])) {
            $result = 0;
            $message = _('Param error.');
            goto _return;
        }

        if (!$this->__checkSign(['id', 'token', 'limit'])) {
            $result = 0;
            $message = _('Sign error.');
            goto _return;
        }

        $user_id = $_POST['id'];
        $limit = $_POST['limit'];

        $this->__checkUser($user_id);

        $Model_pushqueue = \pushqueueModel::getByUserId($user_id, $limit);

        $data = [];

        foreach ($Model_pushqueue as $v0) {
            $a_cooperation = null;
            $a_template = null;

            if ($v0['cooperation']) {
                $a_cooperation = [
                    'identity' => $v0['cooperation']['identity'],
                ];
            }

            if ($v0['template']) {
                $a_template = [
                    'template_id' => $v0['template']['template_id'],
                ];
            }

            $data[] = [
                'cooperation' => $a_cooperation,
                'pushqueue' => [
                    'image_url' => parent::type2image_url($v0['pushqueue']['target2type'], $v0['pushqueue']['target2type_id']),
                    'inserttime' => $v0['pushqueue']['inserttime'],
                    'message' => $v0['pushqueue']['message'],
                    'target2type' => $v0['pushqueue']['target2type'],
                    'target2type_id' => $v0['pushqueue']['target2type_id'],
                    'url' => $v0['pushqueue']['url'],
                ],
                'template' => $a_template,
            ];
        }

        _return:
        json_encode_return($result, $message, null, $data);
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

    function getupdatelist()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $m_noticequeue = Model('noticequeue')->where(array(array(array(array('user_id', '=', $user_id)), 'and')))->order(array('inserttime' => 'desc'))->fetchAll();
        $data = array();
        foreach ($m_noticequeue as $v0) {
            $where = array();
            $where[] = array(array(array('notice_id', '=', $v0['notice_id']), array('`type`', '=', 'album')), 'and');
            $m_notice = Model('notice')->where($where)->fetch();
            if (!empty($m_notice)) {
                $column = array(
                    'album.album_id',
                    'album.name album_name',
                    'album.description album_description',
                    'album.cover',
                    'album.location',
                    'user.user_id',
                    'user.name user_name',
                );
                $join = array(
                    array('left join', 'user', 'using(user_id)'),
                );
                $where = array(
                    array(array(array('album.album_id', '=', $m_notice['id']), array('album.act', '=', 'open'), array('user.act', '=', 'open')), 'and')
                );
                $m_album = Model('album')->column($column)->join($join)->where($where)->fetch();

                if (empty($m_album)) continue;

                $inserttime = new \DateTime($m_notice['inserttime']);
                $now = new \DateTime();
                $diff = $inserttime->diff($now);
                $picture = \Core::get_userpicture($m_album['user_id']);
                $data[] = array(
                    'authorid' => $m_album['user_id'],
                    'author' => $m_album['user_name'],
                    'picfileurl' => file_exists(PATH_STORAGE . $picture) ? URL_STORAGE . getimageresize($picture, 160, 160) : null,
                    'albumid' => $m_album['album_id'],
                    'title' => $m_album['album_name'],
                    'description' => $m_album['album_description'],
                    'coverurl' => empty($m_album['cover']) ? null : URL_UPLOAD . getimageresize($m_album['cover'], 334, 501),
                    'location' => $m_album['location'],
                    'updatetime' => $diff->y . ',' . $diff->m . ',' . $diff->d . ',' . $diff->h . ',' . $diff->i,
                );
            }
        }
        json_encode_return(1, null, null, $data);
    }

    function geturpoints()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        json_encode_return(1, null, null, ['points' => Model('user')->getPoint($user_id, 'google')]);
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

        list ($result0, $message0) = array_decode_return((new \userModel())->ableToLogin($account, $password));
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

        $picture = \Core::get_userpicture($m_user['user_id']);

        $c_album = (new \albumModel)->column(['sum(albumstatistics.count) as count', 'sum(albumstatistics.viewed) as viewed'])->join([['left join', 'albumstatistics', 'using(album_id)']])->where([[[['album.user_id', '=', $m_user['user_id']], ['album.act', '=', 'open']], 'and']])->fetch();

        $data = [
            'birthday' => $m_user['birthday'] == '0000-00-00' ? '1900-01-01' : $m_user['birthday'],
            'cellphone' => $cellphone,
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
        if (!$this->__checkParamIsSet(array('account', 'pwd', 'nickname', 'cellphone', 'smspassword', 'surl', 'way', 'wayid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('account', 'pwd', 'nickname', 'cellphone', 'smspassword', 'surl', 'way', 'wayid'))) json_encode_return(0, _('Sign error.'));
        $account = $_POST['account'];
        $pwd = $_POST['pwd'];
        $nickname = $_POST['nickname'];
        $cellphone = $_POST['cellphone'];
        $smspassword = $_POST['smspassword'];
        $surl = $_POST['surl'];
        $way = $_POST['way'];
        $wayid = $_POST['wayid'];

        //檢查創作者代號
        if ($surl == null) json_encode_return(0, _('Please enter creative-code.'));
        if (!preg_match('/^[a-zA-Z0-9]+$/', $surl)) json_encode_return(0, _('The creative-code enter only letters and numbers.'));
        if (preg_match('/^index$/i', $surl)) json_encode_return(0, _('Only English letters and numbers are allowed for Author code name.') . 'index');
        $m_user = Model('user')->where(array(array(array(array('creative_code', '=', $surl)), 'and')))->fetch();
        if (!empty($m_user)) {
            json_encode_return(0, _('The creative-code already exists, please use another.'));
        }

        switch ($way) {
            case 'none':
                list($phone1, $phone2) = explode(',', $cellphone);
                $cellphone = '+' . $phone1 . $phone2;

                $where = array(
                    array(array(array('user_account', '=', $account), array('user_cellphone', '=', $cellphone)), 'and')
                );
                $m_smspassword = Model('smspassword')->where($where)->fetch();
                if (empty($m_smspassword)) {
                    json_encode_return(0, _('SMS-password does not exist.'));
                }
                if ($smspassword != $m_smspassword['smspassword']) {
                    json_encode_return(0, _('SMS-password is incorrect.'));
                }

                $m_user = Model('user')->where(array(array(array(array('account', '=', $account), array('way', '=', 'none')), 'and')))->fetch();
                if (!empty($m_user)) {
                    json_encode_return(0, _('The account already exists, please use another.'));
                }

                $m_user = Model('user')->where(array(array(array(array('cellphone', '=', $cellphone)), 'and')))->fetch();
                if (!empty($m_user)) {
                    json_encode_return(0, _('The cellphone number already exists, please use another.'));
                }

                Model('user');
                Model('userstatistics');
                Model('token');
                Model('smspassword');
                Model()->beginTransaction();

                $add = array(
                    'account' => $account,
                    'password' => $pwd,
                    'name' => $nickname,
                    'cellphone' => $cellphone,
                    'email' => $account,
                    'act' => 'open',
                    'creative_code' => $surl,
                    'lastloginip' => remote_ip(),
                    'lastlogintime' => inserttime(),
                    'inserttime' => inserttime(),
                );
                $user_id = Model('user')->add($add);
                if (!$user_id) {
                    Model()->rollBack();
                    json_encode_return(0, _('[User] occur exception, please contact us.'));
                }

                Model('userstatistics')->add(array('user_id' => $user_id));

                $param = array(
                    'user_id' => $user_id,
                    'token' => $token = encrypt(array('user_id' => $user_id, 'time' => time()))
                );
                Model('token')->add($param);

                $where = array(
                    array(array(array('user_account', '=', $account), array('user_cellphone', '=', $cellphone)), 'or')
                );
                if (!Model('smspassword')->where($where)->delete()) {
                    Model()->rollBack();
                    json_encode_return(0, _('[SMSpassword] occur exception, please contact us.'));
                }

                Model()->commit();
                break;

            case 'facebook':
                Model('user');
                Model('user_' . $way);
                Model('userstatistics');
                Model('token');
                Model()->beginTransaction();

                $add = array(
                    'account' => $account,
                    'password' => uniqid(null, true),
                    'name' => $nickname,
                    'email' => $account,
                    'birthday' => '1900-01-01',
                    'creative_code' => $surl,
                    'way' => $way,
                    'act' => 'open',
                    'lastloginip' => remote_ip(),
                    'lastlogintime' => inserttime(),
                    'inserttime' => inserttime(),
                );
                $user_id = Model('user')->add($add);

                Model('user_' . $way)->add(array('user_id' => $user_id, $way . '_id' => $wayid));

                Model('userstatistics')->add(array('user_id' => $user_id));

                $add = array(
                    'user_id' => $user_id,
                    'token' => $token = encrypt(array('user_id' => $user_id, 'time' => time()))
                );
                Model('token')->add($add);

                Model()->commit();
                break;

            default:
                json_encode_return(0, _('Unknown case of way.'));
                break;
        }

        json_encode_return(1, null, null, array('id' => $user_id, 'token' => $token));
    }

    function requestsmspwd()
    {
        if (!$this->__checkParamIsSet(array('account', 'cellphone', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('account', 'cellphone'))) json_encode_return(0, _('Sign error.'));
        $account = $_POST['account'];
        $cellphone = $_POST['cellphone'];

        list($phone1, $phone2) = explode(',', $cellphone);
        $cellphone = '+' . $phone1 . $phone2;

        $m_user = Model('user')->where(array(array(array(array('account', '=', $account), array('way', '=', 'none')), 'and')))->fetch();
        if (!empty($m_user)) {
            json_encode_return(0, _('The account already exists, please use another.'));
        }
        $m_user = Model('user')->where(array(array(array(array('cellphone', '=', $cellphone)), 'and')))->fetch();
        if (!empty($m_user)) {
            json_encode_return(0, _('The cellphone number already exists, please use another.'));
        }
        $where = array(
            array(array(array('user_account', '=', $account), array('user_cellphone', '=', $cellphone)), 'and'),
        );
        $m_smspassword = Model('smspassword')->where($where)->fetch();
        if (empty($m_smspassword)) {
            $smspassword = random_password(4, 's');
            Model('smspassword')->add(array('user_account' => $account, 'user_cellphone' => $cellphone, 'smspassword' => $smspassword));
        } else {
            $smspassword = $m_smspassword['smspassword'];
        }
        //sms
        $message = 'pinpinbox SMS password : ' . $smspassword;
        list($sms_result, $sms_message) = array_decode_return(\Core::extension('sms', 'every8d')->send($cellphone, $message));
        if (!$sms_result) {
            json_encode_return(0, _('[SMS] occur exception, please contact us.'));
        }
        json_encode_return(1);
    }

    function retrievealbump()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'albumid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'albumid'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $albumid = $_POST['albumid'];

        $this->__checkUser($user_id);

        $column = array(
            'album.name album_name',
            'album.description',
            'album.preview',
            'album.location',
            'album.point',
            'album.act',
            'user.name user_name',
        );
        $join = array(
            array('left join', 'user', 'using(user_id)'),
        );
        $where = array(
            array(array(array('album_id', '=', $albumid)), 'and'),
        );
        $m_album = Model('album')->column($column)->join($join)->where($where)->fetch();
        if (empty($m_album)) {
            json_encode_return(0, _('Album does not exist.'));
        } elseif ($m_album['act'] != 'open') {
            json_encode_return(0, _('Album is not open.'));
        }
        $a_preview = empty($m_album['preview']) ? array() : json_decode($m_album['preview'], true);
        $data = array(
            'title' => $m_album['album_name'],
            'author' => $m_album['user_name'],
            'location' => $m_album['location'],
            'description' => strip_tags($m_album['description']),
            'prize' => $m_album['point'],
        );
        foreach ($a_preview as $k0 => $v0) {
            $data['s' . ($k0 + 1)] = URL_UPLOAD . getimageresize($v0, 684, 1026);
        }
        json_encode_return(1, null, null, $data);
    }

    function retrievealbumpbypn()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'productn', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'albumid'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $productn = $_POST['productn'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('`index`', '=', $productn)), 'and'),
        );
        $m_albumindex = Model('albumindex')->where($where)->fetch();
        if (empty($m_albumindex)) {
            json_encode_return(0, _('Album Index does not exist.'));
        }
        $column = array(
            'album.album_id',
            'album.name album_name',
            'album.description',
            'album.preview',
            'album.location',
            'album.point',
            'album.act',
            'user.name user_name',
        );
        $join = array(
            array('left join', 'user', 'using(user_id)'),
        );
        $where = array(
            array(array(array('album_id', '=', $m_albumindex['album_id'])), 'and'),
        );
        $m_album = Model('album')->column($column)->join($join)->where($where)->fetch();
        if (empty($m_album)) {
            json_encode_return(0, _('Album does not exist.'));
        } elseif ($m_album['act'] != 'open') {
            json_encode_return(0, _('Album is not open.'));
        }
        $a_preview = empty($m_album['preview']) ? array() : json_decode($m_album['preview'], true);
        $data = array(
            'albumid' => $m_album['album_id'],
            'title' => $m_album['album_name'],
            'author' => $m_album['user_name'],
            'location' => $m_album['location'],
            'description' => strip_tags($m_album['description']),
            'prize' => $m_album['point'],
        );
        foreach ($a_preview as $k0 => $v0) {
            $data['s' . ($k0 + 1)] = URL_UPLOAD . getimageresize($v0, 684, 1026);
        }
        json_encode_return(1, null, null, $data);
    }

    function retrieveauthor()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'albumid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'albumid'))) json_encode_return(0, _('Sign error.'));
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
        if (!$this->__checkParamIsSet(array('id', 'token', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        $where = array(
            array(array(array('level', '=', 0)), 'and'),
        );
        $m_categoryarea = Model('categoryarea')->where($where)->fetchAll();
        $tmp0 = array();
        foreach ($m_categoryarea as $v0) {
            $tmp0[] = $v0['categoryarea_id'] . ',' . $v0['name'];
        }
        json_encode_return(1, null, null, implode(',', $tmp0));
    }

    function retrievehotrank()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'rankid', 'categoryid', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'rankid', 'categoryid'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $rankid = $_POST['rankid'];
        $categoryid = $_POST['categoryid'];

        $this->__checkUser($user_id);

        $categoryarea_id = $categoryid;//由接口 retrievecatgeorylist 取得而傳過來的, 因此為 categoryarea_id
        $column = array(
            'album.album_id',
            'album.name',
            'album.cover',
            'album.point',
        );
        $join = array(
            array('left join', 'user', 'using(user_id)'),
            array('left join', 'category', 'using(category_id)'),
            array('left join', 'categoryarea_category', 'using(category_id)'),
        );
        $where = array(
            array(array(array('album.act', '=', 'open'), array('user.act', '=', 'open'), array('categoryarea_category.categoryarea_id', '=', $categoryarea_id)), 'and'),
        );
        switch ($rankid) {
            default:
            case 0://熱門下載
                $join[] = array('left join', 'albumstatistics', 'using(album_id)');
                $m_album = Model('album')->column($column)->join($join)->where($where)->order(array('albumstatistics.count' => 'desc', 'album.album_id' => 'desc'))->limit('0,10')->fetchAll();
                break;

            case 1://免費下載
                $where[] = array(array(array('album.point', '=', 0)), 'and');
                $m_album = Model('album')->column($column)->join($join)->where($where)->order(array('album.album_id' => 'desc'))->limit('0,10')->fetchAll();
                break;

            case 2://付費下載
                $where[] = array(array(array('album.point', '>', 0)), 'and');
                $m_album = Model('album')->column($column)->join($join)->where($where)->order(array('album.album_id' => 'desc'))->limit('0,10')->fetchAll();
                break;
        }
        $data = array();
        foreach ($m_album as $v0) {
            $data[] = array(
                'title' => $v0['name'],
                'prize' => $v0['point'],
                'coverurl' => empty($v0['cover']) ? null : URL_UPLOAD . getimageresize($v0['cover'], 168, 252),
                'albumid' => $v0['album_id'],
            );
        }
        json_encode_return(1, null, null, $data);
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

    function updateprofile()
    {
        if (!$this->__checkParamIsSet(array('id', 'token', 'nickname', 'gender', 'birthday', 'selfdescription', 'sign'))) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(array('id', 'token', 'nickname', 'gender', 'birthday', 'selfdescription'))) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];
        $nickname = $_POST['nickname'];
        //^$cellphone = $_POST['cellphone'];
        $gender = $_POST['gender'];
        $birthday = $_POST['birthday'];
        $selfdescription = $_POST['selfdescription'];

        $this->__checkUser($user_id);

        //^
        /*
        list($phone1, $phone2) = explode(',', $cellphone);
        $cellphone = '+'.$phone1.$phone2;
        */
        $where = array(
            array(array(array('user_id', '=', $user_id)), 'and'),
        );
        $param = array();
        $param['name'] = $nickname;
        //^$param['cellphone'] = $cellphone;
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
        $param['gender'] = $gender;
        $param['birthday'] = date('Y-m-d', strtotime($birthday));
        $param['description'] = $selfdescription;
        if (!Model('user')->where($where)->edit($param)) {
            json_encode_return(0, _('[User] occur exception, please contact us.'));
        }
        json_encode_return(1);
    }

    function updateprofilepic()
    {
        //此接口特別不包含 picfile 做驗證
        if (!$this->__checkParamIsSet(['id', 'token', 'sign'])) json_encode_return(0, _('Param error.'));
        if (!$this->__checkSign(['id', 'token'])) json_encode_return(0, _('Sign error.'));
        $user_id = $_POST['id'];

        $this->__checkUser($user_id);

        if (empty($_FILES['file'])) {
            json_encode_return(0, 'Input name must be [file].');
        } else {
            if ($_FILES['file']['error'] == UPLOAD_ERR_OK) {
                if (!in_array(exif_imagetype($_FILES['file']['tmp_name']), [IMAGETYPE_JPEG, IMAGETYPE_PNG])) json_encode_return(0, _('Upload file type only can be JPEG / JPG / PNG.'));

                if (move_uploaded_file($_FILES['file']['tmp_name'], PATH_STORAGE . \userModel::getPicture($user_id))) {
                    \userModel::setPicture($user_id);
                } else {
                    json_encode_return(0, _('Upload failed, please try again.'));
                }
            } else {
                json_encode_return(0, \Core::$_config['CONFIG']['UPLOAD']['ERROR_MESSAGE'][$_FILES['file']['error']]);
            }
        }

        json_encode_return(1, null, null, URL_STORAGE . \userModel::getPicture($user_id));
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
}