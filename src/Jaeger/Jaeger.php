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

use Jaeger\Sampler\Sampler;
use OpenTracing\Exceptions\UnsupportedFormat;
use OpenTracing\SpanContext;
use OpenTracing\Formats;
use OpenTracing\Tracer;
use Jaeger\Reporter\Reporter;
use OpenTracing\StartSpanOptions;
use OpenTracing\Reference;
use Jaeger\Propagator\Propagator;

class Jaeger implements Tracer{

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
                                ScopeManager $scopeManager){

        $this->reporter = $reporter;

        $this->sampler = $sampler;

        $this->scopeManager = $scopeManager;

        $this->setTags($this->sampler->getTags());
        $this->setTags($this->getEnvTags());

        if($serverName == '') {
            $this->serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'unknow server';
        }else{
            $this->serverName = $serverName;
        }
    }


    /**
     * @param array $tags  key => value
     */
    public function setTags(array $tags = []){
        if(!empty($tags)) {
            $this->tags = array_merge($this->tags, $tags);
        }
    }


    /**
     * init span info
     * @param string $operationName
     * @param array $options
     * @return Span
     */
    public function startSpan($operationName, $options = []){

        if (!($options instanceof StartSpanOptions)) {
            $options = StartSpanOptions::create($options);
        }

        $parentSpan = $this->getParentSpanContext($options);
        if($parentSpan == null || !$parentSpan->traceIdLow){
            $low = $this->generateId();
            $spanId = $low;
            $flags = $this->sampler->IsSampled();
            $spanContext = new \Jaeger\SpanContext($spanId, 0, $flags, null, 0);
            $spanContext->traceIdLow = $low;
            if($this->gen128bit == true){
                $spanContext->traceIdHigh = $this->generateId();
            }
        }else{
            $spanContext = new \Jaeger\SpanContext($this->generateId(),
                $parentSpan->spanId, $parentSpan->flags, $parentSpan->baggage, 0);
            $spanContext->traceIdLow = $parentSpan->traceIdLow;
            if($parentSpan->traceIdHigh){
                $spanContext->traceIdHigh = $parentSpan->traceIdHigh;
            }
        }

        $startTime = $options->getStartTime() ? intval($options->getStartTime() * 1000000) : null;
        $span = new Span($operationName, $spanContext, $options->getReferences(), $startTime);
        if(!empty($options->getTags())) {
            foreach ($options->getTags() as $k => $tag) {
                $span->setTag($k, $tag);
            }
        }
        if($spanContext->isSampled() == 1) {
            $this->spans[] = $span;
        }

        return $span;
    }


    public function setPropagator(Propagator $propagator){
        $this->propagator = $propagator;
    }


    /**
     * 注入
     * @param SpanContext $spanContext
     * @param string $format
     * @param $carrier
     */
    public function inject(SpanContext $spanContext, $format, &$carrier){
        if($format == Formats\TEXT_MAP){
            $this->propagator->inject($spanContext, $format, $carrier);
        }else{
            throw UnsupportedFormat::forFormat($format);
        }
    }


    /**
     * 提取
     * @param string $format
     * @param $carrier
     */
    public function extract($format, $carrier){
        if($format == Formats\TEXT_MAP){
            return $this->propagator->extract($format, $carrier);
        }else{
            throw UnsupportedFormat::forFormat($format);
        }
    }


    public function getSpans(){
        return $this->spans;
    }


    public function reportSpan(){
        if($this->spans) {
            $this->reporter->report($this);
            $this->spans = [];
        }
    }


    public function getScopeManager(){
        return $this->scopeManager;
    }


    public function getActiveSpan(){
        $activeScope = $this->getScopeManager()->getActive();
        if ($activeScope === null) {
            return null;
        }

        return $activeScope->getSpan();
    }


    public function startActiveSpan($operationName, $options = []){
        if (!$options instanceof StartSpanOptions) {
            $options = StartSpanOptions::create($options);
        }

        $parentSpan = $this->getParentSpanContext($options);
        if ($parentSpan === null && $this->getActiveSpan() !== null) {
            $parentContext = $this->getActiveSpan()->getContext();
            $options = $options->withParent($parentContext);
        }

        $span = $this->startSpan($operationName, $options);
        return $this->getScopeManager()->activate($span, $options->shouldFinishSpanOnClose());
    }


    private function getParentSpanContext(StartSpanOptions $options)
    {
        $references = $options->getReferences();

        $parentSpan = null;

        foreach ($references as $ref) {
            $parentSpan = $ref->getContext();
            if ($ref->isType(Reference::CHILD_OF)) {
                return $parentSpan;
            }
        }

        if ($parentSpan) {
            if (($parentSpan->isValid()
                || (!$parentSpan->isTraceIdValid() && $parentSpan->debugId)
                || count($parentSpan->baggage) > 0)
            ) {
                return $parentSpan;
            }
        }

        return null;
    }


    public function getEnvTags(){
        $tags = [];
        if(isset($_SERVER['JAEGER_TAGS']) && $_SERVER['JAEGER_TAGS'] != ''){
            $envTags = explode(',', $_SERVER['JAEGER_TAGS']);
            foreach ($envTags as $envK => $envTag){
                list($key, $value) = explode('=', $envTag);
                $tags[$key] = $value;
            }
        }

        return $tags;
    }


    public function gen128bit(){
        $this->gen128bit = true;
    }


    /**
     * 结束,发送信息到jaeger
     */
    public function flush(){
        $this->reportSpan();
        $this->reporter->close();
    }


    private function generateId(){
        return microtime(true) * 10000 . rand(10000, 99999);
    }
}
