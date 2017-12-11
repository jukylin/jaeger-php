<?php

namespace Jaeger;


class SpanContext implements \OpenTracing\SpanContext{
    // traceID represents globally unique ID of the trace.
    // Usually generated as a random number.
    public $traceId;

    // spanID represents span ID that must be unique within its trace,
    // but does not have to be globally unique.
    public $spanId;

    // parentID refers to the ID of the parent span.
    // Should be 0 if the current span is a root span.
    public $parentId;

    // flags is a bitmap containing such bits as 'sampled' and 'debug'.
    public $flags;

    // Distributed Context baggage. The is a snapshot in time.
    public $baggage;

    // debugID can be set to some correlation ID when the context is being
    // extracted from a TextMap carrier.
    public $debugId;


    public function __construct($traceId, $spanId, $parentId, $flags, $baggage = null, $debugId = 0, $obj = null){
        $this->traceId = $traceId;
        $this->spanId = $spanId;
        $this->parentId = $parentId;
        $this->flags = $flags;
        $this->baggage = $baggage;
        $this->debugId = $debugId;
    }


//    public static function getInstance($traceId, $spanId, $parentId, $flags, $baggage = null, $debugId = 0){
//        return new self($traceId, $spanId, $parentId, $flags, $baggage, $debugId);
//    }

    public function getBaggageItem($key){

    }


    public function withBaggageItem($key, $value){

    }

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }


    public function buildString(){
        return $this->traceId.':'.$this->spanId.':'.$this->parentId.':'.$this->flags;
    }


    /**
     * 是否取样
     * @return mixed
     */
    public function isSampled(){
        return $this->flags;
    }
}