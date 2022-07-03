<?php

namespace Zhixu\Phprpc\frame;

use Swoole\Coroutine\Server;
use Swoole\Coroutine\Server\Connection;
use Swoole\Exception;
use Zhixu\Phprpc\lib\PrintTool\PrintTool;
use Zhixu\Phprpc\zhixuInterface\RpcControllerInterface;
use Zhixu\Phprpc\zhixuTrait\Single;

/**
 * Class rpc服务端
 * @Author Billion
 * @Time 2022/7/3 20:08
 */
class RpcServer {

    use Single;

    const STATUS_SUCCESS = 200;
    const STATUS_DATA_ERROR = -1;
    const STATUS_SERVER_NOT_EXIST = -2;
    const STATUS_SERVER_ACTION_NOT_EXIST = -3;

    private static $server;
    private static $serverIp;
    private static $serverPort;

    // 服务信息
    private static $rpcServers;

    // 连接管理
    private static $connections;

    // 注册服务
    public static function registerServer(string $serverName, RpcControllerInterface $serverClass) {
        self::$rpcServers[$serverName] = $serverClass;
    }

    // 处理客户端发来的数据
    private static function processClientData(string $clientAddr, string $data) {
        $rpcResponse = new RpcResponse();
        // 解析Message
        $msg = Message::parseMessage($data);
        // 判断是否成功
        if ($msg->getSuccess()) {
            // 解析服务
            $serverName = $msg->controller;
            // 判断服务是否存在
            if (isset(self::$rpcServers[$serverName])) { // 服务存在 - 回调服务
                /** @var RpcControllerInterface $rpcController */
                $rpcController = self::$rpcServers[$serverName];
                // 响应rpc
                $rpcResponse = $rpcController->callRpcAction($msg->action, $msg->data);
            } else { // 服务不存在
                $rpcResponse->status = self::STATUS_SERVER_NOT_EXIST;
                $rpcResponse->message = "服务不存在!";
                $rpcResponse->data = $serverName;
            }
        } else { // 发送响应给客户端
            $rpcResponse->status = self::STATUS_DATA_ERROR;
            $rpcResponse->message = "数据类型错误!";
        }

        // 返回响应
        self::sendResponseToClient($clientAddr, $rpcResponse);

    }

    // 发送响应给客户端
    public static function sendResponseToClient(string $clientAddr, RpcResponse $rpcResponse) {
        // 获取连接
        if (isset(self::$connections[$clientAddr])) {
            /** @var Connection $conn */
            $conn = self::$connections[$clientAddr];
            // 发送响应给客户端
            $rpcData = [
                's' => $rpcResponse->status,
                'm' => $rpcResponse->message,
                'd' => $rpcResponse->data,
            ];
            $data = json_encode($rpcData);
            $conn->send($data);
        }
    }

    /**
     * 启动
     * @throws Exception
     */
    public static function run(string $serverIp, string $serverPort) {
        self::$serverIp = $serverIp;
        self::$serverPort = $serverPort;

        // 启动服务
        $server = new Server($serverIp, $serverPort, false, true);
        // 监听连接
        $server->handle(function (Connection $conn) {
            // 获取客户端ip信息
            $socket = $conn->exportSocket();
            $peerName = $socket->getpeername();
            // 判断是否获取成功
            if ($peerName) {
                [
                    'address' => $address,
                    'port' => $port,
                ] = $peerName;
                $clientAddr = "$address:$port";
                // 将连接和客户端绑定起来
                self::$connections[$clientAddr] = $conn;
            } else {
                $clientAddr = '';
            }

            // 输出连接
            PrintTool::print("客户端连接 : ".$clientAddr);

            // 循环读取客户端的信息
            while (true) {
                // 接受客户端发来的数据 - 超时1秒
                $data = $conn->recv();
                // 判断是否连接出错
                if ($data === '' || $data === false) {
                    $errCode = swoole_last_error();
                    $errMsg = socket_strerror($errCode);
                    $msg = "客户端断开连接 : errCode: {$errCode}, errMsg: {$errMsg}\n";
                    PrintTool::print($msg);
                    $conn->close();
                    // 删除连接
                    unset(self::$connections[$clientAddr]);
                    break;
                }

                // 读取到服务端的数据
                PrintTool::print("读取到客户端发来的数据! : " . $data);

                // 开协程处理
                go(function () use ($clientAddr, $data) {
                    self::processClientData($clientAddr, $data);
                });

            }
        });

        // 输出启动信息!
        PrintTool::print("PHP [ RPC ] 服务器启动！监听 =>  $serverIp:$serverPort");
        $author = new Author();
        PrintTool::print("Author : $author->name,License : $author->license, Email : $author->email, I hope you enjoy it! 😄");

        // 启动服务器
        $server->start();

    }


}