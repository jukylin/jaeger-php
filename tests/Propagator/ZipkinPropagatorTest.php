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
use Jaeger\Propagator\ZipkinPropagator;
use Jaeger\SpanContext;
use OpenTracing\Formats;
use PHPUnit\Framework\TestCase;

class ZipkinPropagatorTest extends TestCase
{
    public function getSpanContext()
    {
        return new SpanContext(1562237095801441413, 0, 1, null, 1);
    }

    public function testInject()
    {
        $context = $this->getSpanContext();
        $context->traceIdLow = 1562237095801441413;
        $zipkin = new ZipkinPropagator();
        $carrier = [];

        $zipkin->inject($context, Formats\TEXT_MAP, $carrier);

        $this->assertTrue('15ae2e5c8e2ecc85' == $carrier[Constants\X_B3_TRACEID]);
        $this->assertTrue(0 == $carrier[Constants\X_B3_PARENT_SPANID]);
        $this->assertTrue('15ae2e5c8e2ecc85' == $carrier[Constants\X_B3_SPANID]);
        $this->assertTrue(1 == $carrier[Constants\X_B3_SAMPLED]);
    }

    public function testInject128Bit()
    {
        $context = $this->getSpanContext();
        $context->traceIdLow = 1562289663898779811;
        $context->traceIdHigh = 1562289663898881723;

        $zipkin = new ZipkinPropagator();
        $carrier = [];

        $zipkin->inject($context, Formats\TEXT_MAP, $carrier);

        $this->assertTrue('15ae5e2c04f50ebb15ae5e2c04f380a3' == $carrier[Constants\X_B3_TRACEID]);
        $this->assertTrue(0 == $carrier[Constants\X_B3_PARENT_SPANID]);
        $this->assertTrue('15ae2e5c8e2ecc85' == $carrier[Constants\X_B3_SPANID]);
        $this->assertTrue(1 == $carrier[Constants\X_B3_SAMPLED]);
    }

    public function testExtract()
    {
        $zipkin = new ZipkinPropagator();
        $carrier = [];
        $carrier[Constants\X_B3_TRACEID] = '15ae2e5c8e2ecc85';
        $carrier[Constants\X_B3_PARENT_SPANID] = 1;
        $carrier[Constants\X_B3_SPANID] = '15ae2e5c8e2ecc85';
        $carrier[Constants\X_B3_SAMPLED] = 1;

        $context = $zipkin->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue('1562237095801441413' == $context->traceIdLow);
        $this->assertTrue(1 == $context->parentId);
        $this->assertTrue('1562237095801441413' == $context->spanId);
        $this->assertTrue(1 == $context->flags);
    }

    public function testExtract128Bit()
    {
        $zipkin = new ZipkinPropagator();
        $carrier = [];
        $carrier[Constants\X_B3_TRACEID] = '15ae5e2c04f50ebb15ae5e2c04f380a3';
        $carrier[Constants\X_B3_PARENT_SPANID] = 0;
        $carrier[Constants\X_B3_SPANID] = '15ae5e2c04f380a3';
        $carrier[Constants\X_B3_SAMPLED] = 1;

        $context = $zipkin->extract(Formats\TEXT_MAP, $carrier);
        $this->assertTrue(1562289663898779811 == $context->traceIdLow);
        $this->assertTrue(1562289663898881723 == $context->traceIdHigh);
        $this->assertTrue(0 == $context->parentId);
        $this->assertTrue(1562289663898779811 == $context->spanId);
        $this->assertTrue(1 == $context->flags);
    }

    public function testExtractReturnsNull()
    {
        $jaeger = new ZipkinPropagator();
        $carrier = [];

        $context = $jaeger->extract(Formats\TEXT_MAP, $carrier);
        $this->assertNull($context);
    }
}
