<?php

namespace Zhixu\Phprpc\controller;

use Zhixu\Phprpc\frame\RpcResponse;
use Zhixu\Phprpc\frame\RpcServer;
use Zhixu\Phprpc\zhixuInterface\RpcControllerInterface;
use Zhixu\Phprpc\zhixuTrait\RpcController;

/**
 * Class 测试控制器
 * @Author Billion
 * @Time 2022/7/3 23:03
 */
class TestController implements RpcControllerInterface {

    use RpcController;

    // 测试1
    private function test1(string $data) : RpcResponse{
        return $this->success('我是PHP的 test1',[
            'yourData' => $data,
        ]);
    }

    // 实现rpc回调
    public function callRpcAction(string $action, string $data): RpcResponse {
        switch ($action){
            case 'test1':
                return $this->test1($data);
            default:
                $rpcResponse = new RpcResponse();
                $rpcResponse->status = RpcServer::STATUS_SERVER_ACTION_NOT_EXIST;
                $rpcResponse->message = "action 不存在!";
                $rpcResponse->data = $action;
                return $rpcResponse;
        }

    }
}