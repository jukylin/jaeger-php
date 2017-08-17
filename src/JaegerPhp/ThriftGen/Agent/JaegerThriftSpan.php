<?php

namespace JaegerPhp\ThriftGen\Agent;


use JaegerPhp\Jaeger;

class JaegerThriftSpan{


    public function buildJaegerProcessThrift(Jaeger $jaeger){
        return new Process($jaeger::$serviceName);
    }

}