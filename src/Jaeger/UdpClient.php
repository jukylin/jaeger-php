<?php

namespace Jaeger;

use Jaeger\Thrift\AgentClient;

/**
 * 把数据发射到 jaeger-agent
 * Class UdpClient
 * @package Jaeger
 */

class UdpClient{

    private $host = '';

    private $post = '';

    private $socket = '';

    public function __construct($hostPost){
        list($this->host, $this->post) = explode(":", $hostPost);
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }


    /**
     * @return bool
     */
    public function isOpen(){
        return $this->socket !== null;
    }


    /**
     * 发送数据
     * @param $batch
     * @return bool
     */
    public function emitBatch($batch){
        $buildThrift = (new AgentClient())->buildThrift($batch);
        if(isset($buildThrift['len']) && $buildThrift['len'] && $this->isOpen()) {
            $len = $buildThrift['len'];
            $enitThrift = $buildThrift['thriftStr'];
            $res = socket_sendto($this->socket, $enitThrift, $len, 0, $this->host, $this->post);
            if($res === false) {
                throw new \Exception("emit failse");
            }

            return true;
        }else{
            return false;
        }
    }


    public function close(){
        socket_close($this->socket);
        $this->socket = null;
    }
}