<?php

namespace JaegerPhp;

class Helper{

    const TracerStateHeaderName = 'Uber-Trace-Id';

    const UDP_PACKET_MAX_LENGTH = 65000;

    public static function microtimeToInt(){
        return intval(microtime(true) * 1000000);
    }


    public static function identifier(){
        return strrev(microtime(true) * 10000 . rand(1000, 9999));
    }


    /**
     * 转为16进制
     * @param $string
     * @return string
     */
    public static function toHex($string){
        return sprintf("%x", $string);
    }


    /**
     * 用于计算性能
     * @return float
     */
    public static function getmicrotime()
    {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
}