<?php
namespace app\controller;
use app\BaseController;
use think\facade\Db;
use think\facade\Filesystem;
use net\IpLocation;
use GatewayClient\Gateway;

class Xchat extends BaseController
{
	// 初始化操作
	protected function initialize()
	{
		if ($this->request->action() != 'initService') {
			// 你自已项目的用户uid, 0表示访客
			$uid = 0;
			// 游客处理
			$uid == 0 && $uid = md5($this->request->session('PHPSESSID'));
			// 定义UID常量
			define('UID', $uid);
		}
	}

	// 客服模式
	public function initVisitor()
	{
		cache('xchat_'. UID, ['uid'=>UID, 'time'=>time()]);
		// 定义客服系统主体信息
        $app = [
            'name'      => '携信客服',                   // 客服系统名称
            'avatar'    => '/static/chat/img/logo.png'  // 图标
        ];
		// 客服数据 kefu字段用于对话时判断对方是否为客服
		$kefu = Db::name('im_user')
                    ->where('status', 0)
					->order('sort ASC')
					->column('uid AS id,nickname AS name,avatar,login_status AS status', 'uid');
		array_walk($kefu, function(&$vo) {
			$vo['type'] = 'kefu';
			$vo['kefu'] = 1;
		});
		// 用户数据
		$city = $this->getName($this->request->ip());
		if (is_int(UID) && UID > 0) {
            // 判断是否为客服
            if (!$conf = Db::name('im_user')->where('uid', UID)->field('nickname,avatar,logout')->find()) {
                // 登录用户以访客模式访问客服系统访客端
                $conf = [
                    'nickname'  => '昵称',  // 项目的用户昵称
                    'avatar'    => '/static/chat/img/noavatar.jpg', // 你自已项目的用户图标
                    'logout'    => time()
                ];
            }
		} else {
			$conf = [
				'nickname'	=> $city .'网友',
				'avatar'	=> '/static/chat/img/noavatar.jpg',
				'logout'	=> time()
			];
		}
		// 更新访客最新信息到第一条数据 - 不论是否存在
		Db::name('im_chatlog')
			->where('sender', UID)
			->order('utime DESC')
			->limit(1)
			->update([
				'city'	=> $city,
				'refer'	=> $_SERVER['HTTP_REFERER'] ?? '',
				'num'	=> ['exp', '`num`+1'],
				'utime'	=> time()
			]);
		// 欢迎语、默认展示
		$res = Db::name('im_reply')
                ->field('id,pid,name,content,type')
                ->order('sort DESC')
                ->select();
		$app['chat_auto'] = [];
		$app['chat_show'] = [];
		foreach ($res as $vo) {
			if ($vo['type'] == 1) {
				$vo['time'] = time();
				$app['chat_auto'][] = $vo;
			} else {
				$pid = (String) $vo['pid'];
				if (isset($app['chat_show'][$pid])) {
					if ($pid == 0) {
						$app['chat_show'][$pid][$vo['id']] = $vo;
					} else {
						$app['chat_show'][$pid]['_'][] = $vo;
					}
				} else {
					if ($pid == 0) {
						$app['chat_show'][$pid] = [];
						$app['chat_show'][$pid][$vo['id']] = $vo;
					} else {
						$app['chat_show'][$pid] = ['_'=>[]];
						$app['chat_show'][$pid]['_'][] = $vo;;
					}
				}
			}
		}

		return json([
			'code'			=> 1,
			'msg'			=> '',
			'data'			=> [
				'mine'			=> [
					'type'		=> 'kefu',
					'id'		=> UID,
	   				'name'		=> $conf['nickname'],
					'avatar'	=> $conf['avatar'],
					'status'	=> 'online',
					'time'		=> $conf['logout'],
					'city'		=> $city,
					'refer'		=> $_SERVER['HTTP_REFERER'] ?? ''
	   			],
	   			'kefu'			=> $kefu,
				'time'			=> time(),	// 初始化时间
				'app'			=> $app
			]
		]);
	}
	// 客服服务模式
	public function initService()
	{
		// 此处模拟 im_user中有2个客服uid=1或2
		cache('xchat_1', ['uid'=>1, 'time'=>time()]);
		define('UID', 1);

		// 客服验证
		is_int(UID) || $this->error('客服模式，请授权登录');
        // 定义客服系统主体信息
        $app = [
            'name'      => '携信客服',                   // 客服系统名称
            'avatar'    => '/static/chat/img/logo.png'  // 图标
        ];
		// 用户数据
		$conf = Db::name('im_user')
					->where('uid', UID)
					->where('status', 0)
					->field('nickname,avatar,login_status,logout')
					->find() ?: $this->error('客服不存在或已禁用');

		// 更新客服登录时间 - 用于获取离线消息
		$conf['login_status'] == 'offline' && Db::name('im_user')->where('uid', UID)->update([
			'login_status'  => 'online',
			'login_ip'      => $this->request->ip(),
			'login_time'	=> time(),
			'login_num'     => ['exp', `login_num` + 1]
		]);
		// 其它客服数据 kefu字段用于对话时判断对方是否为客服
		$kefu = Db::name('im_user')
					->where('uid', '<>', UID)
					->limit(20)
					->order('sort ASC')
					->column('uid AS id,nickname AS name,avatar,login_status AS status', 'uid');
		array_walk($kefu, function(&$vo) {
			$vo['type'] = 'kefu';
			$vo['kefu'] = 1;
		});

		// 快捷回复
		$res = Db::name('im_match')
					->where('uid', UID)
					->field('id,content')
					->order('sort DESC')
					->limit(20)
					->select();
		$app['chat_fast'] = $res;
		// 在线访客
		$visitor = [];
		Gateway::$registerAddress = '127.0.0.1:1238';
		if ($list = Gateway::getClientSessionsByGroup('kefu_'. UID)) {
			foreach ($list as $val) {
				// 地址
				$city = $this->getName($val['ip']);
				$visitor[$val['uid']] = [
					'type'		=> 'kefu',
					'id'		=> $val['uid'],
					'name'		=> empty($val['name']) ? $city : $val['name'],
					'avatar'	=> empty($val['avatar']) ? '/static/chat/img/noavatar.jpg' : $val['avatar'],
					'status'	=> 'online',
					'time'		=> time(),
					'city'		=> $city
				];
			}
		} else if ($list = Gateway::getClientSessionsByGroup('kefu_'. 0)) {
			foreach ($list as $val) {
				// 地址
				$city = $this->getName($val['ip']);
				$visitor[$val['uid']] = [
					'type'		=> 'kefu',
					'id'		=> $val['uid'],
					'name'		=> empty($val['name']) ? $city : $val['name'],
					'avatar'	=> empty($val['avatar']) ? '/static/chat/img/noavatar.jpg' : $val['avatar'],
					'status'	=> 'online',
					'time'		=> time(),
					'city'		=> $city
				];
			}
		}

		return json([
			'code'			=> 1,
			'msg'			=> '',
			'data'			=> [
				'mine'			=> [
					'type'		=> 'kefu',
					'id'		=> UID,
	   				'name'		=> $conf['nickname'],
					'avatar'	=> $conf['avatar'],
					'status'	=> $conf['login_status'] == 'hide' ? 'hide' : 'online',
					'time'		=> $conf['logout']
	   			],
				'kefu'			=> $kefu,
				'time'			=> time(),
				'visitor'		=> ['data'=>(object)$visitor, 'total'=>count($visitor), 'limit'=>10],
				'app'			=> $app,
			]
		]);
	}

	// 获取历史访客
	public function getHistory()
	{
		// 历史接待
		$res = Db::table('(SELECT sender,MAX(utime) AS utime FROM im_chatlog WHERE receiver = "'. UID .'" GROUP BY sender) T')
				->join('im_chatlog T2', 'T.sender = T2.sender AND T.utime = T2.utime', 'LEFT')
				->group('T.sender')
				->order('T.utime DESC')
				->field('T2.sender AS id,T2.sender_nickname AS name,T2.sender_avatar AS avatar,T2.utime AS time,T2.ctime,T2.num,T2.refer,T2.city')
				->paginate(10);
		return json([
			'code'	=> 1,
			'data'	=> ['data'=>$res->items(), 'limit'=>10, 'total'=>$res->total()]
		]);
	}

	// 获取聊天记录
	public function chatlog($id)
	{
		$res = Db::name('im_chatlog')
					->where(function($query) use($id) {
						$query->where(function($query) use($id) {
							$query->where('sender', UID)->where('receiver', $id);
						})->whereOr(function($query) use($id) {
							$query->where('receiver', UID)->where('sender', $id);
						});
					})->order('ctime DESC')
					->field('sender AS id,sender_nickname AS name,sender_avatar AS avatar,message,ctime AS time')
					->paginate(10);
		// 反转
		$data = array_reverse($res->items());

		return json(['data'=>$data, 'total'=>$res->total(), 'limit'=>10]);
	}

	// 默认展示|自动欢迎
	public function reply($type = 0, $pid = 0)
	{
		in_array($type, [0, 1]) || $this->error('参数错误');

		if ($this->request->isJson()) {
			if ($type == 1) {
				$data = Db::name('im_reply')
                            ->where('type', 1)
                            ->field('id,content,sort')
                            ->order('sort DESC')
                            ->limit(5)
                            ->select();
			} else {
				if ($pid > 0) {
					$data = Db::name('im_reply')
							->where('type', 0)
							->where('pid', $pid)
							->field('id,name,content,sort')
							->limit(5)
							->order('sort DESC')
							->select();
					$this->result($data, 1);
				} else {
					$data = Db::name('im_reply')
							->where('type', 0)
							->where('pid', 0)
							->field('id,name,sort')
							->order('sort DESC')
							->limit(5)
							->select();
				}
			}
			$this->result($data, 1);
		} else {
			$this->assign('type', $type);
			return $this->fetch();
		}
	}

	// 添加reply回复
	public function reply_add($type = 0)
	{
		in_array($type, [0, 1]) || $this->error('参数错误');
		$this->request->isPost() || $this->error('访问错误');

		$opt = $this->request->post();

		isset($opt['pid']) || $opt['pid'] = 0;

		$data = [
			'type'	=> $type,
			'sort'	=> $opt['sort'],
			'ctime'	=> time(),
		];

		if ($type == 1) {
			$data['content'] = $opt['content'];
		} else {
			if ($opt['pid'] == 0) {
				$data['name'] = $opt['name'];
			} else {
				$data['name'] = $opt['name'];
				$data['content'] = $opt['content'];
				$data['pid'] = $opt['pid'];
			}
		}

		$id = Db::name('im_reply')->insertGetId($data);
		$this->result($id, 1, '添加成功');
	}
	// reply 编辑
	public function reply_edit($id)
	{
		$this->request->isPost() || $this->error('访问错误');
		$opt = $this->request->post();
		Db::name('im_reply')
			->where('id', $id)
			->update($opt);
		$this->success('编辑成功');
	}

	// 删除
	public function reply_del($id)
	{
		$pid = Db::name('im_reply')
				->where('id', $id)
				->value('pid');
		if ($pid > 0 || !Db::name('im_reply')->where('pid', $id)->count()) {
			Db::name('im_reply')
				->where('id', $id)
				->delete() || $this->error('删除失败');
		} else {
			$this->error('请先删除分组下内容');
		}
		$this->success('删除成功');
	}

	// 快捷回复 0知识库|1个人快捷
	public function match($type = 0)
	{
        // 此处用到非vue模板，关连样式、JS等文件较多，请自行实现
		return $this->fetch();
	}

	public function match_add($type = 0)
	{
        // 此处用到非vue模板，关连样式、JS等文件较多，请自行实现
		return $this->fetch();
	}

	public function match_edit($id)
	{
        // 此处用到非vue模板，关连样式、JS等文件较多，请自行实现
		return $this->fetch();
	}

	// 快捷回复删除
	public function match_del($id)
	{
		Db::name('im_match')
			->where('uid', UID)
			->where('id', $id)
			->delete() || $this->error('删除失败');
		$this->success('删除成功');
	}

	// 快捷回复检索
	public function match_search($key, $type = 0)
	{
		if ($key = preg_replace('/[^0-9a-zA-Z\x{4e00}-\x{9fa5}]+/u', '', $key)) {
			$res = Db::name('im_match')
					->where('uid', $type == 1 ? 0 : UID)
					->where('name', 'like', '%'. $key .'%')
					->order('sort DESC')
					->limit(20)
					->select();
		} else {
			$res = [];
		}

		$this->result($res, 1);
	}


	// 上传图片
	public function upload_img()
	{
		// 宣传封面图
	    if ($files = $this->request->file('file')) {
			if (is_array($files)) {
				$bat = true;
			} else {
				$bat = false;
				$files = [$files];
			}
			$pathArr = [];
			foreach ($files as $file) {
				$savename = Filesystem::putFile('xchat', $file);
				array_push($pathArr, Filesystem::getDiskConfig('public', 'url') .'/'. $savename);
			}
			$data = [
				'code'	=> 1,
				'data'	=> $bat ? $pathArr : $pathArr[0]
			];
	    } else {
	    	$data = [
				'msg'	=> 'File not exists',
				'code'	=> 0
			];
	    }

	    return json($data);
	}

	// 上传文件
	public function upload_file()
	{
		if ($file = $this->request->file('file')) {
	    	$savename = Filesystem::putFile('xchat', $file);
	        $data = [
				'code'	=> 1,
				'data'	=> [
					'src'	=> Filesystem::getDiskConfig('public', 'url') .'/'. $savename,
					'name'	=> $file->getName('name')
				]
			];
	    } else {
			$data = [
				'msg'	=> 'File not exists',
				'code'	=> 0
			];
	    }

	    return json($data);
	}

    // 加截访客聊天模板
    public function visitor()
    {
        return $this->fetch();
    }
	// 加截客服聊天模板
    public function service()
    {
        return $this->fetch();
    }

	// 获取用户地址
	protected function getName($ip)
	{
		// 根据ip获取用户地址
		if ($ip) {
			$api = new IpLocation();
			$res = $api->getlocation($ip);
			if ($res && !empty($res['country'])) {
				if (isset($res['province'])) {
					$res['country'] .= str_replace('省', '', $res['province']);
					if (isset($res['city']) && $res['city'] != $res['province']) {
						$res['country'] .= str_replace('市', '', $res['city']);
					}
				}
				return $res['country'];
			}
		}
		return '未知网友';
	}
}
