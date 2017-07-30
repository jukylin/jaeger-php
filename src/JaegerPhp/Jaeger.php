<?php

namespace JaegerPhp;

use OpenTracing\SpanReference;
use OpenTracing\SpanContext;
use OpenTracing\Propagators\Writer;
use OpenTracing\Propagators\Reader;
use OpenTracing\Propagator;
use OpenTracing\Tracer;
use JaegerPhp\ThriftGen\Agent\AgentClient;

class Jaeger implements Tracer{

    public static $spans = [];

    public static $serviceName = '';

    public static $handleProto = null;

    public function __construct($serviceName = ''){
        if($serviceName == '') {
            self::$serviceName = $_SERVER['SERVER_NAME'];
        }else{
            self::$serviceName = $serviceName;
        }
    }

    public function startSpan($operationName, SpanReference $parentReference = null
        , $startTimestamp = null, array $tags = []
    ){
        $parentSpan = null;
        $spanContext = $parentReference->referencedContext();
        if($spanContext->traceId){
            $parentSpan = $spanContext;
        }

        if(!$parentSpan){
            $traceId = Helper::identifier();
            $spanId = Helper::identifier();
            $newSpan = new JSpanContext($traceId, $spanId, 0, 1, null, 0);
        }else{
            $newSpan = new JSpanContext($parentSpan->traceId, Helper::identifier(), $parentSpan->spanId, 1, null, 0, $this);
        }

        $span = new JSpan($operationName, $newSpan);
        self::$spans[] = $span;

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
            throw new Exception("不支持format");
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
            throw new Exception("不支持format");
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

            return new JSpanContext(0, 0, 0, 0, null, 0);;
        }else{
            throw new Exception("不支持format");
        }
    }


    /**
     * 结束,发送信息到jaeger
     */
    public function flush(){
        $buildSpan = [];

        $buildSpan['process'] = [
            'serviceName' => self::$serviceName,
            'tags' => [
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
            ],
        ];

        foreach(self::$spans as $span){
            $spContext = $span->spanContext;
            $span = [
                'traceIdLow' => intval($spContext->traceId),
                'traceIdHigh' => 0,
                'spanId' => intval($spContext->spanId),
                'parentSpanId' => intval($spContext->parentId),
                'operationName' => $span->getOperationName(),
                'flags' => intval($spContext->flags),
                'startTime' => $span->startTime,
                'duration' => $span->duration,
                'tags' => $this->buildTags($span->tags),
                'logs' => $this->buildLogs($span->logs),
            ];
            if($spContext->parentId != 0){
                $span['references'] = [
                    [
                        'refType' =>  1,
                        'traceIdLow' => intval($spContext->traceId),
                        'traceIdHigh' => 0,
                        'spanId' => intval($spContext->parentId),
                    ],
                ];
            }
            $spans[] = $span;
        }

        $buildSpan['spans'] = $spans;

        (new AgentClient())->buildThrift($buildSpan);
    }


    private function buildTags($tags){
        $resultTags = [];
        if($tags){
            foreach ($tags as $key => $tag){
                $resultTags[] = [
                    'key' => $key,
                    'vStr' => $tag,
                    'vType' => "STRING"
                ];
            }
        }


        return $resultTags;
    }


    private function buildLogs($logs){
        $resultLogs = [];
        if($logs){
            foreach($logs as $log){
                $resultLogs[] = [
                    "timestamp" => $log['timestamp'],
                    "fields" => [
                        [
                            'key' => $log['key'],
                            'vType' => 'STRING',
                            'vStr' => $log['value'],
                        ]
                    ],
                ];
            }
        }


        return $resultLogs;
    }


}


?>