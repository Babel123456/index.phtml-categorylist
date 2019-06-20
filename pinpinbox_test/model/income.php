<?php

class incomeModel extends Model
{
    protected $database = 'cashflow';
    protected $table = 'income';
    protected $memcache = 'cashflow';
    protected $join_table = ['user'];

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

    static function ableToTurnSettlementToIncome($user_id)
    {
        $result = \Lib\Result::SYSTEM_OK;
        $message = null;

        list ($result, $message) = array_decode_return((new \userModel)->usable_v2($user_id));
        if ($result != \Lib\Result::SYSTEM_OK) {
            goto _return;
        }

        if (\userModel::isDownlineOfBusinessUserOfCompany($user_id)) {
            $result = \Lib\Result::USER_ERROR;
            $message = _('您不能申請結算。');
            goto _return;
        }

        _return:
        return array_encode_return($result, $message);
    }
}