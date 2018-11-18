<?php

namespace Functional\Maybe;

interface Maybe {
    function ifSome(callable $f) : Maybe;
}
