<?php

namespace Jaeger;


class ScopeManager implements \OpenTracing\ScopeManager{

    private $scopes = [];


    /**
     * append scope
     * @param \OpenTracing\Span $span
     * @param bool $finishSpanOnClose
     * @return Scope
     */
    public function activate(\OpenTracing\Span $span, $finishSpanOnClose){
        $scope = new Scope($this, $span, $finishSpanOnClose);
        $this->scopes[] = $scope;
        return $scope;
    }


    /**
     * get last scope
     * @return mixed|null
     */
    public function getActive(){
        if (empty($this->scopes)) {
            return null;
        }

        return $this->scopes[count($this->scopes) - 1];
    }


    /**
     * del scope
     * @param Scope $scope
     * @return bool
     */
    public function delActive(Scope $scope){
        $scopeLength = count($this->scopes);

        if($scopeLength <= 0){
            return false;
        }

        for ($i = 0; $i < $scopeLength; $i++) {
            if ($scope === $this->scopes[$i]) {
                array_splice($this->scopes, $i, 1);
            }
        }

        return true;
    }
}
