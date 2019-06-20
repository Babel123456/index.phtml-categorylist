<?php

class categoryModel extends Model
{
    protected $database = 'site';
    protected $table = 'category';
    protected $memcache = 'site';
    protected $join_table = array('categoryarea_category');

    function __construct()
    {
        parent::__construct_child();
    }

    function cachekeymap()
    {
        return array(
            array('class' => __CLASS__),
            array('class' => 'albumModel'),
            array('class' => 'albumstatisticsModel'),
        );
    }

    static function getCategoryByCategoryAreaId($categoryarea_id)
    {
        return array_column(
            (new \categoryareaModel)
                ->column(['category.category_id'])
                ->join([
                    ['INNER JOIN', 'categoryarea_category', 'ON categoryarea_category.categoryarea_id = categoryarea.categoryarea_id AND categoryarea_category.act = \'open\''],
                    ['INNER JOIN', 'category', 'ON category.category_id = categoryarea_category.category_id AND category.act = \'open\''],
                ])
                ->where([[[['categoryarea.categoryarea_id', '=', $categoryarea_id], ['categoryarea.act', '=', 'open']], 'and']])
                ->fetchAll(),
            'category_id'
        );
    }
}