<?php

namespace Zhixu\Phprpc\frame;

use Swoole\Coroutine;
use Zhixu\Phprpc\lib\PrintTool\PrintTool;
use Zhixu\Phprpc\lib\String\StringMethod;

/**
 * Class Tcp客户端
 * @Author Billion
 * @Time 2022/7/3 01:42
 */
class TcpClient {

    const DEFAULT_TIMEOUT = 30; // 默认超时时间 : 30秒
    const CONNECT_ERROR = -1; // 无法连接到服务器

    /** @var Coroutine\Socket  */
    private $socket;

    private $serverIp;
    private $serverPort;
    private $timeout;

    // 构造函数
    public function __construct(string $serverIp, string $serverPort, int $timeout = self::DEFAULT_TIMEOUT) {
        $this->serverIp = $serverIp;
        $this->serverPort = $serverPort;
        $this->timeout = $timeout;
        // 连接服务器
        $this->connectServer();
    }

    // 断开服务器
    public function close(){
        if ($this->socket !== NULL && !$this->socket->isClosed()){
            $this->socket->close();
        }
    }

    // 连接服务器
    private function connectServer() : bool{
        // socket连接上
        $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
        // 设置永不超时
        $timeout = array('sec' => -1, 'usec' => 500000);
        $socket->setOption(SOL_SOCKET, SO_RCVTIMEO, $timeout);
        $socket->setOption(SOL_SOCKET, SO_SNDTIMEO, $timeout);
        // 连接服务端
        $success = $socket->connect($this->serverIp, $this->serverPort);
        if ($success){
            $this->socket = $socket;
            return true;
        }else{
            // 输出连接失败
            var_dump($success, $this->socket->errCode, $this->socket->errMsg);
            $this->socket = NULL;
        }
        return false;
    }


    // 同步发送消息给服务器
    public function syncSendMessageToServer(Message $msg): RpcResponse {
        $rpcResponse = new RpcResponse();
        // 发送给服务端
        $length = $this->sendMessageToServer($msg);
        // 判断是否连接不上服务器
        if ($length == -1){
            return $rpcResponse->setFail("无法连接服务器!");
        }
//        PrintTool::print("数据发送完毕 length : $length");
        // 判断是否发送成功
        if ($length){ // 直接等待服务端发过来即可
//            PrintTool::print("开始接受数据!");
            // 判断客户端连接是否已断开
            if ($this->socket->isClosed()){
                PrintTool::print("[RPC Client] 断线重连... 接收数据!");
                $result = $this->connectServer();
                if (!$result){
                    return $rpcResponse->setFail("重连失败!");
                }
            }

            // 接受服务器的响应消息
            $content = $this->socket->recv(4096,$this->timeout);
//            PrintTool::print("接收到 content : $content");
            // 判断是否接收成功
            if ($content){
                return RpcResponse::parseRpcResponse($content);
            }
            return $rpcResponse->setFail("接收失败! content : $content");
        }
        return $rpcResponse->setFail("发送失败!  length : $length");
    }

    // 发送消息给服务器
    public function sendMessageToServer(Message $msg): int {
        // 判断是否有连接或重连
        if ($this->socket === NULL || $this->socket->isClosed()){ // 重连服务器
            PrintTool::print("[RPC Client] 断线重连... sendMessageToServer");
            $result = $this->connectServer();
            if (!$result){
                return self::CONNECT_ERROR;
            }
        }

        // 连接上了，发送信息
        $data = $msg->buildMessage();
        PrintTool::print("[RPC Client] 发送数据 : ".$data);
        return $this->socket->send($data);
    }


}