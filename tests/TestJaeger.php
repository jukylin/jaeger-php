<?php

require_once '../../autoload.php';

use JaegerPhp\Jaeger;
use JaegerPhp\Reporter\RemoteReporter;
use JaegerPhp\Sampler\ConstSampler;
use JaegerPhp\Transport\TransportUdp;

class TestJaeger extends PHPUnit_Framework_TestCase
{

    public function testGetEnvTags(){

        $tranSport = new TransportUdp();
        $reporter = new RemoteReporter($tranSport);
        $sampler = new ConstSampler();

        $Jaeger = new Jaeger('getEnvTags', $reporter, $sampler);
        $tags = $Jaeger->getEnvTags();

        $this->assertTrue(count($tags) > 0);
    }

}