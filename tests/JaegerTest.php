<?php

use PHPUnit\Framework\TestCase;

use Jaeger\Jaeger;
use Jaeger\Reporter\RemoteReporter;
use Jaeger\Sampler\ConstSampler;
use Jaeger\Transport\TransportUdp;

final class JaegerTest extends TestCase
{
    public function testGetEnvTags()
    {
        $tranSport = new TransportUdp();
        $reporter = new RemoteReporter($tranSport);
        $sampler = new ConstSampler();

        $Jaeger = new Jaeger('getEnvTags', $reporter, $sampler);
        $tags = $Jaeger->getEnvTags();

        $this->assertTrue(count($tags) > 0);
    }
}
