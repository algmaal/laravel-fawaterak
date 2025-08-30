<?php

namespace Algmaal\LaravelFawaterak\Exceptions;

use Exception;

class FawaterakException extends Exception
{
    protected array $context;

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get exception context.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set exception context.
     */
    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }
}
