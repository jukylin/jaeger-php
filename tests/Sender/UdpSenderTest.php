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

use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\Sender\UdpSender;
use PHPUnit\Framework\TestCase;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TMemoryBuffer;

class UdpSenderTest extends TestCase
{
    /**
     * @var UdpSender|null
     */
    public $udpSender = null;

    /**
     * @var AgentClient|null
     */
    public $agentClient = null;

    /**
     * @var TMemoryBuffer|null
     */
    public $tran = null;

    /**
     * @var TCompactProtocol|null
     */
    public $protocol = null;

    public function setUp()
    {
        $this->tran = new TMemoryBuffer();
        $this->protocol = new TCompactProtocol($this->tran);
        $this->agentClient = (new AgentClient($this->protocol, null));
        $this->udpSender = new UdpSender('localhost:6831', $this->agentClient, $this->tran);
    }

    public function testIsOpen()
    {
        $this->assertTrue($this->udpSender->isOpen());
    }


    public function testClose()
    {
        $this->udpSender->close();
        $this->assertFalse($this->udpSender->isOpen());
    }


    public function testEmitBatch()
    {
        $span = new \Jaeger\Thrift\Span(
            [
                'traceIdLow' => 1609214197859399756,
                'traceIdHigh' => 1609214197860113544,
                'spanId' => 1609214197859399756,
                'parentSpanId' => 0,
                'operationName' => 'test',
                'flags' => 1,
                'startTime' => 1609214197860775,
                'duration' => 3216877,
                'tags' => [],
                'logs' => [],
            ]
        );

        $batch = new \Jaeger\Thrift\Batch(
            [
                'process' => new \Jaeger\Thrift\Process([
                    'serviceName' => 'EmitBatch',
                    'tags' => [
                        (new \Jaeger\Thrift\Tag([
                            'key' => 'peer.ipv4',
                            'vType' => 0,
                            'vStr' => '0.0.0.0',
                        ])),
                        (new \Jaeger\Thrift\Tag([
                            'key' => 'peer.port',
                            'vType' => 0,
                            'vStr' => '80',
                        ])),
                        (new \Jaeger\Thrift\Tag([
                            'key' => 'sampler.type',
                            'vType' => 0,
                            'vStr' => 'const',
                        ])),
                        (new \Jaeger\Thrift\Tag([
                            'key' => 'sampler.param',
                            'vType' => 2,
                            'vBool' => true,
                        ])),
                    ],
                ]),
                'spans' => [$span],
            ]
        );
        $this->assertTrue($this->udpSender->emitBatch($batch));
    }
}
