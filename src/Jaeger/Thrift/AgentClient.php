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

namespace Jaeger\Thrift;

use Thrift\Transport\TMemoryBuffer;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Type\TMessageType;
use Thrift\Type\TType;
use Jaeger\Constants;

class AgentClient
{

    public static $tptl = null;

    public function buildThrift($batch)
    {
        $tran = new TMemoryBuffer();
        self::$tptl = new TCompactProtocol($tran);
        self::$tptl->writeMessageBegin('emitBatch', TMessageType::ONEWAY, 1);
        self::$tptl->writeStructBegin('emitBatch_args');

        $this->handleBatch($batch);
        self::$tptl->writeFieldStop();

        self::$tptl->writeStructEnd();
        self::$tptl->writeMessageEnd();

        $batchLen = $tran->available();
        $batchThriftStr = $tran->read(Constants\UDP_PACKET_MAX_LENGTH);

        return ['len' => $batchLen, 'thriftStr' => $batchThriftStr];
    }


    private function handleBatch($batch)
    {
        self::$tptl->writeFieldBegin("batch", TType::STRUCT, 1);

        self::$tptl->writeStructBegin("Batch");
        $this->handleThriftProcess($batch['thriftProcess']);
        $this->handleThriftSpans($batch['thriftSpans']);

        self::$tptl->writeFieldStop();
        self::$tptl->writeStructEnd();

        self::$tptl->writeFieldEnd();
    }


    private function handleThriftSpans($thriftSpans)
    {
        self::$tptl->writeFieldBegin("spans", TType::LST, 2);
        self::$tptl->writeListBegin(TType::STRUCT, count($thriftSpans));

        $agentSpan = Span::getInstance();
        foreach ($thriftSpans as $thriftSpan){
            $agentSpan->setThriftSpan($thriftSpan);
            $agentSpan->write(self::$tptl);
        }

        self::$tptl->writeListEnd();
        self::$tptl->writeFieldEnd();
    }


    private function handleThriftProcess($thriftProcess)
    {
        self::$tptl->writeFieldBegin("process", TType::STRUCT, 1);
        (new Process($thriftProcess))->write(self::$tptl);
        self::$tptl->writeFieldEnd();
    }


}
