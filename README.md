[![Build Status](https://travis-ci.com/jukylin/jaeger-php.svg?branch=master)](https://travis-ci.com/jukylin/jaeger-php)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.6-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/github/license/jukylin/jaeger-php.svg)](https://github.com/jukylin/jaeger-php/blob/master/LICENSE)
[![Coverage Status](https://coveralls.io/repos/github/jukylin/jaeger-php/badge.svg?branch=master)](https://coveralls.io/github/jukylin/jaeger-php?branch=master)
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
    "jukylin/jaeger-php" : "^2.0",
    "opentracing/opentracing":"1.0.0-beta5"
  }
}
```

> composer update


## Init Jaeger-php

```
$config = Config::getInstance();
$tracer = $config->initTracer('example', '0.0.0.0:6831');
```

## 128bit

```
$config->gen128bit();
```

## Extract from Superglobals

```
$spanContext = $tracer->extract(Formats\TEXT_MAP, $_SERVER);
```

## Start Span

```
$serverSpan = $tracer->startSpan('example HTTP', ['child_of' => $spanContext]);

```

## Distributed context propagation
```
$serverSpan->addBaggageItem("version", "2.0.0");
```

## Inject into Superglobals

```
$clientTrace->inject($clientSapn1->spanContext, Formats\TEXT_MAP, $_SERVER);
```


## Tags and Log

```
//can search in Jaeger UI
$span->addTags(['http.status' => "200"]);

//log record
$span->log(['error' => "HTTP request timeout"]);

```

## Close Tracer

```
$config->setDisabled(true);
```

## Zipkin B3 Propagation

> not support ```Distributed context propagation ```

```
$config::$propagator = \Jaeger\Constants\PROPAGATOR_ZIPKIN;
```


## finish span and flush Tracer

```
$span->finish();
$config->flush();
```

##  more example

- [HTTP](https://github.com/jukylin/jaeger-php/blob/master/example/HTTP.php)
- [Hprose](https://github.com/jukylin/blog/blob/master/Uber%E5%88%86%E5%B8%83%E5%BC%8F%E8%BF%BD%E8%B8%AA%E7%B3%BB%E7%BB%9FJaeger%E4%BD%BF%E7%94%A8%E4%BB%8B%E7%BB%8D%E5%92%8C%E6%A1%88%E4%BE%8B%E3%80%90PHP%20%20%20Hprose%20%20%20Go%E3%80%91.md#跨语言调用案例)
- [Istio](https://github.com/jukylin/jaeger-php/blob/master/example/README.md)

## Features

- Transports
    - via Thrift over UDP
    
- Sampling
    - ConstSampler
    - ProbabilisticSampler



## Reference

[OpenTracing](http://opentracing.io/)

[Jaeger](https://uber.github.io/jaeger/)
