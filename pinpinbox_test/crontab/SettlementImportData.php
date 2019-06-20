<?php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

$starttime = isset($argv[1]) ? $argv[1] : date('Y-m-d 00:00:00', strtotime('first day of -1 month'));
$endtime = isset($argv[2]) ? $argv[2] : date('Y-m-d 23:59:59', strtotime('last day of -1 month'));

$param = [
    'starttime' => $starttime,
    'endtime' => $endtime,
];

(new \cronjobModel)->add([
    'method' => 'settlementModel::importData',
    'param' => json_encode($param),
    'state' => 'pretreat',
]);