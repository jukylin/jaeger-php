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

namespace Jaeger\Thrift;

use Jaeger\Jaeger;
use Jaeger\Span;
use OpenTracing\Reference;

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

    public function buildJaegerSpanThrift(Span $span){

        $spContext = $span->spanContext;
        $thriftSpan = [
            'traceIdLow' => $spContext->traceIdLow,
            'traceIdHigh' => $spContext->traceIdHigh,
            'spanId' => $spContext->spanId,
            'parentSpanId' => $spContext->parentId,
            'operationName' => $span->getOperationName(),
            'flags' => intval($spContext->flags),
            'startTime' => $span->startTime,
            'duration' => $span->duration,
            'tags' => $this->buildTags($span->tags),
            'logs' => $this->buildLogs($span->logs),
            'references' => $this->buildReferences($span->references)
        ];

        return $thriftSpan;
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


    private function buildReferences($references){
        $spanRef = [];
        foreach ($references as $ref){
            if($ref->isType(Reference::CHILD_OF)){
                $type = SpanRefType::CHILD_OF;
            }else if($ref->isType(Reference::FOLLOWS_FROM)){
                $type = SpanRefType::FOLLOWS_FROM;
            }
            $ctx = $ref->getContext();
            $spanRef[] = [
                'refType' => $type,
                'traceIdLow' => $ctx->traceIdLow,
                'traceIdHigh' => $ctx->traceIdHigh,
                'spanId' => $ctx->spanId,
            ];
        }

        return $spanRef;
    }
}