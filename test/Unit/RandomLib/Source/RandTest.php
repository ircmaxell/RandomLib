<?php

namespace RandomLib\Source;

use SecurityLib\Strength;

class RandTest extends AbstractSourceTest {

    protected static function getExpectedStrength() {
        if (defined('S_ALL')) {
            return new Strength(Strength::LOW);
        } else {
            return new Strength(Strength::VERYLOW);
        }
    }

}
