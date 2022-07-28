<?php
/*
 * Copyright (c) 2019, The Jaeger Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

namespace tests;

use Jaeger\Config;
use Jaeger\Reporter\NullReporter;
use OpenTracing\NoopTracer;
use OpenTracing\Tracer;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testSetDisabled()
    {
        $config = Config::getInstance();
        $config->setDisabled(true);

        $this->assertTrue(true == $config::$disabled);
    }

    public function testNoopTracer()
    {
        $config = Config::getInstance();
        $config->setDisabled(true);
        $trace = $config->initTracer('test');

        $this->assertTrue($trace instanceof NoopTracer);
    }

    public function testflushMulTracer()
    {
        $report = new NullReporter();
        $config = Config::getInstance();
        $config->setDisabled(false);
        $config->setReporter($report);
        $tracer1 = $config->initTracer('tracer1');
        $this->assertTrue($tracer1 instanceof Tracer);
        $tracer2 = $config->initTracer('tracer2');
        $this->assertTrue($tracer2 instanceof Tracer);
        $this->assertTrue($config->flush());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testEmptyServiceName()
    {
        $report = new NullReporter();
        $config = Config::getInstance();
        $config->setDisabled(false);
        $config->setReporter($report);
        $config->initTracer('');
    }
}
