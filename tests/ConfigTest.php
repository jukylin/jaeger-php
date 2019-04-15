<?php

namespace tests;

use Jaeger\Config;
use OpenTracing\NoopTracer;
use PHPUnit\Framework\TestCase;

final class TestConfig extends TestCase
{
    public function testSetDisabled()
    {
        $config = Config::getInstance();
        $config->setDisabled(true);

        $this->assertTrue($config::$disabled == true);
    }

    public function testNoopTracer()
    {
        $config = Config::getInstance();
        $config->setDisabled(true);
        $trace = $config->initTrace('test');

        $this->assertTrue($trace instanceof NoopTracer);
    }
}
