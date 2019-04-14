<?php

namespace Jaeger;


class ScopeManager implements \OpenTracing\ScopeManager{

    private $active;


    public function activate(\OpenTracing\Span $span, $finishSpanOnClose){
        $this->active = new Scope($this, $span, $finishSpanOnClose);

        return $this->active;
    }


    public function getActive(){
        return $this->active;
    }


    public function setActive(\OpenTracing\Scope $scope = null){
        $this->active = $scope;
    }
}
