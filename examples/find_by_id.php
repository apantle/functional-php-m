<?php

use Functional\Maybe\Maybe;
use Functional\Maybe\None;
use Functional\Maybe\Some;
use function Functional\Maybe\{some,none};

final class Entity {
    public function __construct(string $id) {}
}

$repository = new class {
    public function findById(string $id): Maybe {
        if (/** not found */ false) {
            return none();
        }

        $entity = new \Entity($id);

        return some($entity);
    }
};

/** @var Maybe $maybeEntity */
$maybeEntity = $repository->findById('some_id');

// we can do this...
$maybeEntity->ifSome(function(Entity $entity) {
   // at this point we are sure we do have an Entity
});

// ..or this
switch (true) {
    case $maybeEntity instanceof Some:
        /** @var Entity $entity */
        $entity = $maybeEntity->extract();
        break;
    case $maybeEntity instanceof None:
        // not found
        break;
}
