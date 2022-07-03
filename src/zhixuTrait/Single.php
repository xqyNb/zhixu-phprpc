<?php

namespace Zhixu\Phprpc\zhixuTrait;

/**
 * 单例
 */
trait Single {
    private static $instance = NULL;
    // 禁止实例化
    private function __construct() {}
    // 获取实例
    public static function getInstance() : self{
        if (self::$instance === NULL){
            self::$instance = new static();
        }
        return self::$instance;
    }
}