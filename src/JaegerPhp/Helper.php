<?php

namespace JaegerPhp;

class Helper{

    const TracerStateHeaderName = 'Uber-Trace-Id';

    const UDP_PACKET_MAX_LENGTH = 65000;

    public static function microtimeToInt(){
        return intval(microtime(true) * 1000000);
    }


    public static function identifier(){
        return strrev(microtime(true) * 10000 . rand(10, 99));
    }
}