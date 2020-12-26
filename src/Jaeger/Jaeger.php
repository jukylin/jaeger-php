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

use Jaeger\Propagator\Propagator;
use Jaeger\Reporter\Reporter;
use Jaeger\Sampler\Sampler;
use OpenTracing\Formats;
use OpenTracing\Reference;
use OpenTracing\ScopeManager;
use OpenTracing\SpanContext;
use OpenTracing\StartSpanOptions;
use OpenTracing\Tracer;
use OpenTracing\UnsupportedFormatException;

class Jaeger implements Tracer
{
    private $reporter = null;

    private $sampler = null;

    private $gen128bit = false;

    private $scopeManager;

    public $spans = [];

    public $tags = [];

    public $process = null;

    public $serverName = '';

    public $processThrift = '';

    /** @var Propagator|null */
    public $propagator = null;

    public function __construct($serverName = '', Reporter $reporter, Sampler $sampler,
                                ScopeManager $scopeManager)
    {
        $this->reporter = $reporter;

        $this->sampler = $sampler;

        $this->scopeManager = $scopeManager;

        $this->setTags($this->sampler->getTags());
        $this->setTags($this->getEnvTags());

        if ('' == $serverName) {
            $this->serverName = $_SERVER['SERVER_NAME'] ?? 'unknow server';
        } else {
            $this->serverName = $serverName;
        }
    }

    /**
     * @param array $tags key => value
     */
    public function setTags(array $tags = [])
    {
        if (!empty($tags)) {
            $this->tags = array_merge($this->tags, $tags);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function startSpan(string $operationName, $options = []): \OpenTracing\Span
    {
        if (!($options instanceof StartSpanOptions)) {
            $options = StartSpanOptions::create($options);
        }

        $parentSpan = $this->getParentSpanContext($options);
        if (null == $parentSpan || !$parentSpan->traceIdLow) {
            $low = $this->generateId();
            $spanId = $low;
            $flags = $this->sampler->IsSampled();
            $spanContext = new \Jaeger\SpanContext($spanId, 0, $flags, null, 0);
            $spanContext->traceIdLow = $low;
            if (true == $this->gen128bit) {
                $spanContext->traceIdHigh = $this->generateId();
            }
        } else {
            $spanContext = new \Jaeger\SpanContext($this->generateId(),
                $parentSpan->spanId, $parentSpan->flags, $parentSpan->baggage, 0);
            $spanContext->traceIdLow = $parentSpan->traceIdLow;
            if ($parentSpan->traceIdHigh) {
                $spanContext->traceIdHigh = $parentSpan->traceIdHigh;
            }
        }

        $startTime = $options->getStartTime() ? intval($options->getStartTime() * 1000000) : null;
        $span = new Span($operationName, $spanContext, $options->getReferences(), $startTime);
        if (!empty($options->getTags())) {
            foreach ($options->getTags() as $k => $tag) {
                $span->setTag($k, $tag);
            }
        }
        if (1 == $spanContext->isSampled()) {
            $this->spans[] = $span;
        }

        return $span;
    }

    public function setPropagator(Propagator $propagator)
    {
        $this->propagator = $propagator;
    }

    /**
     * 注入.
     *
     * @param mixed $carrier
     */
    public function inject(SpanContext $spanContext, string $format, &$carrier): void
    {
        if (Formats\TEXT_MAP == $format) {
            $this->propagator->inject($spanContext, $format, $carrier);
        } else {
            throw UnsupportedFormatException::forFormat($format);
        }
    }

    /**
     * 提取.
     *
     * @param mixed $carrier
     */
    public function extract(string $format, $carrier): ?SpanContext
    {
        if (Formats\TEXT_MAP == $format) {
            return $this->propagator->extract($format, $carrier);
        } else {
            throw UnsupportedFormatException::forFormat($format);
        }
    }

    public function getSpans()
    {
        return $this->spans;
    }

    public function reportSpan()
    {
        if ($this->spans) {
            $this->reporter->report($this);
            $this->spans = [];
        }
    }

    public function getScopeManager(): ScopeManager
    {
        return $this->scopeManager;
    }

    public function getActiveSpan(): ?\OpenTracing\Span
    {
        $activeScope = $this->getScopeManager()->getActive();
        if (null === $activeScope) {
            return null;
        }

        return $activeScope->getSpan();
    }

    public function startActiveSpan(string $operationName, $options = []): \OpenTracing\Scope
    {
        if (!$options instanceof StartSpanOptions) {
            $options = StartSpanOptions::create($options);
        }

        $parentSpan = $this->getParentSpanContext($options);
        if (null === $parentSpan && null !== $this->getActiveSpan()) {
            $parentContext = $this->getActiveSpan()->getContext();
            $options = $options->withParent($parentContext);
        }

        $span = $this->startSpan($operationName, $options);

        return $this->getScopeManager()->activate($span, $options->shouldFinishSpanOnClose());
    }

    private function getParentSpanContext(StartSpanOptions $options)
    {
        $references = $options->getReferences();

        $parentSpanContext = null;

        foreach ($references as $ref) {
            $parentSpanContext = $ref->getSpanContext();
            if ($ref->isType(Reference::CHILD_OF)) {
                return $parentSpanContext;
            }
        }

        if ($parentSpanContext) {
            assert($parentSpanContext instanceof \Jaeger\SpanContext);

            if (($parentSpanContext->isValid()
                || (!$parentSpanContext->isTraceIdValid() && $parentSpanContext->debugId)
                || count($parentSpanContext->baggage) > 0)
            ) {
                return $parentSpanContext;
            }
        }

        return null;
    }

    public function getEnvTags()
    {
        $tags = [];
        if (isset($_SERVER['JAEGER_TAGS']) && '' != $_SERVER['JAEGER_TAGS']) {
            $envTags = explode(',', $_SERVER['JAEGER_TAGS']);
            foreach ($envTags as $envK => $envTag) {
                [$key, $value] = explode('=', $envTag);
                $tags[$key] = $value;
            }
        }

        return $tags;
    }

    public function gen128bit()
    {
        $this->gen128bit = true;
    }

    /**
     * 结束,发送信息到jaeger.
     */
    public function flush(): void
    {
        $this->reportSpan();
        $this->reporter->close();
    }

    private function generateId()
    {
        return microtime(true) * 10000 .random_int(10000, 99999);
    }
}
