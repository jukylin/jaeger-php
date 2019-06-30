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

use Thrift\Protocol\TProtocol;
use Thrift\Type\TType;

class Process implements TStruct{

    public static $tptl = null;

    public static $serverName = '';

    public static $thriftTags = [];

    public static $wrote = '';

    public function __construct($processThrift)
    {
        self::$serverName = isset($processThrift['serverName']) ? $processThrift['serverName'] : '';
        self::$thriftTags = isset($processThrift['tags']) ? $processThrift['tags'] : '';
        self::$wrote = isset($processThrift['wrote']) ? $processThrift['wrote'] : '';
    }


    public function write(TProtocol $t){
        self::$tptl = $t;

        if(self::$wrote){
            $tran = self::$tptl->getTransport();
            $tran->write(self::$wrote);
        } else {

            self::$tptl->writeStructBegin("Process");

            $this->handleProcessSName();
            $this->handleProcessTags();
            
            self::$tptl->writeFieldStop();
            self::$tptl->writeStructEnd();
        }


    }


    private function handleProcessSName()
    {
        self::$tptl->writeFieldBegin("serverName", TType::STRING, 1);

        self::$tptl->writeString(self::$serverName);

        self::$tptl->writeFieldEnd();
    }


    private function handleProcessTags()
    {
        if(count(self::$thriftTags) > 0) {
            self::$tptl->writeFieldBegin("tags", TType::LST, 2);
            self::$tptl->writeListBegin(TType::STRUCT, count(self::$thriftTags));

            $tagsObj = Tags::getInstance();
            $tagsObj->setThriftTags(self::$thriftTags);
            $tagsObj->write(self::$tptl);

            self::$tptl->writeListEnd();
            self::$tptl->writeFieldEnd();
        }
    }


    public function read(TProtocol $t){}
}