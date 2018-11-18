<?php

namespace Functional\Maybe;

function none(): Maybe
{
    return new class() implements None
    {
        function ifSome(callable $f): Maybe
        {
            return none();
        }
    };
}

function some($value): Maybe
{
    return new class($value) implements Some
    {
        private $value;

        function __construct($value)
        {
            $this->value = $value;
        }

        function extract()
        {
            return $this->value;
        }

        function ifSome(callable $f): Maybe
        {
            return $f($this->extract()) ?? $this;
        }
    };
}
