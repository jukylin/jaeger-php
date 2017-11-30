<?php

namespace Jaeger;

use Jaeger\ThriftGen\Agent\AgentClient;

/**
 * 把数据发射到 jaeger-agent
 * Class UdpClient
 * @package Jaeger
 */

class UdpClient{

    private $host = '';

    private $post = '';

    public function __construct($hostPost){
        list($this->host, $this->post) = explode(":", $hostPost);
    }


    /**
     * 发送数据
     * @param $batch
     * @return bool
     */
    public function EmitBatch($batch){
        $buildThrift = (new AgentClient())->buildThrift($batch);
        if(isset($buildThrift['len']) && $buildThrift['len']) {
            $len = $buildThrift['len'];
            $enitThrift = $buildThrift['thriftStr'];
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_sendto($sock, $enitThrift, $len, 0, $this->host, $this->post);
            socket_close($sock);

            return true;
        }else{
            return false;
        }
    }
}