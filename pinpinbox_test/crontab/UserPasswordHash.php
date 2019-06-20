<?php
include dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Load.php';

if (PHP_SAPI != 'cli') redirect(frontstageController::url('_', '_404'));

$m_user = Model('user')->column(['user_id', 'password'])->fetchAll();

$editByCase = [];
$array0 = [];
foreach ($m_user as $v0) {
	$array0['when'][] = ['user_id', '=', $v0['user_id'], password_hash($v0['password'], PASSWORD_DEFAULT)];
}
$array0['else'] = '`password`';
$editByCase['`password`'] = $array0;

Model('user')->editByCase($editByCase);