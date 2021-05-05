<?php
use \Workerman\Worker;
use \GatewayWorker\BusinessWorker;
use \GatewayWorker\Lib\Gateway;

// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';

$worker = new BusinessWorker();

$worker->name = 'ImBusiness';

$worker->count = 4;

$worker->registerAddress = '127.0.0.1:1238';

// 如果不是在根目录启动，则运行runAll方法
defined('GLOBAL_START') || Worker::runAll();
