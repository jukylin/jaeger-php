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

namespace tests;

use Jaeger\Constants;
use Jaeger\Jaeger;
use Jaeger\Propagator\JaegerPropagator;
use Jaeger\Reporter\NullReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\ScopeManager;
use Jaeger\Span;
use Jaeger\SpanContext;
use OpenTracing\Formats;
use OpenTracing\Reference;
use PHPUnit\Framework\TestCase;

class JaegerTest extends TestCase
{
    /**
     * @var Jaeger|null
     */
    public $tracer = null;

    public function setUp()
    {
        $reporter = new NullReporter();
        $sampler = new ConstSampler();
        $scopeManager = new ScopeManager();
        $this->tracer = new Jaeger('jaeger', $reporter, $sampler, $scopeManager);
    }

    public function testNew()
    {
        $this->assertInstanceOf(Jaeger::class, $this->tracer);
    }

    public function testGetEnvTags()
    {
        $_SERVER['JAEGER_TAGS'] = 'a=b,c=d';
        $tags = $this->tracer->getEnvTags();
        $this->assertTrue(count($tags) > 0);
    }

    public function testSetTags()
    {
        $this->tracer->setTags(['version' => '2.0.0']);
        $this->assertTrue('2.0.0' == $this->tracer->tags['version']);
    }

    public function testInject()
    {
        $this->tracer->setPropagator(new JaegerPropagator());

        $context = new SpanContext(1, 1, 1, null, 1);

        $this->tracer->inject($context, Formats\TEXT_MAP, $_SERVER);
        $this->assertTrue('0:1:1:1' == $_SERVER[strtoupper(Constants\Tracer_State_Header_Name)]);
    }

    /**
     * @expectedException \OpenTracing\UnsupportedFormatException
     */
    public function testInjectUnSupportFormat()
    {
        $this->tracer->setPropagator(new JaegerPropagator());

        $context = new SpanContext(1, 1, 1, null, 1);
        $this->expectExceptionMessage('The format "http_headers" is not supported.');

        $this->tracer->inject($context, Formats\HTTP_HEADERS, $_SERVER);
    }

    public function testExtract()
    {
        $this->tracer->setPropagator(new JaegerPropagator());

        $carrier[strtoupper(Constants\Tracer_State_Header_Name)] = '1:1:1:1';
        $spanContext = $this->tracer->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(1 == $spanContext->parentId);
        $this->assertTrue(1 == $spanContext->traceIdLow);
        $this->assertTrue(1 == $spanContext->flags);
        $this->assertTrue(1 == $spanContext->spanId);
    }

    /**
     * @expectedException \OpenTracing\UnsupportedFormatException
     */
    public function testExtractUnSupportFormat()
    {
        $this->tracer->setPropagator(new JaegerPropagator());

        $_SERVER[strtoupper(Constants\Tracer_State_Header_Name)] = '1:1:1:1';
        $this->expectExceptionMessage('The format "http_headers" is not supported.');

        $this->tracer->extract(Formats\HTTP_HEADERS, $_SERVER);
    }

    public function testStartSpan()
    {
        $span = $this->tracer->startSpan('test');
        $this->assertNotNull($span->startTime);
        $this->assertNotEmpty($this->tracer->getSpans());
    }

    public function testStartSpanWithFollowsFromTypeRef()
    {
        $rootSpan = $this->tracer->startSpan('root-a');
        $childSpan = $this->tracer->startSpan('span-a', [
            'references' => Reference::createForSpan(Reference::FOLLOWS_FROM, $rootSpan),
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $rootSpan->spanContext->traceIdLow);
        $this->assertSame(current($childSpan->references)->getSpanContext(), $rootSpan->spanContext);

        $otherRootSpan = $this->tracer->startSpan('root-a');
        $childSpan = $this->tracer->startSpan('span-b', [
            'references' => [
                Reference::createForSpan(Reference::FOLLOWS_FROM, $rootSpan),
                Reference::createForSpan(Reference::FOLLOWS_FROM, $otherRootSpan),
            ],
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $otherRootSpan->spanContext->traceIdLow);
    }

    public function testStartSpanWithChildOfTypeRef()
    {
        $rootSpan = $this->tracer->startSpan('root-a');
        $otherRootSpan = $this->tracer->startSpan('root-b');
        $childSpan = $this->tracer->startSpan('span-a', [
            'references' => [
                Reference::createForSpan(Reference::CHILD_OF, $rootSpan),
                Reference::createForSpan(Reference::CHILD_OF, $otherRootSpan),
            ],
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $rootSpan->spanContext->traceIdLow);
    }

    public function testStartSpanWithCustomStartTime()
    {
        $span = $this->tracer->startSpan('test', ['start_time' => 1499355363.123456]);

        $this->assertSame(1499355363123456, $span->startTime);
    }

    public function testStartSpanWithAllRefType()
    {
        $rootSpan = $this->tracer->startSpan('root-a');
        $otherRootSpan = $this->tracer->startSpan('root-b');
        $childSpan = $this->tracer->startSpan('span-a', [
            'references' => [
                Reference::createForSpan(Reference::FOLLOWS_FROM, $rootSpan),
                Reference::createForSpan(Reference::CHILD_OF, $otherRootSpan),
            ],
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $otherRootSpan->spanContext->traceIdLow);
    }

    public function testReportSpan()
    {
        $this->tracer->startSpan('test');
        $this->tracer->reportSpan();
        $this->assertEmpty($this->tracer->getSpans());
    }

    public function testStartActiveSpan()
    {
        $this->tracer->startActiveSpan('test');
        $this->assertNotEmpty($this->tracer->getSpans());
    }

    public function testGetActiveSpan()
    {
        $this->tracer->startActiveSpan('test');

        $span = $this->tracer->getActiveSpan();
        $this->assertInstanceOf(Span::class, $span);
    }

    public function testFlush()
    {
        $this->tracer->startSpan('test');
        $this->tracer->flush();
        $this->assertEmpty($this->tracer->getSpans());
    }

    public function testNestedSpanBaggage()
    {
        $parent = $this->tracer->startSpan('parent');
        $parent->addBaggageItem('key', 'value');

        $child = $this->tracer->startSpan('child', [Reference::CHILD_OF => $parent]);

        $this->assertEquals($parent->getBaggageItem('key'), $child->getBaggageItem('key'));
    }
}
