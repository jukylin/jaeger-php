<?php

namespace JaegerPhp;

use JaegerPhp\ThriftGen\Agent\JaegerThriftSpan;
use OpenTracing\Span;
use JaegerPhp\Jaeger;

class JSpan implements Span{

    private $operationName = '';

    public $startTime = '';

    public $finishTime = '';

    public $spanKind = '';

    public $spanContext = null;

    public $duration = 0;

    public $logs = [];

    public $tags = [];


    public function __construct($operationName, JSpanContext $spanContext){
        $this->setIsClient();
        $this->operationName = $operationName;
        $this->startTime = Helper::microtimeToInt();
        $this->spanContext = $spanContext;
    }

    /**
     * @return string
     */
    public function getOperationName(){
        return $this->operationName;
    }

    /**
     * @return SpanContext
     */
    public function getContext(){
        return $this->spanContext;
    }

    /**
     * @param float|int|\DateTimeInterface|null $finishTime if passing float or int
     * it should represent the timestamp (including as many decimal places as you need)
     * @param array $logRecords
     * @return mixed
     */
    public function finish($finishTime = null, array $logRecords = []){
        $this->finishTime = $finishTime == null ? Helper::microtimeToInt() : $finishTime;
        $this->duration = $this->finishTime - $this->startTime;

        $thriftSpan = (new JaegerThriftSpan)->buildJaegerSpanThrift($this);
        if($this->spanContext->isSampled()) {
            Jaeger::getInstance()->reportSpan($thriftSpan);
        }
    }

    /**
     * @param string $newOperationName
     */
    public function overwriteOperationName($newOperationName){
        $this->operationName = $newOperationName;
    }

    /**
     * Adds tags to the Span in key:value format, key must be a string and tag must be either
     * a string, a boolean value, or a numeric type.
     *
     * As an implementor, consider using "standard tags" listed in {@see \OpenTracing\Ext\Tags}
     *
     * @param array $tags
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function addTags(array $tags){
        $this->tags = array_merge($this->tags, $tags);
    }

    /**
     * Adds a log record to the span
     *
     * @param array $fields
     * @param int|float|\DateTimeInterface $timestamp
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function log(array $fields = [], $timestamp = null){
        $log['timestamp'] = $timestamp ?? Helper::microtimeToInt();
        foreach($fields as $key => $field){
            $field = [
                "value" => $field,
                "key" => $key,
            ];
            $log['fields'][] = $field;
        }
        $this->logs[] = $log;
    }

    /**
     * Adds a baggage item to the SpanContext which is immutable so it is required to use SpanContext::withBaggageItem
     * to get a new one.
     *
     * @param string $key
     * @param string $value
     * @throws SpanAlreadyFinished if the span is already finished
     */
    public function addBaggageItem($key, $value){

    }

    /**
     * @param string $key
     * @return string
     */
    public function getBaggageItem($key){

    }


    public function setIsServer(){
        $this->spanKind = 'server';
        $this->addTags(['span.kind' => 'server']);
    }


    public function setIsClient(){
        $this->spanKind = 'client';
        $this->addTags(['span.kind' => 'client']);
    }


    public function isRPC(){
        if($this->spanKind == 'server'
            || $this->spanKind == 'client'){
            return true;
        }
        return false;
    }


    public function isRPClient(){
        if($this->spanKind == 'client'){
            return true;
        }
        return false;
    }
}