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

use OpenTracing\NoopSpanContext;
use Jaeger\Span;
use PHPUnit\Framework\TestCase;
use Jaeger\ScopeManager;

class ScopeMangerTest extends TestCase
{

    public function testActivate(){
        $span1 = new Span('test', new NoopSpanContext(), []);

        $scopeManager = new ScopeManager();
        $scope = $scopeManager->activate($span1, true);
        $span2 = $scope->getSpan();

        $this->assertTrue($span1 === $span2);
    }

    public function testGetActive(){

        $span = new Span('test', new NoopSpanContext(), []);

        $scopeManager = new ScopeManager();
        $scope1 = $scopeManager->activate($span, true);

        $scope2 = $scopeManager->getActive();
        $this->assertTrue($scope1 === $scope2);
    }


    public function testDelActive(){

        $span = new Span('test', new NoopSpanContext(), []);

        $scopeManager = new ScopeManager();
        $scope = $scopeManager->activate($span, true);

        $res = $scopeManager->delActive($scope);
        $this->assertTrue($res == true);

        $getRes = $scopeManager->getActive();
        $this->assertTrue($getRes === null);
    }

    public function testDelActiveNestedScopes() {
        $scopeManager = new ScopeManager();
        $span1 = new Span('Z', new NoopSpanContext(), []);
        $scope1 = $scopeManager->activate($span1, true);
        $span2 = new Span('Y', new NoopSpanContext(), []);
        $scope2 = $scopeManager->activate($span2, true);
        $span3 = new Span('X', new NoopSpanContext(), []);
        $scope3 = $scopeManager->activate($span3, true);

        $active = $scopeManager->getActive();
        $this->assertTrue($active === $scope3);

        $res = $scopeManager->delActive($scope3);
        $this->assertTrue($res == true);
        $active = $scopeManager->getActive();
        $this->assertTrue($active === $scope2);

        $res = $scopeManager->delActive($scope2);
        $this->assertTrue($res == true);
        $active = $scopeManager->getActive();
        $this->assertTrue($active === $scope1);

        $res = $scopeManager->delActive($scope1);
        $this->assertTrue($res == true);
        $active = $scopeManager->getActive();
        $this->assertTrue($active === null);
    }

    public function testDelActiveReNestScopes() {
        $scopeManager = new ScopeManager();
        $span1 = new Span('A', new NoopSpanContext(), []);
        $scope1 = $scopeManager->activate($span1, true);
        $span2 = new Span('B', new NoopSpanContext(), []);
        $scope2 = $scopeManager->activate($span2, true);

        $active = $scopeManager->getActive();
        $this->assertTrue($active === $scope2);

        // Remove scope2 so that scope1 is active
        $scopeManager->delActive($scope2);
        $active = $scopeManager->getActive();
        $this->assertTrue($active === $scope1);

        // Add a new active scope3
        $span3 = new Span('C', new NoopSpanContext(), []);
        $scope3 = $scopeManager->activate($span3, true);
        $active = $scopeManager->getActive();
        $this->assertTrue($active === $scope3);

        // Delete active scope3
        $scopeManager->delActive($scope3);
        $active = $scopeManager->getActive();
        $this->assertTrue($active === $scope1);

        $scopeManager->delActive($scope1);
        $active = $scopeManager->getActive();
        $this->assertTrue($active === null);
    }
}