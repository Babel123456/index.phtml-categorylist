<?php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

(new \cronjobModel)->add([
    'method' => 'cronjobModel::createPointMonthlyReport',
    'state' => 'pretreat',
]);