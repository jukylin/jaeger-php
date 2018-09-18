<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/autoload.php';

use Jaeger\Config;
use OpenTracing\Formats;
use GuzzleHttp\Client;

$http = new swoole_http_server("0.0.0.0", 8000);
$http->on('request', function ($request, $response) {
    unset($_SERVER['argv']);
    $config = Config::getInstance();
    $config::$propagator = \Jaeger\Constants\PROPAGATOR_ZIPKIN;

    //init server span start
    $tracer = $config->initTrace('Istio', 'jaeger-agent.istio-system:6831');
    $spanContext = $tracer->extract(Formats\TEXT_MAP, $request->header);

    $serverSpan = $tracer->startSpan('Istio1', ['child_of' => $spanContext]);
    $tracer->inject($serverSpan->getContext(), Formats\TEXT_MAP, $_SERVER);
    print_r($_SERVER);
    //client span1 start
    $clientTrace = $config->initTrace('Istio1 HTTP');
    $injectTarget = [];
    $spanContext = $clientTrace->extract(Formats\TEXT_MAP, $_SERVER);
    $clientSapn = $clientTrace->startSpan('Istio1', ['child_of' => $spanContext]);
    $clientTrace->inject($clientSapn->spanContext, Formats\TEXT_MAP, $injectTarget);

    $client = new Client();
    $clientSapn->setTags(["http.url" => "Istio2:8001"]);
    $res = $client->request('GET', 'Istio2:8001' ,['headers' => $injectTarget]);
    $clientSapn->setTags(["http.status_code" => $res->getStatusCode()]);
    //client span1 end

    //server span end
    $serverSpan->finish();
    //trace flush
    $config->flush();

    $response->end("Hello Istio1");
});
$http->start();

?>
