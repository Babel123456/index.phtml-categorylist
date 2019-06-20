<?php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

$param = null;

if (isset($argv[1])) {
    $param['date'] = $argv[1];
}

(new \cronjobModel)->add([
    'method' => 'paymentModel::importData',
    'param' => $param === null ? null : json_encode($param),
    'state' => 'pretreat',
]);