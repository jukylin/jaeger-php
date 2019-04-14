<?php

namespace Jaeger;


/**
 * {@inheritdoc}
 */
class Scope implements \OpenTracing\Scope{
    /**
     * @var ScopeManager
     */
    private $scopeManager;

    /**
     * @var OTSpan
     */
    private $wrapped;

    /**
     * @var OTScope|null
     */
    private $toRestore;

    /**
     * @var bool
     */
    private $finishSpanOnClose;


    /**
     * @param ScopeManager $scopeManager
     * @param OTSpan $wrapped
     * @param bool $finishSpanOnClose
     */
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
            // This shouldn't happen if users call methods in expected order
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
