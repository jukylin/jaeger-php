<?php

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
    $clientSapn = $clientTracer->startSpan('Istio2', ['child_of' => $spanContext]);
    $clientTracer->inject($clientSapn->spanContext, Formats\TEXT_MAP, $injectTarget);

    $client = new \GuzzleHttp\Client();
    $clientSapn->setTags(["http.url" => "Istio3:8002"]);
    $res = $client->request('GET', 'Istio3:8002', ['headers' => $injectTarget]);
    $clientSapn->setTags(["http.status_code" => $res->getStatusCode()]);
    //client span1 end

    //server span end
    $serverSpan->finish();
    //trace flush
    $config->flush();

    $response->end("Hello Istio2");
});
$http->start();

?>