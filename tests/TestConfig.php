<?php

require_once '../../autoload.php';

use JaegerPhp\Config;
use OpenTracing\NoopTracer;

class TestConfig extends PHPUnit_Framework_TestCase
{

    public function testSetDisabled(){
        $config = Config::getInstance();
        $config->setDisabled(true);

        $this->assertTrue($config::$disabled == true);
    }


    public function testNoopTracer(){

        $config = Config::getInstance();
        $config->setDisabled(true);
        $trace = $config->initTrace('test');

        $this->assertTrue($trace instanceof NoopTracer);
    }



}