<?php

namespace Functional\Result;

interface Result {
    public function extract();
    public function ok(callable $f): self;
    public function fail(callable $f): self;
}
