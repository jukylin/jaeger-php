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


use OpenTracing\SpanContext;

class Span implements \OpenTracing\Span{

    private $operationName = '';

    public $startTime = '';

    public $finishTime = '';

    public $spanKind = '';

    public $spanContext = null;

    public $duration = 0;

    public $logs = [];

    public $tags = [];

    public $references = [];

    public function __construct($operationName, \OpenTracing\SpanContext $spanContext, $references, $startTime = null){
        $this->operationName = $operationName;
        $this->startTime = $startTime == null ? $this->microtimeToInt() : $startTime;
        $this->spanContext = $spanContext;
        $this->references = $references;
    }

    /**
     * @return string
     */
    public function getOperationName(): string {
        return $this->operationName;
    }

    /**
     * @return SpanContext
     */
    public function getContext(): SpanContext{
        return $this->spanContext;
    }

    /**
     * @inheritDoc
     */
    public function finish($finishTime = null): void{
        $this->finishTime = $finishTime == null ? $this->microtimeToInt() : $finishTime;
        $this->duration = $this->finishTime - $this->startTime;
    }

    /**
     * @param string $newOperationName
     */
    public function overwriteOperationName(string $newOperationName): void {
        $this->operationName = $newOperationName;
    }


    public function setTag(string $key, $value): void{
        $this->tags[$key] = $value;
    }


    /**
     * Adds a log record to the span
     *
     * @param array $fields [key => val]
     * @param int|float|\DateTimeInterface $timestamp
     */
    public function log(array $fields = [], $timestamp = null): void {
        $log['timestamp'] = $timestamp ? $timestamp : $this->microtimeToInt();
        $log['fields'] = $fields;
        $this->logs[] = $log;
    }

    /**
     * Adds a baggage item to the SpanContext which is immutable so it is required to use SpanContext::withBaggageItem
     * to get a new one.
     *
     * @param string $key
     * @param string $value
     */
    public function addBaggageItem(string $key, string $value): void{
        $this->log([
            'event' => 'baggage',
            'key' => $key,
            'value' => $value,
        ]);

        $this->spanContext->withBaggageItem($key, $value);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getBaggageItem(string $key): ?string{
        return $this->spanContext->getBaggageItem($key);
    }


    private function microtimeToInt(){
        return intval(microtime(true) * 1000000);
    }
}