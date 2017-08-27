<?php

namespace JaegerPhp;

use JaegerPhp\Reporter\RemoteReporter;
use JaegerPhp\Reporter\Reporter;
use JaegerPhp\Transport\TransportUdp;
use OpenTracing\NoopTracer;
use GuzzleHttp\Promise;

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
    public function initTrace($serverName, $agentHostPort = ''){

        if(self::$disabled){
            return NoopTracer::create();
        }

        if($serverName == ''){
            throw new Exception("serverName require");
        }

        if(isset(self::$trace[$serverName]) && empty(self::$trace[$serverName])){
            return self::$trace[$serverName];
        }


        if($this->transport == null){
            $this->transport = new TransportUdp($agentHostPort);
        }

        if($this->reporter == null) {
            $this->reporter = new RemoteReporter($this->transport);
        }

        $trace = new Jaeger($serverName, $this->reporter);
        self::$trace[$serverName] = $trace;


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


    /**
     * 销毁对象
     * @param $serviceName
     */
    public function destroyTrace($serverName){
        if(isset(self::$trace[$serverName])){
            unset(self::$trace[$serverName]);
        }
    }


    public function flushTrace(){
        if(count(self::$trace) > 0) {
            $s = Helper::getmicrotime();
            foreach(self::$trace as $trace){
                $trace->reportSpan();
            }
            $this->reporter->close();
            $e = Helper::getmicrotime();
            echo "end1:".($e - $s);
        }

        return true;
    }
}
