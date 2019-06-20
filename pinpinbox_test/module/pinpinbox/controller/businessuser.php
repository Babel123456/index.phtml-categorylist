<?php

class businessuserController extends frontstageController
{
    function __construct()
    {
    }

    function businesssubuserfastregister()
    {
        $businessuser_id = isset($_GET['businessuser_id']) ? $_GET['businessuser_id'] : null;

        if ($businessuser_id === null) {
            redirect_php(parent::url());
        }

        if (!SDK('Mobile_Detect')->isMobile()) {
            redirect_php(parent::url());
        }

        parent::$data['deeplink'] = parent::deeplink('businessuser', 'businesssubuserfastregister', ['businessuser_id' => $businessuser_id]);

        parent::head();

        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }
}