<?php

namespace JaegerPhp\Transport;


use JaegerPhp\Helper;
use JaegerPhp\JSpan;
use JaegerPhp\ThriftGen\Agent\JaegerThriftSpan;
use JaegerPhp\ThriftGen\Agent\Process;
use JaegerPhp\ThriftGen\Agent\Span;
use JaegerPhp\ThriftGen\Agent\TStruct;
use JaegerPhp\UdpClient;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Protocol\TCompactProtocol;

class TransportUdp implements Transport{

    const EMITBATCHOVERHEAD = 30;

    private $bufferSize = 0;

    private $tran = null;

    public $agentServerHostPort = '0.0.0.0:5775';

    public $thriftProtocol = null;

    public static $thriftSpans = [];

    public static $process = null;

    public static $processThrift = null;

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

        $this->tran = new TMemoryBuffer($maxPacketSize);
        $this->thriftProtocol = new TCompactProtocol($this->tran);
    }


    public function append(JSpan $Jspan){

        $jts = new JaegerThriftSpan();

        if(self::$process == null){
            self::$processThrift = $jts->buildJaegerProcessThrift($Jspan->jaegerObj);
            self::$process = (new Process(self::$processThrift));
            $this->bufferSize += $this->calcSizeOfSerializedThrift(self::$process);
        }

        $thriftSpan = $jts->buildJaegerThrift($Jspan);
        //有点问题,process的大小重复计算了
        $agentSpan = new Span($thriftSpan);
        $spanSize = $this->calcSizeOfSerializedThrift($agentSpan);

        if($spanSize > self::$maxSpanBytes){
            throw new \Exception("Span is too large");
        }

        $this->bufferSize += $spanSize;
        if($this->bufferSize > self::$maxSpanBytes){
            $this->flush();
            self::$thriftSpans = [];
            self::$thriftSpans[] = $thriftSpan;
            $this->bufferSize = 0;
        }else{
            self::$thriftSpans[] = $thriftSpan;
        }
    }


    /**
     * 计算序列化后的thrift字符长度
     * @param TStruct $ts
     * @return int
     */
    private function calcSizeOfSerializedThrift(TStruct $ts){
        $ts->write($this->thriftProtocol);
        return mb_strlen(str_replace(Helper::UDP_PACKET_MAX_LENGTH, "", $this->tran->getBuffer()));
    }


    public function flush(){
        if(count(self::$thriftSpans) <= 0){
            return 0;
        }

        (new UdpClient(self::$hostPort))->EmitBatch(['thriftProcess' => self::$processThrift
            , 'thriftSpans' => self::$thriftSpans]);
    }
}