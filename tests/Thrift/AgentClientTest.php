<?php

namespace tests\Thrift;

use Exception;
use Jaeger\Thrift\AgentClient;
use PHPUnit\Framework\TestCase;

class AgentClientTest extends TestCase
{
    /**
     * @param int $spanCount
     * @dataProvider provideSpanCount
     * @throws Exception
     */
    public function testBuildThriftSuccess($spanCount)
    {
        $batch = $this->createBatchWithSpans($spanCount);

        $subject = new AgentClient();
        $result = $subject->buildThrift($batch);
        self::assertSame($result['len'], strlen($result['thriftStr']));
    }

    public function provideSpanCount()
    {
        return [
            [1],
            [10],
            [100],
            [999]
        ];
    }

    public function testBuildThriftFailsTooLong()
    {
        $batch = $this->createBatchWithSpans(1000);

        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('/thrift string was longer than maximum allowed length/');

        $subject = new AgentClient();
        $subject->buildThrift($batch);
    }

    /**
     * @param int $spanCount
     * @return array
     */
    private function createBatchWithSpans($spanCount)
    {
        $batch = [
            'thriftProcess' => [
                'serverName' => 'some server name',
                'tags' => [
                    'peer.ipv4' => '0.0.0.0'
                ]
            ],
            'thriftSpans' => []
        ];

        for ($i = 0; $i < $spanCount; $i++) {
            $batch['thriftSpans'][] = [
                'spanId' => '123456789',
                'operationName' => 'operation name 123456789012',
                'tags' => [
                    'span.kind' => 'client'
                ],
                'traceIdLow' => '999012393582',
                'traceIdHigh' => '12338923423',
                'parentSpanId' => null,
                'flags' => null,
                'startTime' => time(),
                'duration' => 5,
                'references' => []
            ];
        }
        return $batch;
    }
}
