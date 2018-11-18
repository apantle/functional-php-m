<?php

namespace Functional\Either;

/**
 * @param string|array $reason
 * @return Either
 */
function no($reason): Either
{
    $reason = is_string($reason) ?
        new SingleReason($reason) :
        new MultipleReasons($reason);

    return new class($reason) implements No
    {
        /** @var Reason */
        private $reason;

        public function __construct(Reason $reason)
        {
            $this->reason = $reason;
        }

        function yes(callable $f): Either
        {
            return $this;
        }

        function no(callable $f): Either
        {
            return $f($this->reason) ?? $this;
        }

        function extract()
        {
            return $this->reason;
        }
    };
}

function yes($value = null): Either
{
    return new class($value) implements Yes
    {
        private $value;

        function __construct($value)
        {
            $this->value = $value;
        }

        function yes(callable $f): Either
        {
            return $f($this->value) ?? $this;
        }

        function no(callable $f): Either
        {
            return $this;
        }

        public function extract()
        {
            return $this->value;
        }
    };
}
