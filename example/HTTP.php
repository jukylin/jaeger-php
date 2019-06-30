<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/autoload.php';

use Jaeger\Config;
use GuzzleHttp\Client;
use OpenTracing\Formats;
use OpenTracing\Reference;

unset($_SERVER['argv']);


//init server span start
$config = Config::getInstance();
$config->gen128bit();

$tracer = $config->initTrace('example', '0.0.0.0:6831');

$injectTarget = [];
$spanContext = $tracer->extract(Formats\TEXT_MAP, $_SERVER);
$serverSpan = $tracer->startSpan('example HTTP', ['child_of' => $spanContext]);
$serverSpan->addBaggageItem("version", "1.8.9");

$tracer->inject($serverSpan->getContext(), Formats\TEXT_MAP, $_SERVER);
//init server span end
$clientTrace = $config->initTrace('HTTP');

//client span1 start
$injectTarget1 = [];
$spanContext = $clientTrace->extract(Formats\TEXT_MAP, $_SERVER);
$clientSapn1 = $clientTrace->startSpan('HTTP1', ['child_of' => $spanContext]);
$clientTrace->inject($clientSapn1->spanContext, Formats\TEXT_MAP, $injectTarget1);

$method = 'GET';
$url = 'https://github.com/';
$client = new Client();
$res = $client->request($method, $url,['headers' => $injectTarget1]);

$clientSapn1->setTags(['http.status_code' => 200
    , 'http.method' => 'GET', 'http.url' => $url]);
$clientSapn1->log(['message' => "HTTP1 ". $method .' '. $url .' end !']);
$clientSapn1->finish();
//client span1 end

//client span2 start
$injectTarget2 = [];
$spanContext = $clientTrace->extract(Formats\TEXT_MAP, $_SERVER);
$clientSpan2 = $clientTrace->startSpan('HTTP2',
    ['references' => [
        Reference::create(Reference::FOLLOWS_FROM, $clientSapn1->spanContext),
        Reference::create(Reference::CHILD_OF, $spanContext)
    ]]);

$clientTrace->inject($clientSpan2->spanContext, Formats\TEXT_MAP, $injectTarget2);

$method = 'GET';
$url = 'https://github.com/search?utf8=✓&q=jaeger-php';
$client = new Client();
$res = $client->request($method, $url, ['headers' => $injectTarget2]);

$clientSpan2->setTags(['http.status_code' => 200
    , 'http.method' => 'GET', 'http.url' => $url]);
$clientSpan2->log(['message' => "HTTP2 ". $method .' '. $url .' end !']);
$clientSpan2->finish();
//client span2 end

//server span end
$serverSpan->finish();
//trace flush
$config->flush();

echo "success\r\n";
