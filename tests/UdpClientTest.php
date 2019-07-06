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
use Jaeger\UdpClient;
use Jaeger\Thrift\AgentClient;

class UdpClientTest extends TestCase
{
    public $udpClient = null;

    public $agentClient = null;

    public function setUp(){
        $this->agentClient = $this->createMock(AgentClient::class);
        $this->udpClient = new UdpClient('localhost:6831', $this->agentClient);
    }


    public function testIsOpen(){
        $this->assertTrue($this->udpClient->isOpen());
    }


    public function testEmitBatch(){

        $this->agentClient->expects($this->once())->method('buildThrift')
            ->willReturn(['len'=> 3 , 'thriftStr' => 123]);
        $batch = ['thriftProcess' => ''
            , 'thriftSpans' => ''];

        $this->assertTrue($this->udpClient->emitBatch($batch));
    }


    public function testEmitBatchFalse(){
        $batch = ['thriftProcess' => ''
            , 'thriftSpans' => ''];

        $this->agentClient->expects($this->any())->method('buildThrift')
            ->willReturn(['thriftStr' => 123]);

        $this->assertFalse($this->udpClient->emitBatch($batch));

        $this->udpClient->close();
        $this->agentClient->expects($this->any())->method('buildThrift')
            ->willReturn(['len'=> 3 , 'thriftStr' => 123]);


        $this->assertFalse($this->udpClient->emitBatch($batch));
    }

    public function testClose(){
        $this->udpClient->close();
        $this->assertFalse($this->udpClient->isOpen());
    }
}