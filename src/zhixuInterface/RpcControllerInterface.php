<?php

namespace Zhixu\Phprpc\zhixuInterface;

use Zhixu\Phprpc\frame\RpcResponse;

/**
 * rpc 服务控制接口
 */
interface RpcControllerInterface {

    // 响应回调
    public function callRpcAction(string $action, string $data): RpcResponse;

}