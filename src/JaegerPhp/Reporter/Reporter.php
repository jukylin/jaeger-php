<?php

namespace JaegerPhp\Reporter;

use JaegerPhp\JSpan;

interface Reporter{

    public function report($thriftSpan);

    public function close();
}
