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

use Jaeger\Transport\TransportUdp;
use PHPUnit\Framework\TestCase;
use Jaeger\Jaeger;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\ScopeManager;

class TransportUdpTest extends TestCase
{
    /**
     * @var TransportUdp|null
     */
    public $tran = null;

    /**
     * @var Jaeger|null
     */
    public $tracer = null;

    public function setUp()
    {
        $this->tran = new TransportUdp('localhost:6831');

        $reporter = new RemoteReporter($this->tran);
        $sampler = new ConstSampler();
        $scopeManager = new ScopeManager();

        $this->tracer = new Jaeger('jaeger', $reporter, $sampler, $scopeManager);
    }

    public function testBuildAndCalcSizeOfProcessThrift()
    {
        $span = $this->tracer->startSpan('BuildAndCalcSizeOfProcessThrift');
        $span->finish();
        $this->tran->buildAndCalcSizeOfProcessThrift($this->tracer);
        $this->assertEquals(95, $this->tran->procesSize);
    }

    public function testSpanIsTooLarge()
    {
        $this->tran::$maxSpanBytes = 50;
        $span = $this->tracer->startSpan('SpanIsTooLarge');
        $span->finish();
        $this->tran->append($this->tracer);
        $this->assertEquals(0, $this->tran->flushSpanNum);
    }

    public function testSplitEmit()
    {
        $i = 0;
        $this->tran::$maxSpanBytes = 150;
        $span = $this->tracer->startSpan('SplitEmit1');
        $i++;
        $span->finish();

        $span = $this->tracer->startSpan('SplitEmit2');
        $i++;
        $span->finish();

        $span = $this->tracer->startSpan('SplitEmit3');
        $i++;
        $span->finish();

        $this->tran->append($this->tracer);
        $this->assertEquals(3, $this->tran->flushSpanNum);
    }
}
