<?php

namespace JaegerPhp\Reporter;

use JaegerPhp\JSpan;
use JaegerPhp\Transport\Transport;

class RemoteReporter implements Reporter{

    public $tran = null;

    public function __construct(Transport $tran)
    {
        $this->tran = $tran;
    }

    public function report(JSpan $span)
    {
        $this->tran->append($span);
    }


    public function close()
    {
        $this->tran->flush();
    }
}