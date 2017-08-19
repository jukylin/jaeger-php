<?php

namespace JaegerPhp\Transport;

use JaegerPhp\JSpan;

interface Transport {
    public function append($thriftSpan);

    public function flush();
}