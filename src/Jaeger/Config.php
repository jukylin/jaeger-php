<?php

namespace Jaeger;

use Jaeger\Reporter\RemoteReporter;
use Jaeger\Reporter\Reporter;
use Jaeger\Transport\TransportUdp;
use OpenTracing\NoopTracer;
use Jaeger\Sampler\Sampler;
use Jaeger\Sampler\ConstSampler;

class Config {

    private $transport = null;

    private $reporter = null;

    private $sampler = null;

    private $tags = [];

    private $gen128bit = false;

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

        if(isset(self::$trace[$serverName]) && !empty(self::$trace[$serverName])){
            return self::$trace[$serverName];
        }


        if($this->transport == null){
            $this->transport = new TransportUdp($agentHostPort);
        }

        if($this->reporter == null) {
            $this->reporter = new RemoteReporter($this->transport);
        }

        if($this->sampler == null){
            $this->sampler = new ConstSampler(true);
        }

        $trace = new Jaeger($serverName, $this->reporter, $this->sampler);

        if($this->gen128bit == true){
            $trace->gen128bit();
        }

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


    public function setSampler(Sampler $sampler){
        $this->sampler = $sampler;
    }


    public function gen128bit(){
        $this->gen128bit = true;
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
            foreach(self::$trace as $key => $trace){
                $trace->reportSpan();
                unset(self::$trace[$key]);
            }
            $this->reporter->close();
        }

        return true;
    }
}
