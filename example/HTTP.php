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

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/autoload.php';

use Jaeger\Config;
use GuzzleHttp\Client;
use OpenTracing\Formats;
use OpenTracing\Reference;

unset($_SERVER['argv']);


//init server span start
$config = Config::getInstance();
$config->gen128bit();

$config::$propagator = Jaeger\Constants\PROPAGATOR_ZIPKIN;

$tracer = $config->initTracer('example', '0.0.0.0:6831');

$injectTarget = [];
$spanContext = $tracer->extract(Formats\TEXT_MAP, $_SERVER);
$serverSpan = $tracer->startSpan('example HTTP', ['child_of' => $spanContext]);
$serverSpan->addBaggageItem("version", "1.8.9");
print_r($serverSpan->getContext());
$tracer->inject($serverSpan->getContext(), Formats\TEXT_MAP, $_SERVER);
print_r($_SERVER);exit;
//init server span end
$clientTracer = $config->initTracer('HTTP');

//client span1 start
$injectTarget1 = [];
$spanContext = $clientTracer->extract(Formats\TEXT_MAP, $_SERVER);
$clientSapn1 = $clientTracer->startSpan('HTTP1', ['child_of' => $spanContext]);
$clientTracer->inject($clientSapn1->spanContext, Formats\TEXT_MAP, $injectTarget1);

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
$spanContext = $clientTracer->extract(Formats\TEXT_MAP, $_SERVER);
$clientSpan2 = $clientTracer->startSpan('HTTP2',
    ['references' => [
        Reference::create(Reference::FOLLOWS_FROM, $clientSapn1->spanContext),
        Reference::create(Reference::CHILD_OF, $spanContext)
    ]]);

$clientTracer->inject($clientSpan2->spanContext, Formats\TEXT_MAP, $injectTarget2);

$method = 'GET';
$url = 'https://github.com/search?q=jaeger-php';
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
