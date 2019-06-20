<?php

namespace business;

class indexController extends \business\basisController
{
    function __construct()
    {
    }

    function index()
    {
        parent::headbar();
        parent::footbar();
        parent::jquery_set();
        parent::$view[] = view('business', M_CLASS, M_FUNCTION);
    }

    function login()
    {
        if (!empty($_POST)) {
            list ($result, $message) = array_decode_return(\businessuser\Model::ableToLogin($_POST));

            if ($result != \Lib\Result::SYSTEM_OK) {
                goto _return;
            }

            \businessuser\Model::login($_POST);

            $result = \Lib\Result::SYSTEM_OK;
            $message = null;
            $redirect = empty(query_string_parse()['redirect']) ? parent::url() : query_string_parse()['redirect'];

            _return:
            json_encode_return($result, $message, isset($redirect) ? $redirect : null);
        }

        if (\businessuser\Model::getSession()) redirect(empty(query_string_parse()['redirect']) ? parent::url() : query_string_parse()['redirect']);

        parent::$html->set_css(static_file('css/login.css'), 'href');
        parent::$html->set_jquery();

        parent::$view[] = view();
    }

    function logout()
    {
        \businessuser\Model::logout();

        redirect(parent::url('index', 'login'));
    }
}