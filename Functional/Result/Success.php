<?php

namespace Functional\Result;

final class Success implements Result {
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function extract() { return $this->value; }

    public function ok(callable $f): Result {
        return result($f, $this->value);
    }

    function fail(callable $f): Result { return $this; }
}
