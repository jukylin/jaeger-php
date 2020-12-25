<?php

namespace Jaeger;


class ScopeManager implements \OpenTracing\ScopeManager{

    private $scopes = [];

    /**
     * @inheritDoc
     */
    public function activate(\OpenTracing\Span $span, bool $finishSpanOnClose = self::DEFAULT_FINISH_SPAN_ON_CLOSE): \OpenTracing\Scope{
        $scope = new Scope($this, $span, $finishSpanOnClose);
        $this->scopes[] = $scope;
        return $scope;
    }


    /**
     * @inheritDoc
     */
    public function getActive(): ?\OpenTracing\Scope{
        if (empty($this->scopes)) {
            return null;
        }

        return $this->scopes[count($this->scopes) - 1];
    }


    /**
     * @inheritDoc
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
