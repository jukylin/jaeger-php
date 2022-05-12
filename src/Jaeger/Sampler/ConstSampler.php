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

namespace Jaeger\Sampler;

use Jaeger\Constants;

class ConstSampler implements Sampler
{
    private $decision = '';

    private $tags = [];

    public function __construct($decision = true)
    {
        $this->decision = $decision;
        $this->tags[Constants\SAMPLER_TYPE_TAG_KEY] = 'const';
        $this->tags[Constants\SAMPLER_PARAM_TAG_KEY] = $decision;
    }

    public function IsSampled()
    {
        return $this->decision;
    }

    public function Close()
    {
        //nothing to do
    }

    public function getTags()
    {
        return $this->tags;
    }
}
