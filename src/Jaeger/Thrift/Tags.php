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

class Tags implements TStruct{

    public static $tptl = null;

    public static $instance = null;

    public $tags = null;

    public $thriftTags = null;


    private function __construct(){

    }


    private function __clone(){

    }


    public static function getInstance(){
        if(!(self::$instance instanceof self)){
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function write(TProtocol $t){

        self::$tptl = $t;

        if(empty($this->thriftTags)){
            return false;
        }

        foreach($this->thriftTags as $tag) {

            self::$tptl->writeStructBegin("Tag");

            if (isset($tag['key'])) {
                self::$tptl->writeFieldBegin("key", TType::STRING, 1);
                self::$tptl->writeString(strval($tag['key']));
                self::$tptl->writeFieldEnd();
            }

            if (isset($tag['vType'])) {
                self::$tptl->writeFieldBegin('vType', TType::I32, 2);
                self::$tptl->writeI32(Types::stringToTagType($tag['vType']));
                self::$tptl->writeFieldEnd();
            }

            if (isset($tag['vStr'])) {
                self::$tptl->writeFieldBegin('vStr', TType::STRING, 3);
                self::$tptl->writeString($tag['vStr']);
                self::$tptl->writeFieldEnd();
            }

            if (isset($tag['vDouble'])) {
                self::$tptl->writeFieldBegin('vDouble', TType::DOUBLE, 4);
                self::$tptl->writeDouble($tag['vDouble']);
                self::$tptl->writeFieldEnd();
            }

            if (isset($tag['vBool'])) {
                self::$tptl->writeFieldBegin('vBool', TType::BOOL, 5);
                self::$tptl->writeBool($tag['vBool']);
                self::$tptl->writeFieldEnd();
            }

            if (isset($tag['vLong'])) {
                self::$tptl->writeFieldBegin('vLong', TType::I64, 6);
                self::$tptl->writeI64($tag['vLong']);
                self::$tptl->writeFieldEnd();
            }

            if (isset($tag['vBinary'])) {
                self::$tptl->writeFieldBegin('vBinary', TType::STRING, 7);
                self::$tptl->writeByte($tag['vBinary']);
                self::$tptl->writeFieldEnd();
            }

            self::$tptl->writeFieldStop();
            self::$tptl->writeStructEnd();
        }


        return true;
    }


    public function read(TProtocol $t){

    }


    public function setThriftTags($thriftTags){
        $this->thriftTags = $thriftTags;
    }


    public function setTags($tags){
        $this->tags = $tags;
    }


    public function buildTags(){

        $thriftTags = [];
        if(empty($this->tags)){
            return $thriftTags;
        }

        foreach ($this->tags as $k => $v){
            switch(gettype($v)){
                case "string":
                    $thriftTags[] = [
                        'key' => $k,
                        'vType' => 'STRING',
                        'vStr' => $v,
                    ];
                    break;
                case "boolean":
                    $thriftTags[] = [
                        'key' => $k,
                        'vType' => 'BOOL',
                        'vBool' => $v,
                    ];
                    break;
                case "double":
                    $thriftTags[] = [
                        'key' => $k,
                        'vType' => 'DOUBLE',
                        'vDouble' => $v,
                    ];
                    break;
                case "integer":
                    $thriftTags[] = [
                        'key' => $k,
                        'vType' => 'DOUBLE',
                        'vDouble' => $v,
                    ];
                    break;
                case "array":
                    $thriftTags[] = [
                        'key' => $k,
                        'vType' => 'STRING',
                        'vStr' => json_encode($v, JSON_UNESCAPED_UNICODE),
                    ];
                    break;
                default:
                    $thriftTags[] = [
                        'key' => $k,
                        'vType' => 'STRING',
                        'vStr' => is_string($v),
                    ];
            }
        }


        return $thriftTags;
    }
}