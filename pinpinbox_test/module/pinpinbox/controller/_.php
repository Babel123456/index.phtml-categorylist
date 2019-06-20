<?php

class _Controller extends frontstageController
{
    function __construct()
    {
    }

    function _404()
    {
        $view = view(M_PACKAGE, M_CLASS, M_FUNCTION);
        parent::head();
        parent::headbar();
        parent::foot();
        parent::footbar();
        parent::$html->set_css(static_file('css/style.css'), 'href');
        parent::$html->set_css(static_file('css/bootstrap.css'), 'href');
        parent::$view[] = $view;
    }

    function imageresize()
    {
        //2018-05-07 Lion: key 接受的格式為 url 捨去 protocol、domain 後的字串, ex: upload/pinpinbox/diy/20180101/5a9781b32cf7b_100x100.jpg
        if (isset($_GET['key'])) {
            $key = $_GET['key'];

            $url = \Extension\aws\S3::getEndpoint() . $key;

            if (gethttpcode($url) == 200) {
                redirect_php($url);
            } else {
                $url_source = (new \Core\Image)->getSource($url);

                //2018-05-15 Lion: 避免 S3 訪問超時, 先確認 http code (ex. 資源不存在時)
                if (gethttpcode($url_source) == 200 && is_image($url_source)) {
                    if (preg_match("/^(.*)_(\d+)x(\d+).([a-z]+)$/", $key, $matches)) {
                        list (, , $width, $height) = $matches;

                        $path = url2path(URL_ROOT . $key);

                        (new \Core\Image)
                            ->set($url_source)
                            ->setSize($width, $height)
                            ->save($path);

                        if (is_file($path)) {
                            header('Content-Type: ' . mime_content_type($path));
                            readfile($path);

                            \Extension\aws\S3::upload($path, ['Tagging' => 'thumbnail=true']);
                        }
                    }
                }
            }
        }

        die;
    }

    function maintain()
    {
        parent::$view[] = view(M_PACKAGE, M_CLASS, M_FUNCTION);
    }
}