<?php

namespace Zhixu\Phprpc\zhixuTrait;

use Zhixu\Phprpc\frame\RpcResponse;
use Zhixu\Phprpc\frame\RpcServer;

/**
 * 公用方法
 */
trait RpcController {

    // 成功响应
    public function success(string $msg = 'ok', array $data = []): RpcResponse {
        $rpcResponse = new RpcResponse();
        $rpcResponse->status = RpcServer::STATUS_SUCCESS;
        $rpcResponse->message = $msg;
        // 判断有没有数据
        $rpcResponse->data = $data ? json_encode($data) : "";
        return $rpcResponse;
    }

    // 失败响应
    public function fail(int $status, string $msg, array $data = []): RpcResponse {
        $rpcResponse = new RpcResponse();
        $rpcResponse->status = $status;
        $rpcResponse->message = $msg;
        // 判断有没有数据
        $rpcResponse->data = $data ? json_encode($data) : "";
        return $rpcResponse;
    }

}