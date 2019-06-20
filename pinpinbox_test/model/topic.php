<?php

class topicModel extends Model
{
    protected $database = 'site';
    protected $table = 'topic';
    protected $memcache = 'site';
    protected $join_table = [];

    function __construct()
    {
        parent::__construct_child();
    }

    function build($type, $type_id)
    {
        $e_aws = Extension('aws\sns')->createTopic($type, $type_id);

        (new topicModel)->replace([
            '`type`' => $type,
            'type_id' => $type_id,
            'aws_sns_topicarn' => $e_aws['TopicArn'],
        ]);
    }

    function cachekeymap()
    {
        return [
            ['class' => __CLASS__],
        ];
    }

    function destroy($type, $type_id)
    {
        $m_topic = (new topicModel)->column(['aws_sns_topicarn'])->where([[[['`type`', '=', $type], ['type_id', '=', $type_id]], 'and']])->fetch();

        Extension('aws\sns')->deleteTopic($m_topic['aws_sns_topicarn']);

        (new topicModel)->where([[[['`type`', '=', $type], ['type_id', '=', $type_id]], 'and']])->delete();
    }

    /**
     * @param $trigger_user_id
     * @param $type : 推播目標
     * @param $type_id : 推播目標id
     * @param $message : 推播內容
     * @param null $target_type : 點擊推播訊息連結目標
     * @param null $target_type_id : 點擊推播訊息連結目標id
     * @param array $param
     */
    function publish($trigger_user_id, $type, $type_id, $message, $target_type = null, $target_type_id = null, array $param = [])
    {
        $message = replaceSpace($message);//2017-11-01 Lion: 處理跳行、空格

        $param = array_merge($param, [
            'image' => frontstageController::type2image_url($target_type, $target_type_id),
            'type' => $target_type,
            'type_id' => $target_type_id
        ]);

        Extension('aws\sns')->publishTopic(
            (new \topicModel)
                ->column(['aws_sns_topicarn'])
                ->where([[[['`type`', '=', $type], ['type_id', '=', $type_id]], 'and']])->fetchColumn(),
            $message,
            $param
        );

        /**
         * pushqueue
         */
        $m_subscription = (new \subscriptionModel)
            ->column(['DISTINCT(user_id)'])
            ->where([[[['`type`', '=', $type], ['type_id', '=', $type_id]], 'and']])
            ->fetchAll();

        if ($m_subscription) {
            $m_pushqueue = (new \pushqueueModel)
                ->column([
                    'target2type',
                    'target2type_id',
                    'trigger_user_id',
                    'url',
                    'user_id',
                ])
                ->where([[[['viewed', '=', false]], 'and']])
                ->fetchAll();

            $build = function (array $param) {
                ksort($param);

                $array = [];

                foreach ($param as $k_0 => $v_0) {
                    $array[] = $k_0 . '=' . $v_0;
                }

                return md5(implode('&', $array));
            };

            $mapping = [];

            foreach ($m_pushqueue as $v_0) {
                $mapping[] = $build($v_0);
            }

            $add = [];

            foreach ($m_subscription as $v0) {
                //不新增訂閱自已的queue
                if ($type == 'follow' && $type_id == $v0['user_id']) continue;

                //2018-09-27 Lion: 比對未觀看紀錄, 若存在則不建立新資料
                $contrast = [
                    'target2type' => $target_type,
                    'target2type_id' => $target_type_id,
                    'trigger_user_id' => $trigger_user_id,
                    'url' => empty($param['url']) ? null : $param['url'],
                    'user_id' => $v0['user_id'],
                ];

                if (in_array($build($contrast), $mapping)) continue;

                $add[] = [
                    'user_id' => $v0['user_id'],
                    'message' => $message,
                    'target2type' => $target_type,
                    'target2type_id' => $target_type_id,
                    'trigger_user_id' => $trigger_user_id,
                    'state' => 'pretreat',
                    'url' => empty($param['url']) ? null : $param['url'],
                ];
            }

            if ($add) (new \pushqueueModel)->add($add);
        }
    }
}