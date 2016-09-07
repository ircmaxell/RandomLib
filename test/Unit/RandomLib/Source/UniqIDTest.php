<?php

namespace RandomLib\Source;

use SecurityLib\Strength;

class UniqIDTest extends AbstractSourceTest {

    protected static function getExpectedStrength() {
        return new Strength(Strength::LOW);
    }

}
