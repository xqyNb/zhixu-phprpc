<?php

namespace Zhixu\Phprpc\frame;

use Zhixu\Phprpc\zhixuTrait\Success;

/**
 * Class
 * @Author Billion
 * @Time 2022/7/2 21:44
 */
class RpcResponse {
    use Success;

    public $status;
    public $message;
    public $data;

    // 解析RpcResponse
    public static function parseRpcResponse(string $content) : RpcResponse{
        $rpcData = json_decode($content,true);
        $rpcResponse = new static();
        // 解析参数
        $status = $rpcData['s'] ?? false;
        $message = $rpcData['m'] ?? false;
        $data = $rpcData['d'] ?? false;
        // 判断是否解析成功
        if ($status !== false && $message !== false && $data !== false){
            $rpcResponse->status = $status;
            $rpcResponse->message = $message;
            $rpcResponse->data = $data;
            return $rpcResponse->setSuccess();
        }
        return $rpcResponse->setFail('参数解析失败! content -> '.$content);

    }


}