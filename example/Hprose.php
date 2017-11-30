<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/autoload.php';

use Hprose\Client;
use Jaeger\Config;
use OpenTracing\Propagator;
use OpenTracing\Carriers\TextMap;
use OpenTracing\SpanReference;


unset($_SERVER['argv']);

//init server span start
$traceConfig = Config::getInstance();
$trace = $traceConfig->initTrace('example', '0.0.0.0:6831');

$injectTarget = [];
$textMap = TextMap::create($injectTarget);
$spanContext = $trace->extract(Propagator::TEXT_MAP, $textMap);
$serverSpan = $trace->startSpan('example HTTP', SpanReference::createAsChildOf($spanContext));
$trace->inject($serverSpan->getContext(), Propagator::TEXT_MAP, $textMap);
$injectTarget = $textMap->getIterator()->getArrayCopy();
$_SERVER[\Jaeger\Helper::TracerStateHeaderName] = $injectTarget[\Jaeger\Helper::TracerStateHeaderName];
//init server span end

$clientTrace = $traceConfig->initTrace('Hprose');

//client span start
$header = [];
$textMap = TextMap::create($_SERVER);
$spanContext = $clientTrace->extract(Propagator::TEXT_MAP, $textMap);
$clientSapn = $clientTrace->startSpan('get', SpanReference::createAsChildOf($spanContext));
$clientTrace->inject($clientSapn->spanContext, Propagator::TEXT_MAP, $textMap);
$tmp = $textMap->getIterator()->getArrayCopy();
$header[\Jaeger\Helper::TracerStateHeaderName] = $tmp[\Jaeger\Helper::TracerStateHeaderName];

$url = 'http://0.0.0.0:8080/main';
$client = Client::create($url, false);

if($header){
    foreach($header as $key => $val){
        $client->setHeader($key, $val);
    }
}
$clientSapn->addTags(['http.url' => $url]);
$clientSapn->addTags(['http.method' => 'POST']);

$result =  $client->get("Hprose");

$clientSapn->log(['http.result' => $result]);
$clientSapn->finish();
//client span end


//server span end
$serverSpan->finish();
//trace flush
$traceConfig->flushTrace();

echo "success\r\n";


