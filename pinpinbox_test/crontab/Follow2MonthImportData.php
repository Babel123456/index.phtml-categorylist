<?php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

(new \cronjobModel)->add([
    'method' => 'follow2monthModel::importData',
    'state' => 'pretreat',
    'inserttime' => inserttime(),
]);