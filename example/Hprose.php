<?php
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

$clientTrace = $config->initTracer('Hprose');

//client span start
$header = [];
$spanContext = $clientTrace->extract(Formats\TEXT_MAP, $_SERVER);
$clientSapn = $clientTrace->startSpan('get', ['child_of' => $spanContext]);
$clientSapn->addBaggageItem("version", "2.0.0");

$clientTrace->inject($clientSapn->spanContext, Formats\TEXT_MAP, $header);

$url = 'http://0.0.0.0:8080/main';
$client = Client::create($url, false);

if($header){
    foreach($header as $key => $val){
        $client->setHeader($key, $val);
    }
}
$clientSapn->setTags(['http.url' => $url]);
$clientSapn->setTags(['http.method' => 'POST']);

$result =  $client->get("Hprose");

$clientSapn->log(['http.result' => $result]);
$clientSapn->finish();
//client span end


//server span end
$serverSpan->finish();
//trace flush
$config->flush();

echo "success\r\n";


