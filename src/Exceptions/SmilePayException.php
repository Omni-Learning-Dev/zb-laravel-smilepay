<?php

namespace Emmanuelsiziba\SmilePay\Exceptions;

use Exception;

class SmilePayException extends Exception
{
    protected array $context = [];

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context information
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set additional context information
     *
     * @param array $context
     * @return $this
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
}
