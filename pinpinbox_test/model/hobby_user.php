<?php

class hobby_userModel extends Model
{
    protected $database = 'site';
    protected $table = 'hobby_user';
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
            ['class' => 'hobbyModel'],
            ['class' => 'userModel'],
        ];
    }

    static function setHobbyToUser($user_id, array $param)
    {
        if ($user_id && $param) {
            //
            $hobby_userModel = (new \hobby_userModel)
                ->column(['hobby_id'])
                ->where([[[['user_id', '=', $user_id]], 'and']])
                ->fetchAll();

            $array_hobby_id = array_column($hobby_userModel, 'hobby_id');

            //
            $array_hobby_id_input = [];

            foreach (array_unique($param) as $v_0) {
                if (empty($v_0)) continue;

                $array_hobby_id_input[] = $v_0;
            }

            //
            $array_hobby_id_delete = array_diff($array_hobby_id, $array_hobby_id_input);

            if ($array_hobby_id_delete) {
                (new \hobby_userModel)
                    ->where([[[['user_id', '=', $user_id], ['hobby_id', 'IN', $array_hobby_id_delete]], 'and']])
                    ->delete();
            }

            //
            $array_hobby_id_insert = array_diff($array_hobby_id_input, $array_hobby_id);

            if ($array_hobby_id_insert) {
                $add = [];

                foreach ($array_hobby_id_insert as $v_0) {
                    $add[] = [
                        'hobby_id' => $v_0,
                        'user_id' => $user_id,
                    ];
                }

                (new \hobby_userModel)
                    ->add($add);
            }
        }
    }
}