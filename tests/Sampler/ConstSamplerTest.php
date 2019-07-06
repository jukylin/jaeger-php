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

use Jaeger\Sampler\ConstSampler;
use PHPUnit\Framework\TestCase;
use Jaeger\Constants;

class ConstSamplerTest extends TestCase
{

    public function testConstSampler(){
        $sample = new ConstSampler(true);
        $this->assertTrue($sample->IsSampled()  == true);
    }

    public function testConstSamplerGetTag(){
        $sample = new ConstSampler(true);
        $tags = $sample->getTags();
        $this->assertTrue($tags[Constants\SAMPLER_TYPE_TAG_KEY] == 'const');
    }
}