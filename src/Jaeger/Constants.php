<?php
/*
 * Copyright (c) 2019, The Jaeger Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 * in compliance with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under the License
 * is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express
 * or implied. See the License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Jaeger\Constants;

const Tracer_State_Header_Name = 'uber-trace-id';

const Jaeger_Baggage_Header = 'jaeger-baggage';

const Trace_Baggage_Header_Prefix = 'uberctx-';

const Jaeger_Debug_Header = 'jaeger-debug-id';

const EMIT_BATCH_OVER_HEAD = 70;

const UDP_PACKET_MAX_LENGTH = 65000;

const SAMPLER_TYPE_TAG_KEY = 'sampler.type';

const SAMPLER_PARAM_TAG_KEY = 'sampler.param';

const PROPAGATOR_JAEGER = 'jaeger';

const PROPAGATOR_ZIPKIN = 'zipkin';

const X_B3_TRACEID = 'x-b3-traceid';

const X_B3_PARENT_SPANID = 'x-b3-parentspanid';

const X_B3_SPANID = 'x-b3-spanid';

const X_B3_SAMPLED = 'x-b3-sampled';

const CLIENT_SEND = 'cs';

const CLIENT_RECV = 'cr';

const SERVER_SEND = 'ss';

const SERVER_RECV = 'sr';

const WIRE_SEND = 'ws';

const WIRE_RECV = 'wr';

const CLIENT_SEND_FRAGMENT = 'csf';

const CLIENT_RECV_FRAGMENT = 'crf';

const SERVER_SEND_FRAGMENT = 'ssf';

const SERVER_RECV_FRAGMENT = 'srf';

const LOCAL_COMPONENT = 'lc';

const CLIENT_ADDR = 'ca';

const SERVER_ADDR = 'sa';
