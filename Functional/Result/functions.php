<?php

namespace Functional\Result;

function result(callable $f, ...$args): Result
{
    try {
        $value = $f(...$args);
        return new Success($value);
    } catch (\Throwable $e) {
        return new Failure(new ExceptionStack($e));
    }
}
