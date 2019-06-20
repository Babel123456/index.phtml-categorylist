<?php

class hobbyModel extends Model
{
    protected $database = 'site';
    protected $table = 'hobby';
    protected $memcache = 'site';
    protected $join_table = ['hobby_user'];

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
        ];
    }

    function getList()
    {
        $data = [];

        $m_hobby = (new hobbyModel)
            ->column([
                'hobby_id',
                'image',
                '`name`'
            ])
            ->where([[[['act', '=', 'open']], 'and']])
            ->fetchAll();

        foreach ($m_hobby as $v0) {
            $data[] = [
                'hobby' => [
                    'hobby_id' => $v0['hobby_id'],
                    'image_url' => is_image(PATH_UPLOAD . $v0['image']) ? URL_UPLOAD . $v0['image'] : null,
                    'name' => $v0['name'],
                ],
            ];
        }

        return $data;
    }
}