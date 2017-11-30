<?php

namespace Jaeger\ThriftGen\Agent;

use Thrift\Protocol\TProtocol;

interface TStruct {

    public function write(TProtocol $t);

    public function read(TProtocol $t);
}