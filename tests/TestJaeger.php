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