<?php
namespace JaegerPhp\ThriftGen\Agent;

use JaegerPhp\Helper;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Type\TMessageType;
use Thrift\Type\TType;


class AgentClient
{

    public static $handleProto = null;

    public function buildThrift($data)
    {
        $tran = new TMemoryBuffer(Helper::UDP_PACKET_MAX_LENGTH);
        self::$handleProto = new TCompactProtocol($tran);

        self::$handleProto->writeMessageBegin('emitBatch', TMessageType::ONEWAY, 1);

        self::$handleProto->writeStructBegin('emitBatch_args');

        $this->handleBatch($data);

        self::$handleProto->writeFieldStop();
        self::$handleProto->writeStructEnd();

        self::$handleProto->writeMessageEnd();

        $msg = $tran->read(Helper::UDP_PACKET_MAX_LENGTH);
        $msg = str_replace(Helper::UDP_PACKET_MAX_LENGTH, "", $msg);
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $len = strlen($msg);
        socket_sendto($sock, $msg, $len, 0, '172.17.0.5', 5775);
        socket_close($sock);
    }


    public function handleBatch($data)
    {

        self::$handleProto->writeFieldBegin("batch", TType::STRUCT, 1);

        self::$handleProto->writeStructBegin("Batch");

        $this->handleProcess($data);

        $this->handleSpans($data);

        self::$handleProto->writeFieldStop();
        self::$handleProto->writeStructEnd();

        self::$handleProto->writeFieldEnd();
    }


    public function handleSpans($data)
    {
        self::$handleProto->writeFieldBegin("spans", TType::LST, 2);
        self::$handleProto->writeListBegin(TType::STRUCT, count($data['spans']));

        foreach ($data['spans'] as $span) {
            $this->handleSpan($span);
        }

        self::$handleProto->writeListEnd();
        self::$handleProto->writeFieldEnd();
    }


    public function handleSpan($span)
    {
        self::$handleProto->writeStructBegin("Span");

        self::$handleProto->writeFieldBegin('traceIdLow', TType::I64, 1);
        self::$handleProto->writeI64($span['traceIdLow']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin('traceIdHigh', TType::I64, 2);
        self::$handleProto->writeI64($span['traceIdHigh']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin('spanId', TType::I64, 3);
        self::$handleProto->writeI64($span['spanId']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin('parentSpanId', TType::I64, 4);
        self::$handleProto->writeI64($span['parentSpanId']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin('operationName', TType::STRING, 5);
        self::$handleProto->writeString($span['operationName']);
        self::$handleProto->writeFieldEnd();

        if (isset($span['references'])) {
            $this->handleSpanRefes($span['references']);
        }

        self::$handleProto->writeFieldBegin('flags', TType::I32, 7);
        self::$handleProto->writeI32($span['flags']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin('startTime', TType::I64, 8);
        self::$handleProto->writeI64($span['startTime']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin('duration', TType::I64, 9);
        self::$handleProto->writeI64($span['duration']);
        self::$handleProto->writeFieldEnd();

        if (isset($span['tags'])) {
            $this->handleSpanTags($span['tags']);
        }

        if (isset($span['logs'])) {
            $this->handleSpanLogs($span['logs']);
        }

        self::$handleProto->writeFieldStop();
        self::$handleProto->writeStructEnd();
    }


    public function handleSpanLogs($logs)
    {
        self::$handleProto->writeFieldBegin('logs', TType::LST, 11);
        self::$handleProto->writeListBegin(TType::STRUCT, count($logs));

        foreach ($logs as $log) {
            $this->handleLog($log);
        }

        self::$handleProto->writeListEnd();
        self::$handleProto->writeFieldEnd();
    }


    public function handleLog($log)
    {
        self::$handleProto->writeStructBegin("Log");

        self::$handleProto->writeFieldBegin('timestamp', TType::I64, 1);
        self::$handleProto->writeI64($log['timestamp']);
        self::$handleProto->writeFieldEnd();

        $this->handleLogFields($log['fields']);


        self::$handleProto->writeFieldStop();
        self::$handleProto->writeStructEnd();
    }


    public function handleLogFields($fields)
    {
        self::$handleProto->writeFieldBegin('fields', TType::LST, 2);
        self::$handleProto->writeListBegin(TType::STRUCT, count($fields));

        foreach ($fields as $field) {
            $this->handleTag($field);
        }

        self::$handleProto->writeListEnd();
        self::$handleProto->writeFieldEnd();
    }


    public function handleSpanTags($tags)
    {
        self::$handleProto->writeFieldBegin('tags', TType::LST, 10);
        self::$handleProto->writeListBegin(TType::STRUCT, count($tags));

        foreach ($tags as $tag) {
            $this->handleTag($tag);
        }

        self::$handleProto->writeListEnd();
        self::$handleProto->writeFieldEnd();
    }


    public function handleSpanRefes($references)
    {
        self::$handleProto->writeFieldBegin('references', TType::LST, 6);
        self::$handleProto->writeListBegin(TType::STRUCT, count($references));
        foreach ($references as $refe) {
            $this->handleSpanRefe($refe);
        }

        self::$handleProto->writeListEnd();
        self::$handleProto->writeFieldEnd();
    }


    public function handleSpanRefe($refe)
    {
        self::$handleProto->writeStructBegin("SpanRef");

        self::$handleProto->writeFieldBegin("refType", TType::I32, 1);
        self::$handleProto->writeI32($refe['refType']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin("traceIdLow", TType::I64, 2);
        self::$handleProto->writeI64($refe['traceIdLow']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin("traceIdHigh", TType::I64, 3);
        self::$handleProto->writeI64($refe['traceIdHigh']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldBegin("spanId", TType::I64, 4);
        self::$handleProto->writeI64($refe['spanId']);
        self::$handleProto->writeFieldEnd();

        self::$handleProto->writeFieldStop();
        self::$handleProto->writeStructEnd();
    }


    public function handleProcess($data)
    {

        self::$handleProto->writeFieldBegin("process", TType::STRUCT, 1);

        $this->handleProcessProcess($data);

        self::$handleProto->writeFieldEnd();
    }


    public function handleProcessProcess($data)
    {

        self::$handleProto->writeStructBegin("Process");

        $this->handleProcessSName($data);
        $this->handleProcessTags($data);

        self::$handleProto->writeFieldStop();
        self::$handleProto->writeStructEnd();
    }


    public function handleProcessSName($data)
    {
        self::$handleProto->writeFieldBegin("serviceName", TType::STRING, 1);

        self::$handleProto->writeString($data["process"]["serviceName"]);

        self::$handleProto->writeFieldEnd();
    }


    public function handleProcessTags($data)
    {
        self::$handleProto->writeFieldBegin("tags", TType::LST, 2);

        self::$handleProto->writeListBegin(TType::STRUCT, count($data["process"]["tags"]));
        if (isset($data['process']['tags'])) {
            foreach ($data['process']['tags'] as $tag) {
                $this->handleTag($tag);
            }
        }

        self::$handleProto->writeListEnd();
        self::$handleProto->writeFieldEnd();
    }


    public function handleTag($tag)
    {

        self::$handleProto->writeStructBegin("Tag");

        if (isset($tag['key'])) {
            self::$handleProto->writeFieldBegin("key", TType::STRING, 1);
            self::$handleProto->writeString(strval($tag['key']));
            self::$handleProto->writeFieldEnd();
        }

        if (isset($tag['vType'])) {
            self::$handleProto->writeFieldBegin('vType', TType::I32, 2);
            self::$handleProto->writeI32(intval($tag['vType']));
            self::$handleProto->writeFieldEnd();
        }

        if (isset($tag['vStr'])) {
            self::$handleProto->writeFieldBegin('vStr', TType::STRING, 3);
            self::$handleProto->writeString($tag['vStr']);
            self::$handleProto->writeFieldEnd();
        }

        if (isset($tag['vDouble'])) {
            self::$handleProto->writeFieldBegin('vDouble', TType::DOUBLE, 4);
            self::$handleProto->writeDouble($tag['vDouble']);
            self::$handleProto->writeFieldEnd();
        }

        if (isset($tag['vBool'])) {
            self::$handleProto->writeFieldBegin('vBool', TType::BOOL, 5);
            self::$handleProto->writeBool($tag['vBool']);
            self::$handleProto->writeFieldEnd();
        }

        if (isset($tag['vLong'])) {
            self::$handleProto->writeFieldBegin('vLong', TType::I64, 6);
            self::$handleProto->writeI64($tag['vLong']);
            self::$handleProto->writeFieldEnd();
        }

        if (isset($tag['vBinary'])) {
            self::$handleProto->writeFieldBegin('vBinary', TType::STRING, 7);
            self::$handleProto->writeByte($tag['vBinary']);
            self::$handleProto->writeFieldEnd();
        }

        self::$handleProto->writeFieldStop();
        self::$handleProto->writeStructEnd();
    }
}
?>