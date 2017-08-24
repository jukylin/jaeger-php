<?php

namespace JaegerPhp;

use OpenTracing\SpanReference;
use OpenTracing\SpanContext;
use OpenTracing\Propagators\Writer;
use OpenTracing\Propagators\Reader;
use OpenTracing\Propagator;
use OpenTracing\Tracer;
use JaegerPhp\Reporter\Reporter;

class Jaeger implements Tracer{

    private $reporter = null;

    public static $handleProto = null;

    public $spans = [];

    public $tags = [];

    public $process = null;

    public $procesSize = 0;

    public $serviceName = '';

    public $bufferSize = '';

    public $processThrift = '';

    public $spanThrifts = [];

    public function __construct($serviceName = '', Reporter $reporter){

        $this->reporter = $reporter;

        if($serviceName == '') {
            $this->serviceName = $_SERVER['SERVER_NAME'];
        }else{
            $this->serviceName = $serviceName;
        }

        $this->tags = [
            [
                'key' => 'ip',
                'vType' => 'STRING',
                'vStr' => $_SERVER['SERVER_ADDR'],
            ],
            [
                'key' => 'port',
                'vType' => 'STRING',
                'vStr' => $_SERVER['SERVER_PORT'],
            ],
        ];
    }


    /**
     * init span info
     * @param string $operationName
     * @param SpanReference|null $parentReference
     * @param null $startTimestamp
     * @param array $tags
     * @return JSpan
     */
    public function startSpan($operationName, SpanReference $parentReference = null
        , $startTimestamp = null, array $tags = []
    ){
        $parentSpan = null;
        $spanContext = $parentReference->getContext();
        if($spanContext->traceId){
            $parentSpan = $spanContext;
        }

        if(!$parentSpan){
            $traceId = Helper::toHex(Helper::identifier());
            $spanId = Helper::toHex(Helper::identifier());
            $newSpan = new JSpanContext($traceId, $spanId, 0, 1, null, 0);
        }else{
            $newSpan = new JSpanContext($parentSpan->traceId, Helper::toHex(Helper::identifier())
                , $parentSpan->spanId, $parentSpan->flags, null, 0);
        }

        $span = new JSpan($operationName, $newSpan);
        if($newSpan->isSampled() == 1) {
            $this->spans[] = $span;
        }

        return $span;
    }


    public function startSpanWithOptions($operationName, $options){

    }


    /**
     * 注入
     * @param SpanContext $spanContext
     * @param int|string $format
     * @param Writer $carrier
     */
    public function inject(SpanContext $spanContext, $format, Writer $carrier){
        if($format == Propagator::TEXT_MAP){
            $carrier->set(Helper::TracerStateHeaderName, $spanContext->buildString());
        }else{
            throw new Exception("not support format");
        }
    }


    /**
     * 提取
     * @param int|string $format
     * @param Reader $carrier
     */
    public function extract($format, Reader $carrier){
        if($format == Propagator::TEXT_MAP){
            $carrierInfo = $carrier->getIterator();
            if(isset($carrierInfo[Helper::TracerStateHeaderName]) && $carrierInfo[Helper::TracerStateHeaderName]){
                list($traceId, $spanId, $parentId,$flags) = explode(':', $carrierInfo[Helper::TracerStateHeaderName]);
                return new JSpanContext($traceId, $spanId, $parentId, $flags, null, 0);
            }

            return new JSpanContext(0, 0, 0, 0, null, 0);
        }else{
            throw new Exception("not support format");
        }
    }


    public function getSpans(){
        return $this->spans;
    }


    public function reportSpan(){

        $this->reporter->report($this);
    }


    /**
     * 结束,发送信息到jaeger
     */
    public function flush(){
        $this->reportSpan();
        $this->reporter->close();
        Config::getInstance()->destroyTrace($this->serviceName);
    }

}


?>