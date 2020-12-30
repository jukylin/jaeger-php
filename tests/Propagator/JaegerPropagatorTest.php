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
use Jaeger\Propagator\JaegerPropagator;
use Jaeger\SpanContext;
use OpenTracing\Formats;
use PHPUnit\Framework\TestCase;

class JaegerPropagatorTest extends TestCase
{
    /**
     * @var SpanContext|null
     */
    public $spanContext = null;

    public function setUp()
    {
        $this->spanContext = new SpanContext(1, 1, 1, null, 1);
    }

    public function testInject()
    {
        $this->spanContext->traceIdLow = 1562237095801441413;
        $jaeger = new JaegerPropagator();
        $carrier = [];

        $jaeger->inject( $this->spanContext, Formats\TEXT_MAP, $carrier);
        $this->assertTrue('15ae2e5c8e2ecc85:1:1:1' == $carrier[strtoupper(Constants\Tracer_State_Header_Name)]);
    }

    public function testInject128Bit()
    {
        $this->spanContext->traceIdLow = 1562289663898779811;
        $this->spanContext->traceIdHigh = 1562289663898881723;

        $jaeger = new JaegerPropagator();
        $carrier = [];

        $jaeger->inject( $this->spanContext, Formats\TEXT_MAP, $carrier);
        $this->assertTrue('15ae5e2c04f50ebb15ae5e2c04f380a3:1:1:1'
            == $carrier[strtoupper(Constants\Tracer_State_Header_Name)]);
    }

    public function testExtract()
    {
        $jaeger = new JaegerPropagator();
        $carrier = [];
        $carrier[strtoupper(Constants\Tracer_State_Header_Name)] = '15ae2e5c8e2ecc85:15ae2e5c8e2ecc85:0:1';

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(1562237095801441413 == $context->traceIdLow);
        $this->assertTrue(0 == $context->parentId);
        $this->assertTrue(1562237095801441413 == $context->spanId);
        $this->assertTrue(1 == $context->flags);
    }

    public function testExtractDebugId()
    {
        $jaeger = new JaegerPropagator();
        $carrier[Constants\Trace_Baggage_Header_Prefix.'baggage'] = 2;

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(0 == $context->debugId);

        $carrier[Constants\Jaeger_Debug_Header] = 1;
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(1 == $context->debugId);
    }

    public function testExtractUberctx()
    {
        $jaeger = new JaegerPropagator();

        $carrier[Constants\Trace_Baggage_Header_Prefix] = '2.0.0';
        $carrier[Constants\Jaeger_Debug_Header] = true;
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(null == $context->baggage);

        $carrier = [];

        $carrier[Constants\Trace_Baggage_Header_Prefix.'version'] = '2.0.0';
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue('2.0.0' == $context->getBaggageItem('version'));
    }

    public function testExtractBaggageHeader()
    {
        $jaeger = new JaegerPropagator();
        $carrier = [];

        $carrier[Constants\Jaeger_Baggage_Header] = 'version=2.0.0,os=1';
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue('2.0.0' == $context->getBaggageItem('version'));
        $this->assertTrue('1' == $context->getBaggageItem('os'));
    }

    public function testExtractBadBaggageHeader()
    {
        $jaeger = new JaegerPropagator();

        $carrier = [];

        $carrier[Constants\Jaeger_Baggage_Header] = 'version';
        $carrier[Constants\Jaeger_Debug_Header] = true;
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(null == $context->baggage);
    }

    public function testExtract128Bit()
    {
        $jaeger = new JaegerPropagator();
        $carrier = [];
        $carrier[strtoupper(Constants\Tracer_State_Header_Name)]
            = '15ae5e2c04f50ebb15ae5e2c04f380a3:15ae2e5c8e2ecc85:0:1';

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(1562289663898779811 == $context->traceIdLow);
        $this->assertTrue(1562289663898881723 == $context->traceIdHigh);
        $this->assertTrue(0 == $context->parentId);
        $this->assertTrue(1562237095801441413 == $context->spanId);
        $this->assertTrue(1 == $context->flags);
    }

    public function testExtractPsr7()
    {
        $jaeger = new JaegerPropagator();
        $carrier = [];
        $carrier[] = [strtoupper(Constants\Tracer_State_Header_Name) => '15ae2e5c8e2ecc85:15ae2e5c8e2ecc85:0:1'];

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(1562237095801441413 == $context->traceIdLow);
        $this->assertTrue(0 == $context->parentId);
        $this->assertTrue(1562237095801441413 == $context->spanId);
        $this->assertTrue(1 == $context->flags);
    }

    public function testExtractReturnsNull()
    {
        $jaeger = new JaegerPropagator();
        $carrier = [];

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertNull($context);
    }
}
