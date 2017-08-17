<?php

namespace JaegerPhp\ThriftGen\Agent;

use Thrift\Protocol\TProtocol;

class Tags implements TStruct{

    public static $tptl = null;

    public $tags = null;

    public function __construct($tags)
    {
        $this->tags = $tags;
    }


    public function write(TProtocol $t){

        self::$tptl = $t;

        foreach($this->tags as $tag) {

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
    }


    public function read(TProtocol $t){

    }
}