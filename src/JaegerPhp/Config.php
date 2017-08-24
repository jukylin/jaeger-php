<?php

namespace JaegerPhp;

use JaegerPhp\Reporter\RemoteReporter;
use JaegerPhp\Reporter\Reporter;
use JaegerPhp\Transport\TransportUdp;
use OpenTracing\NoopTracer;

class Config {

    private $transport = null;

    private $reporter = null;

    private $tags = [];

    public static $trace = null;

    public static $span = null;

    public static $instance = null;

    public static $disabled = false;

    private function __construct(){

    }


    private function __clone(){

    }


    public static function getInstance()
    {
        if(! (self::$instance instanceof self) )
        {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * init jaeger and set GlobalTracer, return can use flush  buffers
     * @param $serviceName
     * @param string $agentHostPort
     * @return Jaeger|null
     * @throws \Exception
     */
    public function initTrace($serviceName, $agentHostPort = ''){

        if(self::$disabled){
            return NoopTracer::create();
        }

        if($serviceName == ''){
            throw new Exception("serviceName require");
        }

        if(isset(self::$trace[$serviceName]) && empty(self::$trace[$serviceName])){
            return self::$trace[$serviceName];
        }


        if($this->transport == null){
            $this->transport = new TransportUdp($agentHostPort);
        }

        if($this->reporter == null) {
            $this->reporter = new RemoteReporter($this->transport);
        }

        $trace = new Jaeger($serviceName, $this->reporter);
        self::$trace[$serviceName] = $trace;


        return $trace;
    }


    /**
     * 是否开启
     * @param $disabled
     */
    public function setDisabled($disabled){
        self::$disabled = $disabled;
    }


    public function setTransport(Transport\Transport $transport){
        $this->transport = $transport;
    }


    public function setReporter(Reporter $reporter){
        $this->reporter = $reporter;
    }


    public function setTags($tags){
        $this->tags = $tags;
    }


    /**
     * 销毁对象
     * @param $serviceName
     */
    public function destroyTrace($serviceName){
        if(isset(self::$trace[$serviceName])){
            unset(self::$trace[$serviceName]);
        }
    }


    public function flushTrace(){
        if(count(self::$trace) > 0) {
            foreach(self::$trace as $trace){
                $trace->reportSpan();
            }
            $this->reporter->close();
        }

        return 0;
    }
}
