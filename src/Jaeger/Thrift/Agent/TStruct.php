<?php

namespace Jaeger\Thrift\Agent;

use Thrift\Protocol\TProtocol;

interface TStruct {

    public function write(TProtocol $t);

    public function read(TProtocol $t);
}