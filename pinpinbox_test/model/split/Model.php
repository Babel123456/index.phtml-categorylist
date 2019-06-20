<?php

namespace Model;

class split extends \Model
{
    protected
        $database = 'cashflow',
        $join_table = [],
        $memcache = 'cashflow',
        $table = 'split';

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

    static function getIdentity($user_id)
    {
        $mode = (new \businessuser\Model())
            ->column(['businessuser.mode'])
            ->join([
                ['INNER JOIN', 'user', 'ON user.businessuser_id = businessuser.businessuser_id']
            ])
            ->where([[[['user.user_id', '=', $user_id]], 'and']])
            ->fetchColumn();

        switch ($mode) {
            case 'company':
                $identity = \Schema\split::company_downline;
                break;

            case 'personal':
                $identity = \Schema\split::personal_downline;
                break;

            default:
                $mode = (new \businessuser\Model())
                    ->column(['`mode`'])
                    ->where([[[['user_id', '=', $user_id]], 'and']])
                    ->fetchColumn();

                switch ($mode) {
                    case 'company':
                        $identity = \Schema\split::company;
                        break;

                    case 'personal':
                        $identity = \Schema\split::personal;
                        break;

                    default:
                        $identity = \Schema\split::general;
                        break;
                }
                break;
        }

        return $identity;
    }

    static function getPoolOfAlbumId($user_id)
    {
        $where = [[[['album.act', '!=', 'delete'], ['album.state', '=', 'success'], ['album.zipped', '=', 1]], 'and']];

        $businessuserModel = (new \businessuser\Model())
            ->column([
                'businessuser_id',
                'mode',
            ])
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->fetch();

        switch ($businessuserModel['mode']) {
            case 'company':
            case 'personal':
                $array_user_id = array_column(
                    (new \userModel)
                        ->column(['user_id'])
                        ->where([[[['businessuser_id', '=', $businessuserModel['businessuser_id']]], 'and']])
                        ->fetchAll()
                    ,
                    'user_id'
                );

                $where = array_merge($where, [[[['album.user_id', 'IN', array_merge($array_user_id, [$user_id])]], 'and']]);
                break;

            default:
                $where = array_merge($where, [[[['album.user_id', '=', $user_id]], 'and']]);
                break;
        }

        return array_column(
            (new \albumModel)
                ->column(['album.album_id'])
                ->where($where)
                ->fetchAll(),
            'album_id'
        );
    }

    static function getPoolOfTemplateId($user_id)
    {
        $where = [[[['template.act', '!=', 'delete'], ['template.state', '=', 'success']], 'and']];

        $businessuserModel = (new \businessuser\Model())
            ->column([
                'businessuser_id',
                'mode',
            ])
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->fetch();

        switch ($businessuserModel['mode']) {
            case 'company':
            case 'personal':
                $array_user_id = array_column(
                    (new \userModel)
                        ->column(['user_id'])
                        ->where([[[['businessuser_id', '=', $businessuserModel['businessuser_id']]], 'and']])
                        ->fetchAll()
                    ,
                    'user_id'
                );

                $where = array_merge($where, [[[['template.user_id', 'IN', array_merge($array_user_id, [$user_id])]], 'and']]);
                break;

            default:
                $where = array_merge($where, [[[['template.user_id', '=', $user_id]], 'and']]);
                break;
        }

        return array_column(
            (new \templateModel)
                ->column(['template.template_id'])
                ->where($where)
                ->fetchAll(),
            'template_id'
        );
    }

    static function getRatio($user_id, $type)
    {
        $mode = (new \businessuser\Model())
            ->column([
                'businessuser.mode',
            ])
            ->join([
                ['INNER JOIN', 'user', 'ON user.businessuser_id = businessuser.businessuser_id']
            ])
            ->where([[[['user.user_id', '=', $user_id]], 'and']])
            ->fetchColumn();

        switch ($mode) {
            case 'company':
                $ratio = _('依所屬經紀公司規定');
                break;

            case 'personal':
                $ratio = (\Model\split::getRatioForBusinessuserOfPersonalOfHimself($user_id, $type) * 100) . '%';
                break;

            default:
                $ratio = (\Model\split::getRatioForUser($user_id, $type) * 100) . '%';
                break;
        }

        return $ratio;
    }

    /**
     * 取得一般用戶 p 點拆分比
     * @param $user_id
     * @param $type
     * @return float|int
     * @throws \Exception
     */
    static function getRatioForUser($user_id, $type)
    {
        $ratio = (new \Model\userpointsplit)
            ->column(['ratio'])
            ->where([[[['user_id', '=', $user_id]], 'and']])
            ->fetchColumn();

        if ($ratio === false) $ratio = 0.5;

        switch ($type) {
            case 'album':
                switch (\Core::get_userlevel($user_id)) {
                    case 0:
                        $ratio += 0;
                        break;

                    case 1:
                        $ratio += 0.025;
                        break;

                    case 2:
                        $ratio += 0.05;
                        break;

                    case 3:
                        $ratio += 0.075;
                        break;

                    case 4:
                        $ratio += 0.1;
                        break;

                    case 5:
                        $ratio += 0.15;
                        break;

                    default:
                        throw new \Exception("[" . __METHOD__ . "] Unknown case");
                        break;
                }

                //grade
                switch (\Core::get_usergrade($user_id)) {
                    case 'free':
                        $ratio += 0;
                        break;

                    case 'plus':
                        $ratio += 0.2;
                        break;

                    case 'profession':
                        $ratio += 0.2;
                        break;

                    default:
                        throw new \Exception("[" . __METHOD__ . "] Unknown case");
                        break;
                }
                break;

            case 'template':
                //grade
                switch (\Core::get_usergrade($user_id)) {
                    case 'free':
                        $ratio += 0;
                        break;

                    case 'plus':
                        $ratio += 0.1;
                        break;

                    case 'profession':
                        $ratio += 0.1;
                        break;

                    default:
                        throw new \Exception("[" . __METHOD__ . "] Unknown case");
                        break;
                }
                break;

            default:
                throw new \Exception("[" . __METHOD__ . "] Unknown case");
                break;
        }

        if ($ratio > 1) $ratio = 1;

        return $ratio;
    }

    static function getRatioForBusinessuser($user_id)
    {
        $ratio = 0;

        if (\businessuser\Model::isUpline($user_id)) {
            if (\businessuser\Model::isCompany($user_id)) {
                $ratio = self::getRatioForBusinessuserOfCompany();
            } elseif (\businessuser\Model::isPersonal($user_id)) {
                $ratio = self::getRatioForBusinessuserOfPersonalOfBroker();
            }
        }

        return $ratio;
    }

    static function getRatioForBusinessuserOfCompany()
    {
        return 0.7;
    }

    static function getRatioForBusinessuserOfPersonalOfBroker()
    {
        return 0.1;
    }

    static function getRatioForBusinessuserOfPersonalOfHimself($user_id, $type)
    {
        $pointsplitrate = 0.55;

        switch ($type) {
            case 'album':
                switch (\Core::get_userlevel($user_id)) {
                    case 0:
                        $pointsplitrate += 0;
                        break;

                    case 1:
                        $pointsplitrate += 0.025;
                        break;

                    case 2:
                        $pointsplitrate += 0.05;
                        break;

                    case 3:
                        $pointsplitrate += 0.075;
                        break;

                    case 4:
                        $pointsplitrate += 0.1;
                        break;

                    case 5:
                        $pointsplitrate += 0.15;
                        break;

                    default:
                        throw new \Exception('Unknown case of "level".');
                        break;
                }
                break;

            case 'template':
                $pointsplitrate += 0.1;
                break;

            default:
                throw new \Exception('Unknown case of "type".');
                break;
        }

        return $pointsplitrate;
    }

    static function getSum($user_id)
    {
        //
        $array_album_id = self::getPoolOfAlbumId($user_id);

        $sum_album = 0;

        if ($array_album_id) {
            $sum_album = (new \exchangeModel)
                ->column(['SUM(split.point)'])
                ->join([
                    [
                        'INNER JOIN',
                        'split',
                        \userModel::isDownlineOfBusinessUserOfCompany($user_id) ?
                            'ON split.exchange_id = exchange.exchange_id'
                            :
                            'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user_id)
                    ]
                ])
                ->where([[[['exchange.type', '=', 'album'], ['exchange.id', 'IN', $array_album_id]], 'and']])
                ->fetchColumn();
        }

        //
        $array_template_id = self::getPoolOfTemplateId($user_id);

        $sum_template = 0;

        if ($array_template_id) {
            $sum_template = (new \exchangeModel)
                ->column(['SUM(split.point)'])
                ->join([
                    [
                        'INNER JOIN',
                        'split',
                        \userModel::isDownlineOfBusinessUserOfCompany($user_id) ?
                            'ON split.exchange_id = exchange.exchange_id'
                            :
                            'ON split.exchange_id = exchange.exchange_id AND split.user_id = ' . (new \exchangeModel)->quote($user_id)
                    ]
                ])
                ->where([[[['exchange.type', '=', 'template'], ['exchange.id', 'IN', $array_template_id]], 'and']])
                ->fetchColumn();
        }

        return (int)($sum_album + $sum_template);
    }

    static function getSumOfSettlement($user_id)
    {
        return (int)(new \settlementModel())
            ->column(['SUM(point_album) + SUM(point_template)'])
            ->where([[[['user_id', '=', $user_id], ['income_id', '=', 0], ['state', '=', 'pretreat']], 'and']])
            ->fetchColumn();
    }

    static function getSumOfUnsettlement($user_id)
    {
        //
        $array_album_id = self::getPoolOfAlbumId($user_id);

        $sum_album = 0;

        if ($array_album_id) {
            $sum_album = (new \exchangeModel)
                ->column(['SUM(split.point)'])
                ->join([
                    [
                        'INNER JOIN',
                        'split',
                        \userModel::isDownlineOfBusinessUserOfCompany($user_id) ?
                            'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL'
                            :
                            'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL AND split.user_id = ' . (new \exchangeModel)->quote($user_id)
                    ]
                ])
                ->where([[[['exchange.type', '=', 'album'], ['exchange.id', 'IN', $array_album_id]], 'and']])
                ->fetchColumn();
        }

        //
        $array_template_id = self::getPoolOfTemplateId($user_id);

        $sum_template = 0;

        if ($array_template_id) {
            $sum_template = (new \exchangeModel)
                ->column(['SUM(split.point)'])
                ->join([
                    [
                        'INNER JOIN',
                        'split',
                        \userModel::isDownlineOfBusinessUserOfCompany($user_id) ?
                            'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL'
                            :
                            'ON split.exchange_id = exchange.exchange_id AND split.settlement_id IS NULL AND split.user_id = ' . (new \exchangeModel)->quote($user_id)
                    ]
                ])
                ->where([[[['exchange.type', '=', 'template'], ['exchange.id', 'IN', $array_template_id]], 'and']])
                ->fetchColumn();
        }

        return (int)($sum_album + $sum_template);
    }
}