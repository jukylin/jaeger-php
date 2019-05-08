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

use OpenTracing\NoopSpanContext;
use Jaeger\Span;

class TestSpan extends PHPUnit_Framework_TestCase
{

    public function testOverwriteOperationName(){
        $span = new Span('test1', new NoopSpanContext());
        $span->overwriteOperationName('test2');
        $this->assertTrue($span->getOperationName() == 'test2');
    }


    public function testAddTags(){
        $span = new Span('test1', new NoopSpanContext());
        $span->addTags(['test' => 'test']);
        $this->assertTrue((isset($span->tags['test']) && $span->tags['test'] == 'test'));
    }


    public function testFinish(){
        $span = new Span('test1', new NoopSpanContext());
        $span->addTags(['test' => 'test']);
        $span->finish();
        $this->assertTrue(!empty($span->finishTime) && !empty($span->duration));
    }
}