<?php

namespace business;

class profileController extends \business\basisController
{
    function __construct()
    {
    }

    function index()
    {
        parent::$data['action'] = parent::url(M_CLASS, 'edit');

        //form for edit
        $businessuserModel = (new \businessuser\Model)->where([[[['businessuser_id', '=', \businessuser\Model::getSession()['businessuser_id']]], 'and']])->fetch();

        $account = $businessuserModel['account'];
        $name = $businessuserModel['name'];
        $mode = $businessuserModel['mode'];
        $lastlogintime = $businessuserModel['lastlogintime'];

        //form
        $column = [];
        $extra = null;

        $column[] = array('key' => '帳號', 'value' => $account);

        list($html, $js) = parent::$html->password(['id' => 'oldpassword', 'name' => 'oldpassword']);
        $column[] = array('key' => '舊密碼', 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->password(['id' => 'password', 'name' => 'password']);
        $column[] = array('key' => '密碼', 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->password(['id' => 'repassword', 'name' => 'repassword']);
        $column[] = array('key' => '確認密碼', 'value' => $html);
        parent::$html->set_js($js);

        list($html, $js) = parent::$html->text('id="name" name="name" value="' . $name . '"');
        $column[] = array('key' => '名稱', 'value' => $html);
        parent::$html->set_js($js);

        $column[] = array('key' => '模式', 'value' => $mode);

        $column[] = array('key' => '最後登入時間', 'value' => $lastlogintime);

        $column[] = array('key' => 'QR Code', 'value' => '<img src="'.(new \businessuser\Model)->getQrcodeUrl($businessuserModel['businessuser_id']).'"></img>');

        list($html, $js) = parent::$html->submit('value="提交"');
        $column[] = array('key' => '&nbsp;', 'value' => $html);
        parent::$html->set_js($js);

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

    function edit()
    {
        if (is_ajax()) {
            $businessuserSession = \businessuser\Model::getSession();

            $oldpassword = (isset($_POST['oldpassword']) && $_POST['oldpassword'] !== '') ? $_POST['oldpassword'] : null;
            $name = (isset($_POST['name']) && trim($_POST['name']) !== '') ? trim($_POST['name']) : null;
            $password = (isset($_POST['password']) && $_POST['password'] !== '') ? $_POST['password'] : null;

            if ((new \businessuser\Model)->column(['count(1)'])->where([[[['businessuser_id', '!=', $businessuserSession['businessuser_id']], ['name', '=', $name]], 'and']])->fetchColumn()) {
                json_encode_return(0, _('Data already exists by : ') . '名稱');
            }

            $edit = [];
            if ($password !== null) {
                $businessuserModel = (new \businessuser\Model)->column(['`password`'])->where([[[['businessuser_id', '=', $businessuserSession['businessuser_id']]], 'and']])->fetch();
                if (!password_verify($oldpassword, $businessuserModel['password'])) {
                    json_encode_return(0, '舊密碼錯誤。');
                }
                $edit['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $edit['name'] = $name;

            (new \businessuser\Model)->where([[[['businessuser_id', '=', $businessuserSession['businessuser_id']]], 'and']])->edit($edit);

            json_encode_return(1, '成功。');
        }
        die;
    }
}