<?php

class settingsModel extends Model
{
    protected $database = 'site';
    protected $table = 'settings';
    protected $memcache = 'site';
    protected $join_table = [];

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

    function getByKeyword($keyword)
    {
        $return = null;

        $m_settings_lang = (new \settings_langModel)
            ->column(['lang_id', '`value`'])
            ->where([[[['keyword', '=', $keyword], ['lang_id', 'in', [Core\Lang::$default, Core\Lang::get()]]], 'and']])
            ->fetchAll();

        if ($m_settings_lang) {
            $a_settings = array_column($m_settings_lang, 'value', 'lang_id');

            $return = isset($a_settings[Core\Lang::get()]) ? $a_settings[Core\Lang::get()] : $a_settings[Core\Lang::$default];
        }

        return $return;
    }
}