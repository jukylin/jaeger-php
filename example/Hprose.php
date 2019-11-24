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

use Hprose\Client;
use Jaeger\Config;
use OpenTracing\Formats;


unset($_SERVER['argv']);

//init server span start
$config = Config::getInstance();
$tracer = $config->initTracer('example', '0.0.0.0:6831');

$spanContext = $tracer->extract(Formats\TEXT_MAP, $_SERVER);
$serverSpan = $tracer->startSpan('example HTTP', ['child_of' => $spanContext]);
$tracer->inject($serverSpan->getContext(), Formats\TEXT_MAP, $_SERVER);
//init server span end

$clientTracer = $config->initTracer('Hprose');

//client span start
$header = [];
$spanContext = $clientTracer->extract(Formats\TEXT_MAP, $_SERVER);
$clientSpan = $clientTracer->startSpan('get', ['child_of' => $spanContext]);
$clientSpan->addBaggageItem("version", "2.0.0");

$clientTracer->inject($clientSpan->spanContext, Formats\TEXT_MAP, $header);

$url = 'http://0.0.0.0:8080/main';
$client = Client::create($url, false);

if($header){
    foreach($header as $key => $val){
        $client->setHeader($key, $val);
    }
}
$clientSpan->setTag('http.url', $url);
$clientSpan->setTag('http.method' , 'POST');

$result =  $client->get("Hprose");

$clientSpan->log(['http.result' => $result]);
$clientSpan->finish();
//client span end


//server span end
$serverSpan->finish();
//trace flush
$config->flush();

echo "success\r\n";


