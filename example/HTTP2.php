<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/autoload.php';

use Jaeger\Config;
use GuzzleHttp\Client;
use OpenTracing\Formats;
use OpenTracing\Reference;

unset($_SERVER['argv']);


//init server span start
$config = Config::getInstance();

$tracer = $config->initTracer('example', '0.0.0.0:6831');

$top = $tracer->startActiveSpan('level top');
$second = $tracer->startActiveSpan('level second');
$third = $tracer->startActiveSpan('level third');

$num = 0;
for ($i = 0; $i < 10; $i++){
    $num += 1;
}
$third->getSpan()->setTag("num", $num);
sleep(1);
$third->close();

$num = 0;
for ($i = 0; $i < 10; $i++){
    $num += 2;
}
$third->getSpan()->setTag("num", $num);
sleep(1);
$second->close();


$top->close();

//trace flush
$config->flush();

echo "success\r\n";
