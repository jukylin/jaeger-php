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

use PHPUnit\Framework\TestCase;
use OpenTracing\Formats;
use Jaeger\Constants;
use Jaeger\SpanContext;
use Jaeger\Propagator\JaegerPropagator;

class JaegerPropagatorTest extends TestCase{

    public function getSpanContext(){
        return new SpanContext(1562237095801441413, 0, 1, null, 1);
    }


    public function testInject(){
        $context = $this->getSpanContext();
        $context->traceIdLow = 1562237095801441413;
        $jaeger = new JaegerPropagator();
        $carrier = [];

        $jaeger->inject($context, Formats\TEXT_MAP, $carrier);
        $this->assertTrue($carrier[strtoupper(Constants\Tracer_State_Header_Name)] == '15ae2e5c8e2ecc85:15ae2e5c8e2ecc85:0:1');
    }


    public function testInject128Bit(){
        $context = $this->getSpanContext();
        $context->traceIdLow = 1562289663898779811;
        $context->traceIdHigh = 1562289663898881723;

        $jaeger = new JaegerPropagator();
        $carrier = [];

        $jaeger->inject($context, Formats\TEXT_MAP, $carrier);
        $this->assertTrue($carrier[strtoupper(Constants\Tracer_State_Header_Name)]
            == '15ae5e2c04f50ebb15ae5e2c04f380a3:15ae2e5c8e2ecc85:0:1');
    }


    public function testExtract(){

        $jaeger = new JaegerPropagator();
        $carrier = [];
        $carrier[strtoupper(Constants\Tracer_State_Header_Name)] = '15ae2e5c8e2ecc85:15ae2e5c8e2ecc85:0:1';

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->traceIdLow == 1562237095801441413);
        $this->assertTrue($context->parentId == 0);
        $this->assertTrue($context->spanId == 1562237095801441413);
        $this->assertTrue($context->flags == 1);
    }


    public function testExtractDebugId(){

        $jaeger = new JaegerPropagator();
        $carrier[Constants\Trace_Baggage_Header_Prefix . 'baggage'] = 2;

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->debugId == 0);

        $carrier[Constants\Jaeger_Debug_Header] = 1;
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->debugId == 1);
    }


    public function testExtractUberctx(){
        $jaeger = new JaegerPropagator();

        $carrier[Constants\Trace_Baggage_Header_Prefix] = '2.0.0';
        $carrier[Constants\Jaeger_Debug_Header] = true;
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->baggage == null);

        $carrier = [];

        $carrier[Constants\Trace_Baggage_Header_Prefix.'version'] = '2.0.0';
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->getBaggageItem('version') == '2.0.0');
    }


    public function testExtractBaggageHeader(){
        $jaeger = new JaegerPropagator();
        $carrier = [];

        $carrier[Constants\Jaeger_Baggage_Header] = 'version=2.0.0,os=1';
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->getBaggageItem('version') == '2.0.0');
        $this->assertTrue($context->getBaggageItem('os') == '1');
    }


    public function testExtractBadBaggageHeader(){
        $jaeger = new JaegerPropagator();

        $carrier = [];

        $carrier[Constants\Jaeger_Baggage_Header] = 'version';
        $carrier[Constants\Jaeger_Debug_Header] = true;
        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->baggage == null);
    }


    public function testExtract128Bit(){

        $jaeger = new JaegerPropagator();
        $carrier = [];
        $carrier[strtoupper(Constants\Tracer_State_Header_Name)]
            = '15ae5e2c04f50ebb15ae5e2c04f380a3:15ae2e5c8e2ecc85:0:1';

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->traceIdLow == 1562289663898779811);
        $this->assertTrue($context->traceIdHigh == 1562289663898881723);
        $this->assertTrue($context->parentId == 0);
        $this->assertTrue($context->spanId == 1562237095801441413);
        $this->assertTrue($context->flags == 1);
    }


    public function testExtractPsr7(){

        $jaeger = new JaegerPropagator();
        $carrier = [];
        $carrier[] = [strtoupper(Constants\Tracer_State_Header_Name) => '15ae2e5c8e2ecc85:15ae2e5c8e2ecc85:0:1'];

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue($context->traceIdLow == 1562237095801441413);
        $this->assertTrue($context->parentId == 0);
        $this->assertTrue($context->spanId == 1562237095801441413);
        $this->assertTrue($context->flags == 1);
    }


    public function testExtractReturnsNull(){
        $jaeger = new JaegerPropagator();
        $carrier = [];

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertNull($context);
    }
}
