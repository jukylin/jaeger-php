<?php

namespace JaegerPhp\Reporter;

use JaegerPhp\JSpan;

interface Reporter{

    public function report(JSpan $span);

    public function close();
}
