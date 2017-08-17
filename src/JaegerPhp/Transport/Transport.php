<?php

namespace JaegerPhp\Transport;

use JaegerPhp\JSpan;

interface Transport {
    public function append(JSpan $span);

    public function flush();
}