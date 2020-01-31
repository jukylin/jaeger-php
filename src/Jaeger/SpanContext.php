<?php
/*
 * Copyright (c) 2019, The Jaeger Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

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
    // key => val
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
        return isset($this->baggage[$key]) ? $this->baggage[$key] : null;
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


    public function spanIdToString(){
        return sprintf("%x", $this->spanId);
    }


    public function parentIdToString(){
        return sprintf("%x", $this->parentId);
    }


    public function traceIdLowToString(){
        if ($this->traceIdHigh) {
            return sprintf("%x%016x", $this->traceIdHigh, $this->traceIdLow);
        }

        return sprintf("%x", $this->traceIdLow);
    }


    public function flagsToString(){
        return sprintf("%x", $this->flags);
    }


    /**
     * 是否取样
     * @return mixed
     */
    public function isSampled(){
        return $this->flags;
    }


    public function hexToSignedInt($hex)
    {
        //Avoid pure Arabic numerals eg:1
        if (gettype($hex) != "string") {
            $hex .= '';
        }

        $hexStrLen = strlen($hex);
        $dec = 0;
        for ($i = 0; $i < $hexStrLen; $i++) {
            $hexByteStr = $hex[$i];
            if (ctype_xdigit($hexByteStr)) {
                $decByte = hexdec($hex[$i]);
                $dec = ($dec << 4) | $decByte;
            }
        }

        return $dec;
    }


    public function traceIdToString($traceId)
    {
        $len = strlen($traceId);
        if ($len > 16) {
            $this->traceIdHigh = $this->hexToSignedInt(substr($traceId, 0, 16));
            $this->traceIdLow = $this->hexToSignedInt(substr($traceId, 16));
        } else {
            $this->traceIdLow = $this->hexToSignedInt($traceId);
        }
    }


    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isTraceIdValid() && $this->spanId;
    }


    /**
     * @return bool
     */
    public function isTraceIdValid()
    {
        return $this->traceIdLow || $this->traceIdHigh;
    }
}
