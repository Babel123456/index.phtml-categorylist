<?php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

$starttime = empty($argv[1])? date('Y-m-d 00:00:00', strtotime('-1 day')) : $argv[1];
$endtime = empty($argv[2])? date('Y-m-d 23:00:00', strtotime('-1 day')) : $argv[2];

(new userpageviewModel)->crontabForAnalysis($starttime, $endtime);