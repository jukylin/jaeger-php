<?php

namespace JaegerPhp\ThriftGen\Agent;

use Thrift\Protocol\TProtocol;
use Thrift\Type\TType;

class Process implements TStruct{

    public static $tptl = null;

    public static $serviceName = '';

    public static $tags = [];

    public static $writed = '';

    public function __construct($processThrift)
    {
        self::$serviceName = isset($processThrift['serviceName']) ? $processThrift['serviceName'] : '';
        self::$tags = isset($processThrift['tags']) ? $processThrift['tags'] : '';
        self::$writed = isset($processThrift['writed']) ? $processThrift['writed'] : '';
    }


    public function write(TProtocol $t){
        self::$tptl = $t;

        if(self::$writed){
            $tran = self::$tptl->getTransport();
            $tran->write(self::$writed);
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
        self::$tptl->writeFieldBegin("serviceName", TType::STRING, 1);

        self::$tptl->writeString(self::$serviceName);

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