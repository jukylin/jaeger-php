<?php

namespace JaegerPhp;

use OpenTracing\SpanReference;
use OpenTracing\SpanContext;
use OpenTracing\Propagators\Writer;
use OpenTracing\Propagators\Reader;
use OpenTracing\Propagator;
use OpenTracing\Tracer;
use JaegerPhp\UdpClient;
use JaegerPhp\JSpan;
use JaegerPhp\Reporter\Reporter;

class Jaeger implements Tracer{

    private $udpHost = '';

    private $udpPort = '';

    private $reporter = null;

    public static $spans = [];

    public static $serviceName = '';

    public static $handleProto = null;

    public static $tags = [];

    public function __construct($serviceName = '', $udpHost = '0.0.0.0', $udpPort = '5775',Reporter $reporter){

        $this->udpHost = $udpHost;
        $this->udpPort = $udpPort;
        $this->reporter = $reporter;

        if($serviceName == '') {
            self::$serviceName = $_SERVER['SERVER_NAME'];
        }else{
            self::$serviceName = $serviceName;
        }

        self::$tags = [
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
                , $parentSpan->spanId, $parentSpan->flags, null, 0, $this);
        }

        $span = new JSpan($operationName, $newSpan, $this);
        if($newSpan->flags == 1) {
            self::$spans[] = $span;
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
     * 注入
     * @param SpanContext $spanContext
     * @param int|string $format
     * @param array $injectObj
     */
    public function injectJaeger(SpanContext $spanContext, $format, &$injectObj = []){
        if($format == Propagator::TEXT_MAP){
            $injectObj[Helper::TracerStateHeaderName] = $spanContext->buildString();
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


    /**
     *
     * @param \JaegerPhp\JSpan $span
     */
    public function reportSpan(JSpan $span){
        if($span->spanContext->isSampled()){
            $this->reporter->report($span);
        }
    }


    /**
     * 结束,发送信息到jaeger
     */
    public function flush(){
        $this->reporter->close();
    }
}


?>