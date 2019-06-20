<?php

class adModel extends Model
{
    protected $database = 'site';
    protected $table = 'ad';
    protected $memcache = 'site';
    protected $join_table = ['adarea', 'adarea_ad', 'event'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
            ['class' => 'adareaModel'],
            ['class' => 'adarea_adModel'],
        ];
    }

    function getByArea($adarea_id, $lang_id = null, array $where = null, array $order = null, $limit = null)
    {
        list ($result) = array_decode_return((new langModel)->usable($lang_id));

        if ($lang_id === null || $result != 1) $lang_id = \Core\Lang::$default;

        $column = [
            'ad.name',
            'ad.title',
            'ad.image',
            'ad.image_640x254',
            'ad.image_720x96',
            'ad.image_960x540',
            'ad.url',
            'ad.html',
            'ad.html_mobile',
            'album.album_id',
            'event.event_id',
            'template.template_id',
            'user.user_id',
        ];

        $join = [
            ['inner join', 'adarea_ad', 'using(ad_id)'],
            ['inner join', 'adarea', 'using(adarea_id)'],
            ['left join', 'album', 'on album.album_id = ad.album_id'],
            ['left join', 'event', 'on event.event_id = ad.event_id'],
            ['left join', 'template', 'on template.template_id = ad.template_id'],
            ['left join', 'user', 'on user.user_id = ad.user_id'],
        ];

        $array0 = [
            [[['adarea_ad.adarea_id', '=', $adarea_id], ['adarea_ad.lang_id', '=', $lang_id], ['ad.act', '=', 'open'], ['adarea_ad.act', '=', 'open'], ['adarea.act', '=', 'open']], 'and'],
        ];
        $where = array_merge($array0, (array)$where);

        $order = array_merge(['adarea_ad.sequence' => 'asc'], (array)$order);

        $m_ad = (new \adModel)
            ->column($column)
            ->join($join)
            ->where($where)
            ->order($order)
            ->limit($limit)
            ->fetchAll();

        $return = [];
        foreach ($m_ad as $v0) {
            $a_album = null;
            if ($v0['album_id']) {
                $a_album = [
                    'album_id' => $v0['album_id'],
                ];
            }

            $a_event = null;
            if ($v0['event_id']) {
                $a_event = [
                    'event_id' => $v0['event_id'],
                ];
            }

            $a_template = null;
            if ($v0['template_id']) {
                $a_template = [
                    'template_id' => $v0['template_id'],
                ];
            }

            $a_user = null;
            if ($v0['user_id']) {
                $a_user = [
                    'user_id' => $v0['user_id'],
                ];
            }

            $return[] = [
                'ad' => [
                    'name' => $v0['name'],
                    'title' => $v0['title'],
                    'image' => $v0['image'],
                    'image_640x254' => $v0['image_640x254'],
                    'image_720x96' => $v0['image_720x96'],
                    'image_960x540' => $v0['image_960x540'],
                    'url' => $v0['url'],
                    'html' => $v0['html'],
                    'html_mobile' => $v0['html_mobile'],
                ],
                'album' => $a_album,
                'event' => $a_event,
                'template' => $a_template,
                'user' => $a_user,
            ];
        }

        return $return;
    }
}