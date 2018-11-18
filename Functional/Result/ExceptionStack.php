<?php

namespace Functional\Result;

final class ExceptionStack {
    /** @var \Throwable[] **/
    private $stack;

    public function __construct(\Throwable ...$exceptions) {
        $this->stack = $exceptions;
    }

    public function merge(ExceptionStack $exceptionStack) {
        return new ExceptionStack(...array_merge($this->stack, $exceptionStack->stack));
    }

    public function getLastException(): \Throwable {
        return end($this->stack);
    }

    public function toString(): string {
        $string = '';

        foreach ($this->stack as $exception) {
            $string .= $exception->getMessage() . PHP_EOL;
            $string .= $exception->getTraceAsString() . PHP_EOL;
        }

        return $string;
    }
}
