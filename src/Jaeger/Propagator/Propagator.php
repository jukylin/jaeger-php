<?php
namespace Jaeger\Propagator;

use Jaeger\SpanContext;

interface Propagator{

    /**
     * 注入
     * @param SpanContext $spanContext
     * @param string $format
     * @param $carrier
     */
    public function inject(SpanContext $spanContext, $format, &$carrier);


    /**
     * 提取
     * @param string $format
     * @param $carrier
     */
    public function extract($format, $carrier);

}