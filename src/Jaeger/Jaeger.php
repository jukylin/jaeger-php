<?php

namespace Jaeger;

use Jaeger\Sampler\Sampler;
use OpenTracing\SpanContext;
use OpenTracing\Formats;
use OpenTracing\Tracer;
use Jaeger\Reporter\Reporter;
use OpenTracing\SpanOptions;

class Jaeger implements Tracer{

    private $reporter = null;

    private $sampler = null;

    private $gen128bit = false;

    public static $handleProto = null;

    public $spans = [];

    public $tags = [];

    public $process = null;

    public $procesSize = 0;

    public $serverName = '';

    public $bufferSize = '';

    public $processThrift = '';

    public $spanThrifts = [];

    public function __construct($serverName = '', Reporter $reporter, Sampler $sampler){

        $this->reporter = $reporter;

        $this->sampler = $sampler;
        $this->setTags($this->sampler->getTags());
        $this->setTags($this->getEnvTags());

        if($serverName == '') {
            $this->serverName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'unknow server';
        }else{
            $this->serverName = $serverName;
        }
    }


    /**
     * @param array $tags  key => value
     */
    public function setTags(array $tags = []){
        if(!empty($tags)) {
            $this->tags = array_merge($this->tags, $tags);
        }
    }


    /**
     * init span info
     * @param string $operationName
     * @param array $options
     * @return Span
     */
    public function startSpan($operationName, $options = []){

        if (is_array($options)) {
            $options = SpanOptions::create($options);
        }else{
            throw new \Exception("options is not array");
        }

        $references = $options->getReferences();
        $parentSpan = $references[0]->getContext();

        if(!$parentSpan->traceId){
            $low = $this->generateId();
            if($this->gen128bit == true){
                $high = $this->generateId();
                $traceId = Helper::toHex($low, $high);
            }else{
                $traceId = Helper::toHex($low);
            }
            $spanId = Helper::toHex($low);

            $flags = $this->sampler->IsSampled();
            $newSpan = new \Jaeger\SpanContext($traceId, $spanId, 0, $flags, null, 0);
        }else{
            $newSpan = new \Jaeger\SpanContext($parentSpan->traceId, Helper::toHex($this->generateId())
                , $parentSpan->spanId, $parentSpan->flags, null, 0);
        }

        $span = new Span($operationName, $newSpan);
        $span->setTags($options->getTags());

        if($newSpan->isSampled() == 1) {
            $this->spans[] = $span;
        }

        return $span;
    }


    /**
     * 注入
     * @param SpanContext $spanContext
     * @param string $format
     * @param $carrier
     */
    public function inject(SpanContext $spanContext, $format, &$carrier){
        if($format == Formats\TEXT_MAP){
            $carrier[Constants\Tracer_State_Header_Name] = $spanContext->buildString();
        }else{
            throw new \Exception("not support format");
        }
    }


    /**
     * 提取
     * @param string $format
     * @param $carrier
     */
    public function extract($format, $carrier){
        if($format == Formats\TEXT_MAP){
            if(isset($carrier[Constants\Tracer_State_Header_Name]) && $carrier[Constants\Tracer_State_Header_Name]){
                list($traceId, $spanId, $parentId,$flags) = explode(':', $carrier[Constants\Tracer_State_Header_Name]);
                return new \Jaeger\SpanContext($traceId, $spanId, $parentId, $flags, null, 0);
            }

            return new \Jaeger\SpanContext(0, 0, 0, 0, null, 0);
        }else{
            throw new \Exception("not support format");
        }
    }


    public function getSpans(){
        return $this->spans;
    }


    public function reportSpan(){
        if($this->spans) {
            $this->reporter->report($this);
            $this->spans = [];
        }
    }


    public function getEnvTags(){
        $tags = [];
        if(isset($_SERVER['JAEGER_TAGS']) && $_SERVER['JAEGER_TAGS'] != ''){
            $envTags = explode(',', $_SERVER['JAEGER_TAGS']);
            foreach ($envTags as $envK => $envTag){
                list($key, $value) = explode('=', $envTag);
                $tags[$key] = $value;
            }
        }

        return $tags;
    }


    public function gen128bit(){
        $this->gen128bit = true;
    }


    /**
     * 结束,发送信息到jaeger
     */
    public function flush(){
        $this->reportSpan();
        $this->reporter->close();
    }


    private function generateId(){
        return strrev(microtime(true) * 10000 . rand(1000, 9999));
    }
}


?>