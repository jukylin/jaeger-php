<?php
namespace Jaeger\Propagator;

use Jaeger\SpanContext;
use Jaeger\Constants;

class ZipkinPropagator implements Propagator{

    public function inject(SpanContext $spanContext, $format, &$carrier){
        $carrier[Constants\X_B3_TRACEID] = $spanContext ->traceIdLowToString();
        $carrier[Constants\X_B3_PARENT_SPANID] = $spanContext->parentIdToString();
        $carrier[Constants\X_B3_SPANID] = $spanContext->spanIdToString();
        $carrier[Constants\X_B3_SAMPLED] = $spanContext->flagsToString();
    }


    public function extract($format, $carrier){
        $spanContext = new SpanContext(0, 0, 0, null, 0);
        if(isset($carrier[Constants\X_B3_TRACEID]) && $carrier[Constants\X_B3_TRACEID]){
            $spanContext->traceIdLow = hexdec($carrier[Constants\X_B3_TRACEID]);
        }

        if(isset($carrier[Constants\X_B3_PARENT_SPANID]) && $carrier[Constants\X_B3_PARENT_SPANID]){
            $spanContext->parentId = hexdec($carrier[Constants\X_B3_PARENT_SPANID]);
        }

        if(isset($carrier[Constants\X_B3_SPANID]) && $carrier[Constants\X_B3_SPANID]){
            $spanContext->spanId = hexdec($carrier[Constants\X_B3_SPANID]);
        }

        if(isset($carrier[Constants\X_B3_SAMPLED]) && $carrier[Constants\X_B3_SAMPLED]){
            $spanContext->flags = $carrier[Constants\X_B3_SAMPLED];
        }


        return $spanContext;
    }
}