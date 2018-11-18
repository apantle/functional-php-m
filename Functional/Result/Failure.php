<?php

namespace Functional\Result;

class Failure implements Result {
    private $exceptionStack;

    public function __construct(ExceptionStack $exceptionStack) {
        $this->exceptionStack = $exceptionStack;
    }

    public function extract() { return $this->exceptionStack; }

    public function ok(callable $f): Result { return $this; }

    public function fail(callable $f): Result {
        return result($f, $this->exceptionStack)
            ->ok(function() { return new Aborted($this->exceptionStack); })
            ->fail(function(ExceptionStack $exceptionStack) {
                return new Aborted($this->exceptionStack->merge($exceptionStack));
            })
            ->extract();
    }
}
