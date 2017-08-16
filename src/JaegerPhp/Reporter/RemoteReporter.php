<?php

namespace JaegerPhp\Reporter;

use JaegerPhp\JSpan;

class RemoteReporter implements Reporter{

    public function report(JSpan $span)
    {
        print_r($span);exit;
    }


    public function close()
    {
        // TODO: Implement close() method.
    }
}