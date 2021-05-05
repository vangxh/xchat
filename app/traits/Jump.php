<?php
namespace app\traits;

use think\Response;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\View;
use think\facade\Config;

trait Jump
{
    // 公共错误提示
    protected $error = '';

	// 操作成功跳转的快捷方法
    protected function success($msg = '', $url = null, $data = '')
    {
        if (is_null($url)) {
            $url = Request::instance()->isAjax() ? '' : Request::instance()->server('HTTP_REFERER');
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url
        ];

        //$type = Request::instance()->isAjax() ? 'json' : 'html';
        $type = 'json';
        //'html' == $type && $result = View::fetch(Config::get('app.dispatch_success_tmpl'), $result);

        $response = Response::create($result, $type);
        throw new HttpResponseException($response);
    }

    // 操作错误跳转的快捷方法
    protected function error($msg = '', $url = null, $data = '')
    {
        if (is_null($url)) {
            $url = Request::instance()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : url($url);
        }
        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url
        ];
        $type = 'json';
        //$type = Request::instance()->isAjax() ? 'json' : 'html';
        //'html' == $type && $result = View::fetch(Config::get('app.dispatch_error_tmpl'), $result);

        $response = Response::create($result, $type);
        throw new HttpResponseException($response);
    }

	// 返回封装后的API数据到客户端
    protected function result($data, $code = 0, $msg = '', array $header = [], array $options = [])
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'time' => Request::instance()->server('REQUEST_TIME'),
            'data' => $data,
        ];
        $response = Response::create($result, 'json')->header($header)->options($options);
        throw new HttpResponseException($response);
    }

	// 重定向
    protected function redirect($url, $code = 302)
    {
        (Request::instance()->isAjax() || Request::instance()->isJson()) && $this->error('登录已失效，将跳往登录页', $url);
        throw new HttpResponseException(Response::create($url, 'redirect', $code));
    }
}