<?php

namespace Jaeger\Reporter;

use Jaeger\Jaeger;

interface Reporter{

    public function report(Jaeger $jaeger);

    public function close();
}
