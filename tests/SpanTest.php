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

use Jaeger\SpanContext;
use OpenTracing\NoopSpanContext;
use Jaeger\Span;
use PHPUnit\Framework\TestCase;

class SpanTest extends TestCase
{

    public function testOverwriteOperationName(){
        $span = new Span('test', new NoopSpanContext(), []);
        $span->overwriteOperationName('test2');
        $this->assertTrue($span->getOperationName() == 'test2');
    }


    public function testAddTags(){
        $span = new Span('test', new NoopSpanContext(), []);
        $span->setTag('test', 'test');
        $this->assertTrue((isset($span->tags['test']) && $span->tags['test'] == 'test'));
    }


    public function testFinish(){
        $span = new Span('test', new NoopSpanContext(), []);
        $span->setTag('test', 'test');
        $span->finish();
        $this->assertTrue(!empty($span->finishTime) && !empty($span->duration));
    }


    public function testGetContext(){
        $span = new Span('test', new NoopSpanContext(), []);
        $spanContext = $span->getContext();
        $this->assertInstanceOf(NoopSpanContext::class, $spanContext);
    }



    public function testLog(){
        $span = new Span('test', new NoopSpanContext(), []);
        $logs = [
            'msg' => 'is test',
            'msg2' => 'is msg 2'
        ];
        $span->log($logs);
        $this->assertTrue(count($span->logs) == 1);
    }


    public function testGetBaggageItem(){
        $span = new Span('test', new SpanContext(0, 0, 0), []);
        $span->addBaggageItem('version', '2.0.0');

        $version =  $span->getBaggageItem('version');
        $this->assertEquals('2.0.0', $version);

        $service = $span->getBaggageItem('service');
        $this->assertNull($service);
    }
}