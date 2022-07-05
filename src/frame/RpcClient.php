<?php

namespace Zhixu\Phprpc\frame;

use Exception;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Zhixu\Phprpc\lib\PrintTool\PrintTool;
use Zhixu\Phprpc\zhixuTrait\Single;

/**
 * Class RPC客户端 - 自带连接池
 * @Author Billion
 * @Time 2022/7/3 16:37
 */
class RpcClient {

    use Single;

    private static $config;

    // 连接池channel
    private static $poolTcpClientChannel;
    private static $poolCount;

    /**
     * 设置配置
     * @throws Exception
     */
    public static function setConfig(RpcClientConfig $config) {
        self::$config = $config;
        // 初始化连接池子
        self::iniPool();
        // 维持心跳
        go(function (){
            self::heartBeat();
        });
    }

    // 维持心跳
    private static function heartBeat(){
        $poolChannel = self::getPoolTcpClientChannel();
        while (true){
//            PrintTool::print("维持心跳...");
            /** @var TcpClient */
            $tcpClient = $poolChannel->pop();
            $tcpClient->autoReConnect();
            $length = $tcpClient->sendHeartBeat();
            if (!$length){ // 没有连接 - 重新连一个
                $tcpClient->close();
                $tcpClient = new TcpClient(self::$config->serverIp, self::$config->serverPort);
            }
            // 连接放回去
            $poolChannel->push($tcpClient);
            // 根据算法来心跳
            $channelLength = $poolChannel->length();
            $sleepTime = 28 / $channelLength;

            // 协程sleep
            Coroutine::sleep($sleepTime);
        }
    }

    // 自动defer
    public static function defer(): TcpClient {
        $poolChannel = self::getPoolTcpClientChannel();
        // 获取客户端
        // 判断连接池中的连接够不够用
        $length = $poolChannel->length();
//        PrintTool::print("defer length => $length");
        if ($length == 0 && self::$poolCount < self::$config->poolMax) {
            ++self::$poolCount;
            // 放到连接池中去
            $client = new TcpClient(self::$config->serverIp, self::$config->serverPort);
            $poolChannel->push($client);
//            PrintTool::print("defer 自动增加连接 tcpClient!");
        }
        // 从连接至中获取链接
        /** @var TcpClient */
        $tcpClient = $poolChannel->pop();
        // 自动重连
        $tcpClient->autoReConnect();

        // 设置defer后置操作
        Coroutine::defer(function () use ($tcpClient, $poolChannel) {
            // 判断是否是够用了
            $length = $poolChannel->length();
            if ($length >= self::$config->poolMin) { // 说明有闲置的连接 - 释放掉闲置的连接
//                PrintTool::print("[ defer ] 释放闲置的 tcpClient!");
                $tcpClient->close();
                unset($tcpClient);
                --self::$poolCount;
                return;
            }

            // 执行回收
//            PrintTool::print("[ defer ] 回收tcpClient!");
            $poolChannel->push($tcpClient);
        });
        return $tcpClient;
    }

    // 获取 连接池channel
    public static function getPoolTcpClientChannel(): Channel {
        return self::$poolTcpClientChannel;
    }

    /**
     * 初始化连接池
     * @throws Exception
     */
    private static function iniPool() {
        // 判断连接池的数量
        if (self::$config->poolMin <= 0) {
            throw new Exception("RPC 连接池的最小连接数必须大于0！");
        }
        // 判断最大数和最小数
        if (self::$config->poolMax <= self::$config->poolMin) {
            throw new Exception("RPC 连接池的最大连接数必须大于最小连接数!");
        }
        // 初始化连接池
//        PrintTool::print("Channel max = ".self::$config->poolMax);
        self::$poolTcpClientChannel = new Channel(self::$config->poolMax);
        for ($i = 0; $i < self::$config->poolMin; $i++) {
            ++self::$poolCount;
            $tcpClient = new TcpClient(self::$config->serverIp, self::$config->serverPort);
            self::$poolTcpClientChannel->push($tcpClient);
        }

    }


}