<?php

namespace JaegerPhp\ThriftGen\Agent;

use JaegerPhp\Jaeger;
use JaegerPhp\JSpan;


class JaegerThriftSpan{


    public function buildJaegerProcessThrift(Jaeger $jaeger){
        $tags = [];
        $ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        if($ip){
            $tags['ip'] = $ip;
        }

        $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '';
        if($port){
            $tags['port'] = $port;
        }
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

    public function buildJaegerSpanThrift(JSpan $Jspan){

        $spContext = $Jspan->spanContext;
        $span = [
            'traceIdLow' => hexdec($spContext->traceId),
            'traceIdHigh' => 0,
            'spanId' => hexdec($spContext->spanId),
            'parentSpanId' => hexdec($spContext->parentId),
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
                    'refType' => 1,
                    'traceIdLow' => hexdec($spContext->traceId),
                    'traceIdHigh' => 0,
                    'spanId' => hexdec($spContext->parentId),
                ],
            ];
        }


        return $span;
    }



    private function buildTags($tags){
        $resultTags = [];
        if($tags){
            foreach ($tags as $key => $tag){
                if($key == "error"){
                    $resultTags[] = [
                        'key' => $key,
                        'vBool' => $tag,
                        'vType' => "BOOL"
                    ];
                }else{
                    $resultTags[] = [
                        'key' => $key,
                        'vStr' => strval($tag),
                        'vType' => "STRING"
                    ];
                }
            }
        }


        return $resultTags;
    }


    private function buildLogs($logs){
        $resultLogs = [];
        if($logs){
            foreach($logs as $log){
                $fields = [];
                foreach ($log['fields'] as $field){
                    $field = [
                        'key' => $field['key'],
                        'vType' => 'STRING',
                        'vStr' => $field['value'],
                    ];
                    $fields[] = $field;
                }
                $resultLogs[] = [
                    "timestamp" => $log['timestamp'],
                    "fields" => $fields,
                ];
            }
        }

        return $resultLogs;
    }
}