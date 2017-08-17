<?php

namespace JaegerPhp\Transport;


use JaegerPhp\Helper;
use JaegerPhp\JSpan;
use JaegerPhp\ThriftGen\Agent\JaegerThriftSpan;
use JaegerPhp\ThriftGen\Agent\TStruct;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Protocol\TCompactProtocol;

class TransportUdp implements Transport{

    private $process = null;

    private $bufferSize = 0;

    private $tran = null;

    public $agentServerHostPort = '0.0.0.0:5775';

    public $thriftProtocol = null;


    public function __construct($hostport = '', $maxPacketSize = '')
    {
        if($hostport == ""){
            $hostport = $this->agentServerHostPort;
        }

        if($maxPacketSize == 0){
            $maxPacketSize = Helper::UDP_PACKET_MAX_LENGTH;
        }

        $this->tran = new TMemoryBuffer($maxPacketSize);
        $this->thriftProtocol = new TCompactProtocol($this->tran);
    }


    public function append(JSpan $span){
        if($this->process == null){
            $this->process = (new JaegerThriftSpan())->buildJaegerProcessThrift($span->jaegerObj);
            $this->calcSizeOfSerializedThrift($this->process);
        }
        exit;
        $jaeger = $span->jaegerObj;
        print_r($jaeger::$serviceName);exit;
    }



    private function calcSizeOfSerializedThrift(TStruct $ts){
        $ts->write($this->thriftProtocol);
        echo $this->tran->getBuffer();exit;
    }


    public function flush(){

    }
}