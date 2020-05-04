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

use Jaeger\Jaeger;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\ScopeManager;
use Jaeger\Span;
use Jaeger\Transport\TransportUdp;
use OpenTracing\Reference;
use PHPUnit\Framework\TestCase;
use OpenTracing\Formats;
use Jaeger\SpanContext;
use Jaeger\Constants;
use Jaeger\Propagator\JaegerPropagator;

class JaegerTest extends TestCase
{


    public function getJaeger(){

        $tranSport = new TransportUdp();
        $reporter = new RemoteReporter($tranSport);
        $sampler = new ConstSampler();
        $scopeManager = new ScopeManager();

        return new Jaeger('jaeger', $reporter, $sampler, $scopeManager);
    }


    public function testNew(){
        $Jaeger = $this->getJaeger();
        $this->assertInstanceOf(Jaeger::class, $Jaeger);
    }

    public function testGetEnvTags(){

        $_SERVER['JAEGER_TAGS'] = 'a=b,c=d';
        $Jaeger = $this->getJaeger();
        $tags = $Jaeger->getEnvTags();

        $this->assertTrue(count($tags) > 0);
    }


    public function testSetTags(){
        $Jaeger = $this->getJaeger();

        $Jaeger->setTags(['version' => '2.0.0']);
        $this->assertTrue($Jaeger->tags['version'] ==  '2.0.0');
    }


    public function testInject(){
        $Jaeger = $this->getJaeger();
        $Jaeger->setPropagator(new JaegerPropagator());

        $context = new SpanContext(1, 1, 1, null, 1);

        $Jaeger->inject($context, Formats\TEXT_MAP, $_SERVER);
        $this->assertTrue('0:1:1:1' == $_SERVER[strtoupper(Constants\Tracer_State_Header_Name)]);
    }


    public function testInjectUnSupportFormat(){
        $Jaeger = $this->getJaeger();
        $Jaeger->setPropagator(new JaegerPropagator());

        $context = new SpanContext(1, 1, 1, null, 1);
        $this->expectExceptionMessage('The format \'http_headers\' is not supported.');

        $Jaeger->inject($context, Formats\HTTP_HEADERS, $_SERVER);
    }


    public function testExtract(){
        $Jaeger = $this->getJaeger();
        $Jaeger->setPropagator(new JaegerPropagator());

        $carrier[strtoupper(Constants\Tracer_State_Header_Name)] = '1:1:1:1';
        $spanContext = $Jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($spanContext->parentId == 1);
        $this->assertTrue($spanContext->traceIdLow == 1);
        $this->assertTrue($spanContext->flags == 1);
        $this->assertTrue($spanContext->spanId == 1);
    }


    public function testExtractUnSupportFormat(){
        $Jaeger = $this->getJaeger();
        $Jaeger->setPropagator(new JaegerPropagator());

        $_SERVER[strtoupper(Constants\Tracer_State_Header_Name)] = '1:1:1:1';
        $this->expectExceptionMessage('The format \'http_headers\' is not supported.');

        $Jaeger->extract(Formats\HTTP_HEADERS, $_SERVER);
    }


    public function testStartSpan(){
        $Jaeger = $this->getJaeger();
        $span = $Jaeger->startSpan('test');
        $this->assertNotEmpty($span->startTime);
        $this->assertNotEmpty($Jaeger->getSpans());
    }


    public function testStartSpanWithFollowsFromTypeRef()
    {
        $jaeger = $this->getJaeger();
        $rootSpan = $jaeger->startSpan('root-a');
        $childSpan = $jaeger->startSpan('span-a', [
            'references' => Reference::create(Reference::FOLLOWS_FROM, $rootSpan),
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $rootSpan->spanContext->traceIdLow);
        $this->assertSame(current($childSpan->references)->getContext(), $rootSpan->spanContext);

        $otherRootSpan = $jaeger->startSpan('root-a');
        $childSpan = $jaeger->startSpan('span-b', [
            'references' => [
                Reference::create(Reference::FOLLOWS_FROM, $rootSpan),
                Reference::create(Reference::FOLLOWS_FROM, $otherRootSpan),
            ],
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $otherRootSpan->spanContext->traceIdLow);
    }


    public function testStartSpanWithChildOfTypeRef()
    {
        $jaeger = $this->getJaeger();
        $rootSpan = $jaeger->startSpan('root-a');
        $otherRootSpan = $jaeger->startSpan('root-b');
        $childSpan = $jaeger->startSpan('span-a', [
            'references' => [
                Reference::create(Reference::CHILD_OF, $rootSpan),
                Reference::create(Reference::CHILD_OF, $otherRootSpan),
            ],
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $rootSpan->spanContext->traceIdLow);
    }


    public function testStartSpanWithCustomStartTime()
    {
        $jaeger = $this->getJaeger();
        $span = $jaeger->startSpan('test', ['start_time' => 1499355363.123456]);

        $this->assertSame(1499355363123456, $span->startTime);
    }


    public function testStartSpanWithAllRefType()
    {
        $jaeger = $this->getJaeger();
        $rootSpan = $jaeger->startSpan('root-a');
        $otherRootSpan = $jaeger->startSpan('root-b');
        $childSpan = $jaeger->startSpan('span-a', [
            'references' => [
                Reference::create(Reference::FOLLOWS_FROM, $rootSpan),
                Reference::create(Reference::CHILD_OF, $otherRootSpan),
            ],
        ]);

        $this->assertSame($childSpan->spanContext->traceIdLow, $otherRootSpan->spanContext->traceIdLow);
    }


    public function testReportSpan(){
        $Jaeger = $this->getJaeger();
        $Jaeger->startSpan('test');
        $Jaeger->reportSpan();
        $this->assertEmpty($Jaeger->getSpans());
    }

    public function testStartActiveSpan(){
        $Jaeger = $this->getJaeger();
        $Jaeger->startActiveSpan('test');

        $this->assertNotEmpty($Jaeger->getSpans());
    }


    public function testGetActiveSpan(){
        $Jaeger = $this->getJaeger();
        $Jaeger->startActiveSpan('test');

        $span = $Jaeger->getActiveSpan();

        $this->assertInstanceOf(Span::class, $span);
    }


    public function testFlush(){
        $Jaeger = $this->getJaeger();
        $Jaeger->startSpan('test');
        $Jaeger->flush();
        $this->assertEmpty($Jaeger->getSpans());
    }


    public function testNestedSpanBaggage(){
        $tracer = $this->getJaeger();

        $parent = $tracer->startSpan('parent');
        $parent->addBaggageItem('key', 'value');

        $child = $tracer->startSpan('child', [Reference::CHILD_OF => $parent]);

        $this->assertEquals($parent->getBaggageItem('key'), $child->getBaggageItem('key'));
    }
}
