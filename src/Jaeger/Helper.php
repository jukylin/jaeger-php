<?php

namespace Jaeger;

class Helper{

    const TracerStateHeaderName = 'Uber-Trace-Id';

    const SAMPLER_TYPE_TAG_KEY = 'sampler.type';

    const SAMPLER_PARAM_TAG_KEY = 'sampler.param';


    /**
     * 转为16进制
     * @param $string
     * @return string
     */
    public static function toHex($string1, $string2 = ""){
        if($string2 == "") {
            return sprintf("%x", $string1);
        }else{
            return sprintf("%x%016x", $string1, $string2);
        }
    }
}