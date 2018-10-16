<?php

namespace Jaeger\Propagator;

use Jaeger\SpanContext;
use Jaeger\Constants;

class JaegerPropagator implements Propagator{

    public function inject(SpanContext $spanContext, $format, &$carrier){
        $carrier[strtoupper(Constants\Tracer_State_Header_Name)] = $spanContext->buildString();
        if($spanContext->baggage) {
            foreach ($spanContext->baggage as $k => $v) {
                $carrier[strtoupper(Constants\Trace_Baggage_Header_Prefix . $k)] = $v;
            }
        }
    }


    /**
     * 提取
     * @param string $format
     * @param $carrier
     */
    public function extract($format, $carrier){
        $spanContext = new SpanContext(0, 0, 0, null, 0);
        foreach ($carrier as $k => $v){
            $k = strtolower($k);
            $v = urldecode($v);
            if($k == Constants\Tracer_State_Header_Name){
                list($traceId, $spanId, $parentId,$flags) = explode(':', $carrier[strtoupper($k)]);

                $spanContext->spanId = $spanContext->hexToSignedInt($spanId);
                $spanContext->parentId = $spanContext->hexToSignedInt($parentId);
                $spanContext->flags = $flags;
                $spanContext->traceIdToString($traceId);

            }elseif(stripos($k, Constants\Trace_Baggage_Header_Prefix) !== false){
                $safeKey = str_replace(Constants\Trace_Baggage_Header_Prefix, "", $k);
                $spanContext->withBaggageItem($safeKey, $v);
            }elseif($k == Constants\Jaeger_Debug_Header){
                $spanContext->debugId = $v;
            }elseif($k == Constants\Jaeger_Baggage_Header){
                // Converts a comma separated key value pair list into a map
                // e.g. key1=value1, key2=value2, key3 = value3
                // is converted to array { "key1" : "value1",
                //                                     "key2" : "value2",
                //                                     "key3" : "value3" }
                $parseVal = explode(',', $v);
                foreach ($parseVal as $val){
                    $kv = explode('=', trim($val));
                    if(count($kv)){
                        $spanContext->withBaggageItem($kv[0], $kv[1]);
                    }
                }
            }
        }


        return $spanContext;
    }

}
