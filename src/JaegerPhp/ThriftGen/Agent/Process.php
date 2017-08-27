<?php

namespace JaegerPhp\ThriftGen\Agent;

use Thrift\Protocol\TProtocol;
use Thrift\Type\TType;

class Process implements TStruct{

    public static $tptl = null;

    public static $serverName = '';

    public static $tags = [];

    public static $wrote = '';

    public function __construct($processThrift)
    {
        self::$serverName = isset($processThrift['serverName']) ? $processThrift['serverName'] : '';
        self::$tags = isset($processThrift['tags']) ? $processThrift['tags'] : '';
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
        if(count(self::$tags) > 0) {
            self::$tptl->writeFieldBegin("tags", TType::LST, 2);
            self::$tptl->writeListBegin(TType::STRUCT, count(self::$tags));

            (new Tags(self::$tags))->write(self::$tptl);

            self::$tptl->writeListEnd();
            self::$tptl->writeFieldEnd();
        }
    }


    public function read(TProtocol $t){}
}