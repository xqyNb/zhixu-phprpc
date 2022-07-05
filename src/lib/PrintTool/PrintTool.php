<?php

namespace Zhixu\Phprpc\lib\PrintTool;

/**
 * Class 打印
 * @Author Billion
 * @Time 2022/6/30 22:09
 */
class PrintTool {

    private static $debug = true;

    // 设置是否输出debug
    public static function setDebug(bool $debug){
        self::$debug = $debug;
    }


    // 输出信息
    public static function print(string $content){
        if (self::$debug){
            echo $content.PHP_EOL;
        }
    }

}