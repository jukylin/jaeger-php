<?php
namespace JaegerPhp\ThriftGen\Agent;

use JaegerPhp\JSpan;
use Thrift\Protocol\TProtocol;
use Thrift\Type\TType;

class Span implements TStruct{

    public static $thriftSpan = null;

    public static $tptl = null;


    public function __construct($thriftSpan = [])
    {
        self::$thriftSpan = $thriftSpan;
    }


    public function write(TProtocol $t)
    {
        self::$tptl = $t;
        if(isset(self::$thriftSpan['writed']) && self::$thriftSpan['writed']){
            $tran = self::$tptl->getTransport();
            $tran->write(self::$thriftSpan['writed']);
        } else {
            $this->handleSpan(self::$thriftSpan);
        }
    }


    public function read(TProtocol $t)
    {
        // TODO: Implement read() method.
    }


    public function overWriteThriftSpan($thriftSpan){
        self::$thriftSpan = $thriftSpan;
    }


    private function handleSpan($span)
    {
        self::$tptl->writeStructBegin("Span");

        self::$tptl->writeFieldBegin('traceIdLow', TType::I64, 1);
        self::$tptl->writeI64($span['traceIdLow']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin('traceIdHigh', TType::I64, 2);
        self::$tptl->writeI64($span['traceIdHigh']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin('spanId', TType::I64, 3);
        self::$tptl->writeI64($span['spanId']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin('parentSpanId', TType::I64, 4);
        self::$tptl->writeI64($span['parentSpanId']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin('operationName', TType::STRING, 5);
        self::$tptl->writeString($span['operationName']);
        self::$tptl->writeFieldEnd();

        if (isset($span['references'])) {
            $this->handleSpanRefes($span['references']);
        }

        self::$tptl->writeFieldBegin('flags', TType::I32, 7);
        self::$tptl->writeI32($span['flags']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin('startTime', TType::I64, 8);
        self::$tptl->writeI64($span['startTime']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin('duration', TType::I64, 9);
        self::$tptl->writeI64($span['duration']);
        self::$tptl->writeFieldEnd();

        if (isset($span['tags'])) {
            $this->handleSpanTags($span['tags']);
        }

        if (isset($span['logs'])) {
            $this->handleSpanLogs($span['logs']);
        }

        self::$tptl->writeFieldStop();
        self::$tptl->writeStructEnd();
    }


    private function handleSpanLogs($logs)
    {
        self::$tptl->writeFieldBegin('logs', TType::LST, 11);
        self::$tptl->writeListBegin(TType::STRUCT, count($logs));

        foreach ($logs as $log) {
            $this->handleLog($log);
        }

        self::$tptl->writeListEnd();
        self::$tptl->writeFieldEnd();
    }


    private function handleLog($log)
    {
        self::$tptl->writeStructBegin("Log");

        self::$tptl->writeFieldBegin('timestamp', TType::I64, 1);
        self::$tptl->writeI64($log['timestamp']);
        self::$tptl->writeFieldEnd();

        $this->handleLogFields($log['fields']);

        self::$tptl->writeFieldStop();
        self::$tptl->writeStructEnd();
    }


    private function handleLogFields($fields)
    {
        self::$tptl->writeFieldBegin('fields', TType::LST, 2);
        self::$tptl->writeListBegin(TType::STRUCT, count($fields));

        (new Tags($fields))->write(self::$tptl);

        self::$tptl->writeListEnd();
        self::$tptl->writeFieldEnd();
    }


    private function handleSpanTags($tags)
    {
        self::$tptl->writeFieldBegin('tags', TType::LST, 10);
        self::$tptl->writeListBegin(TType::STRUCT, count($tags));

        (new Tags($tags))->write(self::$tptl);

        self::$tptl->writeListEnd();
        self::$tptl->writeFieldEnd();
    }


    private function handleSpanRefes($references)
    {
        self::$tptl->writeFieldBegin('references', TType::LST, 6);
        self::$tptl->writeListBegin(TType::STRUCT, count($references));

        foreach ($references as $refe) {
            $this->handleSpanRefe($refe);
        }

        self::$tptl->writeListEnd();
        self::$tptl->writeFieldEnd();
    }


    private function handleSpanRefe($refe)
    {
        self::$tptl->writeStructBegin("SpanRef");

        self::$tptl->writeFieldBegin("refType", TType::I32, 1);
        self::$tptl->writeI32($refe['refType']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin("traceIdLow", TType::I64, 2);
        self::$tptl->writeI64($refe['traceIdLow']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin("traceIdHigh", TType::I64, 3);
        self::$tptl->writeI64($refe['traceIdHigh']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldBegin("spanId", TType::I64, 4);
        self::$tptl->writeI64($refe['spanId']);
        self::$tptl->writeFieldEnd();

        self::$tptl->writeFieldStop();
        self::$tptl->writeStructEnd();
    }
}