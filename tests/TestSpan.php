<?php

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