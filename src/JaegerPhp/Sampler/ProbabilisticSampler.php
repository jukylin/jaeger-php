<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/9/2
 * Time: 上午1:37
 */

namespace JaegerPhp\Sampler;

use JaegerPhp\Helper;

class ProbabilisticSampler implements Sampler{

    // min 0, max 1
    private $rate = 0;

    private $tags = [];

    public function __construct($rate){
        $this->rate = $rate;
        $this->tags[Helper::SAMPLER_TYPE_TAG_KEY] = 'probabilistic';
        $this->tags[Helper::SAMPLER_PARAM_TAG_KEY] = $rate;
    }


    public function IsSampled(){
        if(mt_rand(1, 1 / $this->rate) == 1){
            return true;
        }else{
            return false;
        }
    }


    public function Close(){
        //nothing to do
    }


    public function getTags(){
        return $this->tags;
    }
}
