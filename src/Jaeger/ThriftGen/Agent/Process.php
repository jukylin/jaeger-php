<?php

namespace Jaeger\ThriftGen\Agent;

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


    public function handleProcessSName()
    {
        self::$tptl->writeFieldBegin("serverName", TType::STRING, 1);

        self::$tptl->writeString(self::$serverName);

        self::$tptl->writeFieldEnd();
    }


    public function handleProcessTags()
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