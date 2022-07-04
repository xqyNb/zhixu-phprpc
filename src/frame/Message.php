<?php

namespace Zhixu\Phprpc\frame;

use Zhixu\Phprpc\zhixuTrait\Success;

/**
 * Class 通讯message
 * @Author Billion
 * @Time 2022/6/30 22:00
 */
class Message {

    use Success;

    public $controller;
    public $action;
    public $data;

    // 编译消息
    public function buildMessage(): string {
        $msgData = [
            'c' => $this->controller,
            'a' => $this->action,
            'd' => $this->data,
        ];
        return json_encode($msgData);
    }
    // 设置

    // 解析message
    public static function parseMessage(string $content): Message {
        $data = json_decode($content, true);
        // 判断是否解析成功
        if ($data) {
            $controller = $data['c'] ?? false;
            $action = $data['a'] ?? false;
            $data = $data['d'] ?? false;
            // 判断四个参数都存在且key、data不是false
            if ($controller && $action && $data !== false) {
                $message = new static();
                $message->controller = $controller;
                $message->action = $action;
                $message->data = $data;
                return $message->setSuccess();
            }
        }
        return (new Message())->setFail("解析失败! content : $content");
    }


}