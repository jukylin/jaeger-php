<?php

require_once '../../autoload.php';

use Jaeger\Jaeger;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Transport\TransportUdp;

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