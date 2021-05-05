<?php
// declare(ticks=1);
use GatewayWorker\Lib\Gateway;
use Workerman\MySQL\Connection;
use Workerman\Redis\Client;
use Workerman\Worker;
use lib\cache\Handle;

class Events
{
    // 缓存局柄
    protected static $redis;
    // 数据库句柄
    protected static $mysql;

    // 初始化
    public static function onWorkerStart($businessWorker)
    {
        // 加载环境变量配置文件
        $cnf = parse_ini_file(__DIR__ .'/../../.env', true);
        // redis
        self::$redis = Handle::init([
            'type'          => 'redis',
            'host'          => '127.0.0.1',
            'port'          => '6379',
            'prefix'        => '',
            'expire'        => 1440
        ]);
        // mysql
        self::$mysql = new Connection(
            $cnf['database']['hostname'],
            $cnf['database']['hostport'],
            $cnf['database']['username'],
            $cnf['database']['password'],
            $cnf['database']['database']
        );
    }

    // websocket连接
    public static function onWebSocketConnect($client_id)
    {
        // 此处删除了授权判断，因单商户无token设置
    }

    // 当客户端发来消息时触发
    public static function onMessage($client_id, $msg)
    {
        try {
            // JSON DECODE
            $msg = json_decode($msg, true) ?: [];
            // 消息格式
            if (!isset($msg['type']) || $msg['type'] == 'PING' || !isset($msg['data'])) {
                return;
            }
            $type = $msg['type'];
            $msg  = $msg['data'];
            // 消息类型
            switch ($type) {
                case 'initVisitor' :
                    // 若为游客，则绑定客户端id
                    $_SESSION['uid'] = $msg['uid'];
                    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                    $_SESSION['kefu'] = 0;
                    Gateway::bindUid($client_id, 'user_'. $_SESSION['uid']);
                    // 默认客服id为真 且客服在线
                    $msg['id'] = intval($msg['id']);
                    if ($msg['id'] > 0 && Gateway::isUidOnline($msg['id'])) {
                        // 绑定客服
                        Gateway::joinGroup($client_id, 'kefu'. $msg['id']);
                        // 向访客发送客服信息
                        Gateway::sendToClient($client_id, json_encode([
                            'type'  => 'connect',
                            'data'  => [
                                'type'  => 'kefu',
                                'id'    => $msg['id'],
                                'kefu'  => 1
                            ]
                        ]));
                        // 向客服发送访客连接信息
                        Gateway::sendToUid('user_'. $msg['id'], json_encode([
                            'type' => 'connect',
                            'data' => [
                                'type'      => 'kefu',
                                'id'        => $_SESSION['uid'],
                                'name'      => $msg['name'],
                                'avatar'    => $msg['avatar'],
                                'status'    => $msg['status'],
                                'time'      => time(),
                                'city'      => $msg['city'],
                                'refer'     => $msg['refer']
                            ]
                        ]));
                    } else {
                        // 在线客服
                        $kefu = Gateway::getUidListByGroup('kefu_online');
                        if (!empty($kefu)) {
                            // 随机选一客服
                            $kefu = array_values($kefu);
                            $kefu = $kefu[rand(0, count($kefu) - 1)];
                            // 向客服发送访客连接信息
                            Gateway::sendToUid($kefu, json_encode([
                                'type'  => 'connect',
                                'data'  => [
                                    'type'      => 'kefu',
                                    'id'        => $_SESSION['uid'],
                                    'name'      => $msg['name'],
                                    'avatar'    => $msg['avatar'],
                                    'status'    => $msg['status'],
                                    'time'      => time(),
                                    'city'      => $msg['city'],
                                    'refer'     => $msg['refer']
                                ]
                            ]));
                            // 转换为对应uid
                            $kefu = str_replace('user_', '', $kefu);
                            // 向访客发送客服信息
                            Gateway::sendToClient($client_id, json_encode([
                                'type'  => 'connect',
                                'data'  => [
                                    'type'  => 'kefu',
                                    'id'    => $kefu,
                                    'kefu'  => 1
                                ]
                            ]));
                            // 绑定客服
                            Gateway::joinGroup($client_id, 'kefu_'. $kefu);
                        } else {
                            // id=0表示客服不在线
                            $kefu = [
                                'type'  => 'kefu',
                                'id'    => $msg['id'],
                                'kefu'  => 1
                            ];
                            if ($msg['id'] == 0) {
                                $kefu['name']   = '机器人-虎子';
                                $kefu['avatar'] = '/static/chat/img/logo-36x36.png';
                            }
                            Gateway::sendToClient($client_id, json_encode([
                                'type' => 'connect',
                                'data' => $kefu
                            ]));
                        }
                    }

                    // 离线消息
                    if ($res = self::$mysql->query('SELECT * FROM im_chatlog WHERE receiver = "'. $_SESSION['uid'] .'" AND ctime > '. $msg['time'])) {
                        $msg = [];
                        foreach ($res as $k => $vo) {
                            $msg[$k] = [
                                'type'      => 'kefu',
                                'id'        => $vo['sender'],
                                'name'      => $vo['sender_nickname'],
                                'avatar'    => $vo['sender_avatar'],
                                'content'   => $vo['message'],
                                'time'      => $vo['ctime']
                            ];
                            unset($res[$k]);
                        }
                        // 向用户发送离线消息
                        Gateway::sendToUid('user_'. $_SESSION['uid'], json_encode([
                            'type'  => 'offmsg',
                            'time'  => time(),
                            'data'  => $msg
                        ]));
                    }
                break;
                case 'initService' :
                    // 若为游客，则绑定客户端id
                    $_SESSION['uid'] = $msg['id'];
                    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                    // 标志为客服
                    $_SESSION['kefu'] = 1;
                    Gateway::bindUid($client_id, 'user_'. $_SESSION['uid']);
                    // 绑定在线客服 用于访客连接自动分配
                    Gateway::joinGroup($client_id, 'kefu_online');
                    // 向在线客服分组发送上线信息
                    Gateway::sendToGroup('kefu_online', json_encode([
                        'type' => 'status',
                        'data' => [
                            'id'        => $_SESSION['uid'],
                            'status'    => $msg['status'],
                            'kefu'      => 1
                        ]
                    ]), [$client_id]);
                    // 离线消息
                    if ($res = self::$mysql->query('SELECT * FROM im_chatlog WHERE receiver = "'. $_SESSION['uid'] .'" AND ctime > '. $msg['time'])) {
                        $msg = [];
                        foreach ($res as $k => $vo) {
                            $msg[$k] = [
                                'type'      => 'kefu',
                                'id'        => $vo['sender'],
                                'name'      => $vo['sender_nickname'],
                                'avatar'    => $vo['sender_avatar'],
                                'content'   => $vo['message'],
                                'time'      => $vo['ctime']
                            ];
                            unset($res[$k]);
                        }
                        // 向用户发送离线消息
                        Gateway::sendToUid('user_'. $_SESSION['uid'], json_encode([
                            'type'  => 'offmsg',
                            'time'  => time(),
                            'data'  => $msg
                        ]));
                    }
                break;
                case 'CHAT' :
                    // 自已和自已不作处理
                    if ($_SESSION['uid'] == $msg['to']['id']) {
                        return;
                    }
                    // 客服模式
                    if ($msg['to']['type'] == 'kefu') {
                        // id=0表示未配置客服，此时机器人自动回复
                        if ($msg['to']['id']) {
                            // 消息内容
                            $val = [
                                $_SESSION['uid'],
                                $msg['mine']['name'],
                                $msg['mine']['avatar'],
                                $msg['mine']['content'],
                                $msg['to']['id'],
                                time(),
                                time()
                            ];
                            // 写入主消息表
                            $cid = self::$mysql->query("INSERT INTO im_chatlog (`sender`,`sender_nickname`,`sender_avatar`,`message`,`receiver`,`utime`,`ctime`) VALUES ('". implode("','", $val) ."')");
                            // 推送内容
                            $content = json_encode([
                                'type'  => 'chat',
                                'time'  => time(),
                                'data'  => [
                                    'type'      => 'kefu',
                                    'id'        => $msg['mine']['id'],
                                    'name'      => $msg['mine']['name'],
                                    'avatar'    => $msg['mine']['avatar'],
                                    'content'   => $msg['mine']['content'],
                                    'time'      => time()
                                ]
                            ]);
                        }
                        // 向用户发送消息
                        if ($msg['to']['id'] && Gateway::isUidOnline('user_'. $msg['to']['id'])) {
                            Gateway::sendToUid('user_'. $msg['to']['id'], $content);
                        } elseif ($msg['to']['kefu'] == 1) {
                            $msg['mine']['content'] = preg_replace('/[^0-9a-zA-Z\x{4e00}-\x{9fa5}]+/u', '', $msg['mine']['content']);
                            if ($msg['mine']['content'] && ($content = self::$mysql->query('SELECT content FROM im_match WHERE `uid` = "'. $msg['to']['id'] .'" AND `name` LIKE "'. $msg['mine']['content'] .'%" LIMIT 1'))) {
                                $content = $content[0]['content'];
                                $robot = 1;
                            } else {
                                $content = '对方暂不在线，您可留言~';
                                $robot = 2;
                            }
                            $content = json_encode([
                                'type'  => 'chat',
                                'time'  => time(),
                                'data'  => [
                                    'type'      => 'kefu',
                                    'id'        => $msg['to']['id'],
                                    'name'      => '机器人-虎子',
                                    'avatar'    => '/static/chat/img/logo-36x36.png',
                                    'content'   => $content,
                                    'time'      => time(),
                                    'robot'     => $robot
                                ]
                            ]);
                            Gateway::sendToUid('user_'. $msg['mine']['id'], $content);
                        }
                    }
                break;
                case 'TRANSFER':
                    if (Gateway::isUidOnline('user_'. $msg['id'])) {
                        // kefu = 1表示转移客服
                        if ($msg['kefu'] == 1) {
                            // 向访客发送客服信息
                            Gateway::sendToUid('user_'. $msg['id'], json_encode([
                                'type'  => 'connect',
                                'data'  => [
                                    'type'  => 'kefu',
                                    'id'    => $_SESSION['uid'],
                                    'kefu'  => 1,
                                    'case'  => 1    // 表示转移连接
                                ]
                            ]));
                            $visitor = Gateway::getClientIdByUid('user_'. $msg['id']);
                            foreach ($visitor as $client) {
                                // 解除客服
                                Gateway::leaveGroup($client, 'kefu_'. $msg['uid']);
                                // 绑定客服
                                Gateway::joinGroup($client, 'kefu_'. $_SESSION['uid']);
                            }
                        } else {
                            // 向客服发送访客转接信息
                            Gateway::sendToUid('user_'. $msg['id'], json_encode([
                                'type'  => 'transfer',
                                'data'  => [
                                    'type'      => 'kefu',
                                    'id'        => $msg['uid'],         // 访客id
                                    'name'      => $msg['name'],        // 访客名称
                                    'avatar'    => $msg['avatar'],      // 图像
                                    'status'    => $msg['status'],      // 状态
                                    'time'      => $msg['time'],
                                    'kefu'      => 0,
                                    'uid'       => $_SESSION['uid'],    // 发起转移客服id
                                ]
                            ]));
                        }
                    }
                break;
                case 'NOTIFY':
                    if (Gateway::isUidOnline('user_'. $msg['id'])) {
                        Gateway::sendToUid('user_'. $msg['id'], json_encode([
                            'type'  => 'notify',
                            'data'  => $msg['data'] ?? '',
                            'msg'   => $msg['content']
                        ]));
                    }
                break;
                case 'CLOSE':
                    if (Gateway::isUidOnline('user_'. $msg['id'])) {
                        $client = Gateway::getClientIdByUid('user_'. $msg['id']);
                        Gateway::sendToUid('user_'. $msg['id'], json_encode([
                            'type'  => 'close',
                            'msg'   => '客服已关闭连接'
                        ]));
                        foreach ($client as $val) {
                            Gateway::closeClient($val);
                        }
                    }
                break;
                // case 'PING' : break;
            }
        } catch (\Exception $e) {
            @file_put_contents(__DIR__ .'/../../runtime/log/worker_chat.log', '['. date('Y-m-d H:i:s') .']'. $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    // 当用户断开连接时触发
    public static function onClose($client_id)
    {
        if (isset($_SESSION['uid'])) {
            // 仅用户在在线状态才需要通知及更新数据库
            if (is_int($_SESSION['uid'])) {
                // 更新用户在线状态
                self::$mysql->query('UPDATE im_user SET `logout` = '. time() .',`login_status` = "offline" WHERE `uid` = '. $_SESSION['uid']);
            }
            // 通知其它在线用户
            Gateway::sendToGroup('kefu_online', json_encode([
                'type' => 'status',
                'data' => [
                    'id'        => $_SESSION['uid'],
                    'status'    => 'offline',
                    'kefu'      => $_SESSION['kefu']
                ]
            ]), [$client_id]);
        }

        // 清除SESSION
        $_SESSION = null;
    }
}
