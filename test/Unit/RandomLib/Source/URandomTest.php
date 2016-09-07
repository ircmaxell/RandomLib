<?php

namespace RandomLib\Source;

use SecurityLib\Strength;

class URandomTest extends AbstractSourceTest {

    protected static function getExpectedStrength() {
        return new Strength(Strength::MEDIUM);
    }

}
