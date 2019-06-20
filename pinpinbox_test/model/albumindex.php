<?php

class albumindexModel extends Model
{
    protected $database = 'site';
    protected $table = 'albumindex';
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
            ['class' => 'albumModel'],
        ];
    }

    function ableToDelete(array $param)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        /**
         * 必填
         */
        $album_id = isset($param['album_id']) ? $param['album_id'] : null;
        $index = isset($param['index']) ? $param['index'] : null;
        $user_id = isset($param['user_id']) ? $param['user_id'] : null;

        if ($index === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "index" is required.';
            goto _return;
        }

        list ($result, $message) = array_decode_return((new \albumModel)->usable_v2($album_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function ableToInsert(array $param)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        /**
         * 必填
         */
        $album_id = isset($param['album_id']) ? $param['album_id'] : null;
        $index = isset($param['index']) ? $param['index'] : null;
        $user_id = isset($param['user_id']) ? $param['user_id'] : null;

        if ($index === null) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "index" is required.';
            goto _return;
        }

        list ($result, $message) = array_decode_return((new \albumModel)->usable_v2($album_id, $user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        $count = (new \albumindexModel)
            ->column(['COUNT(1)'])
            ->where([[[['`index`', '=', $index]], 'and']])
            ->fetchColumn();

        if ($count) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('索引已存在。');
            goto _return;
        }

        list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }

    function deleteAlbumIndex(array $param)
    {
        (new \albumindexModel)
            ->where([[[
                ['album_id', '=', $param['album_id']],
                ['`index`', '=', $param['index']],
            ], 'and']])
            ->delete();
    }

    function insertAlbumIndex(array $param)
    {
        (new \albumindexModel)->add([
            'album_id' => $param['album_id'],
            '`index`' => $param['index']
        ]);
    }
}