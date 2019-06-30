<?php

namespace Jaeger;

use Jaeger\Reporter\RemoteReporter;
use Jaeger\Reporter\Reporter;
use Jaeger\Transport\TransportUdp;
use OpenTracing\NoopTracer;
use Jaeger\Sampler\Sampler;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Propagator\JaegerPropagator;
use Jaeger\Propagator\ZipkinPropagator;

class Config {

    private $transport = null;

    private $reporter = null;

    private $sampler = null;

    private $scopeManager = null;

    private $tags = [];

    private $gen128bit = false;

    public static $tracer = null;

    public static $span = null;

    public static $instance = null;

    public static $disabled = false;

    public static $propagator = \Jaeger\Constants\PROPAGATOR_JAEGER;


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
            throw new \Exception("serverName require");
        }

        if(isset(self::$tracer[$serverName]) && !empty(self::$tracer[$serverName])){
            return self::$tracer[$serverName];
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

        if($this->scopeManager == null){
            $this->scopeManager = new ScopeManager();
        }

        $tracer = new Jaeger($serverName, $this->reporter, $this->sampler, $this->scopeManager);

        if($this->gen128bit == true){
            $tracer->gen128bit();
        }

        if(self::$propagator == \Jaeger\Constants\PROPAGATOR_ZIPKIN){
            $tracer->setPropagator(new ZipkinPropagator());
        }else{
            $tracer->setPropagator(new JaegerPropagator());
        }


        self::$tracer[$serverName] = $tracer;


        return $tracer;
    }


    /**
     * 是否开启
     * @param $disabled
     */
    public function setDisabled($disabled){
        self::$disabled = $disabled;

        return $this;
    }


    public function setTransport(Transport\Transport $transport){
        $this->transport = $transport;

        return $this;
    }


    public function setReporter(Reporter $reporter){
        $this->reporter = $reporter;

        return $this;
    }


    public function setSampler(Sampler $sampler){
        $this->sampler = $sampler;

        return $this;
    }


    public function gen128bit(){
        $this->gen128bit = true;

        return $this;
    }


    public function flush(){
        if(count(self::$tracer) > 0) {
            foreach(self::$tracer as $tracer){
                $tracer->reportSpan();
            }
            $this->reporter->close();
        }

        return true;
    }
}
