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

use PHPUnit\Framework\TestCase;
use Jaeger\Config;
use OpenTracing\NoopTracer;

class ConfigTest extends TestCase
{

    public function testSetDisabled(){
        $config = Config::getInstance();
        $config->setDisabled(true);

        $this->assertTrue($config::$disabled == true);
    }


    public function testNoopTracer(){

        $config = Config::getInstance();
        $config->setDisabled(true);
        $trace = $config->initTracer('test');

        $this->assertTrue($trace instanceof NoopTracer);
    }



}