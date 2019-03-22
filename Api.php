<?php
namespace app\common\controller;

use think\exception\HttpResponseException;

use think\facade\Response;
use think\Request;
use wslibs\epiiadmin\jscmd\JsCmd;


/**
 * Created by PhpStorm.
 * User: mrren
 * Date: 2018/8/31
 * Time: 下午2:17
 */
class Api
{


    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;
    protected $responseType = 'json';
    protected $debug = true;
    protected $uid = 1;

    public function getUid()
    {
        return $this->uid;
    }

    public function __construct(Request $request = null)
    {
        $this->request = $request;
        JsCmd::returnData(true);
        $token = $this->request->param('token/s');
        if(!$token){
           $this->uid=0;
        }else{
            $arr = str_split($token,32);
        }
    }

    /**
     * 操作成功返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为1
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function success($data = null, $msg = '', $code = 1, $type = null, array $header = [])
    {
        if (is_string($data)) {
            $msg_tmp = $msg;
            $msg = $data;
            $data = is_array($msg_tmp) ? $msg_tmp : null;
        }
        if (!$msg) {
            $msg = "成功";
        }
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 操作失败返回的数据
     * @param string $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型
     * @param array $header 发送的 Header 信息
     */
    protected function error($msg = '', $data = null, $code = 0, $type = null, array $header = [])
    {
        $this->result($msg, $data, $code, $type, $header);
    }

    /**
     * 返回封装后的 API 数据到客户端
     * @access protected
     * @param mixed $msg 提示信息
     * @param mixed $data 要返回的数据
     * @param int $code 错误码，默认为0
     * @param string $type 输出类型，支持json/xml/jsonp
     * @param array $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function result($msg, $data = null, $code = 0, $type = null, array $header = [])
    {
        $result = [
            'code' => $code,
            'msg' => $msg,
            'time' => $this->request->server('REQUEST_TIME'),
            'data' => $data,
        ];
        // 如果未设置类型则自动判断

        $type = $type ? $type : ($this->request->param(config('var_jsonp_handler')) ? 'jsonp' : $this->responseType);

        if (isset($header['statuscode'])) {
            $code = $header['statuscode'];
            unset($header['statuscode']);
        } else {
            //未设置状态码,根据code值判断
            $code = $code >= 1000 || $code < 200 ? 200 : $code;
        }
        $response = Response::create($result, $type, $code)->header($header);
        throw new HttpResponseException($response);
    }

}