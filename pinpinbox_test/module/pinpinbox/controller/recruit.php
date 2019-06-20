<?php

class recruitController extends frontstageController
{
    function __construct()
    {
    }

    function index()
    {
        if (is_ajax()) {
            $recruit_name = (!empty($_POST['recruit_name'])) ? $_POST['recruit_name'] : null;
            $recruit_telephone = (!empty($_POST['recruit_telephone'])) ? $_POST['recruit_telephone'] : null;
            $recruit_email = (!empty($_POST['recruit_email'])) ? $_POST['recruit_email'] : null;
            $recruit_contact_name = (!empty($_POST['recruit_contact_name'])) ? $_POST['recruit_contact_name'] : null;
            $recruit_recruitintent_id = (!empty($_POST['recruit_recruitintent_id'])) ? $_POST['recruit_recruitintent_id'] : null;
            $recruit_proposal = (!empty($_POST['recruit_proposal'])) ? $_POST['recruit_proposal'] : null;
            $captcha = (isset($_POST['captcha']) && !empty($_POST['captcha'])) ? $_POST['captcha'] : null;

            $recruit_contact = json_encode(array('recruit_contact_name' => $recruit_contact_name));

            if ($recruit_name === null || $recruit_telephone === null || $recruit_email === null || $recruit_contact_name === null || $recruit_recruitintent_id === null || $recruit_proposal === null || $captcha === null) {
                json_encode_return(0, _('Please enter your data.'));
            }

            if (Session::get('captcha') != $captcha) {
                json_encode_return(0, _('Slider validate fail!'));
            }

            Session::delete('captcha');

            $add = array(
                'recruitintent_id' => $recruit_recruitintent_id,
                'state' => 'pretreat',
                'name' => $recruit_name,
                'telephone' => $recruit_telephone,
                'email' => $recruit_email,
                'proposal' => $recruit_proposal,
                'contact' => $recruit_contact,
                'inserttime' => inserttime(),
            );

            (new \recruitModel)->add($add);

            $m_recruitintent = (new \recruitintentModel)->where(array(array(array(array('recruitintent_id', '=', $recruit_recruitintent_id)), 'and')))->fetch();

            //回函內容依照提交類型發送
            $body = $m_recruitintent['reply'];
            email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $recruit_email, _('pinpinbox - Cooperation'), $body);

            //發送訊息給此提案的管理員清單
            $m_admin = Model('admin')->column(array('email'))->where(array(array(array(array('admin_id', 'in', json_decode($m_recruitintent['feedback'], true))), 'and')))->fetchAll();
            $a_email = array();
            foreach ($m_admin as $k => $v) {
                $a_email[] = $v['email'];
            }
            if (!empty($a_email)) {
                $tmp1 = array(
                    _('Name of the Company') . '：' . $recruit_name,
                    _('Tel') . '：' . $recruit_telephone,
                    _('Mailbox') . '：' . $recruit_email,
                    _('Contact Name') . '：' . $recruit_contact_name,
                    _('Proposal') . '：' . $recruit_proposal,
                );
                $body = implode('<br>', $tmp1);
                email(EMAIL_ACCOUNT_INTRANET, EMAIL_PASSWORD_INTRANET, 'pinpinbox', $a_email, _('pinpinbox - Cooperation(Feedback)'), $body);
            }
            json_encode_return(1, _('The proposal has been sent, thanks.'), parent::url());
        }

        $a_event = explode(',', Core::settings('RECRUIT_EVENT'));
        $m_event = Model('event')->column(['name', 'image', 'event_id'])->where([[[['event_id', 'in', $a_event]], 'and']])->limit(2)->fetchAll();
        parent::$data['event'] = $m_event;

        $m_recruitintent = Model('recruitintent')->where(array(array(array(array('act', '=', 'open')), 'and')))->fetchAll();
        $a_recruitintent = array();
        foreach ($m_recruitintent as $k => $v) {
            $a_recruitintent[$k]['recruitintent_id'] = $v['recruitintent_id'];
            $a_recruitintent[$k]['name'] = \Core\Lang::i18n($v['name']);
        }

        parent::$data['recruitintent'] = $a_recruitintent;
        parent::$data['max'] = Session::set('captcha', rand(1, 100));

        $this->seo(
            Core::settings('SITE_TITLE') . ' | ' . _('Cooperation'),
            array(_('Cooperation'))
        );

        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/dropit.css'), 'href');
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/jquery-ui-1.10.4.custom.min.css'), 'href');
        //owl
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.carousel.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.theme.css'), 'href');
        parent::$html->set_css(static_file('js/owl.carousel/css/owl.transitions.css'), 'href');
        parent::$html->set_js(static_file('js/owl.carousel/js/owl.carousel.min.js'), 'src');

        parent::$html->set_js(static_file('js/jquery-ui-1.10.4.custom.min.js'), 'src');
        parent::$html->set_js(static_file('js/jquery.ui.touch-punch.min.js'), 'src');
        parent::$html->set_jquery_validation();
        parent::$html->jbox();
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }
}