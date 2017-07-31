<?php

namespace JaegerPhp;

use JaegerPhp\ThriftGen\Agent\AgentClient;

/**
 * 把数据发射到 jaeger-agent
 * Class UdpClient
 * @package JaegerPhp
 */

class UdpClient{

    private $host = '';
    private $post = '';

    public function __construct($host, $post){
        $this->host = $host;
        $this->post = $post;
    }

    public function EmitBatch($batch){
        $enitThrift = (new AgentClient())->buildThrift($batch);

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $len = strlen($enitThrift);
        socket_sendto($sock, $enitThrift, $len, 0, $this->host, $this->post);
        socket_close($sock);
    }
}