<?php

namespace JaegerPhp\Reporter;

use JaegerPhp\Jaeger;

interface Reporter{

    public function report(Jaeger $jaeger);

    public function close();
}
