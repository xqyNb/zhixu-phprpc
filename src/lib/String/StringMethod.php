<?php

namespace Zhixu\Phprpc\lib\String;

/**
 * Class
 * @Author Billion
 * @Time 2022/7/2 21:52
 */
class StringMethod {

    /**
     * 获取随机字符串
     * @param int $length
     * @return string
     */
    public static function randomString(int $length): string {
        $lower = 'abcdefjhijklmnopqrstuvwxyz';
        $str = $lower . strtoupper($lower);
        $arr = str_split($str);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $index = mt_rand(0, count($arr) - 1);
            $letter = $arr[$index];
            $result .= $letter;
        }
        return $result;
    }

}