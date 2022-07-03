<?php

namespace Zhixu\Phprpc\frame;

/**
 * Class rpc客户端配置
 * @Author Billion
 * @Time 2022/7/3 16:38
 */
class RpcClientConfig {
    public $serverIp;
    public $serverPort;
    public $poolMin;
    public $poolMax;

    // 构造函数
    public function __construct(string $serverIp, int $serverPort, int $poolMin, int $poolMax) {
        $this->serverIp = $serverIp;
        $this->serverPort = $serverPort;
        $this->poolMin = $poolMin;
        $this->poolMax = $poolMax;
    }
}