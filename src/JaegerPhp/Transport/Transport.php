<?php

namespace JaegerPhp\Transport;

use JaegerPhp\Jaeger;

interface Transport {
    public function append(Jaeger $jaeger);

    public function flush();
}