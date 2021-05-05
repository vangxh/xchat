<?php
use \Workerman\Worker;
use \GatewayWorker\Lib\Gateway;

// 自动加载类
require_once __DIR__ . '/../../vendor/autoload.php';

// 内部worker 进程
$worker = new Worker('text://0.0.0.0:1239');
// worker名称
$worker->name = 'ImPush';

$worker->onMessage = function($conn, $data) {
    // JSON数据
    $data = json_decode($data, true);
    // 用户连接是否存在
    if (is_array($data) && isset($data['type']) && isset($data['aid']) && isset($data['to']) && isset($data['msg'])) {
        // 额外发送
        if ($data['type'] == 'friend' && isset($data['uid']) && isset($data['msg1'])) {
            Gateway::sendToUid($data['aid'] . '_u_' . $data['uid'], json_encode([
                'type'  => $data['type'],
                'data'  => $data['msg1']
            ]));
        }
        // 添加好友或群通知
        Gateway::sendToUid($data['aid'] .'_u_'. $data['to'], json_encode([
            'type'  => $data['type'],
            'data'  => $data['msg']
        ]));
        $conn->send('ok');
    }
    $conn->send('fail');
};

// 如果不是在根目录启动，则运行runAll方法
defined('GLOBAL_START') || Worker::runAll();
