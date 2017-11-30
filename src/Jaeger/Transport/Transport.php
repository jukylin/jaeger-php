<?php

namespace Jaeger\Transport;

use Jaeger\Jaeger;

interface Transport {
    public function append(Jaeger $jaeger);

    public function flush();
}