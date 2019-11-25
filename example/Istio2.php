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
use OpenTracing\Formats;

$http = new swoole_http_server("0.0.0.0", 8001);
$http->on('request', function ($request, $response) {
    unset($_SERVER['argv']);
    $config = Config::getInstance();
    $config::$propagator = \Jaeger\Constants\PROPAGATOR_ZIPKIN;

    //init server span start
    $tracer = $config->initTracer('Istio', 'jaeger-agent.istio-system:6831');
    $spanContext = $tracer->extract(Formats\TEXT_MAP, $request->header);
    $serverSpan = $tracer->startSpan('Istio2', ['child_of' => $spanContext]);
    $tracer->inject($serverSpan->getContext(), Formats\TEXT_MAP, $_SERVER);

    //client span1 start
    $clientTracer = $config->initTracer('Istio2 HTTP');
    $injectTarget = [];
    $spanContext = $clientTracer->extract(Formats\TEXT_MAP, $_SERVER);
    $clientSpan = $clientTracer->startSpan('Istio2', ['child_of' => $spanContext]);
    $clientTracer->inject($clientSpan->spanContext, Formats\TEXT_MAP, $injectTarget);

    $client = new \GuzzleHttp\Client();
    $clientSpan->setTag("http.url", "Istio3:8002");
    $res = $client->request('GET', 'Istio3:8002', ['headers' => $injectTarget]);
    $clientSpan->setTag("http.status_code", $res->getStatusCode());
    //client span1 end

    //server span end
    $serverSpan->finish();
    //trace flush
    $config->flush();

    $response->end("Hello Istio2");
});
$http->start();
