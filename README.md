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
$trace = $traceConfig->initTrace('example', '0.0.0.0:6831');
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

##  more example

- [HTTP](https://github.com/jukylin/jaeger-php/blob/master/example/HTTP.php)
- [Hprose](https://github.com/jukylin/blog/blob/master/%E8%B7%A8%E8%AF%AD%E8%A8%80%E5%88%86%E5%B8%83%E5%BC%8F%E8%BF%BD%E8%B8%AA%E7%B3%BB%E7%BB%9FJaeger%E4%BD%BF%E7%94%A8%E4%BB%8B%E7%BB%8D%E5%92%8C%E6%A1%88%E4%BE%8B%E3%80%90PHP%20%20%20Hprose%20%20%20Go%E3%80%91.md#跨语言调用案例)

## Features

- Transports
    - via Thrift over UDP
    
- Sampling
    - ConstSampler
    - ProbabilisticSampler



## Reference

[OpenTracing](http://opentracing.io/)

[Jaeger](https://uber.github.io/jaeger/)
