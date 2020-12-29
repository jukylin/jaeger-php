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

use Jaeger\Span as jspan;
use OpenTracing\Reference;
use Jaeger\Thrift\Process;
use Jaeger\Thrift\Span;
use Jaeger\Thrift\TagType;
use Jaeger\Thrift\Log;
use Jaeger\Thrift\Tag;
use Jaeger\Thrift\SpanRef;
use Jaeger\Thrift\SpanRefType;

class JaegerThrift
{
    public function buildProcessThrift(Jaeger $jaeger): Process
    {
        $tags = [];
        $ip = $_SERVER['SERVER_ADDR'] ?? '0.0.0.0';
        $tags['peer.ipv4'] = $ip;

        $port = $_SERVER['SERVER_PORT'] ?? '80';
        $tags['peer.port'] = $port;

        $tags = array_merge($tags, $jaeger->tags);

        $processThrift = new Process([
            'serviceName' => $jaeger->serviceName,
            'tags' => $this->buildTags($tags),
        ]);

        return $processThrift;
    }

    public function buildSpanThrift(jspan $span): Span
    {
        $spContext = $span->spanContext;
        assert($spContext instanceof \Jaeger\SpanContext);

        $spanVals = [
            'traceIdLow' => intval($spContext->traceIdLow),
            'traceIdHigh' => intval($spContext->traceIdHigh),
            'spanId' => intval($spContext->spanId),
            'parentSpanId' => intval($spContext->parentId),
            'operationName' => $span->getOperationName(),
            'flags' => intval($spContext->flags),
            'startTime' => $span->startTime,
            'duration' => $span->duration,
            'tags' => $this->buildTags($span->tags),
            'logs' => $this->buildLogs($span->logs),
        ];

        if($spContext->parentId != 0) {
            $spanVals['references'] = $this->buildReferences($span->references);
        }

        $thriftSpan = new Span($spanVals);

        return $thriftSpan;
    }

    private function buildLogs(array $logs): array
    {
        $resultLogs = [];
        foreach ($logs as $log) {
            $resultLogs[] = new Log([
                'timestamp' => $log['timestamp'],
                'fields' => $this->buildTags($log['fields']),
            ]);
        }

        return $resultLogs;
    }

    /**
     * @param array<Reference> $references
     *
     * @return array
     */
    private function buildReferences(array $references): array
    {
        $spanRef = [];
        foreach ($references as $ref) {
            if ($ref->isType(Reference::CHILD_OF)) {
                $type = SpanRefType::CHILD_OF;
            } elseif ($ref->isType(Reference::FOLLOWS_FROM)) {
                $type = SpanRefType::FOLLOWS_FROM;
            } else {
                throw new \LogicException('Unsupported reference type');
            }

            $ctx = $ref->getSpanContext();
            assert($ctx instanceof \Jaeger\SpanContext);

            $spanRef[] = new SpanRef([
                'refType' => $type,
                'traceIdLow' => intval($ctx->traceIdLow),
                'traceIdHigh' => intval($ctx->traceIdHigh),
                'spanId' => intval($ctx->spanId),
            ]);
        }

        return $spanRef;
    }


    public function buildTags(array $tags): array
    {
        $jTags = [];
        if (empty($tags)) {
            return $jTags;
        }

        foreach ($tags as $k => $v) {
            switch (gettype($v)) {
                case 'boolean':
                    $jTags[] = new Tag([
                        'key' => $k,
                        'vType' => TagType::BOOL,
                        'vBool' => $v,
                    ]);
                    break;
                case 'integer':
                    $jTags[] = new Tag([
                        'key' => $k,
                        'vType' => TagType::LONG,
                        'vDouble' => $v,
                    ]);
                    break;
                case 'double':
                    $jTags[] = new Tag([
                        'key' => $k,
                        'vType' => TagType::DOUBLE,
                        'vDouble' => $v,
                    ]);
                    break;
                case 'array':
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                default:
                    $jTags[] = new Tag([
                        'key' => $k,
                        'vType' => TagType::STRING,
                        'vStr' => $v,
                    ]);
            }
        }

        return $jTags;
    }
}
