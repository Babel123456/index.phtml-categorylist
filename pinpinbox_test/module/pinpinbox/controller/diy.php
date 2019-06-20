<?php

class diyController extends frontstageController
{
    function __construct()
    {
        $result = 1;
        $message = null;
        $redirect = null;

        $album_id = empty($_REQUEST['album_id']) ? null : $_REQUEST['album_id'];
        if ($album_id === null) {
            $result = 0;
            $message = _('Abnormal process, please try again.');
            $redirect = parent::url();
            goto _return;
        }

        $m_user = Model('user')->getSession();
        if (empty($m_user)) {
            $result = 2;
            $message = _('Please login first.');
            $redirect = parent::url('user', 'login', ['redirect' => parent::url('diy', 'index', ['album_id' => $album_id])]);
            goto _return;
        }

        list($result0, $message0) = array_decode_return(Model('album')->diyable($album_id, $m_user['user_id']));
        if ($result0 != 1) {
            $result = $result0;
            $message = $message0;
            $redirect = parent::url('user', 'album');
            goto _return;
        }

        $ua = $_SERVER["HTTP_USER_AGENT"];
        $chrome = strpos($ua, 'Chrome') ? true : false;
        $safari = strpos($ua, 'Safari') ? true : false;
        if ($safari AND !$chrome) {
            $result = 0;
            $message = _('編輯器目前不支援Safari瀏覽器, 建議使用Chrome訪問pinpinbox!');
            $redirect = $_SERVER['HTTP_REFERER'];
            goto _return;
        }

        _return:

        if ($result != 1) {
            if (is_ajax()) {
                if (in_array(M_FUNCTION, ['img_crop_to_file', 'img_save_to_file'])) {
                    $message = json_encode(['result' => $result, 'message' => $message, 'redirect' => $redirect]);
                    die(json_encode(['status' => 'error', 'message' => $message]));
                } else {
                    json_encode_return($result, $message, $redirect);
                }
            } else {
                redirect($redirect, $message);
            }
        }
    }

    function add_media_photo()
    {
        if (is_ajax()) {
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $user = parent::user_get();
            $data = empty($_POST['data']) ? null : json_decode($_POST['data'], true);
            $upload_type = empty($_POST['upload_type']) ? null : $_POST['upload_type'];
            $video_platform = empty($_POST['video_platform']) ? null : $_POST['video_platform'];
            $setPreview = empty($_POST['setPreview']) ? null : $_POST['setPreview'];

            //存圖位置
            $subdir = M_PACKAGE . DIRECTORY_SEPARATOR . M_CLASS . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
            $suburl = M_PACKAGE . '/' . M_CLASS . '/' . date('Ymd') . '/';
            //賦予新檔名
            $basename = uniqid() . '.jpg';

            mkdir_p(PATH_UPLOAD, $subdir);
            $out_put = PATH_UPLOAD . $subdir . $basename;

            switch ($data['refer']) {
                case 'embed':
                    //取得影片縮圖
                    if (fetch_remote_thumbnail($data['value'], $out_put, $video_platform)) {
                        if (exif_imagetype($out_put) !== IMAGETYPE_JPEG) {
                            (new \Core\Image)
                                ->set($out_put)
                                ->setType('jpg')
                                ->save(null, true, true);
                        }

                        \Extension\aws\S3::upload($out_put);
                    } else {
                        json_encode_return(0, _('影片縮圖擷取失敗,請重新輸入網址'), null, null);
                    }
                    break;

                case 'file':
                    if (!empty($data['poster'])) {
                        $basename = $data['poster'];
                    } else {
                        //video實例截圖
                        $Video = new \Core\Video();

                        if ($Video->setFile(PATH_UPLOAD . $data['value'])->saveScreenShot($out_put, false, false)) {
                            \Extension\aws\S3::upload($Video->getOutPath());
                        } else {
                            json_encode_return(0, _('影片縮圖擷取失敗，請重新上傳檔案。'), null, 'file');
                        }
                    }
                    break;
            }

            $hyperlink = [];
            for ($i = 1; $i <= 2; $i++) {
                $hyperlink[] = [
                    'icon' => '',
                    'text' => $data['url' . $i . '_name'],
                    'url' => $data['url' . $i]
                ];
            }
            /**
             *  photo
             */
            $m_photo = (new \photoModel)
                ->column([
                    'photo.user_id',
                    'photo.photo_id',
                    'photo.image',
                    'photo.sequence',
                    'photo.usefor',
                    'photo.audio_refer',
                    'photo.description',
                    'photo.album_id',
                    'user.name',
                ])
                ->join([['left join', 'user', 'using(user_id)']])
                ->where([[[['album_id', '=', $album_id], ['photo.act', '=', 'open']], 'and']])
                ->order(['sequence' => 'asc'])
                ->fetchAll();

            $sequence = empty($m_photo) ? 0 : max(array_column($m_photo, 'sequence')) + 1;

            if ($sequence > 255) $sequence = 255;

            $photo_id = (new \photoModel)
                ->add([
                    'album_id' => $album_id,
                    'user_id' => $user['user_id'],
                    'description' => $data['description'],
                    'location' => $data['location'],
                    'image' => $suburl . $basename,
                    'usefor' => 'video',
                    'hyperlink' => json_encode($hyperlink),
                    'video_refer' => $data['refer'],
                    'video_target' => $data['value'],
                    'name' => '',
                    'state' => 'success',
                    'sequence' => $sequence,
                    'inserttime' => inserttime(),
                ]);

            /**
             * 處理 album
             */
            $a_image = array_column($m_photo, 'image');
            array_push($a_image, $suburl . $basename);
            $edit = array(
                'cover' => $a_image[0],
                'photo' => json_encode($a_image),
                'state' => 'process',
            );
            Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit($edit);

            list($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($album_id);
            $c_image = count($a_image);
            if ($album_limit < $c_image) $album_limit += $c_image;//包含此次的數量
            $m_album = Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
            //refreshPhoto
            $a_photo = [];

            $preview1 = json_decode($setPreview, true);
            $preview2 = (is_array(json_decode($m_album['preview'], true))) ? json_decode($m_album['preview'], true) : [];
            $set_preview = array_unique(array_merge($preview1, $preview2));

            $Image = new \Core\Image;
            foreach ($m_photo as $v0) {
                $rightsParams = ['act' => ['aviary', 'edit', 'delete'], 'album_id' => $album_id, 'photo_id' => $v0['photo_id']];
                $operate_rights = $this->operate_rights($rightsParams);
                $a_photo[] = [
                    'photo_id' => $v0['photo_id'],
                    'image' => $v0['image'],
                    'image_url' => URL_UPLOAD . $v0['image'],
                    'image_url_thumbnail' => is_file(PATH_UPLOAD . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
                    'icon' => ($v0['usefor'] == 'image') ? null : static_file('images/icon_' . $v0['usefor'] . '.png'),
                    'user_name' => $v0['name'],
                    'usefor' => $v0['usefor'],
                    'music_image_thumbnail' => ($v0['audio_refer'] != 'none') ? '<img src="' . static_file('images/icon05.png') . '" height="11" width="11">' : '',
                    'description_image_thumbnail' => ($v0['description'] != '') ? '<img src="' . static_file('js/editor/images/editor/icon_text.png') . '" height="11" width="11">' : '',
                    'aviary_edit_onclick' => ($operate_rights['aviary']) ? 'aviary_edit' : 'no_right',
                    'photo_setting_onclick' => ($operate_rights['edit']) ? 'photo_setting' : 'no_right',
                    'delete_onclick' => ($operate_rights['delete']) ? 'del' : 'no_right',
                    'set_preview' => (in_array($v0['image'], $set_preview)) ? 'checked="checked"' : null,
                ];
            }

            $rightsParams = ['act' => ['aviary', 'edit', 'delete'], 'album_id' => $album_id, 'photo_id' => $photo_id];
            $operate_rights = $this->operate_rights($rightsParams);
            $a_photo[] = [
                'photo_id' => $photo_id,
                'image' => $suburl . $basename,
                'image_url' => URL_UPLOAD . $suburl . $basename,
                'image_url_thumbnail' => is_file(PATH_UPLOAD . $suburl . $basename) ? fileinfo($Image->set(PATH_UPLOAD . $suburl . $basename)->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
                'icon' => static_file('images/icon_video.png'),
                'user_name' => $user['name'],
                'usefor' => 'video',
                'music_image_thumbnail' => '',
                'description_image_thumbnail' => '',
                'aviary_edit_onclick' => ($operate_rights['aviary']) ? 'aviary_edit' : 'no_right',
                'photo_setting_onclick' => ($operate_rights['edit']) ? 'photo_setting' : 'no_right',
                'delete_onclick' => ($operate_rights['delete']) ? 'del' : 'no_right',
                'set_preview' => 'checked="checked"',
            ];

            //當完成最後的一張
            if ($c_image >= $album_limit) {
                json_encode_return(4, _('The number of photos in a album reached the maximum.'), null, ['photo' => $a_photo, 'album_limit' => $album_limit]);
            }

            json_encode_return(1, _('Success.'), null, ['photo' => $a_photo, 'album_limit' => $album_limit]);

        }
    }

    function current_photo_num()
    {
        if (is_ajax()) {
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            if ($album_id === null) json_encode_return(0, _('Abnormal process, please try again.'));

            list ($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($album_id);

            $count = (new \photoModel)
                ->column(['COUNT(1)'])
                ->where([[[['album_id', '=', $album_id], ['photo.act', '=', 'open']], 'and']])
                ->fetchColumn();

            $quota = $album_limit - $count;

            json_encode_return(1, null, null, ['album_limit' => (int)$quota, 'album_left' => $album_left]);
        }

        die;
    }

    function delete()
    {
        if (is_ajax()) {
            $photo_id = empty($_POST['photo_id']) ? null : $_POST['photo_id'];
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $setPreview = empty($_POST['setPreview']) ? null : $_POST['setPreview'];
            if ($photo_id === null) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_user = (new userModel)->getSession();

            list($result0, $message0) = array_decode_return((new photoModel)->ableToDeletePhoto($photo_id, $m_user['user_id']));
            if ($result0 != 1) json_encode_return($result0, $message0);

            (new photoModel)->deletePhoto($photo_id);

            $column = [
                'photo.user_id',
                'photo.album_id',
                'photo.photo_id',
                'photo.image',
                'photo.audio_refer',
                'photo.description',
                'photo.usefor',
                'photo.duration',
                'user.name user_name',
            ];
            $m_photo = Model('photo')->join([['left join', 'user', 'using(user_id)']])->column($column)->where([[[['album_id', '=', $album_id], ['photo.act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();
            list($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($album_id);
            $c_image = count(array_column($m_photo, 'image'));
            if ($album_limit < $c_image) $album_limit += $c_image;//包含此次的數量
            $m_album = Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
            //refreshPhoto
            $a_photo = [];

            $preview1 = json_decode($setPreview, true);
            $preview2 = (is_array(json_decode($m_album['preview'], true))) ? json_decode($m_album['preview'], true) : [];
            $set_preview = array_unique(array_merge($preview1, $preview2));

            $Image = new \Core\Image;
            foreach ($m_photo as $v0) {
                $rightsParams = ['act' => ['aviary', 'edit', 'delete'], 'album_id' => $album_id, 'photo_id' => $v0['photo_id']];
                $operate_rights = $this->operate_rights($rightsParams);
                $a_photo[] = [
                    'photo_id' => $v0['photo_id'],
                    'image' => $v0['image'],
                    'image_url' => URL_UPLOAD . $v0['image'],
                    'image_url_thumbnail' => is_file(PATH_UPLOAD . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
                    'icon' => ($v0['usefor'] == 'image') ? null : static_file('images/icon_' . $v0['usefor'] . '.png'),
                    'user_name' => $v0['user_name'],
                    'music_image_thumbnail' => ($v0['audio_refer'] != 'none') ? '<img src="' . static_file('images/icon05.png') . '" height="11" width="11">' : '',
                    'description_image_thumbnail' => ($v0['description'] != '') ? '<img src="' . static_file('js/editor/images/editor/icon_text.png') . '" height="11" width="11">' : '',
                    'usefor' => $v0['usefor'],
                    'aviary_edit_onclick' => ($operate_rights['aviary']) ? 'aviary_edit' : 'no_right',
                    'photo_setting_onclick' => ($operate_rights['edit']) ? 'photo_setting' : 'no_right',
                    'delete_onclick' => ($operate_rights['delete']) ? 'del' : 'no_right',
                    'set_preview' => (in_array($v0['image'], $set_preview)) ? 'checked="checked"' : null,
                ];
            }

            json_encode_return(1, _('Delete success.'), null, ['photo' => $a_photo, 'album_limit' => $album_limit]);
        }
        die;
    }

    function deleteCooperation()
    {
        if (is_ajax()) {
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $user_id = empty($_POST['user_id']) ? null : $_POST['user_id'];

            $result = 0;
            $message = '您無法進行此操作';

            $rightsParam = [
                'act' => ['cooperationDeleteUser'],
                'album_id' => $album_id,
                'target_user_id' => $user_id,
            ];
            $right = $this->operate_rights($rightsParam);

            if ($right['cooperationDeleteUser']) {
                $m_cooperation = (new cooperationModel())->deleteCooperation('album', $album_id, $user_id);
                $result = $m_cooperation['result'];
            }

            json_encode_return($result, $message);
        }
    }

    function delete_slot_item()
    {
        if (is_ajax()) {
            $result = 1;
            $message = null;
            $data = null;

            $m_user = (new \userModel)->getSession();

            $photo_id = (!empty($_POST['photo_id'])) ? $_POST['photo_id'] : 0;
            $image = empty($_POST['image']) ? null : $_POST['image'];
            $sign = (isset($_POST['sign']) && trim($_POST['sign']) !== '') ? trim($_POST['sign']) : null;

            $array0 = $_POST;
            unset($array0['sign']);

            if ($sign === null || $sign !== encrypt($array0)) {
                $result = 0;
                $message = _('Abnormal process, please try again.');
                goto _return;
            }

            list($result0, $message0) = array_decode_return((new \photoModel)->ableToUpdatePhoto($photo_id, $m_user['user_id']));
            if ($result0 != 1) {
                $result = $result0;
                $message = $message0;
                goto _return;
            }

            $m_photo = (new \photoModel)
                ->column(['exchange'])
                ->where([[[['photo_id', '=', $photo_id]], 'and']])
                ->fetch();

            if ($m_photo['exchange']) {
                $result = 0;
                $message = _('無法刪除獎項');
                goto _return;
            }

            \Core\File::delete([PATH_UPLOAD . $image]);

            $data = $image;

            _return:
            json_encode_return($result, $message, null, $data);
        }
        die;
    }

    function diy_aviary()
    {
        if (is_ajax()) {
            $url = $_REQUEST['url'];
            $sign = empty($_POST['sign']) ? null : $_POST['sign'];
            $t = empty($_POST['t']) ? null : $_POST['t'];
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $photo_id = empty($_POST['photo_id']) ? null : $_POST['photo_id'];
            $user = parent::user_get();
            if ($sign === null || $t === null || $album_id === null || $photo_id === null) json_encode_return(0, _('Abnormal process, please try again.'));
            $m_photo_user_id = Model('photo')->column(['user_id'])->where([[[['photo_id', '=', $photo_id], ['act', '=', 'open']], 'and']])->fetchColumn();
            $rightsParams = ['act' => ['aviary'], 'album_id' => $album_id, 'photo_id' => $photo_id];
            if (!$this->operate_rights($rightsParams)['aviary']) json_encode_return(0, _('您無法對此張照片進行操作.'));
            //for sign
            $encrypt_param[] = $user['user_id'];
            $encrypt_param[] = $t;
            if ($sign != encrypt($encrypt_param)) json_encode_return(0, _('Abnormal process, please try again.'));
            $image = Model('photo')->column(['image'])->where([[[['photo_id', '=', $photo_id], ['album_id', '=', $album_id], ['user_id', '=', $m_photo_user_id], ['act', '=', 'open']], 'and']])->fetchColumn();

            if (is_file(PATH_UPLOAD . $image)) {
                \Core\File::delete([PATH_UPLOAD . $image]);
                $_fileinfo = pathinfo($image);
                $newName = uniqid() . '.jpg';
                $newImage = $_fileinfo['dirname'] . DIRECTORY_SEPARATOR . $newName;

                //更新 Table:album 內的相片資料
                $m_album = Model('album')->column(['preview', 'cover', 'photo'])->where([[[['album_id', '=', $album_id]], 'and']])->fetch();
                $editAlbum = [];
                foreach ($m_album as $k => $v) {
                    $arrayTmp = json_decode($v, true);
                    if (is_array($arrayTmp)) {
                        $key = array_search($image, $arrayTmp);
                        if ($key || $key === 0) {
                            $arrayTmp[$key] = $newImage;
                        } //有符合的成員
                        $m_album[$k] = json_encode($arrayTmp);
                    } else {
                        if ($v == $image) $m_album[$k] = $newImage;
                    }
                    $editAlbum[$k] = $m_album[$k];
                }

                //更新 Table:photo 內的相片資料
                $m_photo = Model('photo')->column(['image'])->where([[[['photo_id', '=', $photo_id], ['album_id', '=', $album_id], ['user_id', '=', $m_photo_user_id]], 'and']])->fetch();
                $editPhoto = [];
                foreach ($m_photo as $k => $v) {
                    $arrayTmp = json_decode($v, true);
                    if (is_array($arrayTmp)) {
                        $key = array_search($image, $arrayTmp);
                        if ($key || $key === 0) {
                            $arrayTmp[$key] = $newImage;
                        } //有符合的成員
                        $m_photo[$k] = json_encode($arrayTmp);
                    } else {
                        if ($v == $image) $m_photo[$k] = $newImage;
                    }

                    $editPhoto[$k] = $m_photo[$k];
                }

                Model('photo')->where([[[['photo_id', '=', $photo_id], ['album_id', '=', $album_id], ['user_id', '=', $m_photo_user_id]], 'and']])->edit($editPhoto);

                $result = file_put_contents(PATH_UPLOAD . $newImage, file_get_contents($_REQUEST['url']));

                $Image = new \Core\Image();
                $Image->set(PATH_UPLOAD . $newImage)->setSize(\Config\Image::S1, \Config\Image::S1)->save(null, true);

                $editAlbum['state'] = 'process';

                Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit($editAlbum);

                if (is_file(PATH_UPLOAD . $newImage)) {
                    \Extension\aws\S3::upload(PATH_UPLOAD . $newImage);
                }
                json_encode_return(1, _('Success.'), null, ['newImage' => URL_UPLOAD . $newImage]);
            } else {
                json_encode_return(0, _('File does not exist, please contact us.'));
            }
        }
        die;
    }

    function file_upload()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $user_grade = Core::get_usergrade($user['user_id']);

            //不同 grade 上限大小不同, 由settings設定
            $file_upload_maxsize = json_decode(Core::settings('FILE_UPLOAD_MAXSIZE'), true)[$user_grade];
            $overUploadMaxsizeMsg = ($user_grade == 'free') ? _('File is too large, [upper limit : 12MB]') : _('File is too large, [upper limit : 200MB]');

            if (isset($_FILES['files'])) {
                switch ($_FILES['files']['error'][0]) {
                    case 0:
                        $upload_folder = DIRECTORY_SEPARATOR . M_CLASS . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;

                        mkdir_p(PATH_UPLOAD, M_PACKAGE . $upload_folder);

                        if ($_FILES['files']['size'][0] > $file_upload_maxsize) json_encode_return(0, $overUploadMaxsizeMsg);

                        /*依使用模式進行不同處理 (video / slot)*/
                        switch ($_POST['photo_type']) {
                            case 'audio'://音樂上傳
                                $data['input_name'] = $_FILES['files']['name'][0];
                                $data['output_name'] = $filename = M_PACKAGE . $upload_folder . uniqid() . '.' . pathinfo($_FILES['files']['name'][0], PATHINFO_EXTENSION);

                                $out_file = PATH_UPLOAD . $filename;

                                if (move_uploaded_file($_FILES['files']['tmp_name'][0], $out_file)) {
                                    $Audio = new \Core\Audio();

                                    $Audio->setFile($out_file);

                                    if ($Audio->getType() !== 'mp3') {
                                        $out_file = $Audio->setType('mp3')->save(null, true, true);

                                        $data['output_name'] = fileinfo($out_file)['suburl'];
                                    }

                                    \Extension\aws\S3::upload($out_file);

                                    json_encode_return(1, null, null, $data);
                                }

                                json_encode_return(0, _('Abnormal process, please try again.'));
                                break;

                            case 'video'://影片上傳
                                switch ($_FILES['files']['type'][0]) {
                                    case 'video/mp4':
                                        $extension = '.mp4';
                                        break;

                                    default:
                                        json_encode_return(0, _('Upload file type only can be mp4.'));
                                        break;
                                }
                                $data['input_name'] = $_FILES['files']['name'][0];
                                $data['output_name'] = $filename = M_PACKAGE . $upload_folder . uniqid() . $extension;

                                $out_file = PATH_UPLOAD . $filename;

                                if (move_uploaded_file($_FILES['files']['tmp_name'][0], $out_file)) {
                                    \Extension\aws\S3::upload($out_file);

                                    json_encode_return(1, null, null, $data);
                                }

                                json_encode_return(0, _('Abnormal process, please try again.'));
                                break;

                            //拉霸圖片上傳
                            case 'slot':
                            case 'exchange':
                                switch ($_FILES['files']['type'][0]) {
                                    case 'image/jpeg':
                                    case 'image/jpg':
                                        $extension = '.jpg';
                                        break;

                                    case 'image/png':
                                        $extension = '.png';
                                        break;

                                    default:
                                        json_encode_return(0, _('Upload file type only can be JPEG / JPG / PNG.'));
                                        break;
                                }

                                $filename = uniqid() . $extension;
                                $data['output_name'] = M_PACKAGE . $upload_folder . $filename;
                                $data['output_url'] = URL_UPLOAD . M_PACKAGE . $upload_folder . $filename;
                                $out_img = PATH_UPLOAD . M_PACKAGE . $upload_folder . $filename;

                                if (move_uploaded_file($_FILES['files']['tmp_name'][0], $out_img)) {
                                    list ($src_w, $src_h) = getimagesize($out_img);

                                    if ($src_w > 1336 || $src_h > 2004) {
                                        if ($_POST['photo_type'] == 'exchange') {
                                            $src_x = $src_y = 0;
                                            $newImg = imagecreatetruecolor($src_w, $src_h);
                                            $srcImg = imagecreatefromjpeg($out_img);

                                            imagecopy($newImg, $srcImg, 0, 0, $src_x, $src_y, $src_w, $src_h);
                                            imagejpeg($newImg, $out_img);
                                            imagedestroy($newImg);
                                        } else {
                                            if ($src_w > 1336) {
                                                //原圖寬超過比例定義截圖位置
                                                $src_x = ceil(($src_w / 2) - 668);
                                                $new_w = 1336;
                                            } else {
                                                $src_x = 0;
                                                $new_w = $src_w;
                                            }

                                            if ($src_h > 2004) {
                                                //原圖高超過比例定義截圖位置
                                                $src_y = ceil(($src_h / 2) - 1002);
                                                $new_h = 2004;
                                            } else {
                                                $src_y = 0;
                                                $new_h = $src_h;
                                            }

                                            $newImg = imagecreatetruecolor($new_w, $new_h);
                                            $srcImg = imagecreatefromjpeg($out_img);

                                            imagecopy($newImg, $srcImg, 0, 0, $src_x, $src_y, $new_w, $new_h);
                                            imagejpeg($newImg, $out_img);
                                            imagedestroy($newImg);
                                        }
                                    }

                                    if (is_file($out_img)) {
                                        \Extension\aws\S3::upload($out_img);
                                    }

                                    json_encode_return(1, null, null, $data);
                                }

                                json_encode_return(0, _('Abnormal process, please try again.'));
                                break;

                            case 'video_cover' :
                                switch ($_FILES['files']['type'][0]) {
                                    case 'image/jpeg':
                                    case 'image/jpg':
                                        $extension = '.jpg';
                                        break;

                                    case 'image/png':
                                        $extension = '.png';
                                        break;

                                    default:
                                        json_encode_return(0, _('Upload file type only can be JPEG / JPG / PNG.'));
                                        break;
                                }
                                $basename = uniqid() . $extension;
                                $filename = M_PACKAGE . $upload_folder . $basename;
                                $fileurl = URL_UPLOAD . $filename;
                                $filedir = PATH_UPLOAD . $filename;
                                $data = [
                                    'basename' => $basename,
                                    'filename' => $filename,
                                    'fileurl' => $fileurl,
                                    'filedir' => $filedir
                                ];

                                if (move_uploaded_file($_FILES['files']['tmp_name'][0], $filedir)) {
                                    \Extension\aws\S3::upload($filedir);

                                    json_encode_return(1, null, null, $data);
                                }

                                json_encode_return(0, _('Abnormal process, please try again.'));
                                break;

                            default :
                                json_encode_return(0, _('Error phototype upload.'));
                                break;
                        }
                        break;

                    case 1:
                        json_encode_return(0, _('Exceeded upload size limit : ') . ini_get('upload_max_filesize') . 'B');
                        break;

                    default:
                        json_encode_return(0, _('Abnormal process, please try again.'));
                        break;
                }
            }
        }
    }

    function index()
    {
        $diy = [];

        //邏輯檢查於 __construct() 的 Model('album')->diyable() 處理了
        $m_album = (new \albumModel)
            ->where([[[['album_id', '=', $_GET['album_id']]], 'and']])
            ->fetch();

        $cooperation_qrcode = (new albumModel())->getqrcode('album', $m_album['album_id'], true, false);
        $m_album['cooperation_qrcode'] = 'data:image/jpg;base64,' . $cooperation_qrcode;
        parent::$data['album'] = $m_album;

        $user = parent::user_get();

        $rightsParams = ['act' => ['sort'], 'album_id' => $_GET['album_id']];
        $operate_rights = $this->operate_rights($rightsParams);

        $user['sort'] = $operate_rights['sort'];

        parent::$data['user'] = $user;

        $diy['user']['grade'] = Core::get_usergrade($user['user_id']);

        $m_cooperation = (new \cooperationModel)
            ->column(['cooperation.*', 'user.name'])
            ->where([[[['type', '=', 'album'], ['type_id', '=', $m_album['album_id']]], 'and']])
            ->join([['left join', 'user', 'using(user_id)']])
            ->fetchAll();

        $a_edit_group_id = [];
        $a_cooperation = [];
        foreach ($m_cooperation as $v0) {
            $a_edit_group_id[] = $v0['user_id'];

            //協作清單內的資料
            if ($v0['identity'] != 'admin') {
                $a_cooperation[] = [
                    'user_id' => $v0['user_id'],
                    'name' => $v0['name'],
                    'cover' => URL_STORAGE . \userModel::getPicture($v0['user_id']),
                    'identity' => $v0['identity'],
                ];
            }
        }

        parent::$data['cooperation_list'] = $a_cooperation;

        $owner = $this->owner_get($m_album['album_id']);

        parent::$data['owner'] = $owner;

        $checked = ['singular' => null];
        $audio_default = ['none' => [], 'singular' => [], 'plural' => []];

        switch ($m_album['audio_mode']) {
            case 'none':
                $audio_default['none']['js'] = '$(\'input[name="audio_mode"][value="' . $m_album['audio_mode'] . '"]\').trigger(\'click\');';
                break;

            case 'singular':
                $audio_src = ($m_album['audio_refer'] == 'system') ? URL_STATIC_FILE . M_PACKAGE . '/zh_TW/audio/' . $m_album['audio_target'] . '.mp3' : null;
                $audio_default['singular']['js'] = '$(\'input[name="audio_mode"][value="' . $m_album['audio_mode'] . '"]\').trigger(\'click\');';
                $audio_default['singular']['js'] .= '$(\'input[name="singular_mode"][value="' . $m_album['audio_refer'] . '"]\').trigger(\'click\');';
                $audio_default['singular']['src1'] = ($m_album['audio_refer'] == 'system') ? $audio_src : null;
                $audio_default['singular']['src2'] = ($m_album['audio_refer'] == 'file') ? URL_UPLOAD . $m_album['audio_target'] : null;
                $audio_default['singular']['target'] = ($m_album['audio_refer'] == 'file') ? $m_album['audio_target'] : null;
                $checked['singular'] = ($m_album['audio_loop']) ? 'checked="checked"' : null;
                break;

            case 'plural':
                $audio_default['plural']['js'] = '$(\'input[name="audio_mode"][value="' . $m_album['audio_mode'] . '"]\').trigger(\'click\');';
                break;
        }

        parent::$data['audio_default'] = $audio_default;

        parent::$data['checked'] = $checked;

        //版型可使用邏輯已於 template::_taketemplate 處理，這裡判斷若該版型 act 不為 open，則給予預設版型
        $m_template = (new \templateModel)
            ->column(['frame', 'name', 'act'])
            ->where([[[['template_id', '=', $m_album['template_id']]], 'and']])
            ->fetch();

        if (!$m_album['template_id']) {
            // 若m_album['template_id'] = 0 則視為快速建立模式,不對template的act做驗證
            $template_id = 0;
        } else {
            $template_id = ($m_template['act'] == 'open') ? $m_album['template_id'] : 1;
        }
        parent::$data['template'] = $m_template;
        parent::$data['template_id'] = $template_id;
        $diy['diy']['upload_type'] = (!$template_id) ? 0 : 1;

        //identity 1: admin   0:協作者
        $identity = $this->user_get($m_album['album_id']);
        $diy['user']['identity'] = $identity['identity'];

        //sign
        $encrypt_param = [];
        $encrypt_param[] = $user['user_id'];
        $encrypt_param[] = $time = time();
        $sign = ['t' => $time, 's' => encrypt($encrypt_param)];
        parent::$data['sign'] = $sign;

        //取得使用者相片數量限制
        list ($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($m_album['album_id']);

        if ($album_limit > 0) $edit_num = 1; //還有可編輯的張數 從第一張開始編
        $album_photo_count = 0;

        $a_photo = array();
        if ($m_album['state'] == 'success' || $m_album['state'] == 'process') {
            $where = [
                [[['photo.album_id', '=', $m_album['album_id']], ['photo.user_id', 'in', $a_edit_group_id], ['photo.act', '=', 'open']], 'and']
            ];
            $column = [
                'photo.user_id',
                'photo.photo_id',
                'photo.image',
                'photo.description',
                'photo.usefor',
                'photo.duration',
                'audio_refer',
                'user.name user_name',
            ];
            $m_photo = (new \photoModel)
                ->join([['left join', 'user', 'using(user_id)']])
                ->column($column)
                ->where($where)
                ->order(['photo.sequence' => 'asc'])
                ->fetchAll();

            $album_photo_count = count($m_photo);
            $set_preview = (is_array(json_decode($m_album['preview'], true))) ? json_decode($m_album['preview'], true) : [];

            $Image = new \Core\Image;

            foreach ($m_photo as $k0 => $v0) {
                if (is_image(PATH_UPLOAD . $v0['image'])) {
                    $Image->set(PATH_UPLOAD . $v0['image']);

                    switch ($Image->getType()) {
                        case 1:
                            //2017-08-21 Lion: 不做 resize 處理
                            break;

                        default:
                            $Image->setSize(\Config\Image::S1, \Config\Image::S1);
                            break;
                    }

                    $image_url_thumbnail = fileinfo($Image->save())['url'];
                } else {
                    $image_url_thumbnail = null;
                }

                $operate_photo_rights = $this->operate_rights(['act' => ['aviary', 'edit', 'delete'], 'album_id' => $m_album['album_id'], 'photo_id' => $v0['photo_id']]);

                $a_photo[$k0] = [
                    'photo_id' => $v0['photo_id'],
                    'image' => $v0['image'],
                    'image_url' => URL_UPLOAD . $v0['image'],
                    'image_url_thumbnail' => $image_url_thumbnail,
                    'duration' => $v0['duration'],
                    'user_name' => $v0['user_name'],
                    'usefor' => $v0['usefor'],
                    'music_image_thumbnail' => ($v0['audio_refer'] != 'none') ? '<img src="' . static_file('images/icon05.png') . '" height="11" width="11">' : null,
                    'description_image_thumbnail' => ($v0['description'] != '') ? '<img src="' . static_file('js/editor/images/editor/icon_text.png') . '" height="11" width="11">' : null,
                    'aviary_edit_onclick' => $operate_photo_rights['aviary'] ? 'aviary_edit' : 'no_right',
                    'photo_setting_onclick' => $operate_photo_rights['edit'] ? 'photo_setting' : 'no_right',
                    'delete_onclick' => $operate_photo_rights['delete'] ? 'del' : 'no_right',
                    'set_preview' => (in_array($v0['image'], $set_preview)) ? 'checked="checked"' : null,
                ];

                $a_photo[$k0]['type_photo'] = ($v0['usefor'] == 'image') ? null : '<img src="' . static_file('images/icon_' . $v0['usefor'] . '.png') . '">';
            }

            //下一張
            $edit_num = $album_photo_count + 1;
        }

        parent::$data['album_limit'] = $album_limit;
        parent::$data['edit_num'] = (empty($edit_num)) ? 0 : $edit_num;
        parent::$data['photo'] = $a_photo;

        //取得 template 的 frame
        $column = ['frame_id', 'user_id', 'name', 'url'];
        $where = [
            [[['template_id', '=', $template_id], ['act', '=', 'open']], 'and']
        ];
        $m_frame = Model('frame')->column($column)->where($where)->order(['frame_id' => 'asc'])->fetchAll();
        $a_frame = [];
        $Image = new \Core\Image;
        foreach ($m_frame as $k0 => $v0) {
            $a_frame[] = [
                'frame_id' => $v0['frame_id'],
                'url' => is_file(PATH_STORAGE . SITE_LANG . '/user/' . $v0['user_id'] . '/' . $v0['url']) ? fileinfo($Image->set(PATH_STORAGE . SITE_LANG . '/user/' . $v0['user_id'] . '/' . $v0['url'])->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
            ];
        }
        parent::$data['frame'] = $a_frame;

        //audio
        $m_audio = Model('audio')->where([[[['act', '=', 'open']], 'and']])->fetchAll();
        parent::$data['audio'] = (!empty($m_audio)) ? $m_audio : null;

        //導覽教學(trip)
        $trip_tip = true;
        $where = array(
            array(array(array('user_id', '=', $user['user_id']), array('act', '!=', 'delete')), 'and')
        );
        $t_album = Model('album')->column(array('count(1)'))->where($where)->fetchColumn();
        if (!empty($t_album) && $t_album != 0) $trip_tip = false;
        parent::$data['trip_tip'] = $trip_tip;

        //上傳容量上限
        $user_grade = Core::get_usergrade($user['user_id']);
        //不同 grade 上限大小不同, 由settings設定
        $file_upload_maxsize = json_decode(Core::settings('FILE_UPLOAD_MAXSIZE'), true)[$user_grade];
        $overUploadMaxsizeMsg = ($user_grade == 'free') ? _('File is too large, [upper limit : 12MB]') : _('File is too large, [upper limit : 200MB]');
        $uploadFileMaxSizeData = ['file_upload_maxsize' => $file_upload_maxsize, 'overUploadMaxsizeMsg' => $overUploadMaxsizeMsg];
        parent::$data['uploadFileMaxSizeData'] = $uploadFileMaxSizeData;

        //文字提示訊息
        $diyPageTips = ($user_grade == 'free') ? _('支援圖片、影片、音檔、PDF；單件最大可達15MB 影片最大可達12MB') : _('支援圖片、影片、音檔、PDF；單件最大可達15MB 影片最大可達200MB');
        parent::$data['diyPageTips'] = $diyPageTips;

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Create'),
            array(_('Create'))
        );

        //已完成相片列表的最後一個+號目標位置
        $diy['diy']['plus_btn'] = ($diy['diy']['upload_type']) ? '.changeto1:first' : '#mask02';

        //共用編輯按鈕的權限
        $click_cooperate_btn = ($this->operate_rights(['act' => ['click_cooperate_btn'], 'album_id' => $m_album['album_id']]));
        $diy['diy']['cooperate_btn'] = ($click_cooperate_btn['click_cooperate_btn'])
            ? '<li><a href="javascript:void(0)" style="font-size: 14px;" class="con_icon06 cooperate_icon">' . _('共同編輯') . '</a></li>'
            : null;

        parent::$data['diy'] = $diy;
        parent::head();
        parent::$html->set_js(static_file('js/jquery.bxslider/jquery.bxslider.js'), 'src');
        parent::foot();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);

        //lightgallery
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery.min.css', 'href');
        parent::$html->set_css(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/css/lightgallery-custom.min.css', 'href');
        parent::$html->set_js('https://cdn.jsdelivr.net/picturefill/2.3.1/picturefill.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lightgallery-all-modify.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-audio.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/dist/js/lg-subhtml.min.js', 'src');
        parent::$html->set_js(URL_STATIC_FILE . M_PACKAGE . '/' . SITE_LANG . '/js/lightGallery-master/lib/jquery.mousewheel.min.js', 'src');
        parent::$html->set_css(static_file('css/font-awesome/css/font-awesome.css'), 'href');
        //mediaelement
        parent::$html->set_css(static_file('js/mediaelement-2.22.0/mediaelementplayer.min.css'), 'href');
        parent::$html->set_js(static_file('js/mediaelement-2.22.0/mediaelement-and-player.min.js'), 'src');

        parent::$html->set_js(static_file('js/Bootstrap-3-Typeahead-master/bootstrap3-typeahead-modify.min.js'), 'src');

        parent::$html->set_css(static_file('js/editor/css/editor.css'), 'href');
        parent::$html->set_css(static_file('css/bootstrap-diy/bootstrap.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui.min.css'), 'href');
        parent::$html->set_css(static_file('js/croppic/css/croppic.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.bxslider/jquery.bxslider.css'), 'href');
        parent::$html->set_css(static_file('js/pace/css/templates/pace-theme-loading-bar.tmpl.css'), 'href');
        parent::$html->set_css(static_file('js/pace/css/themes/blue/pace-theme-loading-bar.css'), 'href');
        parent::$html->set_css(static_file('js/trip/css/trip.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.fileupload/css/jquery.fileupload.css'), 'href');

        parent::$html->set_js(static_file('js/jquery-ui.min.js'), 'src');
        parent::$html->set_js(static_file('js/croppic/js/croppic.min.js'), 'src');
        parent::$html->set_js(static_file('js/editor/js/editor.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-process.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-video.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-audio.js'), 'src');

        parent::$html->set_js(static_file('js/pace/js/pace.min.js'), 'src');
        parent::$html->set_js(static_file('js/trip/js/trip.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.easytabs.min.js'), 'src');
        parent::$html->set_js(static_file('js/addclear.min.js'), 'src');

        parent::$html->set_js(static_file('js/jquery-ui-datetimepicker/jquery-ui-0308.min.js'), 'src');
        parent::$html->set_css(static_file('js/jquery-ui-datetimepicker/jquery-ui-0308.min.css'), 'href');

        parent::$html->set_js(static_file('js/jquery-ui-datetimepicker/jquery-ui-timepicker-addon.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery-ui-datetimepicker/jquery-ui-timepicker-zh-TW.js'), 'src');
        parent::$html->set_css(static_file('js/jquery-ui-datetimepicker/jquery-ui-timepicker-addon.min.css'), 'href');

        //快速建立所需要的檔案
        if (!$m_album['template_id']) {
            parent::$html->set_css(static_file('css/bootstrap.min.css'), 'href');
            parent::$html->set_css(static_file('css/dropit.css'), 'href');
            parent::$html->set_css(static_file('css/style.css'), 'href');
            parent::$html->set_css(static_file('js/jquery.fileupload/css/jquery.fileupload-ui.css'), 'href');

            parent::$html->set_js(static_file('js/jquery.ui.widget.js'), 'src');
            parent::$html->set_js(static_file('js/angular.min.js'), 'src');
            parent::$html->set_js(static_file('js/load-image.all.min.js'), 'src');
            parent::$html->set_js(static_file('js/canvas-to-blob.min.js'), 'src');
            parent::$html->set_js(static_file('js/jquery.iframe-transport.js'), 'src');

            parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-image.js'), 'src');
            parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-validate.js'), 'src');
            parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-angular.js'), 'src');
            parent::$html->set_js(static_file('js/app.js'), 'src');

        }
    }

    function img_crop_to_file()
    {
        if (is_ajax()) {
            $imgUrl = $_POST['imgUrl'];
            $imgInitW = $_POST['imgInitW'];
            $imgInitH = $_POST['imgInitH'];
            $imgW = $_POST['imgW'];
            $imgH = $_POST['imgH'];
            $imgY1 = $_POST['imgY1'];
            $imgX1 = $_POST['imgX1'];
            $cropW = $_POST['cropW'];
            $cropH = $_POST['cropH'];
            $angle = $_POST['rotation'];

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
                    $type = '.jpg';
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

            $Ymd = date('Ymd');
            $basename = uniqid('tmp_') . $type;
            $subpath = M_PACKAGE . DIRECTORY_SEPARATOR . M_CLASS . DIRECTORY_SEPARATOR . $Ymd . DIRECTORY_SEPARATOR . $basename;
            $path = PATH_UPLOAD . $subpath;
            $suburl = M_PACKAGE . '/' . M_CLASS . '/' . $Ymd . '/' . $basename;
            $url = URL_UPLOAD . $suburl;

            if (imagejpeg($final_image, $path, 100)) {
                $response = array(
                    'status' => 'success',
                    'url' => $url
                );

                //檢查 filename，避免由前端竄寫而處理錯誤的圖檔
                $fileinfo = fileinfo($imgUrl);
                if (is_file($fileinfo['path']) && substr($fileinfo['filename'], 0, 4) == 'tmp_') unlink($fileinfo['path']);
            } else {
                $response = [
                    'status' => 'error',
                    'message' => json_encode(['result' => 0, 'message' => _('Abnormal process, please try again.')]),
                ];
            }
            die(json_encode($response));
        }
        die;
    }

    function img_remove()
    {
        $this->img_reset();
    }

    function img_reset()
    {
        if (is_ajax()) {
            if (!empty($_POST['imgUrl'])) {
                //檢查 filename，避免由前端竄寫而處理錯誤的圖檔
                $fileinfo = fileinfo($_POST['imgUrl']);
                if (is_file($fileinfo['path']) && substr($fileinfo['filename'], 0, 4) == 'tmp_') unlink($fileinfo['path']);
            }
            json_encode_return(1);
        }
        die;
    }

    function img_save_to_file()
    {
        if ($_FILES['img']['error'] > 0) {
            $response = [
                'status' => 'error',
                'message' => json_encode(['result' => 0, 'message' => _('Exceeded upload size limit : ') . ini_get('upload_max_filesize') . 'B']),
            ];
        } else {
            //upload路徑 -- 建立今天的dir
            $str = M_PACKAGE . '/' . M_CLASS . '/' . date('Ymd') . '/';
            mkdir_p(PATH_UPLOAD, $str);

            //上傳圖片在日期dir 下
            $imagePath = PATH_UPLOAD . $str;

            $extension = strtolower(end(explode('.', $_FILES['img']['name'])));

            if (in_array($extension, ['jpeg', 'jpg', 'png'])) {
                $save_filename = uniqid('tmp_') . '.' . $extension;

                if (move_uploaded_file($_FILES['img']['tmp_name'], $imagePath . $save_filename)) {
                    $image = new Core\Image;

                    $file = $image->set($imagePath . $save_filename)->save(null, true);

                    if (is_file($file)) {
                        \Extension\aws\S3::upload($file);
                    }

                    $response = [
                        'status' => 'success',
                        'url' => URL_UPLOAD . $str . $save_filename,
                        'width' => $image->getWidth(),
                        'height' => $image->getHeight()
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => json_encode(['result' => 0, 'message' => _('Abnormal process, please try again.')]),
                    ];
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => json_encode(['result' => 0, 'message' => _('Upload file type only can be JPEG / JPG / PNG.')]),
                ];
            }
        }

        die(json_encode($response));
    }

    function image_combine()
    {
        if (is_ajax()) {
            $background = empty($_POST['background']) ? null : $_POST['background'];
            $foreground = empty($_POST['foreground']) ? null : $_POST['foreground'];
            $success_item = empty($_POST['success_item']) ? 0 : $_POST['success_item']; //已壓縮完成的相片數量
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $setPreview = empty($_POST['setPreview']) ? null : $_POST['setPreview'];
            $user = parent::user_get();

            if ($background == null || $foreground == null || $album_id == null) {
                json_encode_return(0, _('Abnormal process, please try again.'));
            }

            $background_path = PATH_STORAGE . SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $background;
            if (!is_file($background_path)) {
                json_encode_return(0, _('Template does not exist, please contact us.'));
            }

            //子路徑
            $subdir = M_PACKAGE . DIRECTORY_SEPARATOR . M_CLASS . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
            $suburl = M_PACKAGE . '/' . M_CLASS . '/' . date('Ymd') . '/';

            $im_background = new Imagick($background_path);

            foreach (json_decode($foreground, true) as $v0) {
                //檢查 filename，避免由前端竄寫而處理錯誤的圖檔
                $fileinfo = fileinfo($v0['url']);
                if (is_file($fileinfo['path']) && substr($fileinfo['filename'], 0, 4) == 'tmp_') {
                    $im_foreground = new Imagick($fileinfo['path']);
                    $im_background->compositeImage($im_foreground, Imagick::COMPOSITE_DSTOVER, (int)$v0['left'], (int)$v0['top']);
                    $im_foreground->clear();

                    unlink($fileinfo['path']);
                } else {
                    json_encode_return(0, _('Upload file does not exist, please try again.'));
                }
            }

            //賦予新檔名
            $basename = uniqid() . '.jpg';

            $im_background->setImageFormat('jpeg');
            $im_background->resizeImage(668, 1002, \Imagick::FILTER_CATROM, 1);
            $im_background->writeImage(PATH_UPLOAD . $subdir . $basename);
            $im_background->clear();

            if (is_file(PATH_UPLOAD . $subdir . $basename)) {
                \Extension\aws\S3::upload(PATH_UPLOAD . $subdir . $basename);
            }

            /**
             * 取用 photo_id
             */
            $column = [
                'photo.user_id',
                'photo.photo_id',
                'photo.image',
                'photo.sequence',
                'photo.usefor',
                'photo.audio_refer',
                'photo.description',
                'photo.album_id',
                'user.name',
            ];
            $m_photo = Model('photo')->column($column)->join([['left join', 'user', 'using(user_id)']])->where([[[['album_id', '=', $album_id], ['photo.act', '=', 'open']], 'and']])->order(['sequence' => 'asc'])->fetchAll();

            $photo_id = Model('photo')->column(['photo_id'])->where([[[['state', '=', 'pretreat'], ['photo.act', '=', 'open']], 'and']])->fetchColumn();

            $sequence = empty($m_photo) ? 0 : max(array_column($m_photo, 'sequence')) + 1;
            if ($sequence > 255) $sequence = 255;
            $param = [
                'album_id' => $album_id,
                'user_id' => $user['user_id'],
                'image' => $suburl . $basename,
                'usefor' => 'image',
                'state' => 'success',
                'sequence' => $sequence,
                'inserttime' => inserttime(),
            ];
            ($photo_id) ? Model('photo')->where([[[['photo_id', '=', $photo_id]], 'and']])->edit($param) : $photo_id = Model('photo')->add($param);

            /**
             * 處理 album
             */
            $a_image = array_column($m_photo, 'image');
            array_push($a_image, $suburl . $basename);
            $edit = array(
                'cover' => $a_image[0],
                'photo' => json_encode($a_image),
                'state' => 'process',
            );
            Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit($edit);

            list($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($album_id);
            $c_image = count($a_image);
            if ($album_limit < $c_image) $album_limit += $c_image;//包含此次的數量

            $m_album = Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->fetch();

            //refreshPhoto
            $a_photo = [];

            $preview1 = json_decode($setPreview, true);
            $preview2 = (is_array(json_decode($m_album['preview'], true))) ? json_decode($m_album['preview'], true) : [];
            $set_preview = array_unique(array_merge($preview1, $preview2));

            $Image = new \Core\Image;
            foreach ($m_photo as $v0) {
                $rightsParams = ['act' => ['aviary', 'edit', 'delete'], 'album_id' => $album_id, 'photo_id' => $v0['photo_id']];
                $operate_rights = $this->operate_rights($rightsParams);
                $a_photo[] = [
                    'photo_id' => $v0['photo_id'],
                    'image' => $v0['image'],
                    'image_url' => URL_UPLOAD . $v0['image'],
                    'image_url_thumbnail' => is_file(PATH_UPLOAD . $v0['image']) ? fileinfo($Image->set(PATH_UPLOAD . $v0['image'])->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
                    'icon' => ($v0['usefor'] == 'image') ? null : static_file('images/icon_' . $v0['usefor'] . '.png'),
                    'user_name' => $v0['name'],
                    'usefor' => $v0['usefor'],
                    'music_image_thumbnail' => ($v0['audio_refer'] != 'none') ? '<img src="' . static_file('images/icon05.png') . '" height="11" width="11">' : '',
                    'description_image_thumbnail' => ($v0['description'] != '') ? '<img src="' . static_file('js/editor/images/editor/icon_text.png') . '" height="11" width="11">' : '',
                    'aviary_edit_onclick' => ($operate_rights['aviary']) ? 'aviary_edit' : 'no_right',
                    'photo_setting_onclick' => ($operate_rights['edit']) ? 'photo_setting' : 'no_right',
                    'delete_onclick' => ($operate_rights['delete']) ? 'del' : 'no_right',
                    'set_preview' => (in_array($v0['image'], $set_preview)) ? 'checked="checked"' : null,
                ];
            }
            $rightsParams = ['act' => ['aviary', 'edit', 'delete'], 'album_id' => $album_id, 'photo_id' => $photo_id];
            $operate_rights = $this->operate_rights($rightsParams);
            $a_photo[] = [
                'photo_id' => $photo_id,
                'image' => $suburl . $basename,
                'image_url' => URL_UPLOAD . $suburl . $basename,
                'image_url_thumbnail' => is_file(PATH_UPLOAD . $suburl . $basename) ? fileinfo($Image->set(PATH_UPLOAD . $suburl . $basename)->setSize(\Config\Image::S1, \Config\Image::S1)->save())['url'] : null,
                'icon' => null,
                'user_name' => $user['name'],
                'usefor' => 'image',
                'music_image_thumbnail' => '',
                'description_image_thumbnail' => '',
                'aviary_edit_onclick' => ($operate_rights['aviary']) ? 'aviary_edit' : 'no_right',
                'photo_setting_onclick' => ($operate_rights['edit']) ? 'photo_setting' : 'no_right',
                'delete_onclick' => ($operate_rights['delete']) ? 'del' : 'no_right',
                'set_preview' => 'checked="checked"',
            ];

            //當完成最後的一張
            if ($c_image >= $album_limit) {
                json_encode_return(4, _('The number of photos in a album reached the maximum.'), null, ['photo' => $a_photo, 'album_limit' => $album_limit]);
            }

            json_encode_return(1, _('Success.'), null, ['photo' => $a_photo, 'album_limit' => $album_limit]);
        }
        die;
    }

    function insertCooperation()
    {
        if (is_ajax()) {
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $user_id = empty($_POST['user_id']) ? null : $_POST['user_id'];

            $result = 0;
            $message = '您無法進行此操作';

            $rightsParam = [
                'act' => ['cooperationInviteUser'],
                'album_id' => $album_id,
            ];
            $right = $this->operate_rights($rightsParam);

            if ($right['cooperationInviteUser']) {
                $m_cooperation = (new cooperationModel())->insertCooperation('album', $album_id, $user_id);
                $result = $m_cooperation['result'];
            }

            json_encode_return($result, $message);
        }
    }

    function load_album_setting()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : null;
            $m_album = Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->fetch();

            $where = [
                [[['album_id', '=', $m_album['album_id']], ['photo.act', '=', 'open']], 'and']
            ];

            $m_photo = Model('photo')->column(['photo_id', 'image', 'usefor', 'duration', 'audio_refer', 'audio_loop', 'audio_target'])->where($where)->order(['sequence' => 'asc'])->fetchAll();

            $m_audio = Model('audio')->where([[[['act', '=', 'open']], 'and']])->fetchAll();
            $a_audio = null;

            foreach ($m_audio as $k0 => $v0) {
                $a_audio .= '<option data-audio_id="' . $v0['audio_id'] . '" value="' . URL_STATIC_FILE . $v0['file'] . '">' . $v0['name'] . '</option>';
            }
            $Image = new \Core\Image;
            $Audio = new \Core\Audio;

            $data = array();
            foreach ($m_photo as $k0 => $v0) {
                $manual = 'checked="checked"';
                $auto = null;
                $autocontent_style = null;
                if ($v0['duration'] > 0) {
                    $auto = 'checked="checked"';
                    $manual = null;
                    $autocontent_style = 'style="display: list-item;"';
                }

                //audio長度
                $audio_duration = 0;
                if ($v0['audio_refer'] == 'system') {
                    foreach ($m_audio as $k1 => $v1) {
                        if ($v0['audio_target'] == $v1['audio_id']) {
                            $audio_duration = gmdate("i:s", $Audio->setFile(PATH_STATIC_FILE . $v1['file'])->getDuration());
                        }
                    }
                } elseif ($v0['audio_refer'] == 'file') {
                    $audio_duration = gmdate("i:s", $Audio->setFile(PATH_UPLOAD . $v0['audio_target'])->getDuration());
                }

                //set duration
                $photo_duration = ($v0['duration'] == 0) ? 1 : $v0['duration'];

                //逐張列出
                $data['turn'][] = '<div class="turn_list" data-photo_id="' . $v0['photo_id'] . '">
							<h4>P' . ($k0 + 1) . '</h4>
							<div class="turn_left"><img src="' . URL_UPLOAD . $v0['image'] . '"></div>
							<div class="turn_right">
								<ul>
									<li><small>' . _('音樂長度') . ':' . $audio_duration . '秒</small></li>	
									<li>
										<label >
											<input type="radio" name="group' . ($k0 + 1) . '" value="manual" class="triggerpager' . ($k0 + 1) . '" data-rel="pageturn' . ($k0 + 1) . '" ' . $manual . '/>
											' . _('手動翻頁') . '
										</label>
									</li>
									<li>
										<label >
											<input type="radio" name="group' . ($k0 + 1) . '" value="auto" class="triggerpager' . ($k0 + 1) . '" data-rel="pageauto' . ($k0 + 1) . '" ' . $auto . '/>
											' . _('自動翻頁') . '
										</label>
									</li>
									<li class="autocontent page' . ($k0 + 1) . ' pageauto' . ($k0 + 1) . '" ' . $autocontent_style . '>
										<input type="number" onkeyup="numcheck(this)" maxlength="5" min="1" max="86400" name="text' . ($k0 + 1) . '" class="second" value="' . $photo_duration . '">秒
									</li>
								</ul>
							</div>
						</div>';

                //預設值
                $checked = ['none' => null, 'system' => null, 'file' => null];
                $checked[$v0['audio_refer']] = 'checked="checked"';
                $active = ['none' => null, 'system' => null, 'file' => null];
                $active[$v0['audio_refer']] = 'style="display:block;"';
                $repeat = ($v0['audio_loop']) ? 'checked="checked"' : null;
                $selected_audio = $a_audio;
                if ($v0['audio_refer'] == 'system') {
                    $selected_audio = str_replace('data-audio_id="' . $v0['audio_target'] . '"', 'data-audio_id="' . $v0['audio_target'] . '" selected="selected"', $a_audio);
                }

                $file_src = null;
                if ($v0['audio_refer'] == 'file') {
                    if (file_exists(PATH_UPLOAD . $v0['audio_target'])) $file_src = URL_UPLOAD . $v0['audio_target'];
                }

                $data['audio'][] = '<div class="multi_item audio_list" data-photo_id="' . $v0['photo_id'] . '">
										<h4>P' . ($k0 + 1) . '</h4>
										<div class="item_left"><img src="' . URL_UPLOAD . $v0['image'] . '"></div>
										<div class="item_right">
											<ul>
												<li><small>' . _('自動翻頁') . ':' . $v0['duration'] . '秒</small></li>
												<li><label><input type="radio" name="photo_audio_mode_' . ($k0 + 1) . '" value="none" class="triggermusic' . ($k0 + 1) . '" data-rel="nomusic" ' . $checked['none'] . '/>' . _('無音樂') . '</label></li>
												<li><label><input type="radio" name="photo_audio_mode_' . ($k0 + 1) . '" value="system" class="triggermusic' . ($k0 + 1) . '" data-rel="p' . ($k0 + 1) . '_music_1" ' . $checked['system'] . ' />' . _('使用預設音樂') . '</label></li>
												<li class="music_content03 page01 p' . ($k0 + 1) . '_music_1 system_content" ' . $active['system'] . '>
													<div class="musicdown">
														<select name="plural_' . ($k0 + 1) . '" class="music">' . $selected_audio . '</select>
													</div>	
													<label><input type="checkbox" class="repeat" ' . $repeat . ' name="system_repeat_' . ($k0 + 1) . '">' . _('重複播放') . '</label>
												</li>
												<li><label><input type="radio" name="photo_audio_mode_' . ($k0 + 1) . '" value="file" class="triggermusic' . ($k0 + 1) . '" data-rel="p' . ($k0 + 1) . '_music_2" ' . $checked['file'] . '/>' . _('自行上傳') . '</label></li>
												<li class="music_content03 page01 p' . ($k0 + 1) . '_music_2 file_content" ' . $active['file'] . '>
													<div class="music_upload">
														<div class="inputname music02" name="audio_name_' . ($k0 + 1) . '" onclick="$(\'#plural_fileupload_' . ($k0 + 1) . '\').trigger(\'click\')" data-output_name="' . $v0['audio_target'] . '">' . _('選擇音樂') . '...</div>
														<div class="upload" onclick="$(\'#plural_fileupload_' . ($k0 + 1) . '\').trigger(\'click\')"></div>
													</div>
													<label><input type="checkbox" ' . $repeat . ' name="file_repeat_' . ($k0 + 1) . '" value="" class="repeat">重複播放</label>
													<div style="clear:both;"></div>
													<div class="film_content">
														<input id="plural_fileupload_' . ($k0 + 1) . '" class="fileupload common" style="display:none;" type="file" name="files[]" data-url="file_upload" data-photo_type="audio" data-num="' . ($k0 + 1) . '" data-act="fileupload" multiple accept="audio/mp3" />
														<div id="audio_area">
															<audio controls id="audio_demo_' . ($k0 + 1) . '">
																<source src="' . $file_src . '" type="audio/mpeg">Your browser does not support the audio element.
															</audio> 
														</div>
													</div>
												</li>
											</ul>
										</div>
									</div>';
            }
            json_encode_return(1, null, null, $data);
        }
    }

    function make_qrcode($url = null, $file = null)
    {
        if ($url != null && $file != null) {
            if (!class_exists('QRcode')) include PATH_LIB . '/phpqrcode_1.1.4/phpqrcode.php';

            $codeContents = $url;
            $fileName = urldecode($file);
            $outerFrame = 4;
            $pixelPerPoint = 5;
            $jpegQuality = 95;

            $frame = QRcode::text($codeContents, false, QR_ECLEVEL_M);

            $h = count($frame);
            $w = strlen($frame[0]);

            $imgW = $w + 2 * $outerFrame;
            $imgH = $h + 2 * $outerFrame;

            $base_image = imagecreate($imgW, $imgH);

            $col[0] = imagecolorallocate($base_image, 255, 255, 255); // BG, white
            $col[1] = imagecolorallocate($base_image, 0, 0, 0);     // FG, blakc

            imagefill($base_image, 0, 0, $col[0]);

            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    if ($frame[$y][$x] == '1') {
                        imagesetpixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                    }
                }
            }

            $target_image = imagecreate($imgW * $pixelPerPoint, $imgH * $pixelPerPoint);
            imagecopyresized(
                $target_image,
                $base_image,
                0, 0, 0, 0,
                $imgW * $pixelPerPoint, $imgH * $pixelPerPoint, $imgW, $imgH
            );
            imagedestroy($base_image);
            imagejpeg($target_image, PATH_STORAGE . $fileName, $jpegQuality);
            imagedestroy($target_image);

            return true;
        }

        die();
    }

    function photo_left($album_id = null)
    {
        $user = parent::user_get();
        $m_diyable = Model('album')->diyable($album_id, $user['user_id']);
        $owner = ['user_id' => $m_diyable['data']['user_id']];
        $user_grade = Core::get_usergrade($owner['user_id']);
        $amount = 0;
        $c_photototal = Model('photo')->column(['COUNT(1)'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();

        //相簿相片總和(已用)
        $m_album_count = Model('album')->column(array('photo', 'album_id'))->where(array(array(array(array('user_id', '=', $owner['user_id']), array('album.state', '=', 'success'), array('album.zipped', '=', 1), array('act', '!=', 'delete')), 'and')))->fetchAll();
        foreach ($m_album_count as $k => $v) {
            $amount += count(json_decode($v['photo'], true));
        }

        $photos_per_album = json_decode(Core::settings('PHOTOS_PER_ALBUM'), true);
        $photo_grade_limit = json_decode(Core::settings('PHOTO_GRADE_LIMIT'), true);
        switch ($user_grade) {
            default:
            case 'free':
                $all_limit = $photo_grade_limit['free'];  //身分上限
                $photo_left = $all_limit - $amount; //剩下可用的(共)
                $album_limit = ($photo_left > $photos_per_album['free']) ? $photos_per_album['free'] : $photo_left; //正在編輯的作品上限
                break;

            case 'plus':
                $all_limit = $photo_grade_limit['plus']; //身分上限
                $photo_left = $all_limit - $amount; //剩下可用的(共)
                $album_limit = ($photo_left > $photos_per_album['plus']) ? $photos_per_album['plus'] : $photo_left; //正在編輯的作品上限
                break;

            case 'profession':
                $all_limit = $photo_grade_limit['profession'];
                $photo_left = $photo_grade_limit['profession'];
                $album_limit = $photos_per_album['profession'];
                break;
        }

        $album_left = $album_limit - $c_photototal;

        $return[0] = $all_limit;
        $return[1] = $amount;
        $return[2] = $photo_left;
        $return[3] = $album_limit;
        $return[4] = $album_left;
        return $return;
    }

    function photo_setting()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : 0;
            $photo_id = (!empty($_POST['photo_id'])) ? $_POST['photo_id'] : null;
            $sign = (!empty($_POST['sign'])) ? $_POST['sign'] : null;

            if ($album_id === null || $photo_id === null || $sign === null || $sign != encrypt(['album_id' => $album_id])) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_photo = Model('photo')->column([
                'description',
                'image',
                'location',
                'usefor',
                'hyperlink',
                'exchange',
                'video_refer',
                'video_target',
            ])->where([[[['photo_id', '=', $photo_id], ['album_id', '=', $album_id], ['act', '=', 'open']], 'and']])->fetch();

            $return = [
                'photo_id' => $photo_id,
                'description' => $m_photo['description'],
                'image' => end(explode('/', $m_photo['image'])),
                'imageUrl' => URL_UPLOAD . $m_photo['image'],
                'location' => $m_photo['location'],
                'usefor' => $m_photo['usefor'],
                'url1' => json_decode($m_photo['hyperlink'], true)[0]['url'],
                'url1_name' => json_decode($m_photo['hyperlink'], true)[0]['text'],
                'url2' => json_decode($m_photo['hyperlink'], true)[1]['url'],
                'url2_name' => json_decode($m_photo['hyperlink'], true)[1]['text'],
                'photo' => [
                    'exchange' => $m_photo['exchange'],
                ],
            ];

            switch ($m_photo['usefor']) {
                case 'video':
                    $return['video_refer'] = $m_photo['video_refer'];
                    $return['video_target'] = $m_photo['video_target'];
                    break;

                case 'exchange':
                    $m_photousefor = Model('photousefor')->column(['photousefor_id', 'description', 'amount', 'image', 'name', 'starttime', 'endtime'])->where([[[['photo_id', '=', $photo_id]], 'and']])->fetch();

                    $return['photousefor'] = [
                        'photousefor_id' => $m_photousefor['photousefor_id'],
                        'description' => $m_photo['exchange'] ? nl2br(htmlspecialchars($m_photousefor['description'])) : $m_photousefor['description'],
                        'name' => $m_photousefor['name'],
                        'image' => $m_photousefor['image'],
                        'amount' => $m_photousefor['amount'],
                        'starttime' => (is_null($m_photousefor['starttime'])) ? '' : date('Y-m-d H:i', strtotime($m_photousefor['starttime'])),
                        'endtime' => (is_null($m_photousefor['endtime'])) ? '' : date('Y-m-d H:i', strtotime($m_photousefor['endtime'])),
                    ];
                    break;

                case 'slot':
                    $m_photousefor = Model('photousefor')->column(['photousefor_id', '`name`', 'image', 'description', 'amount', 'starttime', 'endtime', 'useless_award'])->where([[[['photo_id', '=', $photo_id]], 'and']])->fetchAll();

                    foreach ($m_photousefor as $v0) {
                        $return['photousefor'][] = [
                            'photousefor_id' => $v0['photousefor_id'],
                            'name' => $v0['name'],
                            'image' => $v0['image'],
                            'description' => $m_photo['exchange'] ? nl2br(htmlspecialchars($v0['description'])) : $v0['description'],
                            'amount' => $v0['amount'],
                            'starttime' => (is_null($v0['starttime'])) ? '' : date('Y-m-d H:i', strtotime($v0['starttime'])),
                            'endtime' => (is_null($v0['endtime'])) ? '' : date('Y-m-d H:i', strtotime($v0['endtime'])),
                            'useless_award' => $v0['useless_award'],
                        ];
                    }
                    break;
            }

            json_encode_return(1, null, null, $return);
        }
    }

    function save_album()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = $_POST['album_id'];
            $preview_id = isset($_POST['preview_id']) ? $_POST['preview_id'] : [0];
            $preview_page_num = isset($_POST['preview_page_num']) ? $_POST['preview_page_num'] : [0];
            $preview_type = isset($_POST['preview_type']) ? $_POST['preview_type'] : 'all';
            $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : null;
            $join_event = isset($_POST['join_event']) ? $_POST['join_event'] : null;

            Model()->beginTransaction();

            (new \albumModel)->save($album_id, $preview_id, $preview_page_num, $preview_type);//邏輯檢查於 __construct() 的 Model('album')->diyable() 處理了

            //20160704 - 執行任務-新增相本
            $data = (new \taskModel)->doTask('create_free_album', $user['user_id'], 'web', ['type' => 'album', 'type_id' => $album_id]);

            /**
             * 作品製作完成的event導向
             */
            $a_param = [];
            $a_param['album_id'] = $album_id;
            if ($event_id != null) $a_param['event_id'] = $event_id;
            if ($join_event != null) $a_param['join_event'] = $join_event;

            Model()->commit();

            json_encode_return(1, _('Added successfully.'), parent::url('user', 'albumcontent_setting', $a_param), $data);
        }
        die;
    }

    function save_album_ajax()
    {
        if (is_ajax()) {
            /**
             *  若因同時編輯造成兩本相本為process狀態時會觸發此流程
             *  取得"當下"編輯的相本ID，並強迫儲存其他處於process狀態的相本跳脫復數以上process相本異常
             */
            $user = parent::user_get();
            $album_id = isset($_POST['album_id']) ? $_POST['album_id'] : null;

            if ($album_id == null) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_album = (new \albumModel)
                ->column(['album_id'])
                ->where([[[['album_id', '!=', $album_id], ['state', '=', 'process'], ['user_id', '=', $user['user_id']]], 'and']])
                ->fetchAll();

            if (!empty($m_album) && count($m_album) > 0) {
                foreach ($m_album as $k0 => $v0) {
                    if (!(new \albumModel)->save($v0['album_id'])) {
                        json_encode_return(0, _('Abnormal process, please try again.') . '[Wrong album id]');
                    }
                }
            }

            json_encode_return(2, _('儲存完成, 請繼續編輯此作品'));
        }
    }

    function save_album_setting()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : null;
            $data = (!empty($_POST['data'])) ? json_decode($_POST['data'], true) : null;

            $rightsParams = ['act' => ['album_setting'], 'album_id' => $album_id];
            $operate_rights = $this->operate_rights($rightsParams);
            if (!$operate_rights['album_setting']) json_encode_return(0, _('您無法進行此操作.'));

            /**
             * 處理音樂 $data[audio]
             */
            $audio = $data['audio'];
            $param = array();
            switch ($audio['mode']) {
                //無音樂
                case 'none':
                    $param = [
                        'audio_mode' => $audio['mode'],
                        'audio_loop' => 0,
                        'audio_refer' => 'none',
                        'audio_target' => '',
                    ];
                    if (!Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit($param)) json_encode_return(0, _('Update Fail[Audio:None]'));

                    //重置photo
                    $param = [
                        'audio_loop' => 0,
                        'audio_refer' => 'none',
                        'audio_target' => null,
                    ];
                    if (!Model('photo')->where([[[['album_id', '=', $album_id]], 'and']])->edit($param)) json_encode_return(0, _('Update Fail[Audio:None]'));
                    break;

                //單首播放
                case 'singular' :
                    $a_singular = $audio['data']['data'];
                    //未選擇單首播放的類型
                    if (empty($audio['data']['mode'])) json_encode_return(0, _('請選擇一種撥放來源。'));

                    switch ($audio['data']['mode']) {
                        case 'system':
                            //檢查系統音樂
                            $m_audio = Model('audio')->where([[[['audio_id', '=', $a_singular['value']], ['act', '=', 'open']], 'and']])->fetch();
                            if (empty($m_audio) || !file_exists(PATH_STATIC_FILE . $m_audio['file'])) json_encode_return(0, _('System file does\'t exist'));
                            break;

                        case 'file' :
                            //檢查上傳音樂
                            if (empty($a_singular['value']) || !file_exists(PATH_UPLOAD . $a_singular['value'])) json_encode_return(0, _('Upload file does\'t exist'));
                            break;

                        default :
                            json_encode_return(0, _('錯誤的音樂播放類型，請重新選擇。'));
                            break;
                    }

                    $param = [
                        'audio_mode' => $audio['mode'],
                        'audio_loop' => ($a_singular['repeat'] == 'true') ? 1 : 0,
                        'audio_refer' => $audio['data']['mode'],
                        'audio_target' => $a_singular['value'],
                    ];
                    if (!Model('album')->where([[[['album_id', '=', $album_id]], 'and']])->edit($param)) json_encode_return(0, _('Update Fail[Audio:None]'));

                    //重置photo
                    $param = [
                        'audio_loop' => 0,
                        'audio_refer' => 'none',
                        'audio_target' => null,
                    ];
                    if (!Model('photo')->where([[[['album_id', '=', $album_id]], 'and']])->edit($param)) json_encode_return(0, _('Update Fail[Audio:None]'));
                    break;

                case 'plural' :
                    $a_plural = $audio['data'];

                    //逐張處理
                    foreach ($a_plural as $k0 => $v0) {
                        switch ($v0['mode']) {
                            case 'none':
                                $v0['data']['value'] = null;
                                break;

                            case 'system' :
                                //檢查系統音樂
                                $m_audio = Model('audio')->where([[[['audio_id', '=', $v0['data']['value']], ['act', '=', 'open']], 'and']])->fetch();
                                if (empty($m_audio) || !file_exists(PATH_STATIC_FILE . $m_audio['file'])) json_encode_return(0, _('System file does\'t exist'));
                                break;

                            case 'file' :
                                //檢查上傳音樂
                                if (empty($v0['data']['value']) || !file_exists(PATH_UPLOAD . $v0['data']['value'])) json_encode_return(0, _('Upload file does\'t exist'), null, $audio['mode']);
                                break;

                            default :
                                json_encode_return(0, _('錯誤的音樂播放類型，請重新選擇。'));
                                break;
                        }

                        //逐張更新audio 資訊
                        (new \photoModel)
                            ->where([[[['photo_id', '=', $v0['photo_id']]], 'and']])
                            ->edit([
                                'audio_loop' => ($v0['data']['repeat'] == 'true') ? 1 : 0,
                                'audio_refer' => $v0['mode'],
                                'audio_target' => isset($v0['data']['value']) ? $v0['data']['value'] : null,
                            ]);
                    }

                    //如果全部的 photo 皆設定為無音樂，則 album.audio_mode 寫入 none
                    $count = (new \photoModel)
                        ->column(['COUNT(1)'])
                        ->where([[[['album_id', '=', $album_id], ['audio_refer', 'IN', ['embed', 'file', 'system']]], 'and']])
                        ->fetchColumn();

                    if ($count == 0) {
                        $audio['mode'] = 'none';
                    }

                    //將audio 資訊從album 移除，由個別photo控制
                    (new \albumModel)
                        ->where([[[['album_id', '=', $album_id]], 'and']])
                        ->edit([
                            'audio_mode' => $audio['mode'],
                            'audio_loop' => 0,
                            'audio_refer' => 'none',
                            'audio_target' => null,
                        ]);
                    break;

                default :
                    json_encode_return(0, _('錯誤的音樂播放模式，請重新選擇。'));
                    break;
            }

            /**
             * 處理翻頁 $data[turn]
             */
            $turn = $data['turn'];

            foreach ($turn as $k0 => $v0) {
                (new \photoModel)
                    ->where([[[['photo_id', '=', $v0['photo_id']]], 'and']])
                    ->edit(['duration' => $v0['duration']]);
            }

            /**
             * refreshPhoto
             */
            (new \albumModel)->refreshPhoto($album_id);

            json_encode_return(1, _('Edited'), null, null);
        }
    }

    function save_photo_setting()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : 0;
            $photo_id = (!empty($_POST['photo_id'])) ? $_POST['photo_id'] : 0;
            $data = (!empty($_POST['data'])) ? json_decode($_POST['data'], true) : 0;
            $video_platform = empty($_POST['video_platform']) ? null : $_POST['video_platform'];
            $usefor = $data['usefor'];
            if (!in_array($data['usefor'], array('image', 'video', 'exchange', 'slot'))) json_encode_return(0, _('Abnormal process, please try again.'));

            $rightsParams = ['act' => ['edit'], 'album_id' => $album_id, 'photo_id' => $photo_id];
            $operate_rights = $this->operate_rights($rightsParams);
            if (!$operate_rights['edit']) json_encode_return(0, _('您無法對此張照片進行操作.'));

            $photo_owner = Model('photo')->where([[[['photo_id', '=', $photo_id], ['photo.act', '=', 'open']], 'and']])->fetch();

            $param_photo = array();
            $return = array();
            $hyperlink = array();
            for ($i = 1; $i <= 2; $i++) {
                $hyperlink[] = [
                    'icon' => '',
                    'text' => $data['url' . $i . '_name'],
                    'url' => $data['url' . $i]
                ];
            }

            $param_photo = [
                'usefor' => $data['usefor'],
                'location' => $data['location'],
                'hyperlink' => json_encode($hyperlink),
                'description' => $data['description'],
            ];

            /**
             * edit photousefor
             */
            switch ($usefor) {
                case 'image':
                    break;

                case 'video':
                    if (empty($data['value']) || empty($data['refer']) || ($data['refer'] == 'embed' && !is_url($data['value']))) json_encode_return(0, _('Error.'));

                    $param_photo['video_refer'] = $data['refer'];
                    $param_photo['video_target'] = $data['value'];

                    //存圖位置
                    $subdir = M_PACKAGE . DIRECTORY_SEPARATOR . M_CLASS . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
                    $suburl = M_PACKAGE . '/' . M_CLASS . '/' . date('Ymd') . '/';

                    //賦予新檔名
                    $basename = uniqid() . '.jpg';
                    mkdir_p(PATH_UPLOAD, $subdir);
                    $out_put = PATH_UPLOAD . $subdir . $basename;

                    switch ($data['refer']) {
                        case 'embed':
                            //取得影片縮圖
                            if (fetch_remote_thumbnail($data['value'], $out_put, $video_platform)) {
                                if (exif_imagetype($out_put) !== IMAGETYPE_JPEG) {
                                    (new \Core\Image)
                                        ->set($out_put)
                                        ->setType('jpg')
                                        ->save(null, true, true);
                                }

                                \Extension\aws\S3::upload($out_put);
                            } else {
                                json_encode_return(0, _('影片縮圖擷取失敗,請重新輸入網址'));
                            }
                            break;

                        case 'file' :
                            if (!is_null($data['poster'])) {
                                $basename = $data['poster'];
                            } else {
                                //video實例截圖
                                $Video = new \Core\Video();

                                if ($Video->setFile(PATH_UPLOAD . $data['value'])->saveScreenShot($out_put, false, false)) {
                                    \Extension\aws\S3::upload($Video->getOutPath());
                                } else {
                                    json_encode_return(0, _('影片縮圖擷取失敗，請重新上傳檔案。'), null, 'file');
                                }
                            }
                    }
                    $return['image'] = URL_UPLOAD . $suburl . $basename;
                    $param_photo['image'] = $suburl . $basename;
                    break;

                case 'exchange':
                    $m_photo = Model('photo')->column(['exchange'])->where([[[['photo_id', '=', $photo_id]], 'and']])->fetch();

                    if (!$m_photo['exchange']) {
                        if (empty($data['photousefor']['amount'])) json_encode_return(0, _('兌換券設定[數量]需大於 0'));

                        $edit = [
                            'description' => $data['photousefor']['description'],
                            'name' => $data['photousefor']['name'],
                            'amount' => $data['photousefor']['amount'],
                            'image' => $data['photousefor']['image'],
                            '`count`' => $data['photousefor']['amount'],
                            'starttime' => $data['photousefor']['starttime'],
                            'endtime' => $data['photousefor']['endtime'],
                            'inserttime' => inserttime(),
                        ];

                        if (empty($data['photousefor']['photousefor_id'])) {
                            $add = array_merge(['photo_id' => $photo_id], $edit);
                            Model('photousefor')->add($add);
                        } else {
                            Model('photousefor')->where([[[['photousefor_id', '=', $data['photousefor']['photousefor_id']], ['photo_id', '=', $photo_id]], 'and']])->edit($edit);
                        };

                    }
                    break;

                case 'slot':
                    //保留前台"儲存"的按鈕讓使用流程順暢，但已付費完成的slot_item不進行更新。

                    $m_photo = Model('photo')->column(['exchange'])->where([[[['photo_id', '=', $photo_id]], 'and']])->fetch();

                    if (!$m_photo['exchange']) {
                        if (empty($data['photousefor'])) json_encode_return(0, _('至少須設定一個獎項'));

                        //2016-08-04 Lion: 先進行刪除, 否則會刪到之後 add 的
                        $m_photousefor = Model('photousefor')->column(['photousefor_id'])->where([[[['photo_id', '=', $photo_id]], 'and']])->fetchAll();

                        $array0 = array_diff(array_column($m_photousefor, 'photousefor_id'), array_column($data['photousefor'], 'photousefor_id'));

                        if (count($array0) > 0) {
                            Model('photousefor')->where([[[['photousefor_id', 'in', $array0]], 'and']])->delete();
                        }

                        $add = [];
                        $replace = [];
                        foreach ($data['photousefor'] as $v0) {
                            if (empty($v0['photousefor_id'])) {
                                $add[] = [
                                    'photo_id' => $photo_id,
                                    '`name`' => $v0['name'],
                                    'image' => $v0['image'],
                                    'description' => $v0['description'],
                                    'amount' => $v0['amount'],
                                    '`count`' => $v0['amount'],
                                    'starttime' => $v0['starttime'],
                                    'endtime' => $v0['endtime'],
                                    'useless_award' => $v0['useless_award'],
                                    'inserttime' => inserttime(),
                                ];
                            } else {
                                $replace[] = [
                                    'photousefor_id' => $v0['photousefor_id'],
                                    'photo_id' => $photo_id,
                                    '`name`' => $v0['name'],
                                    'image' => $v0['image'],
                                    'description' => $v0['description'],
                                    'amount' => $v0['amount'],
                                    '`count`' => $v0['amount'],
                                    'starttime' => $v0['starttime'],
                                    'endtime' => $v0['endtime'],
                                    'useless_award' => $v0['useless_award'],
                                    'inserttime' => inserttime(),
                                ];
                            }
                        }
                        if ($add) Model('photousefor')->add($add);
                        if ($replace) Model('photousefor')->replace($replace);
                    }
                    break;
            }
            $return['photo_id'] = $photo_id;

            /**
             * edit photo
             */
            Model('photo')->where([[[['album_id', '=', $album_id], ['photo_id', '=', $photo_id], ['user_id', '=', $photo_owner['user_id']]], 'and']])->edit($param_photo) ? Model('album')->zip($album_id) : json_encode_return(0, _('Abnormal process, please try again.'));

            /**
             * refreshPhoto
             */
            Model('album')->refreshPhoto($album_id);
            if ($usefor == 'video') Model('album')->refreshPhotoInAlbum($album_id, $photo_owner['image'], $suburl . $basename);

            json_encode_return(1, _('Edited'), null, $return);
        }
    }

    function save_upload()
    {
        if (is_ajax()) {
            $user = parent::user_get();

            $current_num = empty($_POST['current_num']) ? 0 : $_POST['current_num'];
            $album_data = empty($_POST['album_data']) ? null : json_decode($_POST['album_data'], true);
            $album_id = empty($_GET['album_id']) ? null : $_GET['album_id'];
            $preview_id = isset($_POST['preview_id']) ? $_POST['preview_id'] : [0];
            $join_event = isset($_POST['join_event']) ? $_POST['join_event'] : null;
            $event_id = isset($_POST['event_id']) ? $_POST['event_id'] : null;

            if ($album_data == null) json_encode_return(0, _('You are required to upload the photo profile first.'));

            list ($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($album_id);

            //檢查上傳數量是否超過使用者身分的單一相簿相片上限
            if (count($album_data) > $album_limit) json_encode_return(0, _('The photo quantity is beyond the limit') . '：' . $album_limit);

            //相本資訊
            $m_album = (new albumModel)->where([[[['album_id', '=', $album_id]], 'and']])->fetch();

            (new Model)->beginTransaction();

            //1.需統一upload路徑，將M_CLASS直接以diy取代  2.目的地路徑
            $subpath_upload = M_PACKAGE . DIRECTORY_SEPARATOR . 'diy' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR;
            mkdir_p(PATH_UPLOAD, $subpath_upload);
            $subpathname_storage = SITE_LANG . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . $m_album['user_id'] . DIRECTORY_SEPARATOR . 'album' . DIRECTORY_SEPARATOR . $album_id;
            mkdir_p(PATH_STORAGE, $subpathname_storage);

            //QRcode
            (new \Core\QRcode())
                ->setTextUrl(parent::url('album', 'content', ['album_id' => $album_id, 'autoplay' => 1, 'categoryarea_id' => \albumModel::getCategoryAreaId($album_id)]))
                ->setLevel(1)
                ->setSize(5)
                ->save(PATH_STORAGE . storagefile($subpathname_storage . DIRECTORY_SEPARATOR . 'qrcode.jpg'));

            set_time_limit(0);

            $Image = new \Core\Image;
            $a_resize_img = [];

            foreach ($album_data as $k0 => $v0) {
                $path_fast_upload_file = PATH_UPLOAD . M_PACKAGE . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR . $user['user_id'] . DIRECTORY_SEPARATOR . 'fast_upload' . DIRECTORY_SEPARATOR . $v0;

                //1. 整理尺寸、檔案類型
                $extension = pathinfo($path_fast_upload_file, PATHINFO_EXTENSION);

                $path_upload_file = PATH_UPLOAD . $subpath_upload . uniqid() . '.' . $extension;

                if (rename($path_fast_upload_file, $path_upload_file)) {
                    $Image->set($path_upload_file);

                    switch ($Image->getType()) {
                        case IMAGETYPE_GIF:
                            //2017-08-18 Lion: gif 不做 resize 處理
                            if (is_file($path_upload_file)) {
                                \Extension\aws\S3::upload($path_upload_file);
                            }
                            break;

                        default:
                            if ($Image->getWidth() > \Config\Image::S7 || $Image->getHeight() > \Config\Image::S7) {
                                $Image->setSize(\Config\Image::S7, \Config\Image::S7);
                            }

                            $path_upload_file = $Image
                                ->setType('jpg')
                                ->save(null, true, true, false);

                            if (is_file($path_upload_file)) {
                                \Extension\aws\S3::upload($path_upload_file);
                            }

                            //2. 同步產生常用尺寸圖檔
                            $Image->set($path_upload_file);

                            $path = $Image
                                ->setSize(\Config\Image::S3, \Config\Image::S3)
                                ->save();

                            if (is_file($path)) {
                                \Extension\aws\S3::upload($path, ['Tagging' => 'thumbnail=true']);
                            }

                            $path = $Image
                                ->setSize(\Config\Image::S6, \Config\Image::S6)
                                ->save();

                            if (is_file($path)) {
                                \Extension\aws\S3::upload($path, ['Tagging' => 'thumbnail=true']);
                            }
                            break;
                    }

                    $a_resize_img[] = str_replace(PATH_UPLOAD, '', $path_upload_file);
                } else {
                    (new Model)->rollBack();

                    json_encode_return(0, _('Abnormal process, please try again.'));
                }
            }

            //photo
            $add = [];
            foreach ($a_resize_img as $k0 => $v0) {
                $add[] = [
                    'album_id' => $album_id,
                    'user_id' => $user['user_id'],
                    'image' => $v0,
                    'usefor' => 'image',
                    'state' => 'success',
                    'sequence' => ($current_num + $k0 + 1),
                    'inserttime' => inserttime(),
                ];
            }
            if ($add) (new photoModel)->add($add);

            $m_cooperation = (new cooperationModel)->where([[[['type', '=', 'album'], ['type_id', '=', $m_album['album_id']]], 'and']])->fetchAll();
            $a_edit_group_id = [];
            foreach ($m_cooperation as $v0) {
                $a_edit_group_id[] = $v0['user_id'];
            }

            $a_photo = [];
            if ($m_album['state'] == 'success' || $m_album['state'] == 'process') {
                $m_photo = (new photoModel)
                    ->column(['image'])
                    ->where([[[['album_id', '=', $m_album['album_id']], ['user_id', 'in', $a_edit_group_id], ['act', '=', 'open']], 'and']])
                    ->order(['sequence' => 'asc'])
                    ->fetchAll();

                $a_photo = array_column($m_photo, 'image');
            }

            //set previe
            $preview_photo = (new photoModel)->column(['image'])->where([[[['album_id', '=', $album_id], ['photo_id', 'in', $preview_id]], 'and']])->fetchAll();
            $a_previewTmp = [];
            if (count($preview_photo) == 0) {
                $a_preview[] = (new albumModel)->column(['cover'])->where([[[['album_id', '=', $album_id]], 'and']])->fetchColumn();
            } else {
                foreach ($preview_photo as $k => $v) {
                    $a_previewTmp[] = $v['image'];
                }
                $a_preview = (count($a_previewTmp) > Core::settings('ALBUM_PREVIEW_LIMIT')) ? array_slice($a_previewTmp, 0, Core::settings('ALBUM_PREVIEW_LIMIT')) : $a_previewTmp;
            }

            //album - cover + photo
            $all_photos = $a_photo + $a_resize_img;
            $all_preview_photo = array_merge($a_preview, $a_resize_img);

            (new \albumModel)
                ->where([[[['album_id', '=', $album_id]], 'and']])
                ->edit([
                    'cover' => $all_photos[0],
                    'cover_hex' => empty($all_photos[0]) ? null : (new \Core\Image)->set(PATH_UPLOAD . $all_photos[0])->getMainHex(),
                    'photo' => json_encode($all_photos),
                    'preview' => json_encode($all_preview_photo),
                ]);

            //album -> zip
            (new \albumModel)->zip($album_id);

            //album - zipped + state
            (new \albumModel)->where([[[['album_id', '=', $album_id]], 'and']])->edit(['zipped' => 1, 'state' => 'success']);

            //建立notice --> 先取得follow list
            if ($m_album['zipped'] != 1) {
                $m_followfrom = (new followfromModel)->column(['`from`'])->where([[[['user_id', '=', $user['user_id']]], 'and']])->fetchAll();

                if ($m_followfrom) {
                    //填入notice
                    $add = [
                        '`type`' => 'album',
                        'id' => $album_id,
                        'state' => 'success',
                        'act' => 'open',
                        'inserttime' => inserttime(),
                    ];
                    $notice_id = (new noticeModel)->add($add);

                    //填入noticequeue
                    $add = array();
                    foreach ($m_followfrom as $v) {
                        $add[] = array(
                            'user_id' => $v['from'],
                            'notice_id' => $notice_id,
                        );
                    }
                    (new noticequeueModel)->add($add);
                }
            }

            (new Model)->commit();

            $redirectParams = ['album_id' => $album_id, 'upload' => 'true'];
            if ($event_id != null) $redirectParams['event_id'] = $event_id;
            if ($join_event != null) $redirectParams['join_event'] = $join_event;

            json_encode_return(1, _('Success.'), parent::url('diy', 'index', $redirectParams));
        }

        die;
    }

    function set_frame()
    {
        if (is_ajax()) {
            $success_item = (!empty($_POST['success_item'])) ? $_POST['success_item'] : 0;
            $g_album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : null;
            $album_count = 0;

            if ($g_album_id != null) {
                $where = array();
                $where[] = array(array(array('album_id', '=', $g_album_id)), 'and');
                $m_album = Model('album')->where($where)->fetch();
                $album_count = count(json_decode($m_album['photo']));
            }

            list($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($g_album_id);
            //可編輯數小於相片數
            if ($album_limit < $album_count) {
                $album_limit = 0;
            }

            //已完成數等於可編輯上限，便無法引入版型
            if (($success_item) >= ($album_limit)) {
                json_encode_return(0, _('The number of photos in a album reached the maximum.'), null, 'over');
            }

            $user = parent::user_get();
            $frame_id = (!empty($_POST['frame_id'])) ? $_POST['frame_id'] : 1;

            //取得 1.frame_id / 2. 每一個 W:縷空寬度 H:縷空高度 T:縷空距高  L:縷空距左
            $where = array(
                array(array(array('frame_id', '=', $frame_id), array('act', '=', 'open')), 'and'),
            );
            $m_frame = Model('frame')->where($where)->fetch();

            /**
             * 0527 避免前端違法取用frame_id，驗證取用frame資格
             * 0604 將kind轉至template，再取用frame.template_id往template做驗證，若template.kind = basic則不至templatequeue做購買查詢
             */

            $where = array(
                array(array(array('template_id', '=', $m_frame['template_id']), array('act', '=', 'open')), 'and'),
            );
            $m_template = Model('template')->where($where)->fetch();

            if (empty($m_template)) json_encode_return(0, _('Template does not exist.'));

            if ($m_template['user_id'] == $user['user_id']) {
                //使用者為創作人 => 直接取用
                $m_templatequeue = true;
            } elseif ($m_template['kind'] != 'basic') {
                //查找購買版型紀錄
                $where = array();
                $where[] = array(array(array('template_id', '=', $m_frame['template_id']), array('user_id', '=', $user['user_id'])), 'and');
                $m_templatequeue = Model('templatequeue')->where($where)->fetch();
            } else {
                //因template.kind == basic 直接取用
                $m_templatequeue = true;
            }

            $m_diyable = Model('album')->diyable($g_album_id, $user['user_id']);
            if ($m_diyable['data']['template_id'] == $m_frame['template_id']) $m_templatequeue = true;

            if (!empty($m_templatequeue)) {
                $str = URL_STORAGE . SITE_LANG . '/user/' . $m_frame['user_id'] . '/';
                //畫出div
                $layout = '<div class="tem_style"><img src="' . $str . $m_frame['url'] . '" height="2004" width="1336" alt="' . $m_frame['user_id'] . '/' . $m_frame['url'] . '"></div>';
                foreach (json_decode($m_frame['blank'], true) as $k => $v) {
                    $layout .= '<div class="temp" style="width:' . $v['W'] . 'px; height:' . $v['H'] . 'px; top:' . $v['T'] . 'px; left:' . $v['L'] . 'px">
									<div id="cropContainer' . $k . '" class="crop_upload"></div>
								</div>';
                }

                $return = array();
                $return['layout'] = $layout;
                $return['blank_num'] = count($m_frame['blank']);
                $return['name'] = $m_frame['name'];

                json_encode_return(1, $amount, null, $return);
            } else {
                json_encode_return(0, _('You can\'t use this frame, please try again.'));
            }
        }
        die;
    }

    function server()
    {
    }

    function sign()
    {
        if (is_ajax()) {
            $result = 1;
            $messgae = null;
            $data = null;

            if (empty($_POST)) {
                $result = 0;
                $messgae = _('Abnormal process, please try again.');
                goto _return;
            }

            $data = encrypt($_POST);

            _return:
            json_encode_return($result, $messgae, null, $data);
        }
        die;
    }

    function slot_point()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = (!empty($_POST['album_id'])) ? $_POST['album_id'] : 0;
            $photo_id = (!empty($_POST['photo_id'])) ? $_POST['photo_id'] : 0;

            $user_use_slot = Model('photo')->column(['COUNT(1)'])->where([[[['photo_id', '!=', $photo_id], ['album_id', '=', $album_id], ['user_id', '=', $user['user_id']], ['usefor', '=', 'slot'], ['exchange', '!=', 1], ['photo.act', '=', 'open']], 'and']])->fetchColumn();

            //用戶 P 點 >= slot 單價 * (未付清的頁數 + 1), 才可開啟 slot 編輯
            $slot = (Core::get_userpoint($user['user_id'], 'web') >= ((int)Core::settings('SLOT_PRICE') * ((int)$user_use_slot + 1))) ? true : false;

            json_encode_return(1, null, null, $slot);
        }
    }

    function slot_buy()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = empty($_POST['album_id']) ? 0 : $_POST['album_id'];
            $sign = empty($_POST['sign']) ? null : $_POST['sign'];

            if ($album_id === null || $sign === null || $sign != encrypt($param = array('album_id' => $album_id))) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_photo = Model('photo')->where([[[['album_id', '=', $album_id], ['user_id', '=', $user['user_id']], ['exchange', '!=', 1], ['usefor', '=', 'slot'], ['photo.act', '=', 'open']], 'and']])->fetchAll();
            $slot_price_amount = count($m_photo) * (int)Core::settings('SLOT_PRICE');

            // 進行交易(Slot相片)
            (new Model)->beginTransaction();

            $inserttime = inserttime();
            $point = Core::get_userpoint($user['user_id'], 'web');
            if ($point < $slot_price_amount) {
                json_encode_return(0, _('User\'s point is not enough to get the exchange.'));
            }

            (new \photoModel)
                ->where([[[['album_id', '=', $album_id], ['user_id', '=', $user['user_id']], ['exchange', '!=', 1], ['usefor', '=', 'slot']], 'and']])
                ->edit([
                    'exchange' => 1,
                ]);

            $exchange_id = (new exchangeModel)
                ->add([
                    'user_id' => $user['user_id'],
                    'platform' => 'web',
                    'type' => 'photo',
                    'id' => $album_id,
                    'point_before' => $point,
                    'point' => $slot_price_amount,
                    'inserttime' => $inserttime,
                ]);

            if (!$exchange_id) {
                (new Model)->rollBack();

                json_encode_return(0, _('[Exchange] occur exception, please contact us.'));
            }

            $tmp0 = array(
                'user_id' => $user['user_id'],
                'trade' => 'exchange',
                'trade_id' => $exchange_id,
                'platform' => 'web',
                'point' => -$slot_price_amount,
            );
            if (!Core::set_userpoint($tmp0)) {
                (new Model)->rollBack();

                json_encode_return(0, _('[UserPoint] occur exception, please contact us.'));
            }

            (new Model)->commit();

            json_encode_return(1, _('Purchase success!'), null, 'Modal');

        };
    }

    function slot_unpaid()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = empty($_POST['album_id']) ? 0 : $_POST['album_id'];
            $sign = empty($_POST['sign']) ? null : $_POST['sign'];

            if ($album_id === null || $sign === null || $sign != encrypt($param = array('album_id' => $album_id))) json_encode_return(0, _('Abnormal process, please try again.'));

            $m_photo = Model('photo')->where([[[['album_id', '=', $album_id], ['user_id', '=', $user['user_id']], ['exchange', '!=', 1], ['usefor', '=', 'slot']], 'and']])->fetchAll();
            json_encode_return(1, null, null, count($m_photo));
        }
    }

    function sorted()
    {
        if (is_ajax()) {
            $user = parent::user_get();
            $a_photo = empty($_POST['photo']) ? null : $_POST['photo'];

            if ($a_photo === null) json_encode_return(0, _('Abnormal process, please try again.'));

            //photo
            $editByCase = [];
            $tmp0 = [];
            array_pop($a_photo);
            foreach ($a_photo as $v0) {
                $tmp0['when'][] = ['photo_id', '=', $v0['photo_id'], $v0['sequence']];
            }
            $tmp0['else'] = 'sequence';
            $editByCase['sequence'] = $tmp0;
            (new \photoModel)->where([[[['album_id', '=', $_POST['album_id']]], 'and']])->editByCase($editByCase);

            (new \albumModel)->refreshPhoto($_POST['album_id']);

            json_encode_return(1, null, null, 'Modal');
        }
        die;
    }

    function onBeforeUnLoadSave()
    {
        /**
         * 170512 -只要離開視窗則強制儲存(#1279) - Mars
         */
        if (is_ajax()) {
            $user = parent::user_get();
            $album_id = isset($_POST['album_id']) ? $_POST['album_id'] : null;
            $result = 0;
            if ($album_id != null) {
                $c_photo = Model('photo')->column(['COUNT(1)'])->where([[[['album_id', '=', $album_id], ['state', '=', 'success'], ['act', '=', 'open'], ['user_id', '=', $user['user_id']]], 'and']])->fetchColumn();
                if ($c_photo > 0) {
                    Model('album')->save($album_id);
                    $result = 1;
                }
            }

            json_encode_return($result, null, null, 'Modal');
        }
    }

    function owner_get($album_id = null)
    {
        $owner = null;
        if ($album_id != null) {
            $where = [[[['album.album_id', '=', $album_id]], 'and']];
            $m_user = Model('album')->column(['user.*'])->join([['left join', 'user', 'using(user_id)']])->where($where)->fetch();
            $owner = $m_user;
        }
        return $owner;
    }

    function operate_rights($params)
    {
        $return = array();

        $act = ($params['act']) ? $params['act'] : null;
        $album_id = ($params['album_id']) ? $params['album_id'] : null;
        $photo_id = (isset($params['photo_id'])) ? $params['photo_id'] : null;
        $target_user_id = (isset($params['target_user_id'])) ? $params['target_user_id'] : null;

        if (is_array($act) && $album_id != null) {

            $user = parent::user_get();
            $album_owner = $this->owner_get($album_id);

            $identity = Model('cooperation')->column(['identity'])->where([[[['type', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user['user_id']]], 'and']])->fetchColumn();
            if (!empty($target_user_id)) {
                $target_user_identity = Model('cooperation')->column(['identity'])->where([[[['type', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $target_user_id]], 'and']])->fetchColumn();
            }

            $photo_owner = ['user_id' => null];
            if ($photo_id != null) {
                $photo_owner = Model('photo')->column(['user.name user_name', 'photo.*'])->join([['left join', 'user', 'using(user_id)']])->where([[[['photo.photo_id', '=', $photo_id]], 'and']])->fetch();
            }

            foreach ($act as $k0 => $v0) {
                $result = false;
                switch ($v0) {
                    //取得作品設定( 音樂 / 翻頁)權
                    case 'album_setting':
                        if ((!empty($identity) && $identity == 'admin')) $result = 'admin';
                        break;

                    case 'click_cooperate_btn':
                        if (!in_array($identity, ['viewer', 'editor'])) $result = true;
                        break;
                    //相片進階編輯權
                    case 'aviary' :
                        if ((!empty($identity) && $identity == 'admin') || ($photo_owner['user_id'] == $user['user_id'])) $result = true;
                        break;

                    //相片資訊編輯權
                    case 'edit' :
                        if ((!empty($identity) && $identity == 'admin') || ($photo_owner['user_id'] == $user['user_id'])) $result = true;
                        break;

                    //刪除權
                    case 'delete' :
                        if ((!empty($identity) && $identity == 'admin') || ($photo_owner['user_id'] == $user['user_id'])) $result = true;
                        break;

                    //排序權
                    case 'sort' :
                        if (!empty($identity) && $identity == 'admin') $result = true;
                        break;

                    //邀請其他帳號共用
                    case 'cooperationInviteUser' :

                        if (!empty($identity) && (($identity == 'admin') || (($identity == 'approver')))) $result = true;

                        break;

                    //編輯其他帳號共用權限
                    //刪除其他帳號共用
                    case 'cooperationUpdateUser' :
                    case 'cooperationDeleteUser' :

                        if ((!empty($identity) && $identity == 'admin')) {
                            $result = true;
                        } else if ($identity == 'approver' && in_array($target_user_identity, ['editor', 'viewer'])) {
                            $target_update_identity = (isset($params['identity'])) ? $params['identity'] : null;
                            $result = ($target_update_identity == 'approver') ? false : true;
                        }

                        break;


                }
                $return[$v0] = $result;
            }
        }
        return $return;
    }

    function user_get($album_id = null)
    {
        $user = parent::user_get();
        $m_cooperation = Model('cooperation')->where([[[['type', '=', 'album'], ['type_id', '=', $album_id], ['user_id', '=', $user['user_id']]], 'and']])->fetch();
        $user['identity'] = ($m_cooperation['identity'] == 'admin') ? true : false;
        return $user;
    }

    function updateCooperation()
    {
        if (is_ajax()) {
            $album_id = empty($_POST['album_id']) ? null : $_POST['album_id'];
            $user_id = empty($_POST['user_id']) ? null : $_POST['user_id'];
            $identity = empty($_POST['identity']) ? null : $_POST['identity'];

            $result = 0;
            $message = '您無法進行此操作';

            $rightsParam = [
                'act' => ['cooperationUpdateUser'],
                'album_id' => $album_id,
                'target_user_id' => $user_id,
                'identity' => $identity,
            ];
            $right = $this->operate_rights($rightsParam);

            if ($right['cooperationUpdateUser']) {
                $m_cooperation = (new cooperationModel())->updateCooperation('album', $album_id, $user_id, $identity);
                $result = $m_cooperation['result'];
            }

            json_encode_return($result, $message);

        }
    }

    function upload()
    {
        $user = parent::user_get();
        $album_id = $_GET['album_id'];
        $judge0 = empty($user) ? true : false;
        $m_album = (new albumModel())->process($user['user_id'])->fetch();
        $judge1 = (!empty($m_album) && $m_album['album_id'] != $album_id) ? true : false;

        if (is_ajax()) {
            if ($judge0) json_encode_return(2, null, parent::url('user', 'login', ['redirect' => parent::url('template', 'upload')]));
            if ($judge1) json_encode_return(3, null, parent::url('diy', 'index', array('album_id' => $m_album['album_id'])));

            if (isset($_FILES['files'])) {
                switch ($_FILES['files']['error'][0]) {
                    case 0:
                        $upload_folder = '/template/' . date('Ymd') . '/' . $user['user_id'] . '/fast_upload/';

                        mkdir_p(PATH_UPLOAD, M_PACKAGE . $upload_folder);

                        switch ($_FILES['files']['type'][0]) {
                            case 'image/gif':
                                if (SITE_EVN == 'production') {
                                    json_encode_return(0, _('Upload file type only can be JPEG / JPG / PNG.'));
                                } else {
                                    $extension = '.gif';
                                }
                                break;

                            case 'image/jpeg':
                            case 'image/jpg':
                                $extension = '.jpg';
                                break;

                            case 'image/png':
                                $extension = '.png';
                                break;

                            case 'application/pdf':
                                $extension = '.pdf';
                                break;

                            default:
                                //json_encode_return(0, _('Upload file type only can be GIF / JPEG / JPG / PNG.'));
                                json_encode_return(0, _('Upload file type only can be JPEG / JPG / PNG.'));
                                break;
                        }

                        $filename = uniqid() . $extension;
                        $out_img = PATH_UPLOAD . M_PACKAGE . $upload_folder . $filename;

                        if (move_uploaded_file($_FILES['files']['tmp_name'][0], $out_img)) {
                            //針對pdf上傳處理
                            if ($extension == '.pdf') {
                                list ($all_limit, $amount, $photo_left, $album_limit, $album_left) = $this->photo_left($album_id);

                                $Image = new \Core\Image;
                                $putPDF2ImageFolder = '/template/' . date('Ymd') . '/' . $user['user_id'] . '/fast_upload/';
                                mkdir_p(PATH_UPLOAD, M_PACKAGE . $putPDF2ImageFolder);

                                list ($result, $message, $a_filename) = $Image->setPdftoImage($out_img, $user, $album_left);

                                switch ($result) {
                                    case 0:
                                        json_encode_return(0, $message, parent::url('diy', 'index', ['album_id' => $album_id]), null);
                                        break;

                                    case 1:
                                        json_encode_return(1, null, null, $a_filename); //因應pdf頁數可能會是多張
                                        break;

                                    case 2:
                                        $user_grade = Core::get_usergrade($user['user_id']);

                                        switch ($user_grade) {
                                            case 'free':
                                                json_encode_return(3, $message, parent::url('diy', 'index', ['album_id' => $album_id]), null);
                                                break;

                                            case 'plus':
                                                json_encode_return(4, $message, parent::url('diy', 'index', ['album_id' => $album_id]), null);
                                                break;

                                            case 'profession':
                                                json_encode_return(5, $message, parent::url('diy', 'index', ['album_id' => $album_id]), null);
                                                break;

                                            default:
                                                json_encode_return(0, _('Abnormal process, please try again.'));
                                                break;
                                        }
                                        break;

                                    default:
                                        json_encode_return(0, _('Abnormal process, please try again.'), parent::url('diy', 'index', ['album_id' => $album_id]), null);
                                        break;
                                }
                            } else {
                                json_encode_return(1, null, null, $filename);  //單張
                            }
                        }

                        json_encode_return(0, _('Abnormal process, please try again.'), parent::url('diy', 'index', ['album_id' => $album_id]), null);
                        break;

                    case 1:
                        json_encode_return(0, _('Exceeded upload size limit : ') . ini_get('upload_max_filesize') . 'B', parent::url('diy', 'index', ['album_id' => $album_id]), null);
                        break;

                    default:
                        json_encode_return(0, _('Abnormal process, please try again.'), parent::url('diy', 'index', ['album_id' => $album_id]), null);
                        break;
                }
            }
        }

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.fileupload/css/jquery.fileupload.css'), 'href');
        parent::$html->set_css(static_file('js/jquery.fileupload/css/jquery.fileupload-ui.css'), 'href');

        parent::$html->set_js(static_file('js/jquery.ui.widget.js'), 'src');
        parent::$html->set_js(static_file('js/angular.min.js'), 'src');
        parent::$html->set_js(static_file('js/load-image.all.min.js'), 'src');
        parent::$html->set_js(static_file('js/canvas-to-blob.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.iframe-transport.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload.js'), 'src');

        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-process.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-image.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-validate.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.fileupload/js/jquery.fileupload-angular.js'), 'src');
        parent::$html->set_js(static_file('js/app.js'), 'src');
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::$html->jbox();
    }
}