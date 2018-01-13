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