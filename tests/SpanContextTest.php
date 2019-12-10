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
use Jaeger\SpanContext;

class SpanContextTest extends TestCase
{


    public function getSpanContext(){
        return new SpanContext(1, 1, 1, null, 1);
    }


    public function testNew(){
        $spanContext = $this->getSpanContext();
        $this->assertInstanceOf(SpanContext::class, $spanContext);
    }


    public function testWithBaggageItem(){
        $spanContext = $this->getSpanContext();
        $res = $spanContext->withBaggageItem('version', '2.0.0');
        $this->assertTrue($res);
    }


    public function testGetBaggageItem(){
        $spanContext = $this->getSpanContext();
        $res = $spanContext->withBaggageItem('version', '2.0.0');

        $version = $spanContext->getBaggageItem('version');
        $this->assertTrue($res == $version);

        $service = $spanContext->getBaggageItem('service');
        $this->assertNull($service);
    }


    public function testBuildString(){
        $spanContext = $this->getSpanContext();
        $spanContext->traceIdLow = 1;
        $this->assertTrue($spanContext->buildString() == '1:1:1:1');

        $spanContext->traceIdHigh = 1;
        $this->assertTrue($spanContext->buildString() == '10000000000000001:1:1:1');
    }


    public function testSpanIdToString(){
        $spanContext = $this->getSpanContext();
        $this->assertTrue($spanContext->spanIdToString() == '1');

        $spanContext->spanId = "111111";
        $this->assertTrue($spanContext->spanIdToString() == '1b207');
    }


    public function testTraceIdLowToString(){
        $spanContext = $this->getSpanContext();
        $spanContext->traceIdLow = "111111";
        $this->assertTrue($spanContext->traceIdLowToString() == '1b207');

        $spanContext->traceIdHigh = "111111";
        $this->assertTrue($spanContext->traceIdLowToString() == '1b207000000000001b207');
    }


    public function testTraceIdToString(){
        $spanContext = $this->getSpanContext();
        $spanContext->traceIdToString('1b207000000000001b207');
        $this->assertTrue($spanContext->traceIdLow == '111111');
        $this->assertTrue($spanContext->traceIdHigh == '1954685383581106176');

        $spanContext->traceIdLow = null;
        $spanContext->traceIdHigh = null;
        $spanContext->traceIdToString('1b207');
        $this->assertTrue($spanContext->traceIdLow == '111111');
        $this->assertTrue(!$spanContext->traceIdHigh);
    }



}