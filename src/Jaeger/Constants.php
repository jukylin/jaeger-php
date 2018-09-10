<?php
namespace Jaeger\Constants;

const Tracer_State_Header_Name = 'uber-trace-id';

const Jaeger_Baggage_Header = 'jaeger-baggage';

const Trace_Baggage_Header_Prefix = 'uberctx-';

const Jaeger_Debug_Header = "jaeger-debug-id";

const EMIT_BATCH_OVER_HEAD = 30;

const UDP_PACKET_MAX_LENGTH = 65000;

const SAMPLER_TYPE_TAG_KEY = 'sampler.type';

const SAMPLER_PARAM_TAG_KEY = 'sampler.param';

const PROPAGATOR_JAEGER = 'jaeger';

const PROPAGATOR_ZIPKIN = 'zipkin';

const X_B3_TRACEID = 'x-b3-traceid';

const X_B3_PARENT_SPANID = 'x-b3-parentspanid';

const X_B3_SPANID = 'x-b3-spanid';

const X_B3_SAMPLED = 'x-b3-sampled';