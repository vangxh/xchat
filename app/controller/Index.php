<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;

class Index extends BaseController
{
    public function index()
    {
        // 你项目的用户uid
        $uid = 0;
        $this->assign('uid', $uid);
        return $this->fetch();
    }

    // 客服端
    public function kefu()
    {
        // im_user中已插入2个客服账号 [uid=1或2]
        $uid = 1;
        $this->assign('uid', $uid);
        return $this->fetch();
    }
}
