<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/autoload.php';

use Jaeger\Config;
//use GuzzleHttp\Client;
use OpenTracing\Propagator;
use OpenTracing\Carriers\TextMap;
use OpenTracing\SpanReference;

unset($_SERVER['argv']);

//init server span start
$tracerConfig = Config::getInstance();
$tracer = $tracerConfig->initTrace('example', '0.0.0.0:6831');

$injectTarget = [];
$textMap = TextMap::create($injectTarget);
$spanContext = $tracer->extract(Propagator::TEXT_MAP, $textMap);
$serverSpan = $tracer->startSpan('example HTTP', SpanReference::createAsChildOf($spanContext));
$tracer->inject($serverSpan->getContext(), Propagator::TEXT_MAP, $textMap);
$injectTarget = $textMap->getIterator()->getArrayCopy();
$_SERVER[\Jaeger\Helper::TracerStateHeaderName] = $injectTarget[\Jaeger\Helper::TracerStateHeaderName];
//init server span end


$clientTrace = $tracerConfig->initTrace('HTTP');

//client span1 start
$injectTarget1 = [];
$textMap = TextMap::create($_SERVER);
$spanContext = $clientTrace->extract(Propagator::TEXT_MAP, $textMap);
$clientSapn1 = $clientTrace->startSpan('HTTP1', SpanReference::createAsChildOf($spanContext));
$clientTrace->inject($clientSapn1->spanContext, Propagator::TEXT_MAP, $textMap);
$tmp = $textMap->getIterator()->getArrayCopy();
$injectTarget1[\Jaeger\Helper::TracerStateHeaderName] = $tmp[\Jaeger\Helper::TracerStateHeaderName];

$method = 'GET';
$url = 'https://github.com/';
//$client = new Client();
//$res = $client->request($method, $url,['headers' => $injectTarget1]);

$clientSapn1->addTags(['http.status_code' => 200
    , 'http.method' => 'GET', 'http.url' => $url]);
$clientSapn1->log(['message' => "HTTP1 ". $method .' '. $url .' end !']);
$clientSapn1->finish();
//client span1 end


//client span2 start
$injectTarget2 = [];
$textMap = TextMap::create($_SERVER);
$spanContext = $clientTrace->extract(Propagator::TEXT_MAP, $textMap);
$clientSpan2 = $clientTrace->startSpan('HTTP2', SpanReference::createAsChildOf($spanContext));
$clientTrace->inject($clientSpan2->spanContext, Propagator::TEXT_MAP, $textMap);
$tmp = $textMap->getIterator()->getArrayCopy();
$injectTarget2[\Jaeger\Helper::TracerStateHeaderName] = $tmp[\Jaeger\Helper::TracerStateHeaderName];

$method = 'GET';
$url = 'https://github.com/search?utf8=✓&q=jaeger-php';
//$client = new Client();
//$res = $client->request($method, $url, ['headers' => $injectTarget2]);

$clientSpan2->addTags(['http.status_code' => 200
    , 'http.method' => 'GET', 'http.url' => $url]);
$clientSpan2->log(['message' => "HTTP2 ". $method .' '. $url .' end !']);
$clientSpan2->finish();
//client span2 end

//server span end
$serverSpan->finish();
//trace flush
$tracerConfig->flush();

echo "success\r\n";
