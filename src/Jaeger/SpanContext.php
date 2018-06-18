<?php

namespace Jaeger;


class SpanContext implements \OpenTracing\SpanContext{
    // traceID represents globally unique ID of the trace.
    // Usually generated as a random number.
    public $traceIdLow;

    public $traceIdHigh;


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


    public function __construct($spanId, $parentId, $flags, $baggage = null, $debugId = 0){
        $this->spanId = $spanId;
        $this->parentId = $parentId;
        $this->flags = $flags;
        $this->baggage = $baggage;
        $this->debugId = $debugId;
    }


    public function getBaggageItem($key){
        return isset($this->baggage[$key]) ? $this->baggage[$key] : false;
    }


    public function withBaggageItem($key, $value){
        $this->baggage[$key] = $value;
        return true;
    }

    public function getIterator()
    {
        // TODO: Implement getIterator() method.
    }


    public function buildString(){
        if($this->traceIdHigh){
            return sprintf("%x%016x:%x:%x:%x", $this->traceIdHigh, $this->traceIdLow,
                $this->spanId, $this->parentId, $this->flags);
        }

        return sprintf("%x:%x:%x:%x", $this->traceIdLow, $this->spanId, $this->parentId, $this->flags);
    }


    /**
     * 是否取样
     * @return mixed
     */
    public function isSampled(){
        return $this->flags;
    }


    public function traceIdToString($traceId){
        $this->traceIdLow = hexdec($traceId);
    }


}
