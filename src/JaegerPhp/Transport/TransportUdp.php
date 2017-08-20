<?php

namespace JaegerPhp\Transport;


use JaegerPhp\Helper;
use JaegerPhp\Jaeger;
use JaegerPhp\ThriftGen\Agent\JaegerThriftSpan;
use JaegerPhp\ThriftGen\Agent\Process;
use JaegerPhp\ThriftGen\Agent\Span;
use JaegerPhp\ThriftGen\Agent\TStruct;
use JaegerPhp\UdpClient;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Protocol\TCompactProtocol;

class TransportUdp implements Transport{

    const EMITBATCHOVERHEAD = 30;

    private $tran = null;

    public $agentServerHostPort = '0.0.0.0:5775';

    public $thriftProtocol = null;

    public static $thriftSpans = [];

    public static $bufferSize = 0;

    public static $process = null;

    public static $processThrift = null;

    public static $processSize = 0;

    public static $hostPort = '';

    // sizeof(Span) * numSpans + processByteSize + emitBatchOverhead <= maxPacketSize
    public static $maxSpanBytes = 0;

    public function __construct($hostport = '', $maxPacketSize = '')
    {
        if($hostport == ""){
            $hostport = $this->agentServerHostPort;
        }
        self::$hostPort = $hostport;

        if($maxPacketSize == 0){
            $maxPacketSize = Helper::UDP_PACKET_MAX_LENGTH;
        }

        self::$maxSpanBytes = $maxPacketSize - self::EMITBATCHOVERHEAD;

        $this->tran = new TMemoryBuffer();
        $this->thriftProtocol = new TCompactProtocol($this->tran);
    }


    public function append($thriftSpan){

        $jts = new JaegerThriftSpan();

        if(self::$process == null){
            self::$processThrift = $jts->buildJaegerProcessThrift(Jaeger::getInstance());
            self::$process = (new Process(self::$processThrift));
            self::$processSize = $this->getAndCalcSizeOfSerializedThrift(self::$process, self::$processThrift);
            self::$bufferSize += self::$processSize;
        }

        $agentSpan = new Span($thriftSpan);
        $spanSize = $this->getAndCalcSizeOfSerializedThrift($agentSpan, $thriftSpan);

        if($spanSize > self::$maxSpanBytes){
            throw new Exception("Span is too large");
        }

        self::$bufferSize += $spanSize;
        if(self::$bufferSize > self::$maxSpanBytes){
            $this->flush();
            self::$thriftSpans = [];
            self::$thriftSpans[] = $thriftSpan;
            self::$bufferSize = self::$processSize;
        }else{
            self::$thriftSpans[] = $thriftSpan;
        }
    }


    /**
     * 获取和计算序列化后的thrift字符长度
     * @param TStruct $ts
     * @param $serializedThrift
     * @return mixed
     */
    private function getAndCalcSizeOfSerializedThrift(TStruct $ts, &$serializedThrift){
        $ts->write($this->thriftProtocol);
        $serThriftStrlen = $this->tran->available();
        //获取后buf清空
        $serializedThrift['writed'] = $this->tran->read(Helper::UDP_PACKET_MAX_LENGTH);
        return $serThriftStrlen;
    }


    public function flush(){
        if(count(self::$thriftSpans) <= 0){
            return 0;
        }

        (new UdpClient(self::$hostPort))->EmitBatch(['thriftProcess' => self::$processThrift
            , 'thriftSpans' => self::$thriftSpans]);
    }
}