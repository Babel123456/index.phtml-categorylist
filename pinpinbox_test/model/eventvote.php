<?php

class eventvoteModel extends Model
{
    protected $database = 'site';
    protected $table = 'eventvote';
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

    static function ableToVote($event_id, $album_id, $user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        if (empty($event_id)) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "event_id" is required.';
            goto _return;
        } else {
            list ($result, $message) = array_decode_return((new \eventModel)->usable_v2($event_id));
            if ($result != \Lib\Result::SYSTEM_OK) {
                goto _return;
            }

            //作品是否有參加活動
            $count = (new \eventjoinModel)
                ->column(['COUNT(1)'])
                ->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])
                ->fetchColumn();

            if ($count == 0) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('該作品沒有參加這個活動。');
                goto _return;
            }

            if (\eventModel::hasVoted($event_id, $album_id, $user_id)) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('今天已經對該作品投過票囉, 請明天再來。');
                goto _return;
            }

            if (\eventModel::getVoteLeft($event_id, $user_id) <= 0) {
                $result = \Lib\Result::USER_ERROR;
                $message = _('Number of votes exceeds the limit.');
                goto _return;
            }
        }

        if (empty($album_id)) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "album_id" is required.';
            goto _return;
        } else {
            list ($result, $message) = array_decode_return((new \albumModel)->usable_v2($album_id, $user_id));
            if ($result != \Lib\Result::SYSTEM_OK) {
                goto _return;
            }
        }

        if (empty($user_id)) {
            $result = \Lib\Result::SYSTEM_ERROR;
            $message = 'Param error. "user_id" is required.';
            goto _return;
        } else {
            list ($result, $message) = array_decode_return((new \userModel())->usable_v2($user_id));
            if ($result != \Lib\Result::SYSTEM_OK) {
                goto _return;
            }
        }

        _return:
        return array_encode_return($result, $message);
    }

    static function vote($event_id, $album_id, $user_id)
    {
        (new \eventvoteModel)->add([
            'album_id' => $album_id,
            'event_id' => $event_id,
            'user_id' => $user_id,
        ]);

        $count = (new \eventvoteModel)
            ->column(['COUNT(1)'])
            ->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])
            ->fetchColumn();

        (new \eventjoinModel)
            ->where([[[['event_id', '=', $event_id], ['album_id', '=', $album_id]], 'and']])
            ->edit(['count' => $count]);
    }
}