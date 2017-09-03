<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/9/2
 * Time: 上午1:33
 */

namespace JaegerPhp\Sampler;


interface Sampler
{
    public function IsSampled();

    public function Close();

    public function getTags();
}