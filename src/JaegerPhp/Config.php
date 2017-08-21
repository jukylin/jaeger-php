<?php

namespace JaegerPhp;

use JaegerPhp\Reporter\RemoteReporter;
use JaegerPhp\Transport\TransportUdp;
use OpenTracing\GlobalTracer;
use OpenTracing\Carriers\TextMap;
use OpenTracing\Propagator;
use OpenTracing\SpanContext;
use OpenTracing\SpanReference;

class Config {

    private $transport = null;

    private $reporter = null;

    private $tags = [];

    public static $trace = null;

    public static $span = null;

    public static $instance = null;

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
        if($serviceName == ''){
            throw new Exception("serviceName require");
        }

        if($this->transport == null){
            $this->transport = new TransportUdp($agentHostPort);
        }

        if($this->reporter == null) {
            $this->reporter = new RemoteReporter($this->transport);
        }

        self::$trace = Jaeger::getInstance($serviceName, $this->reporter);
        GlobalTracer::set(self::$trace);
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


    /**
     * 从超全局变量提取TracerStateHeaderName和创建span
     * @param $operationName
     * @return null
     */
    public function startSpan($operationName){
        $mapText = array_merge($_REQUEST, $_SERVER);
        $spanContext = self::$trace->extract(Propagator::TEXT_MAP, TextMap::create($mapText));

        return self::$trace->startSpan($operationName
            , SpanReference::createAsChildOf($spanContext));
    }


    /**
     * 把TracerStateHeaderName 注入到超全局变量 或者 $injectTarget
     */
    public function inject(SpanContext $spanContext, &$injectTarget = []){

        if(empty($injectTarget)){
            $injectTarget = &$_SERVER;
        }

        self::$trace->injectJaeger($spanContext, Propagator::TEXT_MAP
            , $injectTarget);

        return true;
    }


    public function flushTrace(){
        if(self::$trace != null) {
            self::$trace->flush();
        }
        return 0;
    }
}
