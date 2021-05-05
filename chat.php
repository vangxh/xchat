<?php
use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// 标记是全局启动
define('GLOBAL_START', 1);

// 加载所有app/*/start.php，以便启动所有服务
foreach (glob(__DIR__.'/worker/chat/start*.php') as $start_file) {
    require_once $start_file;
}

Worker::$logFile = __DIR__ .'/runtime/log/worker_chat.log';
Worker::$pidFile = __DIR__ .'/runtime/pid/worker_chat.pid';
// 运行所有服务
Worker::runAll();
