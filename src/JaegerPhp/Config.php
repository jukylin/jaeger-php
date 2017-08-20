<?php

namespace JaegerPhp;

use JaegerPhp\Reporter\RemoteReporter;
use JaegerPhp\Transport\TransportUdp;
use OpenTracing\GlobalTracer;

class Config {

    private $transport = null;

    private $reporter = null;

    private $tags = [];

    /**
     * init jaeger and set GlobalTracer, return can use flush  buffers
     * @param $serviceName
     * @param string $agentHostPort
     * @return Jaeger|null
     * @throws \Exception
     */
    public function InitTrace($serviceName, $agentHostPort = ''){
        if($serviceName == ''){
            throw new \Exception("serviceName require");
        }

        if($this->transport == null){
            $this->transport = new TransportUdp($agentHostPort);
        }

        if($this->reporter == null) {
            $this->reporter = new RemoteReporter($this->transport);
        }

        $trace = Jaeger::getInstance($serviceName, $this->reporter);
        GlobalTracer::set($trace);


        return $trace;
    }


    public function setTransport(Transport $transport){
        $this->transport = $transport;
    }


    public function setReporter(Reporter $reporter){
        $this->reporter = $reporter;
    }


    public function setTags($tags){
        $this->tags = $tags;
    }


    public static function getTrace(){
        return GlobalTracer::get();
    }
}
