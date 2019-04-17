<?php



use OpenTracing\NoopSpanContext;
use Jaeger\Span;
use PHPUnit\Framework\TestCase;

class TestSpan extends TestCase
{
    public function testOverwriteOperationName(){
        $span = new Span('test1', new NoopSpanContext(), []);
        $span->overwriteOperationName('test2');
        $this->assertTrue($span->getOperationName() == 'test2');
    }

    public function testAddTags(){
        $span = new Span('test1', new NoopSpanContext(), []);
        $span->setTags(['test' => 'test']);
        $this->assertTrue((isset($span->tags['test']) && $span->tags['test'] == 'test'));
    }

    public function testFinish(){
        $span = new Span('test1', new NoopSpanContext(), []);
        $span->setTags(['test' => 'test']);
        $span->finish();
        $this->assertTrue(!empty($span->finishTime) && !empty($span->duration));
    }
}