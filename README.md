# jaeger-php

## principle of Distributed Tracing

<img src="https://upload.cc/i/OhsjA0.jpg" width="700px" height="650px" />

## install
> install via composer

> vim composer.json 

```
{
  "minimum-stability": "dev",
  "require":           {
    "jukylin/jaeger-php" : "^1.0",
    "opentracing/opentracing":"1.0-beta1"
  }
}
```

> composer update


## Init Jaeger-php

```
$traceConfig = Config::getInstance();
$trace = $traceConfig->initTrace('example', '0.0.0.0:5775');
```

## Extract from Superglobals

```
$textMap = TextMap::create($_SERVER);
$spanContext = $trace->extract(Propagator::TEXT_MAP, $textMap);
```

## Start Span

```
$serverSpan = $trace->startSpan('example HTTP', SpanReference::createAsChildOf($spanContext));

```

## Inject into Superglobals

```
$textMap = TextMap::create($_SERVER);
$clientTrace->inject($clientSapn1->spanContext, Propagator::TEXT_MAP, $textMap);
$tmp = $textMap->getIterator()->getArrayCopy();
$_SERVER[\JaegerPhp\Helper::TracerStateHeaderName] = $tmp[\JaegerPhp\Helper::TracerStateHeaderName];

```


## Tags and Log

```
//can search in Jaeger UI
$span->addTags(['http.status' => "200"]);

//log record
$span->log(['error' => "HTTP request timeout"]);

```

## finish span and flush trace 

```
$span->finish();
$traceConfig->flushTrace();
```

##   [more example](https://github.com/jukylin/jaeger-php/tree/master/example) 

## Features

- Transports
    - via Thrift over UDP
    
- Sampling
    - ConstSampler
    - ProbabilisticSampler



## Reference

[OpenTracing](http://opentracing.io/)

[Jaeger](https://uber.github.io/jaeger/)