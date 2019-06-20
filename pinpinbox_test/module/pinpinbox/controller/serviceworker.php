<?php

class serviceworkerController extends frontstageController
{
    function notification()
    {
        $response = json_encode('');

        if (!empty($_POST['token'])) {
            $token = end(explode('/', $_POST['token']));

            $m_device = (new \deviceModel)
                ->column(['user_id'])
                ->where([[[['token', '=', $token], ['enabled', '=', true]], 'and']])
                ->fetch();

            if ($m_device) {
                $m_pushqueue = (new \pushqueueModel)
                    ->column([
                        'message',
                        'pushqueue_id',
                        'target2type',
                        'target2type_id',
                        'url',
                    ])
                    ->where([[[['user_id', '=', $m_device['user_id']], ['receive5web', '=', false]], 'and']])
                    ->order(['inserttime' => 'asc'])
                    ->limit('0,1')
                    ->fetch();

                if ($m_pushqueue) {
                    (new \pushqueueModel)
                        ->where([[[['pushqueue_id', '=', $m_pushqueue['pushqueue_id']]], 'and']])
                        ->edit([
                            'receive5web' => true
                        ]);

                    $url = null;

                    if ($m_pushqueue['target2type'] !== null && $m_pushqueue['target2type_id'] !== null) {
                        $url = parent::type2url($m_pushqueue['target2type'], $m_pushqueue['target2type_id']);
                    } elseif ($m_pushqueue['url'] !== null) {
                        $url = $m_pushqueue['url'];
                    }

                    $response = json_encode([
                        'body' => $m_pushqueue['message'],
                        'title' => (new settingsModel)->getByKeyword('SITE_TITLE'),
                        'icon' => parent::type2image_url($m_pushqueue['target2type'], $m_pushqueue['target2type_id']),
                        'data' => [
                            'url' => $url,
                        ],
                    ]);
                }
            }
        }

        die($response);
    }

    function register()
    {
        $m_user = (new userModel)->getSession();

        if ($m_user && !empty($_POST['token'])) {
            $BrowserDetection = new BrowserDetection;

            $param = [
                'user_id' => $m_user['user_id'],
                'identifier' => md5(SITE_EVN . $m_user['user_id']),
                'browser' => strtolower($BrowserDetection->getName()),
                'token' => end(explode('/', $_POST['token'])),
            ];

            list ($result0) = array_decode_return(deviceModel::ableToDestroy($param));

            if ($result0 == 1) deviceModel::destroy($param);

            list ($result1) = array_decode_return(deviceModel::ableToBuild($param));

            if ($result1 == 1) deviceModel::build($param);

            json_encode_return(1);
        }

        die;
    }
}