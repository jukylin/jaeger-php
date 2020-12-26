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

class Span implements \OpenTracing\Span
{
    private $operationName = '';

    public $startTime = '';

    public $finishTime = '';

    public $spanKind = '';

    public $spanContext = null;

    public $duration = 0;

    public $logs = [];

    public $tags = [];

    public $references = [];

    public function __construct($operationName, SpanContext $spanContext, $references, $startTime = null)
    {
        $this->operationName = $operationName;
        $this->startTime = null == $startTime ? $this->microtimeToInt() : $startTime;
        $this->spanContext = $spanContext;
        $this->references = $references;
    }

    /**
     * {@inheritDoc}
     */
    public function getOperationName(): string
    {
        return $this->operationName;
    }

    /**
     * {@inheritDoc}
     */
    public function getContext(): SpanContext
    {
        return $this->spanContext;
    }

    /**
     * {@inheritDoc}
     */
    public function finish($finishTime = null): void
    {
        $this->finishTime = null == $finishTime ? $this->microtimeToInt() : $finishTime;
        $this->duration = $this->finishTime - $this->startTime;
    }

    /**
     * {@inheritDoc}
     */
    public function overwriteOperationName(string $newOperationName): void
    {
        $this->operationName = $newOperationName;
    }

    /**
     * {@inheritDoc}
     */
    public function setTag(string $key, $value): void
    {
        $this->tags[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function log(array $fields = [], $timestamp = null): void
    {
        $log['timestamp'] = $timestamp ? $timestamp : $this->microtimeToInt();
        $log['fields'] = $fields;
        $this->logs[] = $log;
    }

    /**
     * {@inheritDoc}
     */
    public function addBaggageItem(string $key, string $value): void
    {
        $this->log([
            'event' => 'baggage',
            'key' => $key,
            'value' => $value,
        ]);

        $this->spanContext->withBaggageItem($key, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getBaggageItem(string $key): ?string
    {
        return $this->spanContext->getBaggageItem($key);
    }

    private function microtimeToInt()
    {
        return intval(microtime(true) * 1000000);
    }
}
