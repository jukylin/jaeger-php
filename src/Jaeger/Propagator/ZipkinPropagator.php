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
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', str_replace('_', ' ', strtolower(substr($key, 5))));
            $headers[$header] = $value;
        }

        $spanContext = new SpanContext(0, 0, 0, null, 0);
        if(isset($headers[Constants\X_B3_TRACEID]) && $headers[Constants\X_B3_TRACEID]){
            $spanContext->traceIdLow = hexdec($headers[Constants\X_B3_TRACEID]);
        }

        if(isset($_SERVER[Constants\X_B3_TRACEID]) && $_SERVER[Constants\X_B3_TRACEID]){
            $spanContext->traceIdLow = hexdec($_SERVER[Constants\X_B3_TRACEID]);
        }

        if(isset($headers[Constants\X_B3_PARENT_SPANID]) && $headers[Constants\X_B3_PARENT_SPANID]){
            $spanContext->parentId = hexdec($headers[Constants\X_B3_PARENT_SPANID]);
        }

        if(isset($_SERVER[Constants\X_B3_PARENT_SPANID]) && $_SERVER[Constants\X_B3_PARENT_SPANID]){
            $spanContext->parentId = hexdec($_SERVER[Constants\X_B3_PARENT_SPANID]);
        }

        if(isset($headers[Constants\X_B3_SPANID]) && $headers[Constants\X_B3_SPANID]){
            $spanContext->spanId = hexdec($headers[Constants\X_B3_SPANID]);
        }

        if(isset($_SERVER[Constants\X_B3_SPANID]) && $_SERVER[Constants\X_B3_SPANID]){
            $spanContext->spanId = hexdec($_SERVER[Constants\X_B3_SPANID]);
        }

        if(isset($headers[Constants\X_B3_SAMPLED]) && $headers[Constants\X_B3_SAMPLED]){
            $spanContext->flags = $headers[Constants\X_B3_SAMPLED];
        }

        if(isset($_SERVER[Constants\X_B3_SAMPLED]) && $_SERVER[Constants\X_B3_SAMPLED]){
            $spanContext->flags = $_SERVER[Constants\X_B3_SAMPLED];
        }
        
        return $spanContext;
    }
}
