<?php

namespace Jaeger;

class Scope implements \OpenTracing\Scope
{
    /**
     * @var ScopeManager
     */
    private $scopeManager = null;

    /**
     * @var Span
     */
    private $span = null;

    /**
     * @var bool
     */
    private $finishSpanOnClose;

    /**
     * Scope constructor.
     *
     * @param bool $finishSpanOnClose
     */
    public function __construct(ScopeManager $scopeManager, Span $span, $finishSpanOnClose)
    {
        $this->scopeManager = $scopeManager;
        $this->span = $span;
        $this->finishSpanOnClose = $finishSpanOnClose;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        if ($this->finishSpanOnClose) {
            $this->span->finish();
        }

        $this->scopeManager->deactivate($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getSpan(): \OpenTracing\Span
    {
        return $this->span;
    }
}
