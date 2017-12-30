<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/9/2
 * Time: 上午1:36
 */

namespace Jaeger\Sampler;


use Jaeger\Constants;

class ConstSampler implements Sampler{

    private $decision = '';

    private $tags = [];

    public function __construct($decision = true){
        $this->decision = $decision;
        $this->tags[Constants\SAMPLER_TYPE_TAG_KEY] = 'const';
        $this->tags[Constants\SAMPLER_PARAM_TAG_KEY] = $decision;
    }

    public function IsSampled(){
        return $this->decision;
    }


    public function Close(){
        //nothing to do
    }


    public function getTags(){
        return $this->tags;
    }
}