<?php

class notificationsController extends frontstageController
{
    function __construct()
    {
    }

    function index()
    {
        if (is_ajax()) {
        }

        $user = parent::user_get();

        $a_notifications[] = [
            'message' => _('目前沒有通知!'),
            'trigger_user_id' => null,
            'trigger_user_pic' => static_file('images/m_logo.png'),
            'trigger_user_url' => 'javascript:void(0)',
            'target_url' => 'javascript:void(0)',
            'time' => null,
        ];

        if (!empty($user)) {
            $Model_pushqueue = \pushqueueModel::getByUserId($user['user_id'], 30);

            if ($Model_pushqueue) {
                $a_notifications = parent::notifications2data($Model_pushqueue);
            }
        }

        parent::$data['allnotifications'] = $a_notifications;

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Notifications'),
            array(_('Notifications'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui-1.10.4.custom.min.css'), 'href');

        parent::$html->set_js(static_file('js/jquery-ui-1.10.4.custom.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.ui.touch-punch.min.js'), 'src');
        parent::$html->set_jquery_validation();
        parent::$html->jbox();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }
}