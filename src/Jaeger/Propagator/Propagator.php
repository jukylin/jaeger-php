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

namespace Jaeger\Propagator;

use Jaeger\SpanContext;

interface Propagator
{
    /**
     * 注入.
     *
     * @param string $format
     * @param mixed  $carrier
     */
    public function inject(\OpenTracing\SpanContext $spanContext, $format, &$carrier);

    /**
     * 提取.
     *
     * @param string $format
     * @param mixed  $carrier
     *
     * @return SpanContext|null
     */
    public function extract($format, $carrier);
}
