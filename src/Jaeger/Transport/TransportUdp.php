<?php

namespace Jaeger\Transport;


use Jaeger\Helper;
use Jaeger\Jaeger;
use Jaeger\ThriftGen\Agent\JaegerThriftSpan;
use Jaeger\ThriftGen\Agent\Process;
use Jaeger\ThriftGen\Agent\Span;
use Jaeger\ThriftGen\Agent\TStruct;
use Jaeger\UdpClient;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Protocol\TCompactProtocol;

class TransportUdp implements Transport{

    const EMITBATCHOVERHEAD = 30;

    private $tran = null;

    public static $hostPort = '';

    // sizeof(Span) * numSpans + processByteSize + emitBatchOverhead <= maxPacketSize
    public static $maxSpanBytes = 0;

    public static $batchs = [];

    public $agentServerHostPort = '0.0.0.0:5775';

    public $thriftProtocol = null;

    public $procesSize = 0;

    public $bufferSize = 0;

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


    public function buildAndCalcSizeOfProcessThrift(Jaeger $jaeger){
        $jaeger->processThrift = (new JaegerThriftSpan())->buildJaegerProcessThrift($jaeger);
        $jaeger->process = (new Process($jaeger->processThrift));
        $this->procesSize = $this->getAndCalcSizeOfSerializedThrift($jaeger->process, $jaeger->processThrift);
        $this->bufferSize += $this->procesSize;
    }


    /**
     * 收集将要发送的追踪信息
     * @param Jaeger $jaeger
     * @return bool
     */
    public function append(Jaeger $jaeger){

        if($jaeger->process == null){
            $this->buildAndCalcSizeOfProcessThrift($jaeger);
        }

        foreach($jaeger->spans as $k => $span){

            $spanThrift = (new JaegerThriftSpan())->buildJaegerSpanThrift($span);

            $agentSpan = Span::getInstance();
            $agentSpan->setThriftSpan($spanThrift);
            $spanSize = $this->getAndCalcSizeOfSerializedThrift($agentSpan, $spanThrift);

            if($spanSize > self::$maxSpanBytes){
                //throw new Exception("Span is too large");
                continue;
            }

            $this->bufferSize += $spanSize;
            if($this->bufferSize > self::$maxSpanBytes){
                $jaeger->spanThrifts[] = $spanThrift;
                self::$batchs[] = ['thriftProcess' => $jaeger->processThrift
                    , 'thriftSpans' => $jaeger->spanThrifts];

                $this->flush();
            }else{
                $jaeger->spanThrifts[] = $spanThrift;
            }
        }

        self::$batchs[] = ['thriftProcess' => $jaeger->processThrift
            , 'thriftSpans' => $jaeger->spanThrifts];

        return true;
    }


    public function resetBuffer(){
        $this->bufferSize = $this->procesSize;
        self::$batchs = [];
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
        $serializedThrift['wrote'] = $this->tran->read(Helper::UDP_PACKET_MAX_LENGTH);
        return $serThriftStrlen;
    }


    /**
     * @return int
     */
    public function flush(){
        $batchNum = count(self::$batchs);
        if ($batchNum <= 0) {
            return 0;
        }

        $spanNum = 0;
        foreach (self::$batchs as $batch){
            $spanNum += count($batch['thriftSpans']);
            (new UdpClient(self::$hostPort))->EmitBatch($batch);
        }

        $this->resetBuffer();
        return $spanNum;
    }
}