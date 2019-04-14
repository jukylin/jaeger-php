<?php

namespace Jaeger;



class Scope implements \OpenTracing\Scope{


    private $scopeManager;


    private $wrapped;


    private $toRestore;


    private $finishSpanOnClose;


    public function __construct(ScopeManager $scopeManager, \OpenTracing\Span $wrapped, $finishSpanOnClose)
    {
        $this->scopeManager = $scopeManager;
        $this->wrapped = $wrapped;
        $this->finishSpanOnClose = $finishSpanOnClose;
        $this->toRestore = $scopeManager->getActive();
    }


    public function close()
    {
        if ($this->scopeManager->getActive() !== $this) {
            return;
        }

        if ($this->finishSpanOnClose) {
            $this->wrapped->finish();
        }

        $this->scopeManager->setActive($this->toRestore);
    }


    public function getSpan()
    {
        return $this->wrapped;
    }
}
