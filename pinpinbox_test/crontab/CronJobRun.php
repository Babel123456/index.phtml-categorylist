<?php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

(new Model())->beginTransaction();

$m_cronjob = (new cronjobModel)->column([
    'cronjob_id',
    'method',
    'param',
])->where([[[['state', '=', 'pretreat']], 'and']])->limit('0,1')->lock('for update')->fetch();

if ($m_cronjob) {
    define('CRONJOB_ID', $m_cronjob['cronjob_id']);

    try {
        list ($class, $function) = explode('::', $m_cronjob['method']);

        $class = new $class;

        $return = call_user_func([$class, $function], json_decode($m_cronjob['param'], true));

        $state = ($return['result'] == 1) ? 'success' : 'fail';
    } catch (\Exception $e) {
        $state = 'fail';

        (new cronjobModel)->setException($e);
    }

    (new cronjobModel)->where([[[['cronjob_id', '=', CRONJOB_ID]], 'and']])->edit([
        'mysql_connection_id' => (new cronjobModel)->connection_id(),
        '`return`' => isset($return) ? json_encode($return) : '',
        'runtime' => runtime(),
        'state' => $state,
    ]);
}

(new Model())->commit();