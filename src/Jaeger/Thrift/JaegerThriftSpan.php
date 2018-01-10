<?php

namespace Jaeger\Thrift;

use Jaeger\Jaeger;
use Jaeger\Span;

class JaegerThriftSpan{


    public function buildJaegerProcessThrift(Jaeger $jaeger){
        $tags = [];
        $ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
        $tags['peer.ipv4'] = $ip;

        $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '80';
        $tags['peer.port'] = $port;

        $tags = array_merge($tags, $jaeger->tags);
        $tagsObj = Tags::getInstance();
        $tagsObj->setTags($tags);
        $thriftTags = $tagsObj->buildTags();

        $processThrift = [
            'serverName' => $jaeger->serverName,
            'tags' => $thriftTags,
        ];


        return $processThrift;
    }

    public function buildJaegerSpanThrift(Span $Jspan){

        $spContext = $Jspan->spanContext;
        $span = [
            'traceIdLow' => $spContext->traceIdLow,
            'traceIdHigh' => $spContext->traceIdHigh,
            'spanId' => $spContext->spanId,
            'parentSpanId' => $spContext->parentId,
            'operationName' => $Jspan->getOperationName(),
            'flags' => intval($spContext->flags),
            'startTime' => $Jspan->startTime,
            'duration' => $Jspan->duration,
            'tags' => $this->buildTags($Jspan->tags),
            'logs' => $this->buildLogs($Jspan->logs),
        ];

        if ($spContext->parentId != 0) {
            $span['references'] = [
                [
                    'refType' => SpanRefType::CHILD_OF,
                    'traceIdLow' => $spContext->traceIdLow,
                    'traceIdHigh' => $spContext->traceIdHigh,
                    'spanId' => $spContext->parentId,
                ],
            ];
        }


        return $span;
    }



    private function buildTags($tags){
        $tagsObj = Tags::getInstance();
        $tagsObj->setTags($tags);
        $resultTags = $tagsObj->buildTags();

        return $resultTags;
    }


    private function buildLogs($logs){
        $resultLogs = [];
        $tagsObj = Tags::getInstance();
        foreach($logs as $log){
            $tagsObj->setTags($log['fields']);
            $fields = $tagsObj->buildTags();
            $resultLogs[] = [
                "timestamp" => $log['timestamp'],
                "fields" => $fields,
            ];
        }

        return $resultLogs;
    }
}