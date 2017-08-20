<?php

namespace JaegerPhp\Transport;

interface Transport {
    public function append($thriftSpan);

    public function flush();
}