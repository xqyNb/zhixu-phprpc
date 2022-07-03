<?php
require_once "../vendor/autoload.php";

use Swoole\Coroutine;
use Zhixu\Phprpc\frame\Message;
use Zhixu\Phprpc\lib\PrintTool\PrintTool;

// 服务端
go(function (){
    $rpcSever = \Zhixu\Phprpc\frame\RpcServer::getInstance();
    // 注册服务
    // 测试服务
    $rpcSever::registerServer('test',new \Zhixu\Phprpc\controller\TestController());
    // 启动RPC服务器
    $rpcSever::run("127.0.0.1",8866);

    PrintTool::print("PHP - 服务端启动结束!");
});

sleep(1);
PrintTool::print("--------------------");
PrintTool::print("PHP - 开始启动客户端!");

// 测试
function test(\Zhixu\Phprpc\frame\RpcClient $rpcClient, int $num) {
    $time = time();
    $msg = new Message();
    $msg->controller = "test";
    $msg->action = "test1";
    $msg->data = "[ $num ] 我是PHP,自带连接池!";
    // 同步发送给服务器
//    PrintTool::print("获取 defer");
    $client = $rpcClient::defer();
//    $client = $rpcClient->getPoolTcpClientChannel()->pop();
    $rpcResponse = $client->syncSendMessageToServer($msg);
    // 判断是否成功
    if ($rpcResponse->getSuccess()) {
        PrintTool::print("[ $num ] rpcResponse 成功 : status = $rpcResponse->status,message = $rpcResponse->message,data = $rpcResponse->data");
    } else {
        PrintTool::print("[ $num ] rpcResponse 失败！ : " . $rpcResponse->getFailMessage());
    }

//    Coroutine::sleep(2);

    $useTime = time() - $time;
    PrintTool::print("[ $num ] 我完成任务了! useTime = $useTime");
}

// 客户端
go(function () {
    $rpcClientConfig = new \Zhixu\Phprpc\frame\RpcClientConfig('127.0.0.1', 8866, 1, 2);
//    $rpcClientConfig = new \Zhixu\Phprpc\frame\RpcClientConfig('192.168.2.3', 8000, 1, 2);
    $rpcClient = \Zhixu\Phprpc\frame\RpcClient::getInstance();
    $rpcClient::setConfig($rpcClientConfig);

    // 获取到rpcClient了
    PrintTool::print("开始发送同步数据");

    // 协程1
    $barriers = Coroutine\Barrier::make();
    $nums = 1;

    for ($i=0;$i<$nums;$i++){
        go(function () use ($barriers,$rpcClient,$i) {
            test($rpcClient, $i);
        });
    }

//    PrintTool::print("发送同步数据结束！");
    Coroutine\Barrier::wait($barriers);
//

//    Coroutine::sleep(1);

    $length = $rpcClient::getPoolTcpClientChannel()->length();
    PrintTool::print("channel length = $length");

    PrintTool::print("PHP - 运行结束!");


});


