<?php

namespace Jaeger\Reporter;

use Jaeger\Jaeger;
use Jaeger\JSpan;
use Jaeger\Transport\Transport;

class RemoteReporter implements Reporter{

    public $tran = null;

    public function __construct(Transport $tran)
    {
        $this->tran = $tran;
    }

    public function report(Jaeger $jaeger)
    {
        $this->tran->append($jaeger);
    }


    public function close()
    {
        $this->tran->flush();
    }
}