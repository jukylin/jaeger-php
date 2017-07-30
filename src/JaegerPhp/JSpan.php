<?php

namespace JaegerPhp;

use OpenTracing\Span;

class JSpan implements Span{

    public $startTime = '';
    public $finishTime = '';
    private $operationName = '';
    public $spanKind = '';
    public $spanContext = null;
    public $duration = 0;
    public $logs = [];
    public $tags = [];
    private $specialTags = [
        'spanKind'
    ];

    public function __construct($operationName, JSpanContext $spanContext){
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
        foreach($fields as $key => $field){
            $log[] = [
                "value" => $field,
                "timestamp" => $timestamp ?? Helper::microtimeToInt(),
                "key" => $key,
            ];
        }

        $this->logs = array_merge($this->logs, $log);
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